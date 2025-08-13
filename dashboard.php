<?php
// dashboard.php
// requires auth.php in the same folder (session + require_auth())
// require_once __DIR__ . '/auth.php';
// require_auth();
$user = $_SESSION['user'] ?? ['name' => 'Jaimin Parmar'];
$userName = htmlspecialchars($user['name']);
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
          <div class="mini-icon">JD</div>
          <div>
            <div class="text-lg font-semibold">Welcome back, <?php echo $userName; ?></div>
            <div class="text-sm text-gray-500">Keep going — you’re making progress. Last updated: <span id="lastUpdated">Aug 12, 2025</span></div>
          </div>
        </div>
      </div>

      <!-- small quick stats -->
      <div class="flex gap-4 items-center">
        <div class="text-center">
          <div class="text-sm text-gray-500">Medication</div>
          <div class="stat-pill mt-1">91% adherence</div>
        </div>
        <div class="text-center">
          <div class="text-sm text-gray-500">Discharge Readiness</div>
          <div class="stat-pill mt-1">92%</div>
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
                <div class="text-lg font-semibold">91%</div>
              </div>
              <div>
                <button class="px-4 py-2 bg-cyan-500 text-white rounded-lg hover:bg-cyan-600">Set Reminder</button>
              </div>
            </div>

            <div class="mt-2">
              <div class="text-sm text-gray-500">Discharge readiness</div>
              <div class="w-full bg-white/10 rounded-full h-3 mt-2 overflow-hidden">
                <div id="readinessBar" style="width:92%" class="h-3 bg-gradient-to-r from-cyan-400 to-cyan-600"></div>
              </div>
              <div class="text-xs text-gray-500 mt-1">Score: 92 / 100</div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
              <div class="glass p-3 rounded-lg text-center">
                <div class="text-sm text-gray-500">Therapy sessions</div>
                <div class="font-semibold">3 this week</div>
              </div>
              <div class="glass p-3 rounded-lg text-center">
                <div class="text-sm text-gray-500">Exercises done</div>
                <div class="font-semibold">8 / 10</div>
              </div>
              <div class="glass p-3 rounded-lg text-center">
                <div class="text-sm text-gray-500">Hydration</div>
                <div class="font-semibold">Good</div>
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
        </ul>
      </section>

      <!-- Notifications -->
      <section class="glass rounded-xl p-6 reveal card-deep">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-semibold">Notifications</h3>
          <a href="notifications.php" class="text-sm text-cyan-600 hover:underline">View all</a>
        </div>

        <div class="space-y-3">
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
        </div>
      </section>

    </div>

    <!-- right sidebar: mental health + discharge toolkit + AI assistant -->
    <aside class="space-y-6">
      <!-- Mental health mini -->
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
    /* ---------- Chart: Adherence doughnut ---------- */
    const ctx = document.getElementById('adherenceChart').getContext('2d');
    const adherenceChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Taken','Missed'],
        datasets: [{
          data: [91,9],
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
          data: [3,4,3,4,5,4,4], // mock weekly
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

    /* ---------- Mood click handler (mock) ---------- */
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
      // simple rule-based demo responses
      const ql = q.toLowerCase();
      if(ql.includes('next') && ql.includes('session')) {
        out.innerText = 'Your next session is Physiotherapy on Aug 14, 2025 at 11:00 AM with Dr. Mehta.';
      } else if(ql.includes('medic') || ql.includes('medicine')) {
        out.innerText = 'Take your medications as per the schedule. Your next dose is at 08:00 AM.';
      } else if(ql.includes('discharge')) {
        out.innerText = 'Your discharge readiness is currently 92%. You can download the plan from the Discharge Toolkit.';
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

    /* ---------- small: update lastUpdated to current time (demo) ---------- */
    document.getElementById('lastUpdated').innerText = new Date().toLocaleString();

  </script>
</body>
</html>
