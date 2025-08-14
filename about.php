<?php
// about.php — RestorativeCare (Premium Home) — single-file drop-in
// Keep this in the same folder as index.php, admit.php, schedule.php, dashboard.php, etc.
$year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>About Us — RestorativeCare</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">

  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>

  <!-- Lottie (tiny ambient hero accent) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>

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
    body{
      font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
      color:var(--ink);
      background: radial-gradient(circle at -10% -10%, var(--bg1), var(--bg2) 45%);
      overflow-x:hidden;
    }
    .glass{ background:var(--glass); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); border:1px solid var(--glass-border); border-radius:14px; }
    .btn{ display:inline-flex; align-items:center; justify-content:center; gap:.5rem; padding:.8rem 1.05rem; border-radius:12px; font-weight:800; cursor:pointer; transition:transform .15s ease, box-shadow .15s ease; box-shadow:0 10px 30px rgba(6,182,212,.10); user-select:none; }
    .btn-primary{ color:white; background:linear-gradient(90deg,var(--accent),var(--accent-2)); }
    .btn-ghost{ color:var(--accent-2); background:rgba(255,255,255,.92); border:1px solid rgba(6,182,212,.15); }
    .btn:hover{ transform:translateY(-2px); }
    .link{ color:var(--accent-2); font-weight:700 }
    .muted{ color:var(--muted) }
    .shadow-deep{ box-shadow:0 30px 80px rgba(6,20,28,0.08) }
    .chip{ display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; background:#f0fdff; color:#0b5661; font-weight:800; font-size:.75rem }

    /* Nav */
    .nav-wrap{ position:sticky; top:12px; z-index:50 }
    .nav{ display:flex; align-items:center; justify-content:space-between; gap:16px; }
    .nav-brand{ display:flex; align-items:center; gap:12px; font-weight:900; font-size:1.45rem; color:var(--accent-2) }
    .nav-brand-badge{ width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:900; background:linear-gradient(135deg,#60a5fa,var(--accent)); box-shadow:0 12px 26px rgba(6,182,212,.22) }
    .nav-links a{ padding:.55rem .9rem; border-radius:12px; font-weight:700; color:#0f3540; }
    .nav-links a:hover{ background:rgba(6,182,212,.08); transform:translateY(-2px) }

    /* Hero */
    .hero-grid{ display:grid; grid-template-columns: 1.1fr .9fr; gap:22px; align-items:center; }
    @media (max-width:1100px){ .hero-grid{ grid-template-columns:1fr; } }
    .iframe-box{ position:relative; width:100%; padding-top:62%; border-radius:16px; overflow:hidden; background:#000; }
    .iframe-box iframe{ position:absolute; inset:0; width:100%; height:100%; border:0; }

    /* Values wheel */
    .wheel{ position:relative; width:320px; height:320px; margin:auto; }
    .wheel .ring{ position:absolute; inset:0; border-radius:999px; border:1px dashed rgba(6,182,212,.35); }
    .wheel .node{ position:absolute; width:100px; height:100px; border-radius:16px; display:flex; align-items:center; justify-content:center; text-align:center; padding:10px; background:rgba(255,255,255,.92); border:1px solid rgba(6,182,212,.15); box-shadow:0 10px 30px rgba(6,20,28,.08) }
    .wheel .node small{ display:block; color:#0b5661; font-weight:800; font-size:.7rem; margin-top:4px }

    /* Timeline */
    .tl{ position:relative; padding-left:26px; }
    .tl:before{ content:""; position:absolute; left:8px; top:0; bottom:0; width:2px; background:linear-gradient(#e0f2fe,#99f6e4); }
    .tli{ position:relative; margin:16px 0; }
    .tli:before{ content:""; position:absolute; left:-1px; top:8px; width:10px; height:10px; border-radius:999px; background:linear-gradient(135deg,#60a5fa,#06b6d4); box-shadow:0 0 0 6px rgba(6,182,212,.15) }

    /* Team */
    .team-card{ background:linear-gradient(180deg,rgba(255,255,255,.92),rgba(255,255,255,.75)); border:1px solid rgba(6,182,212,.12); border-radius:16px; overflow:hidden; transition:transform .18s ease, box-shadow .18s ease; }
    .team-card:hover{ transform:translateY(-4px); box-shadow:0 24px 60px rgba(6,20,28,.12) }

    /* Stats counters */
    .counter{ font-weight:900; font-size:2rem; color:var(--accent-2) }

    /* Values grid pills */
    .pill{ border-radius:999px; padding:.55rem .9rem; font-weight:800; background:#ecfeff; border:1px solid rgba(6,182,212,.18); color:#075e69 }

    /* Footer quick nav */
    .foot{ display:grid; grid-template-columns: 1.1fr 1fr 1fr 1fr; gap:26px; }
    @media (max-width:1100px){ .foot{ grid-template-columns:1fr } }
  </style>
</head>
<body>

  <!-- NAV -->
  <div class="nav-wrap px-4 md:px-6">
    <nav class="nav glass p-3 md:p-4 shadow-deep">
      <div class="nav-brand">
        <div class="nav-brand-badge">RC</div>
        <div>RestorativeCare</div>
      </div>
      <div class="nav-links hidden md:flex items-center gap-1">
        <a href="index.php">Home</a>
        <!-- <a href="features.php">Features</a> -->
         <a href="blog.php">Blog</a>
        <a class="font-extrabold" href="about.php">About</a>
        <a href="contact.php">Contact</a>
      </div>
      <div class="nav-cta flex items-center gap-2">
        <a href="dashboard.php" class="btn btn-ghost">Dashboard</a>
        <a href="admit.php" class="btn btn-primary">Admit Patient</a>
        <button id="navToggle" class="md:hidden btn btn-ghost" aria-label="Menu"><i data-feather="menu"></i></button>
      </div>
    </nav>
    <div id="mobileMenu" class="glass mt-2 p-3 hidden md:hidden">
      <a href="index.php" class="block p-2 rounded hover:bg-cyan-50">Home</a>
      <!-- <a href="features.php" class="block p-2 rounded hover:bg-cyan-50">Features</a> -->
      <a href="about.php" class="block p-2 rounded hover:bg-cyan-50">About</a>
      <a href="contact.php" class="block p-2 rounded hover:bg-cyan-50">Contact</a>
      <a href="dashboard.php" class="block p-2 rounded hover:bg-cyan-50">Dashboard</a>
      <a href="admit.php" class="block p-2 rounded hover:bg-cyan-50">Admit Patient</a>
    </div>
  </div>

  <!-- HERO -->
  <header class="px-4 md:px-6 mt-6">
    <div class="glass p-5 md:p-7 shadow-deep">
      <div class="hero-grid">
        <div class="animate__animated animate__fadeInLeft">
          <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">About RestorativeCare</div>
          <h1 class="text-3xl md:text-5xl font-extrabold mt-1">Calm Technology. Human Care.</h1>
          <p class="muted mt-3 max-w-2xl">
            We’re a patient-first platform built by clinicians, designers, and engineers who believe care should feel
            simple, supportive, and transparent—from admission to recovery.
          </p>
          <div class="mt-4 flex flex-wrap gap-2">
            <span class="chip"><i data-feather="award" class="w-4 h-4"></i> Safety by design</span>
            <span class="chip"><i data-feather="activity" class="w-4 h-4"></i> Real-time status</span>
            <span class="chip"><i data-feather="heart" class="w-4 h-4"></i> Well-being tools</span>
          </div>
          <div class="mt-5 flex flex-wrap gap-2">
            <a href="#mission" class="btn btn-primary">Our Mission</a>
            <a href="#team" class="btn btn-ghost">Meet the Team</a>
          </div>
          <div class="mt-6 grid grid-cols-3 gap-3">
            <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
              <div class="muted text-xs">Hospitals</div>
              <div class="counter" data-count="48">0</div>
            </div>
            <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
              <div class="muted text-xs">Avg Check-in (sec)</div>
              <div class="counter" data-count="62">0</div>
            </div>
            <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
              <div class="muted text-xs">Patient Satisfaction</div>
              <div class="counter" data-count="97">0</div>
            </div>
          </div>
        </div>

        <!-- Impact Metrics Grid + Growth Chart -->
<div class="glass p-5 shadow-deep">
  <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">Impact Metrics</div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">

    <!-- Stats Grid -->
    <div class="grid grid-cols-3 gap-3">
      <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
        <div class="muted text-xs">Hospitals</div>
        <div class="counter text-2xl font-extrabold" data-count="48">0</div>
      </div>
      <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
        <div class="muted text-xs">Avg Check-in (sec)</div>
        <div class="counter text-2xl font-extrabold" data-count="62">0</div>
      </div>
      <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
        <div class="muted text-xs">Patient Satisfaction</div>
        <div class="counter text-2xl font-extrabold" data-count="97">0</div>
      </div>
    </div>

    <!-- Fixed-Size Growth Chart -->
    <div class="p-3 rounded-lg bg-white/80 border border-cyan-100 flex items-center justify-center">
      <canvas id="growthChart" style="max-width: 100%; height: 250px;"></canvas>
    </div>

  </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const ctx = document.getElementById('growthChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['2020', '2021', '2022', '2023', '2024'],
      datasets: [{
        label: 'Growth (%)',
        data: [10, 25, 45, 70, 95],
        borderColor: '#06b6d4',
        backgroundColor: 'rgba(6, 182, 212, 0.2)',
        fill: true,
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true, max: 100 }
      }
    }
  });

  // Counter animation
  document.querySelectorAll('.counter').forEach(counter => {
    const target = +counter.getAttribute('data-count');
    let count = 0;
    const update = () => {
      count += Math.ceil(target / 50);
      if (count > target) count = target;
      counter.textContent = count;
      if (count < target) requestAnimationFrame(update);
    };
    update();
  });
});
</script>


  </header>

  <!-- MISSION + VALUES -->
  <section id="mission" class="px-4 md:px-6 mt-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="glass p-5 shadow-deep">
        <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">Our mission</div>
        <h2 class="text-2xl md:text-3xl font-extrabold mt-1">Make recovery calmer, clearer, and more connected</h2>
        <p class="muted mt-2">
          We remove friction from critical moments of care and give patients and teams a single, serene workspace:
          admissions, scheduling, notifications, education, discharge, and well-being—without the noise.
        </p>
        <div class="mt-4 flex flex-wrap gap-2">
          <span class="pill"><i data-feather="shield" class="w-4 h-4"></i> Privacy-first</span>
          <span class="pill"><i data-feather="smile" class="w-4 h-4"></i> Patient empathy</span>
          <span class="pill"><i data-feather="zap" class="w-4 h-4"></i> Fast flows</span>
          <span class="pill"><i data-feather="layers" class="w-4 h-4"></i> Interoperable</span>
          <span class="pill"><i data-feather="bar-chart-2" class="w-4 h-4"></i> Outcomes-driven</span>
        </div>
      </div>

      <!-- Values wheel -->
      <div class="glass p-5 shadow-deep">
        <div class="flex items-center justify-between">
          <div class="font-extrabold">Our Values</div>
          <button id="spinWheel" class="btn btn-ghost text-sm"><i data-feather="rotate-cw"></i> Shuffle</button>
        </div>
        <div class="wheel mt-4" id="valuesWheel">
          <div class="ring"></div>
          <!-- nodes positioned by JS in a circle -->
        </div>
        <div class="text-xs muted mt-3 text-center">Hover nodes to see focus. Click to pin.</div>
      </div>
    </div>
  </section>

  <!-- WHY WE'RE DIFFERENT -->
  <section class="px-4 md:px-6 mt-8">
    <div class="glass p-5 shadow-deep">
      <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">What makes us different</div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
        <div class="p-4 rounded-xl bg-white/85 border border-cyan-100">
          <div class="flex items-center gap-2 font-extrabold"><i data-feather="feather"></i> Calm first</div>
          <p class="muted text-sm mt-1">Warm visuals, gentle motion, and low cognitive load. Designed to reduce anxiety in high-stakes moments.</p>
        </div>
        <div class="p-4 rounded-xl bg-white/85 border border-cyan-100">
          <div class="flex items-center gap-2 font-extrabold"><i data-feather="cpu"></i> Contextual automation</div>
          <p class="muted text-sm mt-1">Smart defaults & triage reduce clicks, speed up tasks, and route messages where they matter.</p>
        </div>
        <div class="p-4 rounded-xl bg-white/85 border border-cyan-100">
          <div class="flex items-center gap-2 font-extrabold"><i data-feather="user-check"></i> Patient co-design</div>
          <p class="muted text-sm mt-1">We prototype features with patients & clinicians and ship only what improves outcomes and clarity.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- STORY TIMELINE -->
  <section class="px-4 md:px-6 mt-8">
    <div class="glass p-5 shadow-deep">
      <div class="flex items-center justify-between">
        <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">Our story</div>
        <a href="contact.php" class="link text-sm">Partner with us →</a>
      </div>
      <div class="tl mt-2">
        <div class="tli">
          <div class="font-extrabold">2021 — The spark</div>
          <p class="muted text-sm">Clinicians & designers join forces to reimagine patient journeys with less friction and more empathy.</p>
        </div>
        <div class="tli">
          <div class="font-extrabold">2022 — First pilots</div>
          <p class="muted text-sm">We launch live pilots for admissions & scheduling—cutting check-in time dramatically.</p>
        </div>
        <div class="tli">
          <div class="font-extrabold">2023 — All-in-one workspace</div>
          <p class="muted text-sm">Notifications, education, and discharge tools arrive; patient feedback pushes us further.</p>
        </div>
        <div class="tli">
          <div class="font-extrabold">2024+ — Scale with soul</div>
          <p class="muted text-sm">We grow carefully, partnering with teams that share our patient-first principles.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- TEAM -->
  <section id="team" class="px-4 md:px-6 mt-8">
    <div class="glass p-5 shadow-deep">
      <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">Leadership & Advisors</div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-3">
               <?php
          // Example static team data (replace with DB fetch if needed)
          $team = [
            ["name" => "Dr. Anika Verma", "role" => "Chief Medical Officer", "img" => "https://randomuser.me/api/portraits/women/65.jpg"],
            ["name" => "Raj Mehta", "role" => "Lead UX Designer", "img" => "https://randomuser.me/api/portraits/men/32.jpg"],
            ["name" => "Sarah Cohen", "role" => "Head of Engineering", "img" => "https://randomuser.me/api/portraits/women/48.jpg"],
            ["name" => "Daniel Levi", "role" => "AI Research Advisor", "img" => "https://randomuser.me/api/portraits/men/50.jpg"]
          ];

          foreach ($team as $person) {
            echo '<div class="team-card p-4 text-center">';
            echo '<img src="'.$person['img'].'" alt="'.$person['name'].'" class="w-24 h-24 rounded-full mx-auto shadow-deep">';
            echo '<div class="font-extrabold mt-3">'.$person['name'].'</div>';
            echo '<div class="muted text-sm">'.$person['role'].'</div>';
            echo '</div>';
          }
        ?>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="px-4 md:px-6 mt-10 mb-6">
    <div class="glass p-5 shadow-deep">
      <div class="foot">
        <div>
          <div class="font-extrabold mb-2">RestorativeCare</div>
          <p class="muted text-sm">A calmer way to deliver and receive healthcare. Built with empathy, science, and technology.</p>
        </div>
        <div>
          <div class="font-extrabold mb-2">Quick Links</div>
          <a href="index.php" class="block muted text-sm mb-1">Home</a>
          <!-- <a href="features.php" class="block muted text-sm mb-1">Features</a> -->
          <a href="about.php" class="block muted text-sm mb-1">About</a>
          <a href="contact.php" class="block muted text-sm">Contact</a>
        </div>
        <div>
          <div class="font-extrabold mb-2">Resources</div>
          <a href="#" class="block muted text-sm mb-1">Blog</a>
          <a href="#" class="block muted text-sm mb-1">Help Center</a>
          <a href="#" class="block muted text-sm">Privacy Policy</a>
        </div>
        <div>
          <div class="font-extrabold mb-2">Connect</div>
          <a href="https://www.linkedin.com/in/jaimin-parmar-a4a62a332 " class="block muted text-sm mb-1">Linkedin</a>
          <a href="https://www.instagram.com/jaimin_26807/" class="block muted text-sm mb-1">InstragramJaimin</a>
          <a href="jaiminparmar2687@gmail.com" class="block muted text-sm">Email Us</a>
        </div>  
      </div>
      <div class="mt-5 text-center muted text-xs">© <?php echo $year; ?> RestorativeCare. All rights reserved. by Jaimin Parmar </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script>
    feather.replace();

    // Toggle mobile nav
    document.getElementById("navToggle").addEventListener("click", function(){
      document.getElementById("mobileMenu").classList.toggle("hidden");
    });

    // Counter animation
    document.querySelectorAll(".counter").forEach(counter => {
      let countTo = parseInt(counter.dataset.count);
      let count = 0;
      let step = Math.ceil(countTo / 50);
      let interval = setInterval(() => {
        count += step;
        if (count >= countTo) {
          count = countTo;
          clearInterval(interval);
        }
        counter.textContent = count;
      }, 40);
    });

    // Values wheel generation
    const values = [
      "Transparency","Empathy","Accessibility","Innovation",
      "Safety","Speed","Support","Inclusion"
    ];
    const wheel = document.getElementById("valuesWheel");
    function renderWheel(){
      wheel.querySelectorAll(".node").forEach(n=>n.remove());
      let center = 160;
      let radius = 120;
      values.forEach((val, i) => {
        let angle = (i / values.length) * 2 * Math.PI;
        let x = center + radius * Math.cos(angle) - 50;
        let y = center + radius * Math.sin(angle) - 50;
        let node = document.createElement("div");
        node.className = "node";
        node.style.left = `${x}px`;
        node.style.top = `${y}px`;
        node.innerHTML = `<div>${val}<small>Value</small></div>`;
        wheel.appendChild(node);
      });
    }
    renderWheel();

    document.getElementById("spinWheel").addEventListener("click", () => {
      values.push(values.shift());
      renderWheel();
    });

    // Tab switching for 3D models
    const tabBtns = document.querySelectorAll(".tab-btn");
    tabBtns.forEach(btn => {
      btn.addEventListener("click", () => {
        tabBtns.forEach(b=>b.classList.remove("btn-primary"));
        tabBtns.forEach(b=>b.classList.add("btn-ghost"));
        btn.classList.remove("btn-ghost");
        btn.classList.add("btn-primary");

        let tabName = btn.dataset.modeltab;
        document.querySelectorAll(".iframe-box iframe").forEach(ifr => {
          if (ifr.id === "tab-" + tabName) {
            ifr.classList.remove("hidden");
            if (!ifr.src) ifr.src = ifr.dataset.src;
          } else {
            ifr.classList.add("hidden");
          }
        });
      });
    });
  </script>

</body>
</html>

