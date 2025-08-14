<?php
// index.php — RestorativeCare (Premium Home) — single-file drop-in
// NOTE: keep every page in the same folder as requested (admit.php, schedule.php, dashboard.php, etc.)
$year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>RestorativeCare — Patient-First, Tech-Enabled Care</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Lottie for hero animation -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>

  <!-- Icons (Feather) -->
  <script src="https://unpkg.com/feather-icons"></script>

  <style>
    :root{
      --bg1:#f0faff;
      --bg2:#ffffff;
      --ink:#052026;
      --muted:#6b7280;
      --accent:#06b6d4;
      --accent-2:#0ea5b7;
      --glass:rgba(255,255,255,0.15);
      --glass-border:rgba(255,255,255,0.25);
      --shadow:rgba(6,20,28,0.18);
    }
    html,body{height:100%}
    body{
      font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
      color:var(--ink);
      background: radial-gradient(circle at -10% -10%, var(--bg1), var(--bg2) 45%);
      overflow-x:hidden;
    }
    .glass{
      background:var(--glass);
      backdrop-filter:blur(10px);
      -webkit-backdrop-filter:blur(10px);
      border:1px solid var(--glass-border);
      border-radius:14px;
    }
    .btn{
      display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
      padding:.8rem 1.1rem;border-radius:12px;font-weight:700;cursor:pointer;
      transition:transform .15s ease, box-shadow .15s ease, background .3s ease;
      box-shadow:0 10px 30px rgba(6,182,212,0.10);
      user-select:none;
    }
    .btn-primary{
      color:white;background:linear-gradient(90deg,var(--accent),var(--accent-2));
    }
    .btn-primary:hover{ transform:translateY(-2px) scale(1.02); }
    .btn-ghost{
      color:var(--accent-2); background:rgba(255,255,255,.9); border:1px solid rgba(6,182,212,.15);
    }
    .btn-ghost:hover{ transform:translateY(-2px); }
    .link{ color:var(--accent-2); font-weight:600 }
    .muted{ color:var(--muted) }
    .shadow-deep{ box-shadow:0 30px 80px rgba(6,20,28,0.08) }

    /* ====== NAVBAR ====== */
    .nav-wrap{ position:sticky; top:12px; z-index:50 }
    .nav{ display:flex; align-items:center; justify-content:space-between; gap:16px; }
    .nav-brand{
      display:flex;align-items:center;gap:12px;font-weight:900;font-size:1.45rem;color:var(--accent-2)
    }
    .nav-brand-badge{
      width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;
      color:white;font-weight:800;background:linear-gradient(135deg,#60a5fa,var(--accent));
      box-shadow:0 12px 26px rgba(6,182,212,.22)
    }
    .nav-links a{
      padding:.55rem .9rem;border-radius:12px; font-weight:600; color:#0f3540; transition:background .25s ease, transform .15s ease;
    }
    .nav-links a:hover{
      background:rgba(6,182,212,.08);
      transform:translateY(-2px);
    }
    .nav-cta .btn{ box-shadow:0 18px 40px rgba(6,182,212,.18) }

    /* ====== BACKGROUND FLOATERS ====== */
    .floaters{
      position:fixed; inset:0; z-index:-1; pointer-events:none; overflow:hidden;
    }
    .floater{
      position:absolute; width:140px;height:140px;border-radius:999px;opacity:.12;filter:blur(6px);
      background:radial-gradient(circle at 30% 30%, #a7f3d0,#22d3ee);
      animation: drift 22s ease-in-out infinite;
    }
    .floater:nth-child(2){ left:12%; top:22%; width:180px;height:180px; animation-duration:28s; background:radial-gradient(circle at 30% 30%, #bfdbfe,#67e8f9);}
    .floater:nth-child(3){ left:78%; top:18%; width:220px;height:220px; animation-duration:26s; background:radial-gradient(circle at 30% 30%, #c7d2fe,#5eead4);}
    .floater:nth-child(4){ left:70%; top:72%; width:160px;height:160px; animation-duration:30s; background:radial-gradient(circle at 30% 30%, #99f6e4,#93c5fd);}
    @keyframes drift{
      0%,100%{ transform: translate3d(0,0,0) scale(1) }
      50%{ transform: translate3d(-20px,20px,0) scale(1.06) }
    }

    /* ====== HERO ====== */
    .hero{
      display:grid; grid-template-columns: 1.15fr .85fr; gap:26px; align-items:center;
    }
    @media (max-width: 1024px){ .hero{ grid-template-columns: 1fr; } }
    .hero-title{ font-weight:900; line-height:1.05; letter-spacing:-.02em; }
    .hero-title .accent{ color:var(--accent-2) }
    .hero-card{
      padding:18px; border-radius:18px; background:linear-gradient(180deg,rgba(255,255,255,.6),rgba(255,255,255,.35));
      border:1px solid rgba(6,182,212,.12); backdrop-filter: blur(12px);
    }

    /* ====== 3D FEATURE CAROUSEL ====== */
    .feature-stage{
      position:relative; height:560px; perspective:1400px; overflow:visible;
    }
    .carousel{
      position:absolute; top:50%; left:50%; transform-style:preserve-3d; transform: translate(-50%,-50%) rotateX(0deg) rotateY(0deg);
      width:980px; height:420px; transition: transform 900ms cubic-bezier(.2,.8,.2,1);
    }
.feature-card {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 260px;
  height: 340px;
  padding: 18px;
  border-radius: 18px;
  transform-style: preserve-3d;
  transform-origin: center center -320px; /* smaller radius */
  color: #05313a;
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.88), rgba(255, 255, 255, 0.55));
  border: 1px solid rgba(6, 182, 212, 0.15);
  box-shadow: 0 20px 40px rgba(6, 20, 28, 0.14);
  backdrop-filter: blur(10px);
  transition:
    transform 0.6s cubic-bezier(.2, .8, .2, 1),
    box-shadow 0.4s ease,
    background 0.4s ease;
  overflow: hidden;
}

.feature-card::before {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.35), transparent 70%);
  pointer-events: none;
}

.feature-card:hover {
  transform: translate(-50%, -50%) translateZ(30px) scale(1.06) rotateY(var(--ry, 0deg)) rotateX(var(--rx, 0deg));
  box-shadow: 0 26px 60px rgba(6, 20, 28, 0.22);
}

.f-icon {
  width: 60px;
  height: 60px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #67e8f9, #38bdf8);
  box-shadow: 0 8px 20px rgba(56, 189, 248, 0.3);
  color: white;
  font-size: 1.5rem;
  transform: translateZ(30px);
}

.f-title {
  font-weight: 800;
  font-size: 1.2rem;
  margin-top: 14px;
  transform: translateZ(20px);
}

.f-desc {
  font-size: 0.95rem;
  color: #0f3b43;
  margin-top: 8px;
  line-height: 1.4;
  transform: translateZ(15px);
}

    .f-cta{ margin-top:14px }
    .badge{
      display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; font-weight:700; font-size:.73rem;
      color:#065e69; background:linear-gradient(90deg,#ecfeff,#f0f9ff); border:1px solid rgba(6,182,212,.15);
    }
    .ring-dots{
      position:absolute; inset:0; pointer-events:none;
      background:
        radial-gradient(circle at 50% 50%, rgba(6,182,212,.18), transparent 38%),
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="240" height="240" viewBox="0 0 120 120"><g fill="none" stroke="rgba(6,182,212,0.15)" stroke-width="0.6"><circle cx="60" cy="60" r="40"/></g></svg>') center/420px 420px no-repeat;
      opacity:.25;
      filter:blur(.3px);
    }
    .carousel-controls{
      display:flex; gap:12px; justify-content:center; margin-top:16px;
    }
    .dot{
      width:10px;height:10px;border-radius:999px;background:rgba(6,182,212,.22);
    }
    .dot.active{ background:linear-gradient(90deg,var(--accent),var(--accent-2)); box-shadow:0 0 0 5px rgba(6,182,212,.12) }

    /* ====== WIDGETS STRIP ====== */
    .strip{ display:grid; grid-template-columns: repeat(12,1fr); gap:18px }
    @media (max-width:1100px){ .strip{ grid-template-columns: 1fr } }
    .widget{
      padding:16px; border-radius:16px; background:linear-gradient(180deg,rgba(255,255,255,.86), rgba(255,255,255,.60));
      border:1px solid rgba(6,182,212,.12);
    }
    .widget .w-title{ font-weight:800; font-size:.95rem }
    .chip{ display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; background:#f0fdff; color:#0b5661; font-weight:700; font-size:.75rem }

    /* ====== WHY CHOOSE US ====== */
    .why{
      display:grid; grid-template-columns: 1.1fr .9fr; gap:26px; align-items:center;
    }
    @media (max-width:1100px){ .why{ grid-template-columns:1fr } }
    .counter{
      font-size:2.2rem; font-weight:900; color:var(--accent-2);
      text-shadow: 0 8px 30px rgba(14,165,183,.18);
    }

    /* ====== STORIES SLIDER ====== */
    .stories{
      position:relative; overflow:hidden; border-radius:18px;
      background:linear-gradient(180deg,rgba(255,255,255,.75),rgba(255,255,255,.55));
      border:1px solid rgba(6,182,212,.12);
    }
    .stories-track{
      display:flex; gap:16px; will-change:transform; transition: transform 600ms cubic-bezier(.2,.8,.2,1);
    }
    .story-card{
      min-width:320px; max-width:320px; border-radius:16px; padding:16px; background:white;
      box-shadow:0 14px 40px rgba(6,20,28,.08); border:1px solid rgba(6,182,212,.10);
    }

    /* ====== STICKY QUICK ACTIONS ====== */
    .quickbar{
      position:sticky; bottom:10px; z-index:40; display:flex; justify-content:center;
    }
    .quickbar .qb{
      display:flex; gap:12px; padding:10px; border-radius:16px; background:rgba(255,255,255,.9);
      border:1px solid rgba(6,182,212,.12); box-shadow: 0 18px 40px rgba(6,20,28,.12);
    }
    .qb .btn{ padding:.65rem 1rem }

    /* ====== FOOTER ====== */
    .foot{
      display:grid; grid-template-columns: 1.1fr 1fr 1fr 1fr; gap:26px;
    }
    @media (max-width:1100px){ .foot{ grid-template-columns:1fr } }

    /* Small helpers */
    .sr-only{ position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
  </style>
</head>
<body>

  <!-- Decorative background floaters -->
  <div class="floaters">
    <div class="floater" style="left:-60px; top:-40px"></div>
    <div class="floater"></div>
    <div class="floater"></div>
    <div class="floater"></div>
  </div>

  <!-- NAVBAR -->
  <div class="nav-wrap px-4 md:px-6">
    <nav class="nav glass p-3 md:p-4 shadow-deep">
      <div class="nav-brand">
        <div class="nav-brand-badge">RC</div>
        <div>RestorativeCare</div>
      </div>
      <div class="nav-links hidden md:flex items-center gap-1">
        <a href="index.php">Home</a>
        <!-- <a href="features.php">Features</a> -->
         <a class="font-extrabold" href="blog.php">Blog</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
      </div>
      <div class="nav-cta flex items-center gap-2">
        <a href="dashboard.php" class="btn btn-ghost">Dashboard</a>
        <a href="admit.php" class="btn btn-primary">Admit Patient</a>
        <button id="navToggle" class="md:hidden btn btn-ghost" aria-label="Menu">
          <i data-feather="menu"></i>
        </button>
      </div>
    </nav>
    <!-- mobile dropdown -->
    <div id="mobileMenu" class="glass mt-2 p-3 hidden md:hidden">
      <a href="index.php" class="block p-2 rounded hover:bg-cyan-50">Home</a>
      <a href="features.php" class="block p-2 rounded hover:bg-cyan-50">Features</a>
      <a href="about.php" class="block p-2 rounded hover:bg-cyan-50">About</a>
      <a href="contact.php" class="block p-2 rounded hover:bg-cyan-50">Contact</a>
      <a href="dashboard.php" class="block p-2 rounded hover:bg-cyan-50">Dashboard</a>
      <a href="admit.php" class="block p-2 rounded hover:bg-cyan-50">Admit Patient</a>
    </div>
  </div>

  <!-- HERO -->
  <section class="px-4 md:px-6 mt-6 md:mt-8">
    <div class="hero">
      <div class="animate__animated animate__fadeInLeft">
        <h1 class="hero-title text-4xl md:text-6xl">
          A Patient-First, <span class="accent">Tech-Enabled</span><br/> Restorative Care Platform
        </h1>
        <p class="mt-5 text-lg md:text-xl muted max-w-2xl">
          Real-time updates, seamless scheduling, and holistic well-being tools —
          crafted to make every step of recovery calmer, clearer, and more connected.
        </p>
        <div class="mt-6 flex flex-wrap items-center gap-3">
          <a href="#features-showcase" class="btn btn-primary">Explore Features</a>
          <a href="schedule.php" class="btn btn-ghost">Book Appointment</a>
          <a href="notifications.php" class="link">Open Notifications →</a>
        </div>
        <div class="mt-8 hero-card">
          <div class="flex flex-wrap items-center gap-4">
            <div class="chip"><i data-feather="activity" class="w-4 h-4"></i> Live Status</div>
            <div class="chip"><i data-feather="calendar" class="w-4 h-4"></i> Same-day Slots</div>
            <div class="chip"><i data-feather="shield" class="w-4 h-4"></i> Secure by Design</div>
            <div class="chip"><i data-feather="smile" class="w-4 h-4"></i> Patient-Centric UI</div>
          </div>
        </div>
      </div>
     <div class="relative animate__animated animate__fadeInRight">
  <div class="glass shadow-deep overflow-hidden rounded-2xl flex items-center justify-center" style="height:420px; width:100%;">
    <i data-feather="heart" class="w-32 h-32 text-cyan-500"></i>
  </div>
  <div class="absolute -bottom-4 -left-6 glass p-3 rounded-xl shadow-deep">
    <div class="text-xs muted">Today</div>
    <div class="font-extrabold text-lg">12 Appointments</div>
  </div>
  <div class="absolute -top-6 -right-6 glass p-3 rounded-xl shadow-deep">
    <div class="text-xs muted">Bed Availability</div>
    <div class="font-extrabold text-lg"><span id="bedsFree">34</span> / 48</div>
  </div>
</div>


  <div class="absolute -top-6 -right-6 glass p-3 rounded-xl shadow-deep">
    <div class="text-xs muted">Bed Availability</div>
    <div class="font-extrabold text-lg"><span id="bedsFree">34</span> / 48</div>
  </div>
</div>


       
        <!-- <div class="absolute -bottom-4 -left-6 glass p-3 rounded-xl shadow-deep">
          <div class="text-xs muted">Today</div>
          <div class="font-extrabold text-lg">12 Appointments</div>
        </div> -->
        <div class="absolute -top-6 -right-6 glass p-3 rounded-xl shadow-deep">
          <div class="text-xs muted">Bed Availability</div>
          <div class="font-extrabold text-lg"><span id="bedsFree">34</span> / 48</div>
        </div>
      
    </div>
  </section>

  <!-- MAIN FEATURE SHOWCASE (3D CAROUSEL) -->
  <section id="features-showcase" class="px-4 md:px-6 mt-14">
    <div class="text-center">
      <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">Discover</div>
      <h2 class="text-3xl md:text-4xl font-extrabold mt-2">Everything in One Calm Workspace</h2>
      <!-- <p class="muted max-w-3xl mx-auto mt-2">
        Not a basic grid. Explore our features in a gently rotating 3D space. Click any tile to open its page.
      </p> -->
    </div>

   
  </section> 

  <!-- INFO WIDGETS STRIP -->
  <section class="px-4 md:px-6 mt-16">
    <div class="strip">
      <div class="widget col-span-12 md:col-span-5">
        <div class="flex items-center justify-between">
          <div class="w-title">Live Hospital Status</div>
          <a class="link text-sm" href="dashboard.php">Open Dashboard →</a>
        </div>
        <div class="grid grid-cols-3 gap-3 mt-3">
          <div class="glass p-3 rounded-lg">
            <div class="muted text-xs">Beds Free</div>
            <div class="font-extrabold text-xl" id="widBedsFree">34</div>
          </div>
          <div class="glass p-3 rounded-lg">
            <div class="muted text-xs">ICU Occupancy</div>
            <div class="font-extrabold text-xl"><span id="icuOcc">68</span>%</div>
          </div>
          <div class="glass p-3 rounded-lg">
            <div class="muted text-xs">Avg. Wait</div>
            <div class="font-extrabold text-xl"><span id="avgWait">16</span> min</div>
          </div>
        </div>
        <div class="mt-3 text-xs muted">* Demo data for UI showcase</div>
      </div>

      <div class="widget col-span-12 md:col-span-4">
        <div class="flex items-center justify-between">
          <div class="w-title">Upcoming Appointments</div>
          <a class="link text-sm" href="schedule.php">Manage →</a>
        </div>
        <div class="mt-3 flex flex-col gap-2">
          <div class="glass p-3 rounded-lg flex items-center justify-between">
            <div>
              <div class="font-semibold">Orthopedics • Dr. Mehta</div>
              <div class="muted text-xs">Aug 15, 10:30 AM</div>
            </div>
            <a href="schedule.php" class="btn btn-ghost">Reschedule</a>
          </div>
          <div class="glass p-3 rounded-lg flex items-center justify-between">
            <div>
              <div class="font-semibold">Physio Follow-up • A-2 Wing</div>
              <div class="muted text-xs">Aug 16, 4:00 PM</div>
            </div>
            <a href="schedule.php" class="btn btn-ghost">Check-in</a>
          </div>
        </div>
      </div>

      <div class="widget col-span-12 md:col-span-3">
        <div class="flex items-center justify-between">
          <div class="w-title">Recent Notifications</div>
          <a class="link text-sm" href="notifications.php">Open →</a>
        </div>
        <div class="mt-3 flex flex-col gap-2">
          <div class="glass p-3 rounded-lg">
            <div class="text-sm"><strong>Medication</strong> — Take Cefixime now.</div>
            <div class="text-xs muted">8:00 AM</div>
          </div>
          <div class="glass p-3 rounded-lg">
            <div class="text-sm"><strong>Lab</strong> — New report available.</div>
            <div class="text-xs muted">9:12 AM</div>
          </div>
          <div class="glass p-3 rounded-lg">
            <div class="text-sm"><strong>Message</strong> — Care team follow-up.</div>
            <div class="text-xs muted">11:00 AM</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- WHY CHOOSE US -->
  <section class="px-4 md:px-6 mt-20">
    <div class="why">
      <div class="glass p-6 shadow-deep">
        <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">Why Choose RestorativeCare?</div>
        <h3 class="text-3xl md:text-4xl font-extrabold mt-2">Calm, clear, and designed for real life</h3>
        <p class="muted mt-3">
          We focus on the details that matter: gentle visuals, fast flows, and a single home for everything —
          admissions, scheduling, notifications, and recovery support. No noise. Just care.
        </p>

        <div class="grid grid-cols-2 gap-4 mt-6">
          <div class="glass p-4 rounded-xl">
            <div class="muted text-xs">Avg. patient check-in</div>
            <div class="counter" data-count="62">0</div>
            <div class="text-xs muted">seconds</div>
          </div>
          <div class="glass p-4 rounded-xl">
            <div class="muted text-xs">On-time appointments</div>
            <div class="counter" data-count="98">0</div>
            <div class="text-xs muted">percent</div>
          </div>
          <div class="glass p-4 rounded-xl">
            <div class="muted text-xs">Medication adherence</div>
            <div class="counter" data-count="92">0</div>
            <div class="text-xs muted">percent</div>
          </div>
          <div class="glass p-4 rounded-xl">
            <div class="muted text-xs">Patient satisfaction</div>
            <div class="counter" data-count="97">0</div>
            <div class="text-xs muted">percent</div>
          </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
          <a href="admit.php" class="btn btn-primary">Admit a Patient</a>
          <a href="schedule.php" class="btn btn-ghost">Find a Slot</a>
        </div>
      </div>

      <div class="stories p-4">
        <div class="flex items-center justify-between px-1">
          <div class="font-extrabold">Patient Success Stories</div>
          <div class="flex gap-2">
            <button id="storyPrev" class="btn btn-ghost">‹</button>
            <button id="storyNext" class="btn btn-primary">›</button>
          </div>
        </div>
        <div id="storiesTrack" class="stories-track mt-3 p-1">
          <div class="story-card">
            <div class="flex items-center gap-3">
              <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-10 h-10 rounded-full" alt="">
              <div class="font-semibold">Priya Sharma</div>
            </div>
            <p class="mt-3 text-sm">
              “The scheduling and updates helped me show up on time and feel calmer during physio.
              It didn’t feel like a hospital app — it felt like a companion.”
            </p>
            <div class="mt-2 text-xs muted">Recovery Program • 6 Weeks</div>
          </div>
          <div class="story-card">
            <div class="flex items-center gap-3">
              <img src="https://randomuser.me/api/portraits/men/35.jpg" class="w-10 h-10 rounded-full" alt="">
              <div class="font-semibold">David Cohen</div>
            </div>
            <p class="mt-3 text-sm">
              “Admissions were smooth, and reminders were gentle. I could focus on healing.”
            </p>
            <div class="mt-2 text-xs muted">Knee Surgery • 3 Months</div>
          </div>
          <div class="story-card">
            <div class="flex items-center gap-3">
              <img src="https://randomuser.me/api/portraits/women/12.jpg" class="w-10 h-10 rounded-full" alt="">
              <div class="font-semibold">Sara Levy</div>
            </div>
            <p class="mt-3 text-sm">
              “The design felt warm. Even billing alerts were easier to understand.”
            </p>
            <div class="mt-2 text-xs muted">Chronic Care • 4 Months</div>
          </div>
          <div class="story-card">
            <div class="flex items-center gap-3">
              <img src="https://randomuser.me/api/portraits/men/76.jpg" class="w-10 h-10 rounded-full" alt="">
              <div class="font-semibold">Arjun Verma</div>
            </div>
            <p class="mt-3 text-sm">
              “Love the new homepage. Feature carousel is slick. Everything feels one tap away.”
            </p>
            <div class="mt-2 text-xs muted">Ortho Rehab • 8 Weeks</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- STICKY QUICK ACTIONS -->
  <div class="quickbar px-4 md:px-6 mt-10">
    <div class="qb">
      <a href="schedule.php" class="btn btn-primary"><i data-feather="calendar"></i> Book</a>
      <a href="admit.php" class="btn btn-ghost"><i data-feather="user-plus"></i> Admit</a>
      <a href="notifications.php" class="btn btn-ghost"><i data-feather="bell"></i> Notifications</a>
      <a href="dashboard.php" class="btn btn-ghost"><i data-feather="layout"></i> Dashboard</a>
      <a href="contact.php" class="btn btn-ghost"><i data-feather="message-circle"></i> Contact</a>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="px-4 md:px-6 mt-16 mb-8">
    <div class="foot glass p-5 shadow-deep">
      <div>
        <div class="nav-brand">
          <div class="nav-brand-badge">RC</div>
          <div>RestorativeCare</div>
        </div>
        <p class="muted mt-2 text-sm">
          A calmer, more human healthcare experience — from admission to recovery.
        </p>
        <div class="mt-3 flex gap-2">
          <a href="about.php" class="link text-sm">About</a>
          <!-- <a href="features.php" class="link text-sm">Features</a> -->
           <a class="font-extrabold" href="blog.php">Blog</a>
          <a href="contact.php" class="link text-sm">Contact</a>
        </div>
      </div>
      <div>
        <div class="font-extrabold">Core</div>
        <ul class="mt-2 space-y-1 text-sm">
          <li><a class="link" href="admit.php">Admit Patient</a></li>
          <li><a class="link" href="schedule.php">Smart Scheduling</a></li>
          <li><a class="link" href="notifications.php">Notifications</a></li>
          <li><a class="link" href="dashboard.php">Dashboard</a></li>
        </ul>
      </div>
      <div>
        <div class="font-extrabold">Programs</div>
        <ul class="mt-2 space-y-1 text-sm">
          <li><a class="link" href="mental-health.php">Well-being & Support</a></li>
          <li><a class="link" href="discharge.php">Discharge Toolkit</a></li>
          <li><a class="link" href="education.php">Patient Education</a></li>
        </ul>
      </div>
      <div>
        <div class="font-extrabold">Legal</div>
        <ul class="mt-2 space-y-1 text-sm">
          <li><a class="link" href="privacy.php">Privacy</a></li>
          <li><a class="link" href="terms.php">Terms</a></li>
        </ul>
      </div>
    </div>
    <div class="text-center text-xs muted mt-4">&copy; <?= $year ?> RestorativeCare — All rights reserved.</div>
  </footer>

  <!-- SCRIPTS -->
  <script>
    feather.replace();

    // Mobile menu toggle
    document.getElementById('navToggle')?.addEventListener('click', ()=>{
      const m = document.getElementById('mobileMenu');
      m.classList.toggle('hidden');
    });

    // Lottie animation (healthcare abstract)
    (function initLottie(){
      const container = document.getElementById('heroLottie');
      if(!container) return;
      const anim = lottie.loadAnimation({
        container, renderer:'svg', loop:true, autoplay:true,
        // public domain abstract loop (fallback if blocked: change to any local JSON)
        path:'https://assets6.lottiefiles.com/private_files/lf30_q9j1x2.json'
      });
      // subtle hover speed change
      container.addEventListener('mouseenter', ()=> anim.setSpeed(1.4));
      container.addEventListener('mouseleave', ()=> anim.setSpeed(1.0));
    })();

    // Simple demo counters
    function animateCounter(el, target, duration=1400){
      const start = 0;
      const t0 = performance.now();
      function tick(t){
        const p = Math.min(1, (t - t0) / duration);
        const val = Math.floor(start + (target - start) * (0.5 - Math.cos(p*Math.PI)/2));
        el.textContent = val;
        if(p<1) requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
    }
    document.querySelectorAll('.counter').forEach(el=>{
      const target = parseInt(el.getAttribute('data-count'),10) || 0;
      const onScroll = ()=>{
        const rect = el.getBoundingClientRect();
        if(rect.top < window.innerHeight - 60){
          animateCounter(el, target);
          window.removeEventListener('scroll', onScroll);
        }
      };
      window.addEventListener('scroll', onScroll);
      onScroll();
    });

    // Fake live updates for widgets
    const bedsEl = document.getElementById('bedsFree');
    const widBedsEl = document.getElementById('widBedsFree');
    const icuOccEl = document.getElementById('icuOcc');
    const avgWaitEl = document.getElementById('avgWait');
    setInterval(()=>{
      const delta = Math.round((Math.random()*4)-2);
      const nextBeds = Math.max(18, Math.min(48, (parseInt(bedsEl.textContent)||34)+delta));
      bedsEl.textContent = nextBeds;
      widBedsEl.textContent = nextBeds;
      icuOccEl.textContent = Math.max(42, Math.min(92,(parseInt(icuOccEl.textContent)||68) + Math.round((Math.random()*4)-2)));
      avgWaitEl.textContent = Math.max(6, Math.min(30,(parseInt(avgWaitEl.textContent)||16) + Math.round((Math.random()*3)-1)));
    }, 3200);

    /* =========================
       3D FEATURE CAROUSEL
       ========================= */
    const features = [
      {
        key:'dashboard',
        title:'Digital Dashboard',
        desc:'One calm hub for status, vitals, meds, and progress.',
        icon:'layout',
        href:'dashboard.php',
        badge:'All-in-one'
      },
      {
        key:'schedule',
        title:'Smart Scheduling',
        desc:'Frictionless booking with real-time availability.',
        icon:'calendar',
        href:'schedule.php',
        badge:'Real-time'
      },
      {
        key:'admit',
        title:'Admit Patient',
        desc:'Create a complete profile and start care in minutes.',
        icon:'user-plus',
        href:'admit.php',
        badge:'Fast Intake'
      },
      {
        key:'notifications',
        title:'Notifications',
        desc:'A gentle, patient-centric alert center.',
        icon:'bell',
        href:'notifications.php',
        badge:'Calm Alerts'
      },
      {
        key:'wellbeing',
        title:'Well-being & Support',
        desc:'Check-ins, journaling, and guided resources.',
        icon:'heart',
        href:'mental-health.php',
        badge:'Support'
      },
      {
        key:'education',
        title:'Patient Education',
        desc:'Simple explainers and care instructions.',
        icon:'book-open',
        href:'education.php',
        badge:'Clarity'
      },
      {
        key:'discharge',
        title:'Discharge Toolkit',
        desc:'Smooth hand-off with home-care checklists.',
        icon:'home',
        href:'discharge.php',
        badge:'Aftercare'
      },
      {
        key:'billing',
        title:'Billing & Payments',
        desc:'Transparent summaries and easy settlements.',
        icon:'credit-card',
        href:'billing.php',
        badge:'Transparent'
      }
    ];

    const carousel = document.getElementById('carousel');
    const dots = Array.from(document.querySelectorAll('.dot'));
    let angle = 0;
    let activeIndex = 0;
    const step = 360 / features.length;

    // Build cards positioned on a depth ring
    const radius = 400; // CSS transform-origin Z (also set in .feature-card)
    function buildCards() {
  const radius = 320;
  features.forEach((f, i) => {
    const card = document.createElement('div');
    card.className = 'feature-card';
    card.style.transform = `translate(-50%, -50%) rotateY(${i * step}deg) translateZ(${radius}px)`;

    card.innerHTML = `
      <div class="f-icon"><i data-feather="${f.icon}"></i></div>
      <div class="f-title">${f.title}</div>
      <div class="f-desc">${f.desc}</div>
      <div class="f-cta">
        <a href="${f.href}" class="btn btn-primary">Open</a>
        <span class="badge"><i data-feather="star"></i>${f.badge}</span>
      </div>
    `;

    // Parallax hover
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width - 0.5;
      const y = (e.clientY - rect.top) / rect.height - 0.5;
      card.style.setProperty('--ry', `${x * 10}deg`);
      card.style.setProperty('--rx', `${-y * 8}deg`);
      card.style.transform = `translate(-50%, -50%) rotateY(${i * step + x * 5}deg) rotateX(${y * -4}deg) translateZ(${radius}px)`;
    });

    card.addEventListener('mouseleave', () => {
      card.style.removeProperty('--ry');
      card.style.removeProperty('--rx');
      card.style.transform = `translate(-50%, -50%) rotateY(${i * step}deg) translateZ(${radius}px)`;
    });

    card.addEventListener('click', () => window.location.href = f.href);
    card.setAttribute('tabindex', '0');
    card.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') window.location.href = f.href;
    });

    carousel.appendChild(card);
  });
  feather.replace();
}

    buildCards();

    function updateActive(index){
      activeIndex = (index + features.length) % features.length;
      angle = -activeIndex * step;
      carousel.style.transform = `translate(-50%,-50%) rotateY(${angle}deg)`;
      dots.forEach((d,i)=> d.classList.toggle('active', i===activeIndex));
    }
    updateActive(0);

    document.getElementById('nextBtn').addEventListener('click', ()=> updateActive(activeIndex+1));
    document.getElementById('prevBtn').addEventListener('click', ()=> updateActive(activeIndex-1));
    dots.forEach(d=> d.addEventListener('click', ()=> updateActive(parseInt(d.dataset.dot,10))));

    // Auto rotate (pause on hover)
    let autoTimer = setInterval(()=> updateActive(activeIndex+1), 4000);
    document.querySelector('.feature-stage').addEventListener('mouseenter', ()=> clearInterval(autoTimer));
    document.querySelector('.feature-stage').addEventListener('mouseleave', ()=> autoTimer = setInterval(()=> updateActive(activeIndex+1), 4000));

    // Stories slider
    const track = document.getElementById('storiesTrack');
    let storyIndex = 0;
    function storyGo(dir){
      const items = track.children.length;
      storyIndex = Math.max(0, Math.min(items-1, storyIndex + dir));
      track.style.transform = `translateX(${-storyIndex * 336}px)`; // card + gap
    }
    document.getElementById('storyPrev').addEventListener('click', ()=> storyGo(-1));
    document.getElementById('storyNext').addEventListener('click', ()=> storyGo(+1));

    // Buttons ripple effect
    document.querySelectorAll('.btn').forEach(btn=>{
      btn.style.position = 'relative';
      btn.style.overflow = 'hidden';
      btn.addEventListener('click', function(e){
        const circle = document.createElement('span');
        const d = Math.max(this.clientWidth, this.clientHeight);
        const r = d/2;
        circle.style.width = circle.style.height = d+'px';
        circle.style.left = e.clientX - this.getBoundingClientRect().left - r + 'px';
        circle.style.top = e.clientY - this.getBoundingClientRect().top - r + 'px';
        circle.style.position = 'absolute';
        circle.style.borderRadius = '50%';
        circle.style.background = 'rgba(255,255,255,.6)';
        circle.style.transform = 'scale(0)';
        circle.style.opacity = '1';
        circle.style.animation = 'ripple 600ms ease-out';
        this.appendChild(circle);
        setTimeout(()=>circle.remove(), 620);
      });
    });
    const rippleStyle = document.createElement('style');
    rippleStyle.textContent = `
      @keyframes ripple{
        to{ transform: scale(3); opacity: 0; }
      }
    `;
    document.head.appendChild(rippleStyle);

    // Accessibility: keyboard arrow navigation for carousel
    window.addEventListener('keydown', (e)=>{
      if(e.key === 'ArrowRight') updateActive(activeIndex+1);
      if(e.key === 'ArrowLeft') updateActive(activeIndex-1);
    });
  </script>
</body>
</html>
