<?php
/* =============================================================================
   RestorativeCare — Mental Health (Single File: UI + Logic + DB)
   -----------------------------------------------------------------------------
   What this file does:
   - Uses your SESSION format: $_SESSION['user'] = ['id','name','role']
   - Uses mysqli; reuses $conn or $mysqli if provided by your app
   - Patients auto-load their own data; staff can select a patient or pass ?patient_id=#
   - Mood logging with 5-min rate limit, notifications to admin/superadmin on negative moods
   - Trend & distribution charts (Chart.js)
   - Appointments list (upcoming)
   - PHQ-9 with auto-scoring; stores to mh_assessments/mh_assessment_items if present
   - Safety Plan (versioned by insert) if safety_plans table exists
   - CSV export, date filters, quick patient search
   - Minimal neutral UI markup so your existing theme styles it; no new CSS imported
   - JSON endpoints for mini AJAX use (kept in the same file using ?ajax=... to avoid extra files)

   NOTE:
   - If your app has header/sidebar/footer includes, uncomment the include lines where indicated.
   - No schema changes required to run. Optional tables are detected automatically.
   ========================================================================== */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ------------------------------ Security headers -----------------------------
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ------------------------------ Helpers --------------------------------------
function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function is_post(){ return (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'); }
function method(){ return $_SERVER['REQUEST_METHOD'] ?? 'GET'; }
function now(){ return date('Y-m-d H:i:s'); }
function csrf_token(){ if(empty($_SESSION['csrf_mh'])) $_SESSION['csrf_mh']=bin2hex(random_bytes(32)); return $_SESSION['csrf_mh']; }
function csrf_check($t){ return isset($_SESSION['csrf_mh']) && hash_equals($_SESSION['csrf_mh'], (string)$t); }
function url_base(){
  $uri = strtok($_SERVER['REQUEST_URI'],'?');
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https':'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  return $scheme.'://'.$host.$uri;
}
function flash_set($k,$v){ $_SESSION['__flash'][$k]=(string)$v; }
function flash_get($k){ if(!empty($_SESSION['__flash'][$k])){ $v=$_SESSION['__flash'][$k]; unset($_SESSION['__flash'][$k]); return $v; } return ''; }

// ------------------------------ Auth check -----------------------------------
if (empty($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
  header("Location: login.php");
  exit;
}
$AUTH_ID   = (int)$_SESSION['user']['id'];
$AUTH_NAME = (string)$_SESSION['user']['name'];
$AUTH_ROLE = (string)$_SESSION['user']['role'];

// ------------------------------ DB connection --------------------------------
// Reuse existing mysqli ($conn) if your app already created it.
$mysqli = null;
if (isset($conn) && $conn instanceof mysqli) { $mysqli = $conn; }
elseif (isset($mysqli) && $mysqli instanceof mysqli) { /* already set */ }
else {
  // Fallback to the same defaults you used in login.php
  $DB_HOST='localhost'; $DB_USER='root'; $DB_PASS=''; $DB_NAME='restorativecare'; $DB_PORT=3307;
  $mysqli = @mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
  if(!$mysqli){ http_response_code(500); die('DB connection failed.'); }
  $mysqli->set_charset('utf8mb4');
}
function q($mysqli,$sql,$types='',$params=[]){
  $stmt=$mysqli->prepare($sql);
  if(!$stmt){ throw new Exception('Prepare failed: '.$mysqli->error); }
  if($types){ $stmt->bind_param($types, ...$params); }
  if(!$stmt->execute()){ $e=$stmt->error; $stmt->close(); throw new Exception('Execute failed: '.$e); }
  return $stmt;
}
function has_table($mysqli, $tbl){
  try{
    $stmt = q($mysqli, "SHOW TABLES LIKE ?", "s", [$tbl]);
    $res = $stmt->get_result(); $ok = (bool)$res->fetch_row(); $stmt->close();
    return $ok;
  } catch(Throwable $e){ return false; }
}
function patient_id_for_user($mysqli, $user_id){
  $stmt = q($mysqli, "SELECT id FROM patients WHERE user_id=? LIMIT 1", "i", [$user_id]);
  $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
  return $row ? (int)$row['id'] : 0;
}
function get_patient_brief($mysqli, $patient_id){
  $stmt = q($mysqli, "SELECT p.id, u.name, u.email, u.phone, p.dob, p.gender, p.address
                      FROM patients p JOIN users u ON u.id=p.user_id
                      WHERE p.id=? LIMIT 1","i",[$patient_id]);
  $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
  return $row ?: null;
}
function is_staff($role){ return in_array($role, ['doctor','nurse','admin','superadmin'], true); }

// ------------------------------ Resolve patient ------------------------------
$patient_id = 0;
if ($AUTH_ROLE === 'patient') {
  $patient_id = patient_id_for_user($mysqli, $AUTH_ID);
  if(!$patient_id){ die("No patient record found for your account."); }
} else {
  $patient_id = isset($_GET['patient_id']) ? max(0, (int)$_GET['patient_id']) : 0;
}

// ------------------------------ Feature flags --------------------------------
$HAS_ASSESS  = has_table($mysqli, 'mh_assessments') && has_table($mysqli, 'mh_assessment_items');
$HAS_SAFETY  = has_table($mysqli, 'safety_plans');
$HAS_AUDIT   = has_table($mysqli, 'audit_logs');          // optional if you added it
$HAS_CONSENT = has_table($mysqli, 'mh_consents');         // optional if you added it

// ------------------------------ Audit (optional) -----------------------------
function audit_log($mysqli, $who, $action, $object_type, $object_id, $extra=''){
  global $HAS_AUDIT;
  if(!$HAS_AUDIT) return;
  try{
    q($mysqli, "INSERT INTO audit_logs (user_id, action, object_type, object_id, meta, created_at) VALUES (?,?,?,?,?,NOW())",
      "issis", [(int)$who, (string)$action, (string)$object_type, (int)$object_id, (string)$extra])->close();
  }catch(Throwable $e){ /* ignore */ }
}

// ------------------------------ Consent check (optional) ---------------------
function consent_ok($mysqli, $patient_id, $scope='general'){
  global $HAS_CONSENT;
  if(!$HAS_CONSENT) return true; // if not present, allow
  try{
    $stmt = q($mysqli, "SELECT granted FROM mh_consents WHERE patient_id=? AND scope=? ORDER BY granted_at DESC LIMIT 1","is",[$patient_id,$scope]);
    $res = $stmt->get_result(); $row=$res->fetch_assoc(); $stmt->close();
    if(!$row) return true; // default allow if nothing set
    return (bool)$row['granted'];
  }catch(Throwable $e){ return true; }
}

// ------------------------------ JSON/AJAX endpoints --------------------------
if (isset($_GET['ajax'])) {
  header('Content-Type: application/json');
  $ajax = (string)$_GET['ajax'];

  // only allow staff to query arbitrary patients
  $pid = ($AUTH_ROLE==='patient') ? patient_id_for_user($mysqli,$AUTH_ID) : (int)($_GET['patient_id'] ?? 0);
  if ($pid <= 0 && $AUTH_ROLE==='patient') { echo json_encode(['ok'=>false,'error'=>'No patient record']); exit; }

  // date filters
  $from = $_GET['from'] ?? '';
  $to   = $_GET['to'] ?? '';
  $rangeSql = " AND logged_at >= (NOW() - INTERVAL 60 DAY) ";
  $types = "i";
  $params = [$pid];

  if ($from !== '' && $to !== '') {
    $rangeSql = " AND logged_at BETWEEN ? AND ? ";
    $types = "iss";
    $params = [$pid, $from, $to];
  }

  // mood logs list
  if ($ajax === 'mood_logs') {
    if(!$pid){ echo json_encode(['ok'=>false,'error'=>'patient_id required']); exit; }
    // RBAC: patients can only see self
    if($AUTH_ROLE==='patient' && $pid !== patient_id_for_user($mysqli,$AUTH_ID)){
      echo json_encode(['ok'=>false,'error'=>'forbidden']); exit;
    }
    $stmt = q($mysqli, "SELECT id, mood, note, DATE_FORMAT(logged_at, '%Y-%m-%d %H:%i:%s') as logged_at
                        FROM mood_logs
                        WHERE patient_id=? {$rangeSql}
                        ORDER BY logged_at ASC", $types, $params);
    $res = $stmt->get_result(); $rows=[]; while($r=$res->fetch_assoc()){ $rows[]=$r; }
    $stmt->close();
    echo json_encode(['ok'=>true,'rows'=>$rows]); exit;
  }

  // mood distribution aggregate
  if ($ajax === 'mood_agg') {
    if(!$pid){ echo json_encode(['ok'=>false,'error'=>'patient_id required']); exit; }
    if($AUTH_ROLE==='patient' && $pid !== patient_id_for_user($mysqli,$AUTH_ID)){
      echo json_encode(['ok'=>false,'error'=>'forbidden']); exit;
    }
    $stmt = q($mysqli, "SELECT mood, COUNT(*) c FROM mood_logs
                        WHERE patient_id=? {$rangeSql}
                        GROUP BY mood", $types, $params);
    $res = $stmt->get_result(); $agg=[]; while($r=$res->fetch_assoc()){ $agg[$r['mood']]=(int)$r['c']; }
    $stmt->close();
    echo json_encode(['ok'=>true,'agg'=>$agg]); exit;
  }

  // patient search (staff only)
  if ($ajax === 'patient_search') {
    if(!is_staff($AUTH_ROLE)){ echo json_encode(['ok'=>false,'error'=>'forbidden']); exit; }
    $qStr = trim((string)($_GET['q'] ?? ''));
    if($qStr===''){ echo json_encode(['ok'=>true,'rows'=>[]]); exit; }
    $like = '%'.$qStr.'%';
    $stmt = q($mysqli, "SELECT p.id, u.name, u.email
                        FROM patients p JOIN users u ON u.id=p.user_id
                        WHERE u.name LIKE ? OR u.email LIKE ?
                        ORDER BY u.name ASC LIMIT 15","ss",[$like,$like]);
    $res = $stmt->get_result(); $rows=[]; while($r=$res->fetch_assoc()){ $rows[]=$r; }
    $stmt->close();
    echo json_encode(['ok'=>true,'rows'=>$rows]); exit;
  }

  echo json_encode(['ok'=>false,'error'=>'unknown']); exit;
}

// ------------------------------ Rate limit helper ----------------------------
function recent_mood_count($mysqli, $pid){
  $stmt = q($mysqli, "SELECT COUNT(*) c FROM mood_logs WHERE patient_id=? AND logged_at >= (NOW() - INTERVAL 5 MINUTE)", "i", [$pid]);
  $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
  return (int)($row['c'] ?? 0);
}

// ------------------------------ Notifications --------------------------------
function notify_admins($mysqli, $message, $urgency='high'){
  $stmt = q($mysqli, "SELECT id FROM users WHERE role IN ('admin','superadmin')");
  $res  = $stmt->get_result();
  while($u = $res->fetch_assoc()){
    q($mysqli, "INSERT INTO notifications (user_id, message, urgency) VALUES (?,?,?)", "iss",
      [(int)$u['id'], (string)$message, (string)$urgency])->close();
  }
  $stmt->close();
}
function notify_admins_negative_mood($mysqli, $mood, $pid){
  if (!in_array($mood, ['sad','anxious','angry'], true)) return;
  $msg = "New {$mood} mood log for patient #{$pid}";
  notify_admins($mysqli, $msg, 'high');
}

// ------------------------------ POST handlers --------------------------------
try {
  if (is_post()) {
    if (!csrf_check($_POST['_csrf'] ?? '')) { throw new Exception('Invalid CSRF token'); }
    $action = (string)($_POST['action'] ?? '');

    // Patient context for posts
    $pid = ($AUTH_ROLE === 'patient') ? patient_id_for_user($mysqli, $AUTH_ID)
                                      : max(0, (int)($_POST['patient_id'] ?? $patient_id));

    if ($action === 'mood_create') {
      if ($pid <= 0) throw new Exception('Select a patient first.');
      $allowed = ['happy','neutral','sad','anxious','angry'];
      $mood = in_array($_POST['mood'] ?? '', $allowed, true) ? $_POST['mood'] : 'neutral';
      $note = trim((string)($_POST['note'] ?? ''));
      // rate limit
      if (recent_mood_count($mysqli, $pid) > 0) {
        flash_set('mh_ok','You logged a mood recently. Please try again in a few minutes.');
      } else {
        q($mysqli, "INSERT INTO mood_logs (patient_id, mood, note, logged_at) VALUES (?,?,?,NOW())", "iss", [$pid,$mood,$note])->close();
        notify_admins_negative_mood($mysqli, $mood, $pid);
        audit_log($mysqli, $AUTH_ID, 'create', 'mood_log', $pid, $mood);
        flash_set('mh_ok','Mood saved.');
      }
      header("Location: ".url_base().($pid?("?patient_id=".$pid):"")); exit;
    }

    if ($action === 'phq9_save') {
      if ($pid <= 0) throw new Exception('Select a patient first.');
      $items = [];
      for($i=1;$i<=9;$i++){
        $v = (int)($_POST["phq9_$i"] ?? 0);
        if ($v < 0 || $v > 3) $v = 0;
        $items[$i] = $v;
      }
      $total = array_sum($items);

      if ($HAS_ASSESS) {
        $stmt = q($mysqli, "INSERT INTO mh_assessments (patient_id, type, total_score, taken_at, created_by) VALUES (?,?,?,?,?)",
          "isisi", [$pid, 'PHQ-9', $total, now(), $AUTH_ID]);
        $aid = $mysqli->insert_id; $stmt->close();

        $ins = $mysqli->prepare("INSERT INTO mh_assessment_items (assessment_id, item_no, score) VALUES (?,?,?)");
        if($ins){ for($i=1;$i<=9;$i++){ $ins->bind_param("iii", $aid, $i, $items[$i]); $ins->execute(); } $ins->close(); }

        if ($total >= 20 || $items[9] > 0) {
          notify_admins($mysqli, "PHQ-9 alert (score {$total}) for patient #{$pid}", 'high');
        }
        audit_log($mysqli, $AUTH_ID, 'create', 'assessment', $pid, "PHQ-9:{$total}");
        flash_set('mh_ok', "PHQ-9 saved (Total: {$total}).");
      } else {
        flash_set('mh_ok',"PHQ-9 tables not installed — data not persisted.");
      }
      header("Location: ".url_base().($pid?("?patient_id=".$pid):"")); exit;
    }

    if ($action === 'safety_save') {
      if ($pid <= 0) throw new Exception('Select a patient first.');
      $warning = trim((string)($_POST['warning_signs'] ?? ''));
      $coping  = trim((string)($_POST['coping_strategies'] ?? ''));
      $contacts= trim((string)($_POST['contacts'] ?? ''));
      if ($HAS_SAFETY) {
        q($mysqli, "INSERT INTO safety_plans (patient_id, warning_signs, coping_strategies, contacts, created_by) VALUES (?,?,?,?,?)",
          "isssi", [$pid, $warning, $coping, $contacts, $AUTH_ID])->close();
        audit_log($mysqli, $AUTH_ID, 'create', 'safety_plan', $pid);
        flash_set('mh_ok',"Safety plan saved.");
      } else {
        flash_set('mh_ok',"Safety plan table not installed — data not persisted.");
      }
      header("Location: ".url_base().($pid?("?patient_id=".$pid):"")); exit;
    }

    if ($action === 'export_csv') {
      if ($pid <= 0) throw new Exception('Select a patient first.');
      $from = $_POST['from'] ?? ''; $to = $_POST['to'] ?? '';
      $rangeSql = " AND logged_at >= (NOW() - INTERVAL 60 DAY) ";
      $types = "i"; $params = [$pid];
      if ($from !== '' && $to !== '') { $rangeSql = " AND logged_at BETWEEN ? AND ? "; $types="iss"; $params=[$pid,$from,$to]; }

      $stmt = q($mysqli, "SELECT id, mood, note, logged_at FROM mood_logs WHERE patient_id=? {$rangeSql} ORDER BY logged_at ASC", $types, $params);
      $res = $stmt->get_result();

      header('Content-Type: text/csv');
      header('Content-Disposition: attachment; filename="mood_logs_'.$pid.'.csv"');
      $out = fopen('php://output','w');
      fputcsv($out, ['id','mood','note','logged_at']);
      while($r = $res->fetch_assoc()){ fputcsv($out, $r); }
      fclose($out);
      $stmt->close();
      exit;
    }
  }
} catch(Throwable $e){
  flash_set('mh_err', $e->getMessage());
  header("Location: ".url_base().($patient_id?("?patient_id=".$patient_id):"")); exit;
}

// ------------------------------ Fetch page data ------------------------------
$patient_choices = [];
if ($AUTH_ROLE !== 'patient' && $patient_id === 0) {
  $rs = $mysqli->query("SELECT p.id, u.name, u.email FROM patients p JOIN users u ON u.id=p.user_id ORDER BY u.name ASC");
  if($rs){ while($r=$rs->fetch_assoc()){ $patient_choices[]=$r; } $rs->close(); }
}
$patient_row = null;
if ($patient_id > 0) { $patient_row = get_patient_brief($mysqli, $patient_id); }

// date filter
$from = $_GET['from'] ?? ''; $to = $_GET['to'] ?? '';
$rangeSql = " AND logged_at >= (NOW() - INTERVAL 60 DAY) ";
$types = "i"; $params = [$patient_id];
if ($patient_id > 0 && $from !== '' && $to !== '') {
  $rangeSql = " AND logged_at BETWEEN ? AND ? "; $types="iss"; $params=[$patient_id,$from,$to];
}

// Mood logs (for table preview; charts fetched via inline JSON as well)
$mood_logs = [];
$agg = ['happy'=>0,'neutral'=>0,'sad'=>0,'anxious'=>0,'angry'=>0];
if ($patient_id > 0) {
  $stmt = q($mysqli, "SELECT mood, note, DATE_FORMAT(logged_at, '%Y-%m-%d %H:%i') ts
                       FROM mood_logs
                       WHERE patient_id=? {$rangeSql}
                       ORDER BY logged_at ASC", $types, $params);
  $res = $stmt->get_result();
  while($row = $res->fetch_assoc()){
    $mood_logs[] = $row;
    if(isset($agg[$row['mood']])) $agg[$row['mood']]++;
  }
  $stmt->close();
}

// Upcoming appointments
$appts = [];
if ($patient_id > 0) {
  $stmt = q($mysqli, "SELECT a.id, a.scheduled_at, a.status, u.name AS doctor_name
                      FROM appointments a JOIN users u ON a.doctor_id=u.id
                      WHERE a.patient_id=? AND a.scheduled_at >= NOW()
                      ORDER BY a.scheduled_at ASC LIMIT 10","i",[$patient_id]);
  $res = $stmt->get_result();
  while($row = $res->fetch_assoc()){ $appts[]=$row; }
  $stmt->close();
}

// Last PHQ-9
$last_phq9 = null;
if ($patient_id > 0 && $HAS_ASSESS) {
  $stmt = q($mysqli, "SELECT total_score, taken_at FROM mh_assessments WHERE patient_id=? AND type='PHQ-9' ORDER BY taken_at DESC LIMIT 1","i",[$patient_id]);
  $res = $stmt->get_result(); $last_phq9 = $res->fetch_assoc(); $stmt->close();
}

// Last Safety Plan
$last_safety = null;
if ($patient_id > 0 && $HAS_SAFETY) {
  $stmt = q($mysqli, "SELECT warning_signs, coping_strategies, contacts, created_at FROM safety_plans WHERE patient_id=? ORDER BY created_at DESC LIMIT 1","i",[$patient_id]);
  $res = $stmt->get_result(); $last_safety = $res->fetch_assoc(); $stmt->close();
}

// ------------------------------ UI starts ------------------------------------
/* If you have shared header/sidebar, uncomment and adjust:
include 'header.php';  // your site header
include 'sidebar.php'; // your site sidebar/nav
*/
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Mental Health</title>
  <!-- No CSS injected here; this will inherit your app’s theme and layout. -->
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Minimal fallbacks only (you can remove if your theme provides these) */
    .container{max-width:1200px;margin:0 auto;padding:16px;}
    .row{display:flex;flex-wrap:wrap;gap:16px;}
    .col{flex:1 1 0;}
    .col-4{flex:0 0 calc(33.333% - 16px);}
    .col-6{flex:0 0 calc(50% - 16px);}
    .col-8{flex:0 0 calc(66.666% - 16px);}
    .card{border:1px solid rgba(0,0,0,.08);border-radius:12px;padding:16px;background:#fff;}
    .muted{color:#6b7280;font-size:.92em;}
    .hstack{display:flex;align-items:center;gap:8px;}
    .stack{display:flex;flex-direction:column;gap:8px;}
    .divider{height:1px;background:rgba(0,0,0,.08);margin:8px 0;}
    .btn{display:inline-block;padding:8px 12px;border-radius:8px;border:1px solid rgba(0,0,0,.1);background:#f9fafb;cursor:pointer;}
    .btn.primary{background:#06b6d4;color:white;border-color:#06b6d4;}
    .btn.danger{background:#ef4444;color:white;border-color:#ef4444;}
    .input, select, textarea{width:100%;padding:10px;border-radius:8px;border:1px solid rgba(0,0,0,.15);}
    .table{width:100%;border-collapse:collapse;}
    .table th,.table td{padding:8px;border-bottom:1px solid rgba(0,0,0,.06);text-align:left;}
    .badge{display:inline-block;padding:2px 8px;border-radius:12px;background:#eef2ff;font-size:.85em;}
    .alert{border-radius:8px;padding:10px 12px;margin:8px 0;}
    .alert-success{background:#ecfeff;color:#155e75;border:1px solid #a5f3fc;}
    .alert-danger{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;}
    .toolbar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;justify-content:space-between;margin-bottom:12px;}
    .pill{display:inline-block;padding:4px 10px;border:1px solid rgba(0,0,0,.1);border-radius:999px;background:#fff;cursor:pointer;}
    .pill.active{background:#06b6d4;color:#fff;border-color:#06b6d4;}
    details > summary{cursor:pointer;user-select:none;}
    .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}
    .grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;}
    @media (max-width: 900px){
      .col-6,.col-8,.col-4{flex: 1 1 100%;}
      .grid-3{grid-template-columns: 1fr;}
      .grid-2{grid-template-columns: 1fr;}
    }
  </style>
</head>
<body>

<div class="container">
  <!-- Page Title / Breadcrumb (inherits your style if present) -->
  <div class="hstack" style="justify-content:space-between;margin-bottom:8px;">
    <div class="hstack" style="gap:12px;">
      <h2 style="margin:0;">Mental Health</h2>
      <?php if($patient_row): ?>
        <span class="badge">Patient #<?php echo (int)$patient_id; ?></span>
        <span class="muted"><?php echo esc($patient_row['name']); ?></span>
      <?php endif; ?>
    </div>
    <div class="hstack muted">
      Logged in as <strong>&nbsp;<?php echo esc($AUTH_NAME); ?></strong> (<?php echo esc($AUTH_ROLE); ?>)
    </div>
  </div>

  <!-- Flash messages -->
  <?php if($e = flash_get('mh_err')): ?>
    <div class="alert alert-danger">⚠️ <?php echo esc($e); ?></div>
  <?php endif; ?>
  <?php if($ok = flash_get('mh_ok')): ?>
    <div class="alert alert-success">✅ <?php echo esc($ok); ?></div>
  <?php endif; ?>

  <!-- Staff patient picker (if no patient selected) -->
  <?php if ($AUTH_ROLE !== 'patient' && $patient_id === 0): ?>
    <div class="card stack">
      <h3 style="margin:0;">Select a Patient</h3>
      <form method="get" class="grid-2">
        <div>
          <label class="muted">Choose from list</label>
          <select class="input" name="patient_id" required>
            <option value="">-- choose --</option>
            <?php foreach($patient_choices as $p): ?>
              <option value="<?php echo (int)$p['id']; ?>">
                <?php echo esc($p['name']).' ('.esc($p['email']).')'; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="stack">
          <label class="muted">Or search</label>
          <div class="hstack">
            <input class="input" type="text" id="patientSearch" placeholder="Type name/email..."/>
            <button type="button" class="btn" id="btnSearch">Search</button>
          </div>
          <div id="searchResults" class="muted"></div>
        </div>
        <div>
          <button type="submit" class="btn primary">Open Patient</button>
        </div>
      </form>
      <script>
        (function(){
          const i = document.getElementById('patientSearch');
          const b = document.getElementById('btnSearch');
          const box = document.getElementById('searchResults');
          function r(html){ box.innerHTML = html; }
          async function go(){
            const q = i.value.trim();
            if(!q){ r('<div class="muted">Type to search…</div>'); return; }
            const url = new URL(window.location.href);
            url.searchParams.set('ajax','patient_search');
            url.searchParams.set('q', q);
            const resp = await fetch(url.toString(), {headers:{'Accept':'application/json'}});
            const data = await resp.json();
            if(!data.ok){ r('<div class="muted">No results.</div>'); return; }
            if(!data.rows.length){ r('<div class="muted">No matches.</div>'); return; }
            let html = '<ul>';
            for(const p of data.rows){
              html += `<li><a class="pill" href="?patient_id=${p.id}">${p.name} (${p.email})</a></li>`;
            }
            html += '</ul>';
            r(html);
          }
          b.addEventListener('click', go);
          i.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); go(); } });
        })();
      </script>
    </div>
    <?php /* Early return visual; rest needs a patient context */ ?>
  <?php else: ?>

  <!-- Patient header card -->
  <?php if($patient_row): ?>
    <div class="card">
      <div class="grid-3">
        <div>
          <div class="muted">Name</div>
          <div><strong><?php echo esc($patient_row['name']); ?></strong></div>
        </div>
        <div>
          <div class="muted">Contact</div>
          <div><?php echo esc($patient_row['email']); ?> · <?php echo esc($patient_row['phone']); ?></div>
        </div>
        <div>
          <div class="muted">Demographics</div>
          <div>DOB: <?php echo esc($patient_row['dob']); ?> · Gender: <?php echo esc($patient_row['gender']); ?></div>
        </div>
      </div>
      <?php if(!empty($patient_row['address'])): ?>
        <div class="divider"></div>
        <div class="muted">Address</div>
        <div><?php echo esc($patient_row['address']); ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Toolbar: filters + export -->
  <div class="toolbar">
    <form method="get" class="hstack" style="gap:8px;">
      <?php if($AUTH_ROLE!=='patient'): ?>
        <input type="hidden" name="patient_id" value="<?php echo (int)$patient_id; ?>"/>
      <?php endif; ?>
      <div class="hstack" style="gap:4px;">
        <span class="muted">From</span>
        <input class="input" type="datetime-local" name="from" value="<?php echo esc($from); ?>"/>
      </div>
      <div class="hstack" style="gap:4px;">
        <span class="muted">To</span>
        <input class="input" type="datetime-local" name="to" value="<?php echo esc($to); ?>"/>
      </div>
      <button class="btn">Apply</button>
      <a class="btn" href="<?php echo esc(url_base().($AUTH_ROLE!=='patient'?'?patient_id='.$patient_id:'')); ?>">Reset</a>
    </form>

    <form method="post" class="hstack" style="gap:8px;">
      <input type="hidden" name="_csrf" value="<?php echo esc(csrf_token()); ?>"/>
      <input type="hidden" name="action" value="export_csv"/>
      <?php if($AUTH_ROLE!=='patient'): ?>
        <input type="hidden" name="patient_id" value="<?php echo (int)$patient_id; ?>"/>
      <?php endif; ?>
      <input class="input" type="hidden" name="from" value="<?php echo esc($from); ?>"/>
      <input class="input" type="hidden" name="to" value="<?php echo esc($to); ?>"/>
      <button class="btn">Export CSV</button>
    </form>
  </div>

  <!-- Main grid -->
  <div class="row">

    <!-- Left column -->
    <div class="col col-6">
      <!-- Mood Logger -->
      <div class="card stack">
        <div class="hstack" style="justify-content:space-between;">
          <h3 style="margin:0;">Log Mood</h3>
          <div class="muted">Rate limit: 1 entry / 5 min</div>
        </div>
        <form method="post" class="grid-2" style="align-items:flex-end;">
          <input type="hidden" name="_csrf" value="<?php echo esc(csrf_token()); ?>"/>
          <input type="hidden" name="action" value="mood_create"/>
          <?php if($AUTH_ROLE!=='patient'): ?>
            <input type="hidden" name="patient_id" value="<?php echo (int)$patient_id; ?>"/>
          <?php endif; ?>
          <div>
            <label class="muted">Mood</label>
            <select class="input" name="mood" required>
              <option value="happy">😊 Happy</option>
              <option value="neutral">😐 Neutral</option>
              <option value="sad">😔 Sad</option>
              <option value="anxious">😟 Anxious</option>
              <option value="angry">😠 Angry</option>
            </select>
          </div>
          <div>
            <label class="muted">Note (optional)</label>
            <input class="input" type="text" name="note" placeholder="Add a short note for the care team"/>
          </div>
          <div>
            <button class="btn primary" type="submit">Save</button>
          </div>
        </form>
      </div>

      <!-- Mood Distribution -->
      <div class="card">
        <div class="hstack" style="justify-content:space-between;">
          <h3 style="margin:0;">Mood Distribution</h3>
          <span class="muted">last 60 days (or filter)</span>
        </div>
        <div style="height:260px;">
          <canvas id="moodPie"></canvas>
        </div>
      </div>

      <!-- Appointments -->
      <div class="card">
        <h3 style="margin-top:0;">Upcoming Appointments</h3>
        <?php if($appts): ?>
          <table class="table">
            <thead><tr><th>When</th><th>With</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach($appts as $a): ?>
                <tr>
                  <td><?php echo esc(date('M d, Y H:i', strtotime($a['scheduled_at']))); ?></td>
                  <td>Dr. <?php echo esc($a['doctor_name']); ?></td>
                  <td><span class="badge"><?php echo esc($a['status']); ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="muted">No upcoming appointments.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right column -->
    <div class="col col-6">

      <!-- Mood Trend -->
      <div class="card">
        <div class="hstack" style="justify-content:space-between;">
          <h3 style="margin:0;">Mood Trend</h3>
          <span class="muted">Score: 😊=+2 · 😐=+1 · 😔/😟=-1 · 😠=-2</span>
        </div>
        <div style="height:260px;">
          <canvas id="moodLine"></canvas>
        </div>
        <details style="margin-top:10px;">
          <summary class="muted">Show recent entries</summary>
          <div class="table-responsive" style="margin-top:.5rem;">
            <table class="table">
              <thead><tr><th>When</th><th>Mood</th><th>Note</th></tr></thead>
              <tbody>
              <?php if($mood_logs): foreach(array_reverse($mood_logs) as $r): ?>
                <tr>
                  <td class="muted"><?php echo esc($r['ts']); ?></td>
                  <td><?php echo esc(ucfirst($r['mood'])); ?></td>
                  <td><?php echo esc($r['note']); ?></td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="3" class="muted">No mood entries yet.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </details>
      </div>

      <!-- PHQ-9 -->
      <div class="card">
        <div class="hstack" style="justify-content:space-between;">
          <h3 style="margin:0;">PHQ-9 Assessment</h3>
          <?php if($last_phq9): ?>
            <span class="muted">Last: <strong><?php echo (int)$last_phq9['total_score']; ?></strong> · <?php echo esc(date('M d, Y H:i', strtotime($last_phq9['taken_at']))); ?></span>
          <?php else: ?>
            <span class="muted">No previous scores</span>
          <?php endif; ?>
        </div>
        <form method="post" class="stack" style="margin-top:8px;">
          <input type="hidden" name="_csrf" value="<?php echo esc(csrf_token()); ?>"/>
          <input type="hidden" name="action" value="phq9_save"/>
          <?php if($AUTH_ROLE!=='patient'): ?>
            <input type="hidden" name="patient_id" value="<?php echo (int)$patient_id; ?>"/>
          <?php endif; ?>
          <div class="muted">Over the last 2 weeks, how often… (0=Not at all, 1=Several days, 2=More than half the days, 3=Nearly every day)</div>
          <?php
            $phq = [
              1=>"Little interest or pleasure in doing things",
              2=>"Feeling down, depressed, or hopeless",
              3=>"Trouble falling or staying asleep, or sleeping too much",
              4=>"Feeling tired or having little energy",
              5=>"Poor appetite or overeating",
              6=>"Feeling bad about yourself — or that you are a failure or have let yourself or your family down",
              7=>"Trouble concentrating on things, such as reading the newspaper or watching television",
              8=>"Moving or speaking so slowly that other people could have noticed? Or the opposite — being so fidgety or restless that you have been moving around a lot more than usual",
              9=>"Thoughts that you would be better off dead, or thoughts of hurting yourself",
            ];
            for($i=1;$i<=9;$i++):
          ?>
          <div class="grid-2">
            <label><strong><?php echo $i; ?>.</strong> <?php echo esc($phq[$i]); ?></label>
            <select name="phq9_<?php echo $i; ?>" class="input">
              <option value="0">0</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
            </select>
          </div>
          <?php endfor; ?>
          <div class="hstack" style="justify-content:flex-end;">
            <button class="btn primary">Save PHQ-9</button>
          </div>
          <?php if(!$HAS_ASSESS): ?>
            <div class="muted">Storage tables for PHQ-9 are not installed. Run your MH migration to persist assessments.</div>
          <?php endif; ?>
          <div class="muted"><strong>Clinical note:</strong> If item 9 &gt; 0, follow crisis protocol immediately.</div>
        </form>
      </div>

      <!-- Safety Plan -->
      <div class="card">
        <div class="hstack" style="justify-content:space-between;">
          <h3 style="margin:0;">Safety Plan</h3>
          <?php if($last_safety): ?>
            <span class="muted">Last updated <?php echo esc(date('M d, Y H:i', strtotime($last_safety['created_at']))); ?></span>
          <?php else: ?>
            <span class="muted">No plan on file</span>
          <?php endif; ?>
        </div>

        <?php if($last_safety): ?>
          <details open>
            <summary class="muted">Show current plan</summary>
            <div class="stack" style="margin-top:8px;">
              <div>
                <div class="muted">Warning signs</div>
                <div><?php echo nl2br(esc($last_safety['warning_signs'])); ?></div>
              </div>
              <div>
                <div class="muted">Coping strategies</div>
                <div><?php echo nl2br(esc($last_safety['coping_strategies'])); ?></div>
              </div>
              <div>
                <div class="muted">Contacts</div>
                <div><?php echo nl2br(esc($last_safety['contacts'])); ?></div>
              </div>
            </div>
          </details>
          <div class="divider"></div>
        <?php endif; ?>

        <form method="post" class="stack">
          <input type="hidden" name="_csrf" value="<?php echo esc(csrf_token()); ?>"/>
          <input type="hidden" name="action" value="safety_save"/>
          <?php if($AUTH_ROLE!=='patient'): ?>
            <input type="hidden" name="patient_id" value="<?php echo (int)$patient_id; ?>"/>
          <?php endif; ?>
          <div>
            <label class="muted">Warning signs</label>
            <textarea class="input" name="warning_signs" rows="3" placeholder="Thoughts, images, moods, situations, behavior..."><?php echo $last_safety? esc($last_safety['warning_signs']):''; ?></textarea>
          </div>
          <div>
            <label class="muted">Coping strategies</label>
            <textarea class="input" name="coping_strategies" rows="3" placeholder="Things I can do to take my mind off problems..."><?php echo $last_safety? esc($last_safety['coping_strategies']):''; ?></textarea>
          </div>
          <div>
            <label class="muted">Contacts</label>
            <textarea class="input" name="contacts" rows="3" placeholder="Family, friends, clinicians, hotlines..."><?php echo $last_safety? esc($last_safety['contacts']):''; ?></textarea>
          </div>
          <div class="hstack" style="justify-content:flex-end;">
            <button class="btn primary">Save Safety Plan</button>
          </div>
          <?php if(!$HAS_SAFETY): ?>
            <div class="muted">Safety plan table not installed. Run your MH migration to persist plans.</div>
          <?php endif; ?>
          <div class="muted"><strong>Crisis:</strong> If in immediate danger, call local emergency services now.</div>
        </form>
      </div>

    </div><!-- /Right column -->

  </div><!-- /row -->

  <?php endif; // end staff picker vs patient view ?>
</div><!-- /container -->

<!-- Charts + small UX scripts -->
<script>
(function(){
  // Helper to build URL with params (for AJAX)
  function buildUrl(params){
    const url = new URL(window.location.href);
    for(const k in params){
      if(params[k]===null) url.searchParams.delete(k);
      else url.searchParams.set(k, params[k]);
    }
    return url.toString();
  }

  // Mood score mapping for trend line
  function moodScore(m){
    if(m==='happy') return 2;
    if(m==='neutral') return 1;
    if(m==='sad' || m==='anxious') return -1;
    if(m==='angry') return -2;
    return 0;
  }

  // Chart elements (if present on this view)
  const lineEl = document.getElementById('moodLine');
  const pieEl  = document.getElementById('moodPie');

  // Patient context (if no patient selected, skip)
  const phpPatientId = <?php echo (int)$patient_id; ?>;
  if(!phpPatientId){ return; }

  // Fetch logs for line chart
  async function fetchLogs(){
    const url = new URL(window.location.href);
    url.searchParams.set('ajax','mood_logs');
    url.searchParams.set('patient_id', phpPatientId);
    <?php if($from): ?> url.searchParams.set('from', '<?php echo esc($from); ?>'); <?php endif; ?>
    <?php if($to): ?>   url.searchParams.set('to', '<?php echo esc($to); ?>');   <?php endif; ?>
    const resp = await fetch(url.toString(), {headers:{'Accept':'application/json'}});
    return await resp.json();
  }

  // Fetch aggregate for pie chart
  async function fetchAgg(){
    const url = new URL(window.location.href);
    url.searchParams.set('ajax','mood_agg');
    url.searchParams.set('patient_id', phpPatientId);
    <?php if($from): ?> url.searchParams.set('from', '<?php echo esc($from); ?>'); <?php endif; ?>
    <?php if($to): ?>   url.searchParams.set('to', '<?php echo esc($to); ?>');   <?php endif; ?>
    const resp = await fetch(url.toString(), {headers:{'Accept':'application/json'}});
    return await resp.json();
  }

  // Render Line Chart
  (async function(){
    if(!lineEl || typeof Chart==='undefined') return;
    try{
      const data = await fetchLogs();
      if(!data.ok) return;
      const labels = data.rows.map(r=>r.logged_at);
      const series = data.rows.map(r=>moodScore(r.mood));
      new Chart(lineEl.getContext('2d'), {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{ label:'Mood score', data: series, tension: .3, pointRadius: 2 }]
        },
        options: { responsive:true, scales:{ y:{ suggestedMin:-2, suggestedMax:2 } } }
      });
    } catch(e){ /* ignore */ }
  })();

  // Render Pie Chart
  (async function(){
    if(!pieEl || typeof Chart==='undefined') return;
    try{
      const data = await fetchAgg();
      if(!data.ok) return;
      const agg = data.agg || {};
      const labels = Object.keys(agg).map(k=>k.charAt(0).toUpperCase()+k.slice(1));
      const values = Object.keys(agg).map(k=>agg[k]);
      new Chart(pieEl.getContext('2d'), {
        type: 'doughnut',
        data: { labels: labels, datasets: [{ data: values }] },
        options: { responsive:true }
      });
    } catch(e){ /* ignore */ }
  })();

})();
</script>

<?php
/* If your app has shared footer include, you can uncomment:
include 'footer.php';
*/
?>
</body>
</html>
