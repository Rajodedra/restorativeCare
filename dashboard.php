<?php
// dashboard.php — FULL FILE WITH DB + SESSION INTEGRATION (keeps your UI intact)

/* ----------------------------- Session & Helpers ----------------------------- */
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/auth.php'; // Only this, no esc() function here

/* ----------------------------- DB Connection -------------------------------- */
$DB_HOST = 'localhost';
$DB_PORT = '3307';
$DB_USER = 'root';
$DB_PASS = "";
$DB_NAME = 'restorativecare';

$mysqli = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS,$DB_NAME,$DB_PORT);
if ($mysqli->connect_errno) {
  http_response_code(500);
  die("Database connection failed: " . esc($mysqli->connect_error));
}
$mysqli->set_charset('utf8mb4');

/* ----------------------------- Resolve Current User -------------------------- */
/*
  We don’t implement login here (as requested). If a session user exists, we use it.
  Otherwise we gracefully pick the first patient as a demo user, or fallback to a temp guest.
  Structure expected in $_SESSION['user']: ['id'=>int,'name'=>string,'role'=>'patient'|'doctor'|'admin']
*/
$currentUser = $_SESSION['user'] ?? null;

if (!$currentUser) {
  // Try to find any patient user to act as the dashboard user
  $demoRes = $mysqli->query("SELECT u.id, u.name, u.email, u.role
                               FROM users u
                               WHERE u.role = 'patient'
                               ORDER BY u.id ASC
                               LIMIT 1");
  if ($demoRes && $demoRes->num_rows) {
    $currentUser = $demoRes->fetch_assoc();
  } else {
    // Still let the page render with placeholders if no users exist yet
    $currentUser = ['id' => 0, 'name' => 'Jaimin Parmar', 'role' => 'patient'];
  }
}

$userId   = (int)($currentUser['id'] ?? 0);
$userName = $currentUser['name'] ?? 'Jaimin Parmar';
$userRole = $currentUser['role'] ?? 'patient';

/* ----------------------------- Link to Patient Row --------------------------- */
$patientId = null;
if ($userId > 0) {
  $stmt = $mysqli->prepare("SELECT p.id FROM patients p WHERE p.user_id = ? LIMIT 1");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $stmt->bind_result($pid);
  if ($stmt->fetch()) {
    $patientId = (int)$pid;
  }
  $stmt->close();
}

/* ----------------------------- Data Defaults -------------------------------- */
$adherenceTaken = 0;     // last 7 days taken doses (logs)
$adherenceMissed = 0;    // computed against a simple expectation (2/day over 7 days)
$adherencePct = 0;       // derived

$dischargeReadiness = 0; // % (we’ll map from treatment progress)

$therapySessionsWeek = 0;
$exercisesDone = 0;      // demo metric: we’ll map to number of medication logs capped to 10
$hydrationLabel = 'Good';// simple label based on adherence

$moodSeries = [3,4,3,4,5,4,4]; // default sparkline (Mon..Sun)

$appointments = [];       // upcoming 2
$notifications = [];      // latest 2

/* ----------------------------- Compute Adherence ----------------------------- */
/*
  Heuristic (demo but real DB):
  - Count medication_logs for the patient’s active/latest admission’s treatment_plans in last 7 days.
  - Expectation: 2 doses/day * 7 days = 14 (tweakable without UI change).
*/
$expectedPerDay = 2;
$daysWindow = 7;
$expectedTotal = $expectedPerDay * $daysWindow;

if ($patientId) {
  // Find admissions for patient (use the most recent admission)
  $stmt = $mysqli->prepare("
    SELECT a.id
    FROM admissions a
    WHERE a.patient_id = ?
    ORDER BY a.admitted_on DESC
    LIMIT 1
  ");
  $stmt->bind_param('i', $patientId);
  $stmt->execute();
  $stmt->bind_result($admissionId);
  $hasAdmission = $stmt->fetch();
  $stmt->close();

  if ($hasAdmission) {
    // Count medication logs for all medications under treatment plans for this admission in last 7 days
    $stmt = $mysqli->prepare("
      SELECT COUNT(ml.id) AS taken_count
      FROM treatment_plans tp
      JOIN medications m      ON m.treatment_id = tp.id
      LEFT JOIN medication_logs ml ON ml.medication_id = m.id
      WHERE tp.admission_id = ?
        AND (ml.taken_at IS NULL OR ml.taken_at >= (NOW() - INTERVAL ? DAY))
    ");
    $stmt->bind_param('ii', $admissionId, $daysWindow);
    $stmt->execute();
    $stmt->bind_result($takenCount);
    if ($stmt->fetch()) {
      $adherenceTaken = (int)$takenCount;
    }
    $stmt->close();
  } else {
    // No admission → we can still count all logs linked to this patient's medications across all admissions
    $stmt = $mysqli->prepare("
      SELECT COUNT(ml.id) AS taken_count
      FROM patients p
      JOIN admissions a     ON a.patient_id = p.id
      JOIN treatment_plans tp ON tp.admission_id = a.id
      JOIN medications m      ON m.treatment_id = tp.id
      LEFT JOIN medication_logs ml ON ml.medication_id = m.id
      WHERE p.id = ?
        AND (ml.taken_at IS NULL OR ml.taken_at >= (NOW() - INTERVAL ? DAY))
    ");
    $stmt->bind_param('ii', $patientId, $daysWindow);
    $stmt->execute();
    $stmt->bind_result($takenCount);
    if ($stmt->fetch()) {
      $adherenceTaken = (int)$takenCount;
    }
    $stmt->close();
  }
}

$adherenceMissed = max(0, $expectedTotal - $adherenceTaken);
$totalDosesForPct = max(1, $adherenceTaken + $adherenceMissed);
$adherencePct = (int)round(($adherenceTaken / $totalDosesForPct) * 100);

/* ----------------------------- Discharge Readiness --------------------------- */
/*
  If latest admission exists: use AVG(tp.progress) across its treatment_plans.
  Else: leave 0. (UI still pretty)
*/
if (!empty($admissionId)) {
  $stmt = $mysqli->prepare("
    SELECT COALESCE(AVG(tp.progress),0) AS avg_progress
    FROM treatment_plans tp
    WHERE tp.admission_id = ?
  ");
  $stmt->bind_param('i', $admissionId);
  $stmt->execute();
  $stmt->bind_result($avgProgress);
  if ($stmt->fetch()) {
    $dischargeReadiness = (int)round($avgProgress);
  }
  $stmt->close();
}

/* ----------------------------- Mini Stats ----------------------------------- */
/* Therapy sessions this week:
   Count appointments with status='scheduled' or 'completed' within current week.
*/
if ($patientId) {
  $stmt = $mysqli->prepare("
    SELECT COUNT(a.id)
    FROM appointments a
    WHERE a.patient_id = ?
      AND YEARWEEK(a.scheduled_at, 1) = YEARWEEK(CURDATE(), 1)
      AND a.status IN ('scheduled','completed')
  ");
  $stmt->bind_param('i', $patientId);
  $stmt->execute();
  $stmt->bind_result($therapySessionsWeek);
  $stmt->fetch();
  $stmt->close();
}

/* Exercises done (demo proxy): min(10, floor(adherenceTaken / 1)) */
$exercisesDone = min(10, max(0, (int)floor($adherenceTaken / 1)));

/* Hydration label heuristic based on adherence */
$hydrationLabel = ($adherencePct >= 85 ? 'Excellent' : ($adherencePct >= 60 ? 'Good' : 'Needs attention'));

/* ----------------------------- Upcoming Appointments (2) --------------------- */
if ($patientId) {
  $stmt = $mysqli->prepare("
    SELECT a.id,
           a.scheduled_at,
           a.status,
           a.urgency,
           d.name AS doctor_name
    FROM appointments a
    JOIN users d ON d.id = a.doctor_id
    WHERE a.patient_id = ?
      AND a.scheduled_at >= NOW()
    ORDER BY a.scheduled_at ASC
    LIMIT 2
  ");
  $stmt->bind_param('i', $patientId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $appointments[] = $row;
  }
  $stmt->close();
}

/* ----------------------------- Latest Notifications (2) ---------------------- */
if ($userId) {
  $stmt = $mysqli->prepare("
    SELECT id, message, created_at, read_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 2
  ");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $notifications[] = $row;
  }
  $stmt->close();
}

/* ----------------------------- Mood Sparkline (7 days) ----------------------- */
/*
   Map moods → scores: happy=5, neutral=3, sad=1, anxious=2, angry=1.
   Build an array Mon..Sun for current week. If no value for a day, fallback to 3.
*/
$mapMood = ['happy'=>5, 'neutral'=>3, 'sad'=>1, 'anxious'=>2, 'angry'=>1];
$moodSeries = [3,3,3,3,3,3,3]; // 7 days baseline
if ($patientId) {
  $stmt = $mysqli->prepare("
    SELECT DATE(logged_at) as d, mood
    FROM mood_logs
    WHERE patient_id = ?
      AND logged_at >= (CURDATE() - INTERVAL 6 DAY)
    ORDER BY logged_at ASC
  ");
  $stmt->bind_param('i', $patientId);
  $stmt->execute();
  $res = $stmt->get_result();
  // Build index for last 7 days labels
  $last7 = [];
  for ($i = 6; $i >= 0; $i--) {
    $key = (new DateTime())->modify("-$i day")->format('Y-m-d');
    $last7[$key] = null;
  }
  while ($row = $res->fetch_assoc()) {
    $d = $row['d'];
    $m = strtolower((string)$row['mood']);
    if (array_key_exists($d, $last7)) {
      $last7[$d] = $mapMood[$m] ?? 3;
    }
  }
  $moodSeries = array_map(function($v){ return $v === null ? 3 : (int)$v; }, array_values($last7));
  $stmt->close();
}

/* ----------------------------- Templating Vars -------------------------------- */
$jsMoodData       = json_encode($moodSeries);
$jsAdherenceTaken = (int)$adherenceTaken;
$jsAdherenceMiss  = (int)$adherenceMissed;
$jsAdherencePct   = (int)$adherencePct;
$jsReadinessPct   = (int)$dischargeReadiness;

// For the small stats UI
$uiTherapyThisWeek = (int)$therapySessionsWeek;
$uiExercisesDone   = (int)$exercisesDone;
$uiHydrationLabel  = $hydrationLabel;

// Pre-format appointments for display (date + time + names)
function fmtApptItem(array $a) {
  $dt = new DateTime($a['scheduled_at']);
  $dateText = $dt->format('M j, Y — h:i A');
  $title = 'Appointment';
  // optional nicer title from urgency
  if (!empty($a['urgency'])) {
    $u = ucfirst($a['urgency']);
    $title = ($u === 'High' ? 'Urgent Appointment' : 'Appointment');
  }
  return [
    'title' => $title,
    'date'  => $dateText,
    'doctor'=> $a['doctor_name'] ?? 'Doctor'
  ];
}

// Pre-format notifications (type → icon bg)
function notifIcon($text) {
  $t = strtolower($text);
  if (str_contains($t, 'remind')) return ['bg'=>'bg-yellow-100','icon'=>'https://cdn-icons-png.flaticon.com/512/546/546394.png'];
  if (str_contains($t, 'new') || str_contains($t, 'updated') || str_contains($t, 'resource')) return ['bg'=>'bg-green-100','icon'=>'https://cdn-icons-png.flaticon.com/512/1250/1250615.png'];
  return ['bg'=>'bg-blue-100','icon'=>'https://cdn-icons-png.flaticon.com/512/545/545682.png'];
}

// Last updated display
$lastUpdatedHuman = (new DateTime())->format('M j, Y — h:i A');

?>
<?php

  // requires auth.php in the same folder (session + require_auth())
  if (!isset($_SESSION['user'])) {  
    header('Location: login.php');
   exit; // Show a beautiful login modal
  }
    ?>
    
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Dashboard — RestorativeCare</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Chart.js for charts -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Google font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

  <style>
    :root{
      --glass-bg: rgba(255,255,255,0.15);
      --glass-border: rgba(255,255,255,0.22);
      --accent: #06b6d4; /* cyan-500 */
      --accent-dark: #0891b2;
    }
    body{
      font-family:'Inter',sans-serif;
      background: radial-gradient(circle at 10% 10%, #f0fbff 0%, #ffffff 30%, #f8fbff 100%);
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      color:#0f172a;
    }

    /* glass card */
    .glass {
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      backdrop-filter: blur(8px) saturate(120%);
      -webkit-backdrop-filter: blur(8px) saturate(120%);
    }

    /* subtle 3D tilt effect container */
    .tilt {
      transform-style: preserve-3d;
      transition: transform 0.18s ease-out;
      will-change: transform;
    }

    /* floating hero illustration */
    @keyframes floatY {
      0%,100% { transform: translateY(0) }
      50% { transform: translateY(-10px) }
    }
    .float-slow { animation: floatY 5s ease-in-out infinite; }

    /* card depth shadow for 3D vibe */
    .card-deep {
      box-shadow: 0 10px 30px rgba(14,30,37,0.08), inset 0 1px 0 rgba(255,255,255,0.04);
      transition: box-shadow .2s ease, transform .2s ease;
    }

    .card-deep:hover {
      transform: translateY(-6px) scale(1.01);
      box-shadow: 0 24px 40px rgba(14,30,37,0.12);
    }

    /* reveal helper */
    .reveal { opacity: 0; transform: translateY(12px); transition: all .65s cubic-bezier(.2,.9,.3,1); }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    /* small visuals */
    .stat-pill { background: rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.06); padding:6px 10px; border-radius:999px; font-weight:600; color:#083344; }
    .mini-icon { width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent-dark)); display:flex;align-items:center;justify-content:center;color:white;font-weight:700;box-shadow:0 6px 18px rgba(6,182,212,0.18); }

    /* responsive tweaks */
    @media (min-width: 1024px) {
      .container-grid { display:grid; grid-template-columns: 1.6fr 1fr; gap:28px; align-items:start; }
    }
  </style>
</head>
<body class="p-6">

  <!-- top nav -->
  <header class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-4">
      <div class="text-2xl font-bold text-cyan-600">RestorativeCare</div>
      <div class="text-sm text-gray-500">— Patient Dashboard</div>
    </div>
    <div class="flex items-center gap-4">
       <a href="logout.php" class="ml-3 px-4 py-2 bg-cyan-500 text-white rounded-lg hover:bg-cyan-600">Logout</a>
      <a href="index.php" class="text-sm text-gray-600 hover:text-cyan-600">Home</a>
      <a href="schedule.php" class="text-sm text-gray-600 hover:text-cyan-600">Schedule</a>
      <a href="notifications.php" class="text-sm text-gray-600 hover:text-cyan-600">Notifications</a>
      <a href="mental-health.php" class="text-sm text-gray-600 hover:text-cyan-600">Mental Health</a>
      <a href="discharge.php" class="ml-3 px-4 py-2 bg-cyan-500 text-white rounded-lg hover:bg-cyan-600">Discharge Toolkit</a>
    </div>
  </header>

  <!-- Welcome banner -->
  <section class="glass rounded-xl p-6 mb-6 tilt card-deep" id="welcomeCard">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-4">
          <div class="mini-icon"><?php echo esc(strtoupper(substr($userNameSafe,0,1))) . esc(strtoupper(substr($userNameSafe,1,1))); ?></div>
          <div>
            <div class="text-lg font-semibold">Welcome back, <?php echo $userName; ?></div>
            <div class="text-sm text-gray-500">Keep going — you’re making progress. Last updated:
              <span id="lastUpdated"><?php echo esc($lastUpdatedHuman); ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- small quick stats -->
      <div class="flex gap-4 items-center">
        <div class="text-center">
          <div class="text-sm text-gray-500">Medication</div>
          <div class="stat-pill mt-1"><span id="pillAdh"><?php echo $jsAdherencePct; ?></span>% adherence</div>
        </div>
        <div class="text-center">
          <div class="text-sm text-gray-500">Discharge Readiness</div>
          <div class="stat-pill mt-1"><span id="pillReadiness"><?php echo $jsReadinessPct; ?></span>%</div>
        </div>
        <div>
          <a href="dashboard.php#progress" class="px-4 py-2 bg-white text-cyan-600 rounded-lg font-semibold border border-white/20 hover:bg-white/90">View Details</a>
        </div>
      </div>
    </div>
  </section>

  <!-- main grid -->
  <main class="container-grid">
    <!-- left: progress, appointments, notifications -->
    <div class="space-y-6">
      <!-- Progress cards -->
      <section id="progress" class="glass rounded-xl p-6 reveal card-deep">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-semibold">Treatment Progress</h3>
          <div class="text-sm text-gray-500">Overall recovery</div>
        </div>

        <div class="flex flex-col md:flex-row items-center gap-6">
          <!-- Doughnut chart -->
          <div class="w-48 h-48 bg-white/5 rounded-full p-4 flex items-center justify-center">
            <canvas id="adherenceChart" width="180" height="180"></canvas>
          </div>

          <!-- progress details -->
          <div class="flex-1 space-y-3">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm text-gray-500">Medication adherence</div>
                <div class="text-lg font-semibold"><span id="txtAdh"><?php echo $jsAdherencePct; ?></span>%</div>
              </div>
              <div>
                <button class="px-4 py-2 bg-cyan-500 text-white rounded-lg hover:bg-cyan-600">Set Reminder</button>
              </div>
            </div>

            <div class="mt-2">
              <div class="text-sm text-gray-500">Discharge readiness</div>
              <div class="w-full bg-white/10 rounded-full h-3 mt-2 overflow-hidden">
                <div id="readinessBar" style="width:<?php echo $jsReadinessPct; ?>%" class="h-3 bg-gradient-to-r from-cyan-400 to-cyan-600"></div>
              </div>
              <div class="text-xs text-gray-500 mt-1">Score: <span id="txtReady"><?php echo $jsReadinessPct; ?></span> / 100</div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
              <div class="glass p-3 rounded-lg text-center">
                <div class="text-sm text-gray-500">Therapy sessions</div>
                <div class="font-semibold"><span id="txtTherapy"><?php echo $uiTherapyThisWeek; ?></span> this week</div>
              </div>
              <div class="glass p-3 rounded-lg text-center">
                <div class="text-sm text-gray-500">Exercises done</div>
                <div class="font-semibold"><span id="txtExercise"><?php echo $uiExercisesDone; ?></span> / 10</div>
              </div>
              <div class="glass p-3 rounded-lg text-center">
                <div class="text-sm text-gray-500">Hydration</div>
                <div class="font-semibold" id="txtHydration"><?php echo esc($uiHydrationLabel); ?></div>
              </div>
            </div>

          </div>
        </div>
      </section>

      <!-- Appointments -->
      <section class="glass rounded-xl p-6 reveal card-deep">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-semibold">Appointments</h3>
          <a href="schedule.php" class="text-sm text-cyan-600 hover:underline">Manage</a>
        </div>

        <ul class="space-y-3">
          <?php if (!empty($appointments)): ?>
            <?php foreach ($appointments as $a):
              $f = fmtApptItem($a); ?>
              <li class="flex items-start gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-white/5">
                  <img src="https://cdn-icons-png.flaticon.com/512/2965/2965567.png" alt="" class="w-6 h-6 opacity-90">
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <div>
                      <div class="font-semibold"><?php echo esc($f['title']); ?></div>
                      <div class="text-xs text-gray-500"><?php echo esc($f['date']); ?></div>
                    </div>
                    <div class="text-sm text-gray-500"><?php echo esc($f['doctor']); ?></div>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <!-- Fallback to your original two demo items if no DB data -->
            <li class="flex items-start gap-4">
              <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-white/5">
                <img src="https://cdn-icons-png.flaticon.com/512/2965/2965567.png" alt="" class="w-6 h-6 opacity-90">
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-semibold">Physiotherapy Session</div>
                    <div class="text-xs text-gray-500">Aug 14, 2025 — 11:00 AM</div>
                  </div>
                  <div class="text-sm text-gray-500">Dr. Mehta</div>
                </div>
              </div>
            </li>
            <li class="flex items-start gap-4">
              <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-white/5">
                <img src="https://cdn-icons-png.flaticon.com/512/2913/2913142.png" alt="" class="w-6 h-6 opacity-90">
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-semibold">Mental Health Check-in</div>
                    <div class="text-xs text-gray-500">Aug 16, 2025 — 09:00 AM</div>
                  </div>
                  <div class="text-sm text-gray-500">Counselor</div>
                </div>
              </div>
            </li>
          <?php endif; ?>
        </ul>
      </section>

      <!-- Notifications -->
      <section class="glass rounded-xl p-6 reveal card-deep">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-semibold">Notifications</h3>
          <a href="notifications.php" class="text-sm text-cyan-600 hover:underline">View all</a>
        </div>

        <div class="space-y-3">
          <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $n):
              $ico = notifIcon($n['message'] ?? '');
              $when = (new DateTime($n['created_at']))->format('M j, Y — H:i'); ?>
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg <?php echo esc($ico['bg']); ?> flex items-center justify-center">
                  <img src="<?php echo esc($ico['icon']); ?>" class="w-5 h-5" alt="">
                </div>
                <div>
                  <div class="text-sm"><?php echo esc($n['message']); ?></div>
                  <div class="text-xs text-gray-400"><?php echo esc($when); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <!-- Your original demo items as fallback -->
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                <img src="https://cdn-icons-png.flaticon.com/512/546/546394.png" class="w-5 h-5" alt="">
              </div>
              <div>
                <div class="text-sm"><span class="font-semibold">Reminder:</span> Take 1 tablet of Painkiller — 08:00 AM</div>
                <div class="text-xs text-gray-400">Today · 07:58</div>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                <img src="https://cdn-icons-png.flaticon.com/512/1250/1250615.png" class="w-5 h-5" alt="">
              </div>
              <div>
                <div class="text-sm"><span class="font-semibold">New resource:</span> Post-surgery exercises PDF available</div>
                <div class="text-xs text-gray-400">Yesterday</div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </section>

    </div>

    <!-- right sidebar: mental health + discharge toolkit + AI assistant -->
    <aside class="space-y-6">
      <!-- Mood Tracker -->
      <div class="glass rounded-xl p-6 reveal card-deep">
        <div class="flex items-center justify-between mb-3">
          <h4 class="font-semibold">Mood Tracker</h4>
          <a href="mental-health.php" class="text-sm text-cyan-600 hover:underline">Open</a>
        </div>
        <p class="text-sm text-gray-600 mb-3">How are you feeling today?</p>
        <div class="flex gap-3">
          <button class="px-3 py-2 glass rounded-lg hover:scale-105" onclick="recordMood('happy')">😊</button>
          <button class="px-3 py-2 glass rounded-lg hover:scale-105" onclick="recordMood('neutral')">😐</button>
          <button class="px-3 py-2 glass rounded-lg hover:scale-105" onclick="recordMood('sad')">☹️</button>
        </div>
        <canvas id="moodChart" class="mt-4" height="80"></canvas>
      </div>

      <!-- Discharge toolkit -->
      <div class="glass rounded-xl p-6 reveal card-deep">
        <div class="flex items-center justify-between">
          <h4 class="font-semibold">Discharge Toolkit</h4>
          <a href="discharge.php" class="text-sm text-cyan-600 hover:underline">Open</a>
        </div>
        <p class="text-sm text-gray-600 mt-2">Download your personalized discharge plan and follow-up checklist.</p>
        <div class="mt-4 flex gap-3">
          <a href="discharge_plan_sample.pdf" class="px-3 py-2 bg-white text-cyan-600 rounded-lg hover:bg-white/90">Download PDF</a>
          <a href="discharge.php" class="px-3 py-2 glass rounded-lg">View Checklist</a>
        </div>
      </div>

      <!-- AI Assistant (mock) -->
      <div class="glass rounded-xl p-6 reveal card-deep">
        <div class="flex items-center justify-between mb-3">
          <h4 class="font-semibold">Care Assistant</h4>
          <span class="text-xs text-gray-500">AI (demo)</span>
        </div>
        <div class="text-sm text-gray-600 mb-3">Ask something like “When is my next session?” or “How to take medication?”</div>
        <div class="flex gap-2">
          <input id="assistantInput" class="flex-1 p-2 rounded-lg border border-white/10 bg-transparent outline-none" placeholder="Type your question..." />
          <button onclick="askAssistant()" class="px-3 py-2 bg-cyan-500 text-white rounded-lg">Ask</button>
        </div>
        <div id="assistantReply" class="mt-3 text-sm text-gray-700"></div>
      </div>
    </aside>
  </main>

  <footer class="mt-8 text-center text-sm text-gray-500">
    &copy; <?php echo date('Y'); ?> RestorativeCare — Built for the Hackathon
  </footer>

  <!-- Scripts: charts, interactions, 3D tilt & scroll reveal -->
  <script>
    // ------ Server-fed values ------
    const ADH_TAKEN   = <?php echo (int)$jsAdherenceTaken; ?>;
    const ADH_MISSED  = <?php echo (int)$jsAdherenceMiss; ?>;
    const ADH_PCT     = <?php echo (int)$jsAdherencePct; ?>;
    const READY_PCT   = <?php echo (int)$jsReadinessPct; ?>;
    const MOOD_SERIES = <?php echo $jsMoodData; ?>;

    // Inject into static text (keeps your markup the same but data is live)
    const pillAdh = document.getElementById('pillAdh'); if (pillAdh) pillAdh.textContent = ADH_PCT;
    const pillReady = document.getElementById('pillReadiness'); if (pillReady) pillReady.textContent = READY_PCT;
    const txtAdh = document.getElementById('txtAdh'); if (txtAdh) txtAdh.textContent = ADH_PCT;
    const txtReady = document.getElementById('txtReady'); if (txtReady) txtReady.textContent = READY_PCT;

    /* ---------- Chart: Adherence doughnut ---------- */
    const ctx = document.getElementById('adherenceChart').getContext('2d');
    const adherenceChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Taken','Missed'],
        datasets: [{
          data: [Math.max(0, ADH_TAKEN), Math.max(0, ADH_MISSED)],
          backgroundColor: ['#06b6d4','#e6f7f9'],
          borderWidth: 0
        }]
      },
      options: {
        cutout: '70%',
        plugins: {
          legend: { display: false },
          tooltip: { enabled: true }
        }
      }
    });

    /* ---------- Mood chart (sparkline-like) ---------- */
    const moodCtx = document.getElementById('moodChart').getContext('2d');
    const moodChart = new Chart(moodCtx, {
      type: 'line',
      data: {
        labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        datasets: [{
          label: 'Mood score',
          data: MOOD_SERIES && MOOD_SERIES.length === 7 ? MOOD_SERIES : [3,4,3,4,5,4,4],
          borderColor: '#06b6d4',
          backgroundColor: 'rgba(6,182,212,0.12)',
          tension: 0.4,
          fill: true,
          pointRadius: 0
        }]
      },
      options: {
        scales: { x: { display: false }, y: { display: false } },
        plugins: { legend: { display: false }, tooltip: { enabled: true } }
      }
    });

    /* ---------- Mood click handler (demo write) ----------
       This remains a front-end demo action (no backend write per your request).
    */
    function recordMood(m) {
      const el = document.getElementById('assistantReply');
      el.innerText = 'Thanks — your mood (' + m + ') was recorded (demo).';
      el.classList.add('animate__animated','animate__flash');
      setTimeout(()=> el.classList.remove('animate__animated','animate__flash'),900);
      // Update moodChart (mock push)
      const arr = moodChart.data.datasets[0].data;
      arr.shift(); arr.push(m === 'happy' ? 5 : (m === 'neutral' ? 3 : 1));
      moodChart.update();
    }

    /* ---------- Assistant (mock responses) ---------- */
    function askAssistant() {
      const q = document.getElementById('assistantInput').value.trim();
      const out = document.getElementById('assistantReply');
      if(!q) { out.innerText = 'Please type a question.'; return; }
      const ql = q.toLowerCase();
      if(ql.includes('next') && ql.includes('session')) {
        out.innerText = 'Your next session is listed above in Appointments.';
      } else if(ql.includes('medic') || ql.includes('medicine')) {
        out.innerText = 'Take your medications as per the schedule. Adherence currently at ' + ADH_PCT + '%.';
      } else if(ql.includes('discharge')) {
        out.innerText = 'Your discharge readiness is currently ' + READY_PCT + '%. Check the Discharge Toolkit.';
      } else {
        out.innerText = 'I\'m a demo assistant — please ask about appointments, medication, or discharge.';
      }
      out.classList.add('animate__animated','animate__fadeInUp');
      setTimeout(()=> out.classList.remove('animate__animated','animate__fadeInUp'),850);
    }

    /* ---------- 3D tilt effect on mouse move for welcomeCard ---------- */
    const card = document.getElementById('welcomeCard');
    const wrap = card;
    wrap.addEventListener('mousemove', (e) => {
      const rect = wrap.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width;
      const y = (e.clientY - rect.top) / rect.height;
      const rotateY = (x - 0.5) * 10; // degrees
      const rotateX = (0.5 - y) * 6;
      wrap.style.transform = `perspective(900px) rotateY(${rotateY}deg) rotateX(${rotateX}deg) translateZ(0)`;
    });
    wrap.addEventListener('mouseleave', () => wrap.style.transform = 'none');

    /* ---------- Mouse-reactive background for subtle 3D illusion ---------- */
    document.addEventListener('mousemove', (e) => {
      const rx = (e.clientX / window.innerWidth) * 40 - 20;
      const ry = (e.clientY / window.innerHeight) * 40 - 20;
      document.body.style.background = `radial-gradient(circle at ${20+rx}% ${10+ry}%, #f0fbff 0%, #ffffff 30%, #f8fbff 100%)`;
    });

    /* ---------- IntersectionObserver scroll reveal ---------- */
    const reveals = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries) => {
      for (const ent of entries) {
        if (ent.isIntersecting) {
          ent.target.classList.add('visible','animate__animated','animate__fadeInUp');
        }
      }
    }, { threshold: 0.16 });
    reveals.forEach(r => io.observe(r));

    /* ---------- small: update lastUpdated to current time (already set by PHP, keep for demo) ---------- */
    // document.getElementById('lastUpdated').innerText = new Date().toLocaleString();
  </script>
</body>
</html>
<?php
// Optional: close DB
$mysqli->close();
?>
