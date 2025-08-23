<?php
// admin.php — FULL ADMIN PANEL with DB + SESSION + RBAC (admin/superadmin)
// Style/theme matches your dashboard.php (Tailwind + glass cards + Inter)

/* ----------------------------- Session & Helpers ----------------------------- */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function is_post(){ return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }
function csrf_token(){ if(empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_check($t){ return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$t); }

/* ----------------------------- DB Connection -------------------------------- */
$DB_HOST = 'localhost';
$DB_PORT = '3307';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'restorativecare';
$mysqli = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($mysqli->connect_errno) { http_response_code(500); die('DB connection failed: '.esc($mysqli->connect_error)); }
$mysqli->set_charset('utf8mb4');

/* ----------------------------- Auth / RBAC ---------------------------------- */
$currentUser = $_SESSION['user'] ?? null;
if(!$currentUser){
  // fallback: try superadmin
  $rs = $mysqli->query("SELECT id,name,role FROM users WHERE role IN ('admin','superadmin') ORDER BY role='superadmin' DESC, id ASC LIMIT 1");
  if($rs && $rs->num_rows){ $currentUser = $rs->fetch_assoc(); $_SESSION['user'] = $currentUser; }
}
$userRole = strtolower($currentUser['role'] ?? 'patient');
$userId = (int)($currentUser['id'] ?? 0);
$userName = $currentUser['name'] ?? 'Admin';
$IS_SUPER = ($userRole === 'superadmin');
$IS_ADMIN = ($userRole === 'admin');
$IS_DOCTOR = ($userRole === 'doctor');
$IS_NURSE = ($userRole === 'nurse');
$IS_PATIENT = ($userRole === 'patient');

// Only allow access for valid roles
if(!in_array($userRole, ['admin','superadmin','doctor','nurse','patient'])){
  http_response_code(403); echo 'Access denied.'; exit;
}

/* ----------------------------- Router (tabs) -------------------------------- */
// Define allowed tabs per role
$roleTabs = [
  'superadmin' => ['overview','users','patients','admissions','transfers','visitors','vitals','lab','insurance','payments','schedule','inventory','ot','feedback','resources','audit','settings'],
  'admin'      => ['overview','users','patients','admissions','transfers','visitors','vitals','lab','insurance','payments','schedule','inventory','ot','feedback','resources','audit','settings'],
  // Doctor: Only allowed tabs + new 'reminder' tab
  'doctor'     => ['patients','admissions','transfers','vitals','lab','ot','reminder'],
  'nurse'      => ['overview','patients','admissions','vitals','schedule','feedback','resources','settings'],
  'patient'    => ['overview','patients','feedback','resources','settings'],
];

$validTabs = $roleTabs[$userRole] ?? ['overview'];
$tab = $_GET['tab'] ?? $validTabs[0];
if(!in_array($tab, $validTabs)) $tab = $validTabs[0];

/* ----------------------------- Flash helper --------------------------------- */
function flash($key,$val=null){
  if($val===null){ $v=$_SESSION['flash'][$key]??null; unset($_SESSION['flash'][$key]); return $v; }
  $_SESSION['flash'][$key]=$val;
}

/* ----------------------------- Small util: run prepared queries ------------- */
function q($mysqli, $sql, $types='', ...$params){
  $stmt = $mysqli->prepare($sql);
  if(!$stmt){ throw new Exception('Prepare failed: '.$mysqli->error); }
  if($types){ $stmt->bind_param($types, ...$params); }
  if(!$stmt->execute()){ throw new Exception('Execute failed: '.$stmt->error); }
  return $stmt;
}

/* ----------------------------- Handle POST actions -------------------------- */
try {
  if(is_post() && !csrf_check($_POST['csrf'] ?? '')){ throw new Exception('Invalid CSRF'); }

  // Patients: can only raise feedback/help, cannot modify anything else
  if($IS_PATIENT) {
    if(is_post() && ($_POST['entity']??'')==='feedback'){
      $patient_id = $userId; // Only allow feedback for self
      $rating=(int)($_POST['rating']??5); $comments=trim($_POST['comments']??'');
      q($mysqli,"INSERT INTO feedback(patient_id,rating,comments) VALUES(?,?,?)",'iis',$patient_id,$rating,$comments)->close();
      flash('ok','Help request/feedback sent');
      header('Location: admin.php?tab=feedback'); exit;
    }
    // Block all other POST actions for patient
    if(is_post()) throw new Exception('Patients cannot modify data.');
  }

  // Nurses: allow reminders/feedback/schedule/vitals, block user/patient/admission/lab/inventory/ot/payments/resources/audit/settings modifications
  if($IS_NURSE) {
    $allowedEntities = ['vital','schedule','feedback','patients'];
    if(is_post() && !in_array($_POST['entity']??'', $allowedEntities)){
      throw new Exception('Nurses cannot modify this section.');
    }
    // Allow normal handling for allowed entities
  }

  // Doctors: allow only relevant entities
  if($IS_DOCTOR) {
    $allowedEntities = ['patients','admissions','transfer','vital','lab_order','lab_result','ot','reminder'];
    if(is_post() && !in_array($_POST['entity']??'', $allowedEntities)){
      throw new Exception('Doctors cannot modify this section.');
    }
    // Handle reminder creation
    if(is_post() && ($_POST['entity']??'')==='reminder'){
      $patient_id = (int)($_POST['patient_id']??0);
      $reminder = trim($_POST['reminder']??'');
      if(!$patient_id || !$reminder) throw new Exception('Patient and reminder required.');
      q($mysqli,"INSERT INTO reminders (patient_id,doctor_id,reminder_text,created_at) VALUES (?,?,?,NOW())",'iis',$patient_id,$userId,$reminder)->close();
      flash('ok','Reminder added for patient.');
      header('Location: admin.php?tab=reminder'); exit;
    }
    // Allow normal handling for allowed entities
  }

  // Admin/Superadmin: full access
  if(is_post() && ($_POST['entity']??'')==='user'){
    $action = $_POST['action'] ?? '';
    if($action==='create'){
      $name = trim($_POST['name']??'');
      $email= trim($_POST['email']??'');
      $phone= trim($_POST['phone']??'');
      $role = trim($_POST['role']??'patient');
      if(!$IS_SUPER && $role!=='patient') $role='patient';
      $pass = $_POST['password']??''; $hash = password_hash($pass ?: bin2hex(random_bytes(4)), PASSWORD_DEFAULT);
      q($mysqli, "INSERT INTO users(name,email,phone,role,password_hash) VALUES(?,?,?,?,?)", 'sssss', $name,$email,$phone,$role,$hash)->close();
      flash('ok','User created');
    } elseif($action==='update'){
      $id=(int)($_POST['id']??0);
      $name=trim($_POST['name']??'');
      $email=trim($_POST['email']??'');
      $phone=trim($_POST['phone']??'');
      $role =trim($_POST['role']??'patient');
      if(!$IS_SUPER){ // admins cannot elevate/change roles beyond patient
        $stmt=q($mysqli,"UPDATE users SET name=?, email=?, phone=? WHERE id=?",'sssi',$name,$email,$phone,$id); $stmt->close();
      } else {
        $stmt=q($mysqli,"UPDATE users SET name=?, email=?, phone=?, role=? WHERE id=?",'ssssi',$name,$email,$phone,$role,$id); $stmt->close();
      }
      if(!empty($_POST['password'])){
        $hash=password_hash($_POST['password'], PASSWORD_DEFAULT);
        q($mysqli,"UPDATE users SET password_hash=? WHERE id=?",'si',$hash,$id)->close();
      }
      flash('ok','User updated');
    } elseif($action==='delete'){
      $id=(int)($_POST['id']??0);
      if($IS_SUPER || $id!==$GLOBALS['userId']){ q($mysqli,"DELETE FROM users WHERE id=?",'i',$id)->close(); flash('ok','User deleted'); }
      else { flash('err','Cannot delete yourself'); }
    }
    header('Location: admin.php?tab=users'); exit;
  }

  // PATIENTS (create/update)
  if(is_post() && ($_POST['entity']??'')==='patient'){
    $action=$_POST['action']??''; $user_id=(int)($_POST['user_id']??0); $dob=$_POST['dob']??null; $gender=$_POST['gender']??null; $address=$_POST['address']??null;
    if($action==='create'){
      q($mysqli,"INSERT INTO patients(user_id,dob,gender,address) VALUES(?,?,?,?)",'isss',$user_id,$dob,$gender,$address)->close();
      flash('ok','Patient added');
    } elseif($action==='update'){
      $id=(int)($_POST['id']??0);
      q($mysqli,"UPDATE patients SET user_id=?, dob=?, gender=?, address=? WHERE id=?",'isssi',$user_id,$dob,$gender,$address,$id)->close();
      flash('ok','Patient updated');
    }
    header('Location: admin.php?tab=patients'); exit;
  }

  // ADMISSIONS (quick admit / discharge)
  if(is_post() && ($_POST['entity']??'')==='admission'){
    $action=$_POST['action']??''; $patient_id=(int)($_POST['patient_id']??0);
    if($action==='admit'){
      q($mysqli,"INSERT INTO admissions(patient_id,status) VALUES(?, 'admitted')",'i',$patient_id)->close();
      flash('ok','Patient admitted');
    } elseif($action==='discharge'){
      $id=(int)($_POST['id']??0);
      q($mysqli,"UPDATE admissions SET status='discharged' WHERE id=?",'i',$id)->close();
      flash('ok','Admission discharged');
    }
    header('Location: admin.php?tab=admissions'); exit;
  }

  // TRANSFERS
  if(is_post() && ($_POST['entity']??'')==='transfer'){
    $patient_id=(int)($_POST['patient_id']??0); $from=trim($_POST['from_ward']??''); $to=trim($_POST['to_ward']??''); $reason=trim($_POST['reason']??'');
    q($mysqli,"INSERT INTO patient_transfers(patient_id,from_ward,to_ward,reason) VALUES(?,?,?,?)",'isss',$patient_id,$from,$to,$reason)->close();
    flash('ok','Transfer recorded'); header('Location: admin.php?tab=transfers'); exit;
  }

  // VISITORS
  if(is_post() && ($_POST['entity']??'')==='visitor'){
    $patient_id=(int)($_POST['patient_id']??0); $name=trim($_POST['name']??''); $relation=trim($_POST['relation']??''); $pass=trim($_POST['pass_qr_path']??'');
    q($mysqli,"INSERT INTO visitors(patient_id,name,relation,pass_qr_path) VALUES(?,?,?,?)",'isss',$patient_id,$name,$relation,$pass)->close();
    flash('ok','Visitor pass created'); header('Location: admin.php?tab=visitors'); exit;
  }

  // VITALS (manual input)
  if(is_post() && ($_POST['entity']??'')==='vital'){
    $patient_id=(int)($_POST['patient_id']??0); $bp=trim($_POST['bp']??''); $hr=(int)($_POST['heart_rate']??0); $temp=(float)($_POST['temperature']??0); $spo2=(int)($_POST['spo2']??0); $rr=(int)($_POST['respiratory_rate']??0);
    q($mysqli,"INSERT INTO patient_vitals(patient_id,bp,heart_rate,temperature,spo2,respiratory_rate) VALUES(?,?,?,?,?,?)",'isdiis',$patient_id,$bp,$hr,$temp,$spo2,$rr)->close();
    flash('ok','Vitals saved'); header('Location: admin.php?tab=vitals'); exit;
  }

  // LAB (order + result)
  if(is_post() && ($_POST['entity']??'')==='lab_order'){
    $admission_id=(int)($_POST['admission_id']??0); $test=trim($_POST['test_name']??''); $ordered_by=(int)($_POST['ordered_by']??$userId);
    q($mysqli,"INSERT INTO lab_orders(admission_id,test_name,ordered_by) VALUES(?,?,?)",'isi',$admission_id,$test,$ordered_by)->close();
    flash('ok','Lab test ordered'); header('Location: admin.php?tab=lab'); exit;
  }
  if(is_post() && ($_POST['entity']??'')==='lab_result'){
    $order_id=(int)($_POST['order_id']??0); $result=$_POST['result']??''; $path=trim($_POST['file_path']??'');
    q($mysqli,"INSERT INTO lab_results(order_id,result,file_path) VALUES(?,?,?)",'iss',$order_id,$result,$path)->close();
    q($mysqli,"UPDATE lab_orders SET status='completed' WHERE id=?",'i',$order_id)->close();
    flash('ok','Result uploaded'); header('Location: admin.php?tab=lab'); exit;
  }

  // INSURANCE
  if(is_post() && ($_POST['entity']??'')==='insurance'){
    $patient_id=(int)($_POST['patient_id']??0); $provider=trim($_POST['provider']??''); $amount=(float)($_POST['claim_amount']??0);
    q($mysqli,"INSERT INTO insurance_claims(patient_id,provider,claim_amount) VALUES(?,?,?)",'isd',$patient_id,$provider,$amount)->close();
    flash('ok','Claim submitted'); header('Location: admin.php?tab=insurance'); exit;
  }

  // PAYMENTS
  if(is_post() && ($_POST['entity']??'')==='payment'){
    $patient_id=(int)($_POST['patient_id']??0); $amount=(float)($_POST['amount']??0); $method=trim($_POST['method']??'cash'); $status=trim($_POST['status']??'paid');
    q($mysqli,"INSERT INTO payments(patient_id,amount,method,status) VALUES(?,?,?,?)",'idss',$patient_id,$amount,$method,$status)->close();
    flash('ok','Payment recorded'); header('Location: admin.php?tab=payments'); exit;
  }

  // STAFF SCHEDULE
  if(is_post() && ($_POST['entity']??'')==='schedule'){
    $staff_id=(int)($_POST['staff_id']??0); $start=$_POST['shift_start']??null; $end=$_POST['shift_end']??null; $role=trim($_POST['role']??'support');
    q($mysqli,"INSERT INTO staff_schedule(staff_id,shift_start,shift_end,role) VALUES(?,?,?,?)",'isss',$staff_id,$start,$end,$role)->close();
    flash('ok','Shift added'); header('Location: admin.php?tab=schedule'); exit;
  }

  // INVENTORY
  if(is_post() && ($_POST['entity']??'')==='inventory'){
    $name=trim($_POST['item_name']??''); $stock=(int)($_POST['stock']??0); $th=(int)($_POST['threshold']??5);
    q($mysqli,"INSERT INTO inventory(item_name,stock,threshold) VALUES(?,?,?)",'sii',$name,$stock,$th)->close();
    flash('ok','Inventory item added'); header('Location: admin.php?tab=inventory'); exit;
  }

  // OT SCHEDULE
  if(is_post() && ($_POST['entity']??'')==='ot'){
    $patient_id=(int)($_POST['patient_id']??0); $doctor_id=(int)($_POST['doctor_id']??0); $time=$_POST['scheduled_time']??null; $dur=(int)($_POST['duration_minutes']??60);
    q($mysqli,"INSERT INTO ot_schedule(patient_id,doctor_id,scheduled_time,duration_minutes) VALUES(?,?,?,?)",'iisi',$patient_id,$doctor_id,$time,$dur)->close();
    flash('ok','OT scheduled'); header('Location: admin.php?tab=ot'); exit;
  }

  // FEEDBACK
  if(is_post() && ($_POST['entity']??'')==='feedback'){
    $patient_id=(int)($_POST['patient_id']??0); $rating=(int)($_POST['rating']??5); $comments=trim($_POST['comments']??'');
    q($mysqli,"INSERT INTO feedback(patient_id,rating,comments) VALUES(?,?,?)",'iis',$patient_id,$rating,$comments)->close();
    flash('ok','Feedback stored'); header('Location: admin.php?tab=feedback'); exit;
  }

  // RESOURCES
  if(is_post() && ($_POST['entity']??'')==='resource'){
    $title=trim($_POST['title']??''); $type=trim($_POST['type']??'article'); $url=trim($_POST['url']??'');
    q($mysqli,"INSERT INTO resources(title,type,url) VALUES(?,?,?)",'sss',$title,$type,$url)->close();
    flash('ok','Resource added'); header('Location: admin.php?tab=resources'); exit;
  }

} catch(Throwable $e){ flash('err',$e->getMessage()); }

/* ----------------------------- Fetch reference data for forms --------------- */
$usersAll=[]; $patientsAll=[]; $admissionsAll=[]; $doctorsAll=[];
$rs=$mysqli->query("SELECT id,name,role FROM users ORDER BY name ASC"); if($rs){ while($r=$rs->fetch_assoc()){ $usersAll[]=$r; if($r['role']==='doctor') $doctorsAll[]=$r; } }
$rs=$mysqli->query("SELECT id,user_id FROM patients ORDER BY id DESC"); if($rs){ while($r=$rs->fetch_assoc()){ $patientsAll[]=$r; } }
$rs=$mysqli->query("SELECT id,patient_id,status FROM admissions ORDER BY id DESC LIMIT 200"); if($rs){ while($r=$rs->fetch_assoc()){ $admissionsAll[]=$r; } }

/* ----------------------------- Page query helpers (lists) -------------------- */
function fetch_rows($mysqli,$sql){ $out=[]; $rs=$mysqli->query($sql); if($rs){ while($r=$rs->fetch_assoc()) $out[]=$r; } return $out; }

$rows_overview = [
  'patients'   => fetch_rows($mysqli, "SELECT COUNT(*) c FROM patients")[0]['c'] ?? 0,
  'admissions' => fetch_rows($mysqli, "SELECT COUNT(*) c FROM admissions WHERE status='admitted'")[0]['c'] ?? 0,
  'inventory'  => fetch_rows($mysqli, "SELECT COUNT(*) c FROM inventory")[0]['c'] ?? 0,
  'pending_lab'=> fetch_rows($mysqli, "SELECT COUNT(*) c FROM lab_orders WHERE status<>'completed'")[0]['c'] ?? 0,
];

$flashOk = flash('ok'); $flashErr = flash('err');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Admin Panel — RestorativeCare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    :root{ --glass-bg: rgba(255,255,255,0.15); --glass-border: rgba(255,255,255,0.22); --accent:#06b6d4; --accent-dark:#0891b2; }
    body{ font-family:'Inter',sans-serif; background: radial-gradient(circle at 10% 10%, #f0fbff 0%, #ffffff 30%, #f8fbff 100%); color:#0f172a; }
    .glass{ background:var(--glass-bg); border:1px solid var(--glass-border); backdrop-filter: blur(8px) saturate(120%); -webkit-backdrop-filter: blur(8px) saturate(120%); }
    .card{ box-shadow: 0 10px 30px rgba(14,30,37,.08); border-radius: 14px; }
    .navlink{ display:flex; align-items:center; gap:.6rem; padding:.6rem .8rem; border-radius: .6rem; }
    .navlink.active{ background:white; color:#0891b2; font-weight:700; }
    .btn{ padding:.55rem .9rem; border-radius:.6rem; }
  </style>
</head>
<body class="p-6">

<header class="flex items-center justify-between mb-6">
  <div class="flex items-center gap-3">
    <div class="text-2xl font-extrabold text-cyan-600">RestorativeCare</div>
    <div class="text-sm text-gray-500">— Admin Panel</div>
  </div>
  <div class="flex items-center gap-3">
    <div class="text-sm text-gray-600">Signed in as <span class="font-semibold"><?php echo esc($userName); ?></span> (<?php echo esc($userRole); ?>)</div>
    <a href="logout.php" class="text-sm text-cyan-600 hover:underline">Logout</a>
    <a href="index.php" class="text-sm text-cyan-600 hover:underline">Patient View</a>
  </div>
</header>

<?php if($flashOk): ?><div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg">✅ <?php echo esc($flashOk); ?></div><?php endif; ?>
<?php if($flashErr): ?><div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg">⚠️ <?php echo esc($flashErr); ?></div><?php endif; ?>

<div class="grid grid-cols-12 gap-6">
  <!-- Sidebar -->
  <aside class="col-span-12 md:col-span-3 lg:col-span-2 glass card p-4">
    <?php
      $tabLabels = [
        'overview'=>'📊 Overview',
        'users'=>'👤 Users',
        'patients'=>'🧑‍⚕️ Patients',
        'admissions'=>'🏥 Admissions',
        'transfers'=>'🔁 Transfers',
        'visitors'=>'🎫 Visitors',
        'vitals'=>'❤️ Vitals',
        'lab'=>'🧪 Lab/Radiology',
        'insurance'=>'📄 Insurance',
        'payments'=>'💳 Payments',
        'schedule'=>'📅 Duty Roster',
        'inventory'=>'📦 Inventory',
        'ot'=>'🩺 OT Schedule',
        'reminder'=>'⏰ Add Reminder',
        'feedback'=>'⭐ Feedback',
        'resources'=>'📚 Health Library',
        'audit'=>'📝 Audit Logs',
        'settings'=>'⚙️ Settings'
      ];
      foreach($validTabs as $id){
        $active = $tab===$id ? 'active' : '';
        echo '<a class="navlink '.$active.'" href="admin.php?tab='.esc($id).'">'.($tabLabels[$id]??esc($id)).'</a>';
      }
    ?>
  </aside>

  <!-- Main -->
  <main class="col-span-12 md:col-span-9 lg:col-span-10 space-y-6">

    <?php if($tab==='overview'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-4">Snapshot</h2>
        <div class="grid md:grid-cols-4 gap-4">
          <div class="glass p-4 rounded-lg"><div class="text-sm text-gray-500">Patients</div><div class="text-2xl font-bold"><?php echo (int)$rows_overview['patients']; ?></div></div>
          <div class="glass p-4 rounded-lg"><div class="text-sm text-gray-500">Admitted</div><div class="text-2xl font-bold"><?php echo (int)$rows_overview['admissions']; ?></div></div>
          <div class="glass p-4 rounded-lg"><div class="text-sm text-gray-500">Inventory Items</div><div class="text-2xl font-bold"><?php echo (int)$rows_overview['inventory']; ?></div></div>
          <div class="glass p-4 rounded-lg"><div class="text-sm text-gray-500">Pending Lab</div><div class="text-2xl font-bold"><?php echo (int)$rows_overview['pending_lab']; ?></div></div>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='patients'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Patients</h2>
        <?php if($IS_PATIENT): ?>
          <!-- Patient can only view their own details -->
          <?php
            $rs=$mysqli->query("SELECT p.id, p.user_id, p.dob, p.gender, p.address, u.name uname FROM patients p JOIN users u ON u.id=p.user_id WHERE p.user_id=$userId LIMIT 1");
            $p=$rs?$rs->fetch_assoc():null;
            if($p):
          ?>
          <div class="p-4 bg-white/40 rounded-lg">
            <div><strong>Name:</strong> <?php echo esc($p['uname']); ?></div>
            <div><strong>DOB:</strong> <?php echo esc($p['dob']); ?></div>
            <div><strong>Gender:</strong> <?php echo esc($p['gender']); ?></div>
            <div><strong>Address:</strong> <?php echo esc($p['address']); ?></div>
          </div>
          <?php else: ?>
            <div class="p-4 bg-red-50 text-red-700 rounded-lg">No patient record found.</div>
          <?php endif; ?>
        <?php else: ?>
          <!-- Other roles: show full patient table and forms as before -->
          <form method="post" class="grid md:grid-cols-6 gap-3 bg-white/40 p-3 rounded-lg">
            <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>"/>
            <input type="hidden" name="entity" value="patient"/>
            <input type="hidden" name="action" value="create"/>
            <select class="p-2 border rounded" name="user_id" required>
              <option value="">Select User</option>
              <?php foreach($usersAll as $u) echo '<option value="'.(int)$u['id'].'">'.esc($u['name']).' ('.esc($u['role']).')</option>'; ?>
            </select>
            <input class="p-2 border rounded" type="date" name="dob">
            <select class="p-2 border rounded" name="gender"><option value="male">male</option><option value="female">female</option><option value="other">other</option></select>
            <input class="p-2 border rounded col-span-2" name="address" placeholder="Address">
            <button class="btn bg-cyan-500 text-white">Add Patient</button>
          </form>
          <div class="overflow-x-auto mt-4">
            <table class="min-w-full text-sm">
              <thead><tr class="text-left border-b"><th class="p-2">ID</th><th class="p-2">User</th><th class="p-2">DOB</th><th class="p-2">Gender</th><th class="p-2">Address</th><th class="p-2">Edit</th></tr></thead>
              <tbody>
              <?php $rs=$mysqli->query("SELECT p.id, p.user_id, p.dob, p.gender, p.address, u.name uname FROM patients p JOIN users u ON u.id=p.user_id ORDER BY p.id DESC LIMIT 500"); while($p=$rs->fetch_assoc()): ?>
                <tr class="border-b hover:bg-white/50">
                  <td class="p-2"><?php echo (int)$p['id']; ?></td>
                  <td class="p-2"><?php echo esc($p['uname']); ?></td>
                  <td class="p-2"><?php echo esc($p['dob']); ?></td>
                  <td class="p-2"><?php echo esc($p['gender']); ?></td>
                  <td class="p-2"><?php echo esc($p['address']); ?></td>
                  <td class="p-2">
                    <details>
                      <summary class="cursor-pointer text-cyan-700">Edit</summary>
                      <form method="post" class="mt-2 grid md:grid-cols-6 gap-2">
                        <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
                        <input type="hidden" name="entity" value="patient">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                        <select class="p-2 border rounded" name="user_id">
                          <?php foreach($usersAll as $u) echo '<option '.($p['user_id']==$u['id']?'selected':'').' value="'.(int)$u['id'].'">'.esc($u['name']).' ('.esc($u['role']).')</option>'; ?>
                        </select>
                        <input class="p-2 border rounded" type="date" name="dob" value="<?php echo esc($p['dob']); ?>">
                        <select class="p-2 border rounded" name="gender">
                          <?php foreach(['male','female','other'] as $g) echo '<option '.($p['gender']===$g?'selected':'').' value="'.esc($g).'">'.esc($g).'</option>'; ?>
                        </select>
                        <input class="p-2 border rounded col-span-2" name="address" value="<?php echo esc($p['address']); ?>">
                        <button class="btn bg-cyan-500 text-white">Save</button>
                      </form>
                    </details>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <?php if($tab==='admissions'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Admissions</h2>
        <form method="post" class="grid md:grid-cols-4 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>"/>
          <input type="hidden" name="entity" value="admission"/>
          <input type="hidden" name="action" value="admit"/>
          <select class="p-2 border rounded" name="patient_id" required>
            <option value="">Select Patient</option>
            <?php foreach($patientsAll as $p) echo '<option value="'.(int)$p['id'].'">#'.(int)$p['id'].' (User #'.(int)$p['user_id'].')</option>'; ?>
          </select>
          <button class="btn bg-cyan-500 text-white">Quick Admit</button>
        </form>
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full text-sm">
            <thead><tr class="text-left border-b"><th class="p-2">ID</th><th class="p-2">Patient</th><th class="p-2">Admitted On</th><th class="p-2">Status</th><th class="p-2">Actions</th></tr></thead>
            <tbody>
              <?php $rs=$mysqli->query("SELECT a.id,a.patient_id,a.admitted_on,a.status,u.name uname FROM admissions a JOIN patients p ON p.id=a.patient_id JOIN users u ON u.id=p.user_id ORDER BY a.id DESC LIMIT 500"); while($a=$rs->fetch_assoc()): ?>
              <tr class="border-b hover:bg-white/50">
                <td class="p-2"><?php echo (int)$a['id']; ?></td>
                <td class="p-2"><?php echo esc($a['uname']); ?></td>
                <td class="p-2"><?php echo esc($a['admitted_on']); ?></td>
                <td class="p-2"><?php echo esc($a['status']); ?></td>
                <td class="p-2">
                  <?php if($a['status']!=='discharged'): ?>
                  <form method="post" onsubmit="return confirm('Mark discharged?')">
                    <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
                    <input type="hidden" name="entity" value="admission">
                    <input type="hidden" name="action" value="discharge">
                    <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                    <button class="btn bg-emerald-500 text-white">Discharge</button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='transfers'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Patient Transfers</h2>
        <form method="post" class="grid md:grid-cols-5 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>"/>
          <input type="hidden" name="entity" value="transfer"/>
          <select class="p-2 border rounded" name="patient_id" required>
            <option value="">Patient</option>
            <?php foreach($patientsAll as $p) echo '<option value="'.(int)$p['id'].'">#'.(int)$p['id'].'</option>'; ?>
          </select>
          <input class="p-2 border rounded" name="from_ward" placeholder="From ward">
          <input class="p-2 border rounded" name="to_ward" placeholder="To ward" required>
          <input class="p-2 border rounded" name="reason" placeholder="Reason">
          <button class="btn bg-cyan-500 text-white">Record Transfer</button>
        </form>
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full text-sm"><thead><tr class="text-left border-b"><th class="p-2">ID</th><th class="p-2">Patient</th><th class="p-2">From</th><th class="p-2">To</th><th class="p-2">When</th><th class="p-2">Reason</th></tr></thead>
            <tbody>
              <?php foreach(fetch_rows($mysqli,"SELECT t.*,u.name uname FROM patient_transfers t JOIN patients p ON p.id=t.patient_id JOIN users u ON u.id=p.user_id ORDER BY t.id DESC LIMIT 300") as $t): ?>
                <tr class="border-b hover:bg-white/50"><td class="p-2"><?php echo (int)$t['id']; ?></td><td class="p-2"><?php echo esc($t['uname']); ?></td><td class="p-2"><?php echo esc($t['from_ward']); ?></td><td class="p-2"><?php echo esc($t['to_ward']); ?></td><td class="p-2"><?php echo esc($t['transferred_at']); ?></td><td class="p-2"><?php echo esc($t['reason']); ?></td></tr>
              <?php endforeach; ?>
            </tbody></table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='visitors'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Visitor Management</h2>
        <form method="post" class="grid md:grid-cols-5 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
          <input type="hidden" name="entity" value="visitor">
          <select class="p-2 border rounded" name="patient_id" required>
            <?php foreach($patientsAll as $p) echo '<option value="'.(int)$p['id'].'">#'.(int)$p['id'].'</option>'; ?>
          </select>
          <input class="p-2 border rounded" name="name" placeholder="Visitor name" required>
          <input class="p-2 border rounded" name="relation" placeholder="Relation">
          <input class="p-2 border rounded" name="pass_qr_path" placeholder="QR path (optional)">
          <button class="btn bg-cyan-500 text-white">Issue Pass</button>
        </form>
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full text-sm"><thead><tr class="text-left border-b"><th class="p-2">ID</th><th class="p-2">Patient</th><th class="p-2">Name</th><th class="p-2">Relation</th><th class="p-2">Pass</th><th class="p-2">Time</th></tr></thead>
            <tbody>
              <?php foreach(fetch_rows($mysqli,"SELECT v.*,u.name uname FROM visitors v JOIN patients p ON p.id=v.patient_id JOIN users u ON u.id=p.user_id ORDER BY v.id DESC LIMIT 300") as $v): ?>
                <tr class="border-b hover:bg-white/50"><td class="p-2"><?php echo (int)$v['id']; ?></td><td class="p-2"><?php echo esc($v['uname']); ?></td><td class="p-2"><?php echo esc($v['name']); ?></td><td class="p-2"><?php echo esc($v['relation']); ?></td><td class="p-2"><?php echo esc($v['pass_qr_path']); ?></td><td class="p-2"><?php echo esc($v['visit_time']); ?></td></tr>
              <?php endforeach; ?>
            </tbody></table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='vitals'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Vitals</h2>
        <form method="post" class="grid md:grid-cols-7 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
          <input type="hidden" name="entity" value="vital">
          <select class="p-2 border rounded" name="patient_id" required>
            <?php foreach($patientsAll as $p) echo '<option value="'.(int)$p['id'].'">#'.(int)$p['id'].'</option>'; ?>
          </select>
          <input class="p-2 border rounded" name="bp" placeholder="BP (e.g., 120/80)">
          <input class="p-2 border rounded" name="heart_rate" type="number" placeholder="HR">
          <input class="p-2 border rounded" name="temperature" type="number" step="0.1" placeholder="Temp">
          <input class="p-2 border rounded" name="spo2" type="number" placeholder="SpO2">
          <input class="p-2 border rounded" name="respiratory_rate" type="number" placeholder="RR">
          <button class="btn bg-cyan-500 text-white">Save</button>
        </form>
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full text-sm"><thead><tr class="text-left border-b"><th class="p-2">Time</th><th class="p-2">Patient</th><th class="p-2">BP</th><th class="p-2">HR</th><th class="p-2">Temp</th><th class="p-2">SpO2</th><th class="p-2">RR</th></tr></thead>
          <tbody>
          <?php foreach(fetch_rows($mysqli,"SELECT v.*,u.name uname FROM patient_vitals v JOIN patients p ON p.id=v.patient_id JOIN users u ON u.id=p.user_id ORDER BY v.logged_at DESC LIMIT 300") as $v): ?>
            <tr class="border-b hover:bg-white/50"><td class="p-2"><?php echo esc($v['logged_at']); ?></td><td class="p-2"><?php echo esc($v['uname']); ?></td><td class="p-2"><?php echo esc($v['bp']); ?></td><td class="p-2"><?php echo (int)$v['heart_rate']; ?></td><td class="p-2"><?php echo esc($v['temperature']); ?></td><td class="p-2"><?php echo (int)$v['spo2']; ?></td><td class="p-2"><?php echo (int)$v['respiratory_rate']; ?></td></tr>
          <?php endforeach; ?>
          </tbody></table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='lab'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Lab & Radiology</h2>
        <form method="post" class="grid md:grid-cols-4 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
          <input type="hidden" name="entity" value="lab_order">
          <select class="p-2 border rounded" name="admission_id" required>
            <?php foreach($admissionsAll as $a) echo '<option value="'.(int)$a['id'].'">Admission #'.(int)$a['id'].' ('.esc($a['status']).')</option>'; ?>
          </select>
          <input class="p-2 border rounded" name="test_name" placeholder="Test name" required>
          <select class="p-2 border rounded" name="ordered_by">
            <?php foreach($doctorsAll as $d) echo '<option value="'.(int)$d['id'].'">'.esc($d['name']).'</option>'; ?>
          </select>
          <button class="btn bg-cyan-500 text-white">Order Test</button>
        </form>
        <div class="grid md:grid-cols-2 gap-4 mt-4">
          <div class="glass p-3 rounded-lg">
            <div class="font-semibold mb-2">Open Orders</div>
            <ul class="space-y-2">
              <?php foreach(fetch_rows($mysqli,"SELECT o.*,u.name dname FROM lab_orders o LEFT JOIN users u ON u.id=o.ordered_by WHERE o.status<>'completed' ORDER BY o.id DESC LIMIT 200") as $o): ?>
                <li class="p-2 bg-white/50 rounded">
                  <div class="text-sm"><span class="font-semibold">#<?php echo (int)$o['id']; ?></span> — <?php echo esc($o['test_name']); ?> • by <?php echo esc($o['dname']); ?> • <?php echo esc($o['status']); ?></div>
                  <details class="mt-1">
                    <summary class="text-cyan-700 cursor-pointer">Upload Result</summary>
                    <form method="post" class="mt-2 grid md:grid-cols-3 gap-2">
                      <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
                      <input type="hidden" name="entity" value="lab_result">
                      <input type="hidden" name="order_id" value="<?php echo (int)$o['id']; ?>">
                      <input class="p-2 border rounded col-span-2" name="file_path" placeholder="File path (PDF/image)">
                      <textarea class="p-2 border rounded col-span-3" name="result" placeholder="Result text"></textarea>
                      <button class="btn bg-emerald-500 text-white col-span-1">Mark Completed</button>
                    </form>
                  </details>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div class="glass p-3 rounded-lg">
            <div class="font-semibold mb-2">Recent Results</div>
            <ul class="space-y-2">
              <?php foreach(fetch_rows($mysqli,"SELECT r.*,o.test_name FROM lab_results r JOIN lab_orders o ON o.id=r.order_id ORDER BY r.id DESC LIMIT 200") as $r): ?>
                <li class="p-2 bg-white/50 rounded">
                  <div class="text-sm"><span class="font-semibold">#<?php echo (int)$r['id']; ?></span> — <?php echo esc($r['test_name']); ?> • <a class="text-cyan-700" href="<?php echo esc($r['file_path']); ?>" target="_blank">file</a></div>
                  <div class="text-xs text-gray-600 truncate"><?php echo esc($r['result']); ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='insurance'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Insurance Claims</h2>
        <form method="post" class="grid md:grid-cols-4 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
          <input type="hidden" name="entity" value="insurance">
          <select class="p-2 border rounded" name="patient_id" required>
            <?php foreach($patientsAll as $p) echo '<option value="'.(int)$p['id'].'">#'.(int)$p['id'].'</option>'; ?>
          </select>
          <input class="p-2 border rounded" name="provider" placeholder="Provider" required>
          <input class="p-2 border rounded" type="number" step="0.01" name="claim_amount" placeholder="Amount" required>
          <button class="btn bg-cyan-500 text-white">Submit Claim</button>
        </form>
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full text-sm"><thead><tr class="text-left border-b"><th class="p-2">ID</th><th class="p-2">Patient</th><th class="p-2">Provider</th><th class="p-2">Amount</th><th class="p-2">Status</th><th class="p-2">Created</th></tr></thead>
          <tbody>
            <?php foreach(fetch_rows($mysqli,"SELECT i.*,u.name uname FROM insurance_claims i JOIN patients p ON p.id=i.patient_id JOIN users u ON u.id=p.user_id ORDER BY i.id DESC LIMIT 300") as $i): ?>
              <tr class="border-b hover:bg-white/50"><td class="p-2"><?php echo (int)$i['id']; ?></td><td class="p-2"><?php echo esc($i['uname']); ?></td><td class="p-2"><?php echo esc($i['provider']); ?></td><td class="p-2">₹<?php echo number_format((float)$i['claim_amount'],2); ?></td><td class="p-2"><?php echo esc($i['claim_status']); ?></td><td class="p-2"><?php echo esc($i['created_at']); ?></td></tr>
            <?php endforeach; ?>
          </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='payments'): ?>
              <form method="post" class="grid md:grid-cols-5 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
          <input type="hidden" name="entity" value="payment">
          <select class="p-2 border rounded" name="patient_id" required>
            <option value="">Patient</option>
            <?php foreach($patientsAll as $p) echo '<option value="'.(int)$p['id'].'">#'.(int)$p['id'].'</option>'; ?>
          </select>
          <input class="p-2 border rounded" type="number" step="0.01" name="amount" placeholder="Amount" required>
          <select class="p-2 border rounded" name="method">
            <option value="cash">cash</option>
            <option value="card">card</option>
            <option value="upi">upi</option>
            <option value="bank">bank</option>
          </select>
          <select class="p-2 border rounded" name="status">
            <option value="paid">paid</option>
            <option value="pending">pending</option>
            <option value="failed">failed</option>
            <option value="refunded">refunded</option>
          </select>
          <button class="btn bg-cyan-500 text-white">Record Payment</button>
        </form>

        <div class="overflow-x-auto mt-4">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left border-b">
                <th class="p-2">ID</th>
                <th class="p-2">Patient</th>
                <th class="p-2">Amount</th>
                <th class="p-2">Method</th>
                <th class="p-2">Status</th>
                <th class="p-2">Created</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $rows = fetch_rows($mysqli,"SELECT pay.*, u.name uname
                  FROM payments pay
                  JOIN patients p ON p.id = pay.patient_id
                  JOIN users u ON u.id = p.user_id
                  ORDER BY pay.id DESC LIMIT 500");
                foreach($rows as $row):
              ?>
              <tr class="border-b hover:bg-white/50">
                <td class="p-2"><?php echo (int)$row['id']; ?></td>
                <td class="p-2"><?php echo esc($row['uname']); ?> <span class="text-gray-500">(#<?php echo (int)$row['patient_id']; ?>)</span></td>
                <td class="p-2">₹<?php echo number_format((float)$row['amount'],2); ?></td>
                <td class="p-2"><?php echo esc($row['method']); ?></td>
                <td class="p-2">
                  <span class="px-2 py-0.5 rounded text-xs <?php
                    echo $row['status']==='paid' ? 'bg-emerald-100 text-emerald-700' :
                         ($row['status']==='pending' ? 'bg-amber-100 text-amber-700' :
                         ($row['status']==='failed' ? 'bg-rose-100 text-rose-700' : 'bg-sky-100 text-sky-700'));
                  ?>"><?php echo esc($row['status']); ?></span>
                </td>
                <td class="p-2"><?php echo esc($row['created_at'] ?? ''); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='schedule'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Duty Roster</h2>
        <form method="post" class="grid md:grid-cols-5 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
          <input type="hidden" name="entity" value="schedule">
          <select class="p-2 border rounded" name="staff_id" required>
            <option value="">Staff</option>
            <?php foreach($usersAll as $u) echo '<option value="'.(int)$u['id'].'">'.esc($u['name']).' ('.esc($u['role']).')</option>'; ?>
          </select>
          <input class="p-2 border rounded" type="datetime-local" name="shift_start" required>
          <input class="p-2 border rounded" type="datetime-local" name="shift_end" required>
          <select class="p-2 border rounded" name="role">
            <option value="support">support</option>
            <option value="doctor">doctor</option>
            <option value="nurse">nurse</option>
            <option value="admin">admin</option>
          </select>
          <button class="btn bg-cyan-500 text-white">Add Shift</button>
        </form>
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full text-sm">
            <thead><tr class="text-left border-b">
              <th class="p-2">ID</th><th class="p-2">Staff</th><th class="p-2">Role</th><th class="p-2">Start</th><th class="p-2">End</th>
            </tr></thead>
            <tbody>
              <?php foreach(fetch_rows($mysqli,"SELECT s.*, u.name uname FROM staff_schedule s LEFT JOIN users u ON u.id=s.staff_id ORDER BY s.id DESC LIMIT 400") as $s): ?>
                <tr class="border-b hover:bg-white/50">
                  <td class="p-2"><?php echo (int)$s['id']; ?></td>
                  <td class="p-2"><?php echo esc($s['uname'] ?? ('#'.(int)$s['staff_id'])); ?></td>
                  <td class="p-2"><?php echo esc($s['role']); ?></td>
                  <td class="p-2"><?php echo esc($s['shift_start']); ?></td>
                  <td class="p-2"><?php echo esc($s['shift_end']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='inventory'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Inventory</h2>
        <form method="post" class="grid md:grid-cols-4 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
          <input type="hidden" name="entity" value="inventory">
          <input class="p-2 border rounded md:col-span-2" name="item_name" placeholder="Item name" required>
          <input class="p-2 border rounded" type="number" name="stock" placeholder="Stock" required>
          <input class="p-2 border rounded" type="number" name="threshold" placeholder="Low-stock threshold" value="5">
          <button class="btn bg-cyan-500 text-white md:col-span-1">Add Item</button>
        </form>
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full text-sm">
            <thead><tr class="text-left border-b">
              <th class="p-2">ID</th><th class="p-2">Item</th><th class="p-2">Stock</th><th class="p-2">Threshold</th>
            </tr></thead>
            <tbody>
              <?php foreach(fetch_rows($mysqli,"SELECT * FROM inventory ORDER BY id DESC LIMIT 500") as $it): ?>
                <tr class="border-b hover:bg-white/50 <?php echo ((int)$it['stock'] <= (int)$it['threshold']) ? 'bg-amber-50' : ''; ?>">
                  <td class="p-2"><?php echo (int)$it['id']; ?></td>
                  <td class="p-2"><?php echo esc($it['item_name']); ?></td>
                  <td class="p-2"><?php echo (int)$it['stock']; ?></td>
                  <td class="p-2"><?php echo (int)$it['threshold']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='ot'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">OT Schedule</h2>
        <form method="post" class="grid md:grid-cols-5 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
          <input type="hidden" name="entity" value="ot">
          <select class="p-2 border rounded" name="patient_id" required>
            <option value="">Patient</option>
            <?php foreach($patientsAll as $p) echo '<option value="'.(int)$p['id'].'">#'.(int)$p['id'].'</option>'; ?>
          </select>
          <select class="p-2 border rounded" name="doctor_id" required>
            <option value="">Doctor</option>
            <?php foreach($doctorsAll as $d) echo '<option value="'.(int)$d['id'].'">'.esc($d['name']).'</option>'; ?>
          </select>
          <input class="p-2 border rounded" type="datetime-local" name="scheduled_time" required>
          <input class="p-2 border rounded" type="number" name="duration_minutes" placeholder="Duration (min)" value="60">
          <button class="btn bg-cyan-500 text-white">Schedule</button>
        </form>
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full text-sm">
            <thead><tr class="text-left border-b">
              <th class="p-2">ID</th><th class="p-2">Patient</th><th class="p-2">Doctor</th><th class="p-2">When</th><th class="p-2">Duration</th>
            </tr></thead>
            <tbody>
              <?php
                $rows = fetch_rows($mysqli,"SELECT ot.*, up.name pname, ud.name dname
                  FROM ot_schedule ot
                  JOIN patients p ON p.id = ot.patient_id
                  JOIN users up ON up.id = p.user_id
                  JOIN users ud ON ud.id = ot.doctor_id
                  ORDER BY ot.scheduled_time DESC, ot.id DESC LIMIT 300");
                foreach($rows as $r):
              ?>
              <tr class="border-b hover:bg-white/50">
                <td class="p-2"><?php echo (int)$r['id']; ?></td>
                <td class="p-2"><?php echo esc($r['pname']); ?></td>
                <td class="p-2"><?php echo esc($r['dname']); ?></td>
                <td class="p-2"><?php echo esc($r['scheduled_time']); ?></td>
                <td class="p-2"><?php echo (int)$r['duration_minutes']; ?> min</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='feedback'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Patient Feedback</h2>
        <form method="post" class="grid md:grid-cols-4 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
          <input type="hidden" name="entity" value="feedback">
          <select class="p-2 border rounded" name="patient_id" required>
            <option value="">Patient</option>
            <?php foreach($patientsAll as $p) echo '<option value="'.(int)$p['id'].'">#'.(int)$p['id'].'</option>'; ?>
          </select>
          <input class="p-2 border rounded" type="number" min="1" max="5" name="rating" placeholder="Rating (1-5)" value="5">
          <input class="p-2 border rounded md:col-span-2" name="comments" placeholder="Comments">
          <button class="btn bg-cyan-500 text-white">Save</button>
        </form>
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full text-sm">
            <thead><tr class="text-left border-b">
              <th class="p-2">ID</th><th class="p-2">Patient</th><th class="p-2">Rating</th><th class="p-2">Comments</th><th class="p-2">Created</th>
            </tr></thead>
            <tbody>
              <?php foreach(fetch_rows($mysqli,"SELECT f.*, u.name uname
                FROM feedback f
                JOIN patients p ON p.id=f.patient_id
                JOIN users u ON u.id=p.user_id
                ORDER BY f.id DESC LIMIT 300") as $f): ?>
                <tr class="border-b hover:bg-white/50">
                  <td class="p-2"><?php echo (int)$f['id']; ?></td>
                  <td class="p-2"><?php echo esc($f['uname']); ?></td>
                  <td class="p-2"><?php echo (int)$f['rating']; ?>/5</td>
                  <td class="p-2 max-w-xl truncate" title="<?php echo esc($f['comments']); ?>"><?php echo esc($f['comments']); ?></td>
                  <td class="p-2"><?php echo esc($f['created_at'] ?? ''); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='resources'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Health Library</h2>
        <form method="post" class="grid md:grid-cols-5 gap-3 bg-white/40 p-3 rounded-lg">
          <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
          <input type="hidden" name="entity" value="resource">
          <input class="p-2 border rounded md:col-span-2" name="title" placeholder="Title" required>
          <select class="p-2 border rounded" name="type">
            <option value="article">article</option>
            <option value="video">video</option>
            <option value="pdf">pdf</option>
            <option value="link">link</option>
          </select>
          <input class="p-2 border rounded md:col-span-2" name="url" placeholder="URL" required>
          <button class="btn bg-cyan-500 text-white">Add</button>
        </form>
        <div class="overflow-x-auto mt-4">
          <table class="min-w-full text-sm">
            <thead><tr class="text-left border-b"><th class="p-2">ID</th><th class="p-2">Title</th><th class="p-2">Type</th><th class="p-2">URL</th><th class="p-2">Created</th></tr></thead>
            <tbody>
              <?php foreach(fetch_rows($mysqli,"SELECT * FROM resources ORDER BY id DESC LIMIT 300") as $r): ?>
                <tr class="border-b hover:bg-white/50">
                  <td class="p-2"><?php echo (int)$r['id']; ?></td>
                  <td class="p-2"><?php echo esc($r['title']); ?></td>
                  <td class="p-2"><?php echo esc($r['type']); ?></td>
                  <td class="p-2"><a class="text-cyan-700 underline" href="<?php echo esc($r['url']); ?>" target="_blank"><?php echo esc($r['url']); ?></a></td>
                  <td class="p-2"><?php echo esc($r['created_at'] ?? ''); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='audit'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Audit Logs (rollup)</h2>
        <p class="text-sm text-gray-600 mb-3">Quick rollup of recent events pulled from multiple modules. For a full audit trail, add a dedicated <code>audit_logs</code> table and write to it in each action.</p>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead><tr class="text-left border-b">
              <th class="p-2">When</th><th class="p-2">Event</th>
            </tr></thead>
            <tbody>
              <?php
                // Build a lightweight union of recent events from key tables using available timestamp-like columns
                $sqls = [
                  "SELECT admitted_on AS ts, CONCAT('Admission #', a.id, ' for patient #', a.patient_id, ' (', a.status, ')') AS msg FROM admissions a ORDER BY a.id DESC LIMIT 50",
                  "SELECT transferred_at AS ts, CONCAT('Transfer #', t.id, ' patient #', t.patient_id, ' ', t.from_ward,' → ',t.to_ward) AS msg FROM patient_transfers t ORDER BY t.id DESC LIMIT 50",
                  "SELECT visit_time AS ts, CONCAT('Visitor #', v.id, ' for patient #', v.patient_id, ': ', v.name) AS msg FROM visitors v ORDER BY v.id DESC LIMIT 50",
                  "SELECT logged_at AS ts, CONCAT('Vitals #', v.id, ' for patient #', v.patient_id) AS msg FROM patient_vitals v ORDER BY v.id DESC LIMIT 50",
                  "SELECT created_at AS ts, CONCAT('Insurance claim #', i.id, ' for patient #', i.patient_id, ' (', i.provider, ')') AS msg FROM insurance_claims i ORDER BY i.id DESC LIMIT 50",
                  "SELECT created_at AS ts, CONCAT('Payment #', pay.id, ' for patient #', pay.patient_id, ' ₹', FORMAT(pay.amount,2)) AS msg FROM payments pay ORDER BY pay.id DESC LIMIT 50",
                  "SELECT created_at AS ts, CONCAT('Feedback #', f.id, ' for patient #', f.patient_id, ' rating ', f.rating) AS msg FROM feedback f ORDER BY f.id DESC LIMIT 50",
                  "SELECT scheduled_time AS ts, CONCAT('OT #', ot.id, ' patient #', ot.patient_id, ' with doctor #', ot.doctor_id) AS msg FROM ot_schedule ot ORDER BY ot.id DESC LIMIT 50",
                  "SELECT created_at AS ts, CONCAT('Resource #', r.id, ' added: ', r.title) AS msg FROM resources r ORDER BY r.id DESC LIMIT 50",
                  "SELECT created_at AS ts, CONCAT('Shift #', s.id, ' for staff #', s.staff_id) AS msg FROM staff_schedule s ORDER BY s.id DESC LIMIT 50"
                ];
                $union = implode(" UNION ALL ", $sqls);
                // Wrap to sort by timestamp desc, guard for NULLs
                $auditRows = fetch_rows($mysqli, "SELECT * FROM ($union) x ORDER BY COALESCE(ts,'1970-01-01 00:00:00') DESC LIMIT 200");
                foreach($auditRows as $a):
              ?>
                <tr class="border-b hover:bg-white/50">
                  <td class="p-2 whitespace-nowrap"><?php echo esc($a['ts']); ?></td>
                  <td class="p-2"><?php echo esc($a['msg']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    <?php if($tab==='settings'): ?>
      <section class="glass card p-5">
        <h2 class="text-xl font-semibold mb-3">Settings</h2>
        <div class="grid md:grid-cols-2 gap-6">
          <div class="glass p-4 rounded-lg">
            <div class="font-semibold mb-2">Your Account</div>
            <form method="post" class="grid gap-3">
              <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
              <input type="hidden" name="entity" value="user">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id" value="<?php echo (int)$userId; ?>">
              <input class="p-2 border rounded" name="name" value="<?php echo esc($userName); ?>" placeholder="Name">
              <input class="p-2 border rounded" name="email" value="<?php echo esc($currentUser['email'] ?? ''); ?>" placeholder="Email">
              <input class="p-2 border rounded" name="phone" value="<?php echo esc($currentUser['phone'] ?? ''); ?>" placeholder="Phone">
              <?php if($IS_SUPER): ?>
                <select class="p-2 border rounded" name="role">
                  <?php foreach(['patient','doctor','nurse','admin','superadmin'] as $r) echo '<option '.(($currentUser['role']??'')===$r?'selected':'').' value="'.esc($r).'">'.esc($r).'</option>'; ?>
                </select>
              <?php else: ?>
                <input type="hidden" name="role" value="<?php echo esc($currentUser['role'] ?? 'admin'); ?>">
                <div class="text-xs text-gray-500">Only superadmins can change roles.</div>
              <?php endif; ?>
              <input class="p-2 border rounded" type="password" name="password" placeholder="New password (optional)">
              <button class="btn bg-cyan-500 text-white w-max">Save</button>
            </form>
          </div>

          <div class="glass p-4 rounded-lg">
            <div class="font-semibold mb-2">About</div>
            <ul class="text-sm text-gray-700 space-y-1">
              <li>Environment: <span class="font-medium">MySQL @ <?php echo esc($DB_HOST.':'.$DB_PORT); ?></span></li>
              <li>Database: <span class="font-medium"><?php echo esc($DB_NAME); ?></span></li>
              <li>Signed in as: <span class="font-medium"><?php echo esc($userName.' ('.$userRole.')'); ?></span></li>
            </ul>
            <div class="text-xs text-gray-500 mt-3">Tip: For full auditing, create an <code>audit_logs</code> table and insert one row per change in the POST handlers.</div>
          </div>
        </div>
      </section>
    <?php endif; ?>

  </main>
</div>

</body>
</html>
