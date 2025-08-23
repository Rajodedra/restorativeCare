<?php
/* =============================================================================
   RestorativeCare — Secure Role-Aware Login (Single File)
============================================================================= */

// ============================ CONFIGURATION ==================================
const RC_DB_HOST = 'localhost';
const RC_DB_USER = 'root';
const RC_DB_PASS = '';
const RC_DB_NAME = 'restorativecare';
const RC_DB_PORT = 3307;

const RC_SESSION_NAME       = 'rcsid';
const RC_SESSION_IDLE_SECS  = 1800;
const RC_LOGIN_MAX_FAILS    = 5;
const RC_LOGIN_LOCK_WINDOW  = 60;

const RC_ADMIN_HOME    = 'admin.php';
const RC_DEFAULT_HOME  = 'dashboard.php';

const RC_DEV_SHOW_USERS     = true;

// ============================== BOOTSTRAP ====================================
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=()');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(RC_SESSION_NAME);
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ============================== HELPERS ======================================
function rc_is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }
function rc_ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
function rc_ua(): string { return substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255); }
function rc_is_local(): bool { $h=rc_ip(); return in_array($h,['127.0.0.1','::1']); }
function esc($s): string { return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

function rc_db(): mysqli {
    static $db = null;
    if ($db instanceof mysqli) { return $db; }
    $db = @mysqli_connect(RC_DB_HOST, RC_DB_USER, RC_DB_PASS, RC_DB_NAME, RC_DB_PORT);
    if (!$db) {
        http_response_code(500);
        die('Database connection failed.');
    }
    $db->set_charset('utf8mb4');
    return $db;
}

function rc_q(mysqli $db, string $sql, string $types = '', array $params = []): mysqli_stmt {
    $stmt = $db->prepare($sql);
    if (!$stmt) { throw new RuntimeException('Prepare failed: '.$db->error); }
    if ($types) { $stmt->bind_param($types, ...$params); }
    if (!$stmt->execute()) { throw new RuntimeException('Execute failed: '.$stmt->error); }
    return $stmt;
}

// Session hardening (idle timeout, IP/UA pinning)
function rc_session_touch(): void {
    $now = time();
    if (!isset($_SESSION['__init'])) {
        $_SESSION['__init'] = $now;
        $_SESSION['__ip'] = rc_ip();
        $_SESSION['__ua'] = rc_ua();
        $_SESSION['__last'] = $now;
    }
    if (($_SESSION['__ip'] ?? '') !== rc_ip()) { session_unset(); session_destroy(); die('Session IP changed.'); }
    if (($_SESSION['__ua'] ?? '') !== rc_ua()) { session_unset(); session_destroy(); die('Session UA changed.'); }
    if (($now - (int)($_SESSION['__last'] ?? 0)) > RC_SESSION_IDLE_SECS) {
        session_unset(); session_destroy(); header('Location: login.php?expired=1'); exit;
    }
    $_SESSION['__last'] = $now;
}
rc_session_touch();

// Login throttle
function rc_login_state(): array {
    $_SESSION['login_fail'] = (int)($_SESSION['login_fail'] ?? 0);
    $_SESSION['lock_until'] = (int)($_SESSION['lock_until'] ?? 0);
    $now = time();
    $locked = $now < $_SESSION['lock_until'];
    $msg = '';
    if ($locked) { $msg = 'Too many attempts. Try again in '.($_SESSION['lock_until'] - $now).'s.'; }
    return [$locked, $msg];
}
function rc_login_fail(): void {
    $_SESSION['login_fail'] = (int)$_SESSION['login_fail'] + 1;
    if ($_SESSION['login_fail'] >= RC_LOGIN_MAX_FAILS) {
        $_SESSION['lock_until'] = time() + RC_LOGIN_LOCK_WINDOW;
    }
}
function rc_login_reset(): void { $_SESSION['login_fail'] = 0; $_SESSION['lock_until'] = 0; }

function rc_redirect_for_role(string $role): void {
    $role = strtolower($role);
    if ($role === 'admin' || $role === 'superadmin' || $role === 'doctor') {
        header('Location: '.RC_ADMIN_HOME); exit;
    }
    header('Location: '.RC_DEFAULT_HOME); exit;
}

// ============================== AJAX ENDPOINTS ===============================
if (($_GET['ajax'] ?? '') === 'whois') {
    header('Content-Type: application/json');
    $email = trim((string)($_GET['email'] ?? ''));
    if ($email === '') { echo json_encode(['ok'=>false]); exit; }
    $db = rc_db();
    try {
        $stmt = rc_q($db, 'SELECT id, name, role FROM users WHERE email = ? LIMIT 1', 's', [$email]);
        $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
        if ($row) { echo json_encode(['ok'=>true,'role'=>$row['role'],'name'=>$row['name']]); }
        else { echo json_encode(['ok'=>true,'role'=>null]); }
    } catch (Throwable $e) {
        echo json_encode(['ok'=>false]);
    }
    exit;
}

if (($_GET['ajax'] ?? '') === 'health') {
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true,'time'=>time()]);
    exit;
}

// ============================== HANDLE LOGIN =================================
[$LOCKED, $LOCK_MSG] = rc_login_state();
$flashErr = '';
$flashOk  = '';

if (isset($_GET['expired'])) { $flashErr = 'Your session expired. Please sign in again.'; }

if (rc_is_post() && !$LOCKED) {
    try {
        $email = trim((string)($_POST['email'] ?? ''));
        $pass  = (string)($_POST['password'] ?? '');
        $selRole = trim((string)($_POST['role'] ?? ''));

        if ($email === '' || $pass === '') { throw new RuntimeException('Please enter email and password.'); }

        $db = rc_db();
        // Use the correct field name for password (pswd or password)
        $stmt = rc_q($db, 'SELECT id, name, role, pswd FROM users WHERE email = ? LIMIT 1', 's', [$email]);
        $res  = $stmt->get_result();
        $user = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        if (!$user) { throw new RuntimeException('No account found with that email.'); }

        if ($selRole !== '' && strcasecmp($selRole, (string)$user['role']) !== 0) {
            throw new RuntimeException('This account is registered as "'.esc($user['role']).'". Please select that role.');
        }

        $dbPass = (string)($user['pswd'] ?? '');
        $ok = false;

        // Simple password check (no hashing)
        if ($dbPass !== '' && $pass === $dbPass) {
            $ok = true;
        }

        // Debug: Uncomment to see what is being compared
        // error_log("DB password: '$dbPass', Input password: '$pass'");

        if (!$ok) { throw new RuntimeException('Incorrect password.'); }

        rc_login_reset();
        session_regenerate_id(true);

        $_SESSION['__ip'] = rc_ip();
        $_SESSION['__ua'] = rc_ua();
        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'name'  => (string)$user['name'],
            'role'  => (string)$user['role'],
            'since' => date('c'),
        ];

        // Redirect to index or dashboard (support ?next=dashboard.php for dynamic redirect)
        $next = $_GET['next'] ?? '';
        if ($next && preg_match('/^[a-z0-9_\-\.]+\.php$/i', $next)) {
            header('Location: ' . $next); exit;
        }
        rc_redirect_for_role((string)$user['role']);

    } catch (Throwable $e) {
        rc_login_fail();
        $flashErr = $e->getMessage();
    }
} elseif (rc_is_post() && $LOCKED) {
    $flashErr = $LOCK_MSG;
}

// ============================== VIEW (HTML) ==================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RestorativeCare • Secure Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <style>
    body{font-family: 'Inter', sans-serif; min-height:100vh; background: radial-gradient(1200px 600px at 10% 10%, #e6fbff 0%, #ffffff 35%, #f2faff 55%, #ffffff 80%) fixed; color:#0f172a;}
    .glass{background:rgba(255,255,255,.18); border:1px solid rgba(255,255,255,.28); backdrop-filter: blur(10px) saturate(120%); -webkit-backdrop-filter: blur(10px) saturate(120%);} 
    .card{border-radius:16px; box-shadow:0 12px 38px rgba(15,23,42,.10);} 
    .role-pill{transition:transform .25s ease, box-shadow .25s ease;} .role-pill.active{transform:translateY(-2px) scale(1.02); box-shadow:0 8px 20px rgba(6,182,212,.18);} 
    .btn{padding:.7rem 1rem; border-radius:.8rem;} 
    .floating-blob{position:absolute; filter: blur(50px); opacity:.35; z-index:0; animation: float 14s ease-in-out infinite;} 
    @keyframes float{0%,100%{transform: translateY(0) translateX(0) scale(1);} 50%{transform: translateY(-25px) translateX(10px) scale(1.05);} }
    .input{ background: rgba(255,255,255,.6);} .input:focus{ outline:2px solid rgba(6,182,212,.45); background: #fff;}
  </style>
</head>
<body class="relative">
  <div class="floating-blob w-72 h-72 rounded-full bg-cyan-300 top-[-60px] left-[-40px]"></div>
  <div class="floating-blob w-80 h-80 rounded-full bg-indigo-300 bottom-[-80px] right-[-40px]"></div>

  <header class="px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="text-2xl font-extrabold text-cyan-600">RestorativeCare</div>
      <div class="text-sm text-gray-500">— Secure Login</div>
    </div>
    <a class="text-sm text-cyan-700 hover:underline" href="<?= esc(RC_DEFAULT_HOME) ?>">Go to Site</a>
  </header>

  <main class="px-4 md:px-8 lg:px-16">
    <div class="grid grid-cols-12 gap-6 items-center">
      <!-- Left column: role info / capability preview -->
      <section class="col-span-12 md:col-span-6 lg:col-span-7 relative">
        <div class="glass card p-6 md:p-10" id="infoCard">
          <div class="mb-4">
            <div class="text-3xl md:text-4xl font-extrabold text-slate-800">Welcome back ✨</div>
            <div class="text-slate-600 mt-1">Role-aware access with instant UI previews.</div>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-5 gap-2 md:gap-3 mt-6">
            <?php
              $roles = [
                ['id'=>'superadmin','label'=>'SuperAdmin','emoji'=>'👑','desc'=>'Full control'],
                ['id'=>'admin','label'=>'Admin','emoji'=>'🛡️','desc'=>'Operations'],
                ['id'=>'doctor','label'=>'Doctor','emoji'=>'🩺','desc'=>'Care & orders'],
                ['id'=>'nurse','label'=>'Nurse','emoji'=>'👩‍⚕️','desc'=>'Vitals & meds'],
                ['id'=>'patient','label'=>'Patient','emoji'=>'🧑‍🦽','desc'=>'Personal care'],
              ];
              foreach ($roles as $i=>$r) {
                echo '<button type="button" data-role="'.$r['id'].'" class="role-pill glass card px-3 py-3 text-left hover:shadow-lg '.($i===4?'active':'').'">'
                    .'<div class="text-2xl">'.$r['emoji'].'</div>'
                    .'<div class="font-semibold">'.$r['label'].'</div>'
                    .'<div class="text-xs text-gray-600">'.$r['desc'].'</div>'
                    .'</button>';
              }
            ?>
          </div>
          <div class="mt-6 grid md:grid-cols-2 gap-4" id="capPreview">
            <div class="glass p-4 rounded-xl">
              <div class="text-sm text-gray-500">You will see</div>
              <ul class="mt-2 text-sm space-y-1" id="seeList"></ul>
            </div>
            <div class="glass p-4 rounded-xl">
              <div class="text-sm text-gray-500">You can do</div>
              <ul class="mt-2 text-sm space-y-1" id="doList"></ul>
            </div>
          </div>
          <?php if (RC_DEV_SHOW_USERS && rc_is_local()) :
            $peek = [];
            $db = rc_db();
            $rs = $db->query('SELECT id, name, email, role, pswd FROM users ORDER BY id ASC LIMIT 6');
            if ($rs) { while($r=$rs->fetch_assoc()){ $peek[]=$r; } }
          ?>
          <details class="mt-6">
            <summary class="cursor-pointer text-cyan-700">Dev helper: sample users (localhost)</summary>
            <div class="mt-3 overflow-x-auto">
              <table class="text-sm min-w-full">
                <thead><tr class="text-left border-b"><th class="p-2">ID</th><th class="p-2">Name</th><th class="p-2">Email</th><th class="p-2">Role</th><th class="p-2">Password</th></tr></thead>
                <tbody>
                <?php foreach ($peek as $u): ?>
                  <tr class="border-b hover:bg-white/40">
                    <td class="p-2"><?= (int)$u['id'] ?></td>
                    <td class="p-2"><?= esc($u['name']) ?></td>
                    <td class="p-2"><?= esc($u['email']) ?></td>
                    <td class="p-2"><?= esc($u['role']) ?></td>
                    <td class="p-2"><?= esc($u['pswd']) ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
              <div class="text-xs text-gray-500 mt-2">Default SuperAdmin from your SQL: admin@restorativecare.com / admin123</div>
            </div>
          </details>
          <?php endif; ?>
        </div>
      </section>

      <!-- Right column: login form -->
      <section class="col-span-12 md:col-span-6 lg:col-span-5">
        <div class="glass card p-6 md:p-8 animate__animated animate__fadeInUp" id="loginCard">
          <div class="flex items-center justify-between mb-4">
            <div>
              <div class="text-xl font-semibold">Sign in</div>
              <div class="text-sm text-gray-600">Secure role-based access</div>
            </div>
            <div class="text-3xl" id="roleEmoji">🧑‍🦽</div>
          </div>

          <?php if ($flashErr): ?>
            <div class="mb-3 p-3 bg-red-50 text-red-700 rounded-lg">⚠️ <?= esc($flashErr) ?></div>
          <?php endif; ?>
          <?php if ($LOCKED): ?>
            <div class="mb-3 p-3 bg-amber-50 text-amber-700 rounded-lg">⏳ <?= esc($LOCK_MSG) ?></div>
          <?php endif; ?>

          <form method="post" class="space-y-3">
            <label class="block">
              <span class="text-sm text-gray-600">Email</span>
              <input class="mt-1 w-full p-3 rounded-lg border input" type="email" name="email" id="email" placeholder="you@restorativecare.com" required <?= $LOCKED?'disabled':''; ?> />
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">Password</span>
              <div class="mt-1 relative">
                <input class="w-full p-3 rounded-lg border input pr-10" type="password" name="password" id="password" placeholder="••••••••" required <?= $LOCKED?'disabled':''; ?> />
                <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-slate-700" id="togglePass" tabindex="-1">👁️</button>
              </div>
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">Role</span>
              <select class="mt-1 w-full p-3 rounded-lg border input" name="role" id="role" <?= $LOCKED?'disabled':''; ?>>
                <option value="superadmin">SuperAdmin</option>
                <option value="admin">Admin</option>
                <option value="doctor">Doctor</option>
                <option value="nurse">Nurse</option>
                <option value="patient" selected>Patient</option>
              </select>
              <div class="text-xs text-gray-500 mt-1" id="roleHint">Tip: enter your email first — we’ll auto-detect your role if you’re registered.</div>
            </label>

            <button class="btn w-full bg-cyan-500 hover:bg-cyan-600 text-white transition <?= $LOCKED?'opacity-60 cursor-not-allowed':''; ?>" <?= $LOCKED?'disabled':''; ?>>Sign in</button>
          </form>
        </div>
      </section>
    </div>
  </main>

  <footer class="py-6 text-center text-xs text-gray-500">
    © <?= date('Y') ?> RestorativeCare • Secure • Role-aware
  </footer>
</body>
</html>