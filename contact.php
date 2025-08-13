<?php
// contact.php — RestorativeCare (Premium Home) — single-file drop-in
// Keep this in the same folder as index.php, admit.php, schedule.php, dashboard.php, etc.
$year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Contact — RestorativeCare</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Feather Icons -->
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

    /* Layout */
    .grid-contact{ display:grid; grid-template-columns: 1.1fr .9fr; gap:22px; }
    @media (max-width:1100px){ .grid-contact{ grid-template-columns:1fr; } }

    /* Body map */
    .bodymap svg{ width:100%; height:auto; }
    .hotspot{ fill:rgba(6,182,212,.18); stroke:rgba(6,182,212,.45); stroke-width:1.2; cursor:pointer; transition:transform .15s ease, fill .2s ease, opacity .2s ease; }
    .hotspot:hover{ transform:scale(1.02); fill:rgba(14,165,183,.28) }
    .hotspot[aria-current="true"]{ fill:rgba(14,165,183,.45); }

    /* Mood lights */
    .mood-dot{ width:10px; height:10px; border-radius:999px; display:inline-block; }
    .mood-good{ background:#22c55e }
    .mood-busy{ background:#f59e0b }
    .mood-over{ background:#ef4444 }

    /* Breathing bubble */
    .breath-bubble{
      width:120px; height:120px; border-radius:999px;
      background:radial-gradient(circle at 30% 30%, #a7f3d0,#22d3ee);
      animation: breathe 8s ease-in-out infinite;
      margin:auto;
      filter: drop-shadow(0 18px 40px rgba(6,182,212,.25));
    }
    @keyframes breathe{
      0%,100%{ transform:scale(0.85) }
      50%{ transform:scale(1.05) }
    }

    /* 3D View tabs */
    .tab-btn{ font-weight:800; padding:.55rem .9rem; border-radius:999px; }
    .tab-btn.active{ color:white; background:linear-gradient(90deg,var(--accent),var(--accent-2)); }
    .iframe-box{ position:relative; width:100%; padding-top:62%; border-radius:14px; overflow:hidden; background:#000; }
    .iframe-box iframe{ position:absolute; inset:0; width:100%; height:100%; border:0; }

    /* Floating quick actions */
    .quick-dock{
      position:fixed; right:16px; bottom:16px; display:flex; flex-direction:column; gap:10px; z-index:60;
    }
    .quick-dock .btn{ padding:.7rem .9rem }

    /* Mobile dropdown */
    .nav-links a{ padding:.55rem .9rem; border-radius:12px; font-weight:700; color:#0f3540; }
    .nav-links a:hover{ background:rgba(6,182,212,.08); transform:translateY(-2px) }
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
        <a href="about.php">About</a>
        <a class="font-extrabold" href="contact.php">Contact</a>
      </div>
      <div class="nav-cta flex items-center gap-2">
        <a href="dashboard.php" class="btn btn-ghost">Dashboard</a>
        <a href="admit.php" class="btn btn-primary">Admit Patient</a>
        <button id="navToggle" class="md:hidden btn btn-ghost" aria-label="Menu"><i data-feather="menu"></i></button>
      </div>
    </nav>
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
  <header class="px-4 md:px-6 mt-6">
    <div class="glass p-5 md:p-7 shadow-deep">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
          <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">We’re here for you</div>
          <h1 class="text-3xl md:text-5xl font-extrabold mt-1">Contact Care — Calm, Clear, Human</h1>
          <p class="muted mt-2 max-w-2xl">
            Get answers fast. Choose your body area to route to the right team, speak your symptoms,
            or jump into a live queue. No stress, no noise — just care.
          </p>
          <div class="mt-3 flex flex-wrap gap-2">
            <span class="chip"><i data-feather="clock" class="w-4 h-4"></i> Avg reply <span id="avgReply">—</span></span>
            <span class="chip"><i data-feather="activity" class="w-4 h-4"></i> Queue <span id="queueNow">—</span></span>
            <a href="tel:+1000000000" class="chip link"><i data-feather="phone-call" class="w-4 h-4"></i> Emergency: +1 (000) 000-0000</a>
          </div>
        </div>
        <div class="text-center w-full md:w-auto">
          <div class="breath-bubble" aria-hidden="true"></div>
          <div class="text-xs muted mt-2">Breathe with us • In 4 – Hold 4 – Out 4</div>
        </div>
      </div>
    </div>
  </header>

  <!-- MAIN -->
  <main class="px-4 md:px-6 mt-6">
    <div class="grid-contact">
      <!-- LEFT: Form + Body map -->
      <section class="space-y-4">
        <!-- Department Mood Board -->
        <div class="glass p-4 shadow-deep">
          <div class="flex items-center justify-between">
            <div class="font-extrabold">Live Department Mood</div>
            <a href="#doctorChat" class="link text-sm">Jump to Live Chat →</a>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3 text-sm">
            <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
              <div class="flex items-center justify-between font-semibold">Cardiology <span class="mood-dot mood-good"></span></div>
              <div class="muted text-xs">Energetic • Avg reply 12m</div>
            </div>
            <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
              <div class="flex items-center justify-between font-semibold">Orthopedics <span class="mood-dot mood-busy"></span></div>
              <div class="muted text-xs">Busy • Avg reply 18m</div>
            </div>
            <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
              <div class="flex items-center justify-between font-semibold">Pediatrics <span class="mood-dot mood-over"></span></div>
              <div class="muted text-xs">Overloaded • Avg reply 28m</div>
            </div>
            <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
              <div class="flex items-center justify-between font-semibold">Dermatology <span class="mood-dot mood-good"></span></div>
              <div class="muted text-xs">Calm • Avg reply 10m</div>
            </div>
          </div>
        </div>

        <!-- Smart Contact Card -->
        <div class="glass p-4 shadow-deep">
          <div class="flex items-center justify-between gap-3">
            <div>
              <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">Smart routing</div>
              <h2 class="text-2xl font-extrabold">Tell us where it hurts</h2>
              <p class="muted text-sm">Click on the body map or speak your symptoms. We’ll route you to the right team instantly.</p>
            </div>
            <button id="voiceBtn" class="btn btn-ghost" aria-label="Speak symptoms"><i data-feather="mic"></i> Speak</button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Body map -->
            <div class="bodymap bg-white/80 border border-cyan-100 rounded-xl p-3">
              <svg viewBox="0 0 240 520" xmlns="http://www.w3.org/2000/svg" aria-labelledby="bodyMapTitle" role="img">
                <title id="bodyMapTitle">Interactive body map</title>
                <!-- Silhouette -->
                <defs>
                  <linearGradient id="sil" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="#e6fffb"/>
                    <stop offset="100%" stop-color="#f0f9ff"/>
                  </linearGradient>
                </defs>
                <path d="M120 10c18 0 34 16 34 34v22c0 10 6 18 14 24 12 10 22 24 24 40 8 50 8 102 0 152-2 16-12 30-24 40-8 6-14 14-14 24v70c0 18-16 34-34 34s-34-16-34-34v-70c0-10-6-18-14-24-12-10-22-24-24-40-8-50-8-102 0-152 2-16 12-30 24-40 8-6 14-14 14-24V44c0-18 16-34 34-34z" fill="url(#sil)" stroke="#bae6fd" stroke-width="2"/>
                <!-- Hotspots -->
                <ellipse class="hotspot" id="hs-head" cx="120" cy="50" rx="28" ry="30" data-dept="Neurology" data-tag="Head / Brain" />
                <ellipse class="hotspot" id="hs-chest" cx="120" cy="150" rx="36" ry="28" data-dept="Cardiology" data-tag="Chest / Heart" />
                <ellipse class="hotspot" id="hs-abd" cx="120" cy="220" rx="34" ry="26" data-dept="Gastroenterology" data-tag="Abdomen / Digestive" />
                <ellipse class="hotspot" id="hs-leftArm" cx="70" cy="180" rx="20" ry="26" data-dept="Orthopedics" data-tag="Left Arm / Shoulder" />
                <ellipse class="hotspot" id="hs-rightArm" cx="170" cy="180" rx="20" ry="26" data-dept="Orthopedics" data-tag="Right Arm / Shoulder" />
                <ellipse class="hotspot" id="hs-pelvis" cx="120" cy="285" rx="30" ry="24" data-dept="Urology" data-tag="Pelvis / Uro" />
                <ellipse class="hotspot" id="hs-leftLeg" cx="95" cy="360" rx="22" ry="34" data-dept="Orthopedics" data-tag="Left Leg / Knee" />
                <ellipse class="hotspot" id="hs-rightLeg" cx="145" cy="360" rx="22" ry="34" data-dept="Orthopedics" data-tag="Right Leg / Knee" />
                <ellipse class="hotspot" id="hs-skin" cx="120" cy="120" rx="70" ry="100" fill="transparent" stroke="transparent" data-dept="Dermatology" data-tag="Skin / Dermatology" />
              </svg>
              <div id="mapHint" class="text-xs muted mt-2">Tip: Click a region to auto-select the department.</div>
            </div>

            <!-- Form -->
            <form id="contactForm" class="space-y-3" action="#" method="post" onsubmit="event.preventDefault(); fakeSubmit();">
              <div>
                <label class="text-sm font-semibold">Full Name</label>
                <input required name="name" class="w-full mt-1 rounded-lg border border-cyan-100 bg-white/90 p-3" placeholder="Your name" />
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="text-sm font-semibold">Email</label>
                  <input required type="email" name="email" class="w-full mt-1 rounded-lg border border-cyan-100 bg-white/90 p-3" placeholder="you@domain.com" />
                </div>
                <div>
                  <label class="text-sm font-semibold">Phone</label>
                  <input type="tel" name="phone" class="w-full mt-1 rounded-lg border border-cyan-100 bg-white/90 p-3" placeholder="+1 555 000 0000" />
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="text-sm font-semibold">Department</label>
                  <select id="deptSelect" name="department" class="w-full mt-1 rounded-lg border border-cyan-100 bg-white/90 p-3">
                    <option>General</option>
                    <option>Cardiology</option>
                    <option>Neurology</option>
                    <option>Orthopedics</option>
                    <option>Dermatology</option>
                    <option>Gastroenterology</option>
                    <option>Urology</option>
                    <option>Pediatrics</option>
                    <option>Other</option>
                  </select>
                </div>
                <div>
                  <label class="text-sm font-semibold">Priority</label>
                  <select name="priority" class="w-full mt-1 rounded-lg border border-cyan-100 bg-white/90 p-3">
                    <option>Routine</option>
                    <option>Fast</option>
                    <option>Urgent</option>
                  </select>
                </div>
              </div>
              <div>
                <div class="flex items-center justify-between">
                  <label class="text-sm font-semibold">Symptoms / Message</label>
                  <button type="button" id="aiSuggest" class="text-xs link">AI suggest subject</button>
                </div>
                <textarea id="symptoms" name="message" rows="5" class="w-full mt-1 rounded-lg border border-cyan-100 bg-white/90 p-3" placeholder="Describe what you're experiencing…"></textarea>
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <button class="btn btn-primary" type="submit"><i data-feather="send"></i> Send message</button>
                <a class="btn btn-ghost" href="schedule.php"><i data-feather="calendar"></i> Book instead</a>
                <a class="btn btn-ghost" href="tel:+1000000000"><i data-feather="phone-call"></i> Call Emergency</a>
              </div>
              <div id="formToast" class="hidden mt-2 text-sm"></div>
            </form>
          </div>
        </div>

        <!-- Live wait time -->
        <div class="glass p-4 shadow-deep">
          <div class="w-title font-extrabold">Live Wait Time Prediction</div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
            <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
              <div class="muted text-xs">Walk-in</div>
              <div class="font-extrabold text-2xl"><span id="wtWalkin">—</span> min</div>
            </div>
            <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
              <div class="muted text-xs">Emergency</div>
              <div class="font-extrabold text-2xl"><span id="wtEr">—</span> min</div>
            </div>
            <div class="p-3 rounded-lg bg-white/80 border border-cyan-100">
              <div class="muted text-xs">Online reply</div>
              <div class="font-extrabold text-2xl"><span id="wtOnline">—</span> min</div>
            </div>
          </div>
          <div class="text-xs muted mt-2">* Demo predictions, auto-updating every 10s.</div>
        </div>
      </section>

      <!-- RIGHT: 3D, Chat, FAQs -->
      <aside class="space-y-4">
        <!-- 3D Human Model Tabs -->
        <div class="glass p-4 shadow-deep">
          <div class="flex items-center justify-between">
            <div>
                            <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">3D View</div>
              <h2 class="text-xl font-extrabold">Explore Human Anatomy</h2>
            </div>
            <div class="flex gap-2">
              <button class="tab-btn active" data-model="skeleton">Skeleton</button>
              <button class="tab-btn" data-model="muscle">Muscle</button>
            </div>
          </div>
          <div class="iframe-box mt-3" id="modelViewer">
            <iframe title="Skeleton Model"
              src="https://sketchfab.com/models/f80e51109cf04f38869adb1d0de90daa/embed"
              allow="autoplay; fullscreen; xr-spatial-tracking"
              mozallowfullscreen="true" webkitallowfullscreen="true">
            </iframe>
          </div>
        </div>

        <!-- Live Doctor Chat -->
        <div id="doctorChat" class="glass p-4 shadow-deep">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">Live Chat</div>
              <h2 class="text-xl font-extrabold">Chat with a doctor now</h2>
            </div>
            <a href="chat.php" class="btn btn-primary"><i data-feather="message-square"></i> Open Chat</a>
          </div>
          <div class="mt-3 bg-white/80 border border-cyan-100 rounded-lg p-3 max-h-60 overflow-y-auto">
            <div class="text-sm muted">Recent messages:</div>
            <div class="mt-2 space-y-2">
              <div class="p-2 bg-cyan-50 rounded">
                <span class="font-semibold">Dr. Jane:</span> Hello, how can I help you today?
              </div>
              <div class="p-2 bg-white rounded border border-cyan-100">
                <span class="font-semibold">You:</span> I have chest discomfort since morning.
              </div>
              <div class="p-2 bg-cyan-50 rounded">
                <span class="font-semibold">Dr. Jane:</span> Understood. On a scale from 1 to 10, how severe is it?
              </div>
            </div>
          </div>
        </div>

        <!-- Smart FAQ -->
        <div class="glass p-4 shadow-deep">
          <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">FAQ</div>
          <h2 class="text-xl font-extrabold">Quick Answers</h2>
          <input id="faqSearch" type="text" placeholder="Search a question..."
            class="w-full mt-2 rounded-lg border border-cyan-100 bg-white/90 p-2 text-sm" />
          <div id="faqList" class="mt-3 space-y-2 text-sm">
            <div class="p-2 rounded bg-white/80 border border-cyan-100">
              <strong>How do I book an appointment?</strong>
              <p class="muted text-xs">You can use our schedule page to book in under a minute.</p>
            </div>
            <div class="p-2 rounded bg-white/80 border border-cyan-100">
              <strong>Do you offer telemedicine?</strong>
              <p class="muted text-xs">Yes, we provide 24/7 video consultations for most departments.</p>
            </div>
            <div class="p-2 rounded bg-white/80 border border-cyan-100">
              <strong>Is my data secure?</strong>
              <p class="muted text-xs">We use end-to-end encryption and HIPAA-compliant storage.</p>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </main>

  <!-- Floating quick actions -->
  <div class="quick-dock">
    <a href="#contactForm" class="btn btn-primary"><i data-feather="mail"></i></a>
    <a href="tel:+1000000000" class="btn btn-ghost"><i data-feather="phone-call"></i></a>
    <a href="chat.php" class="btn btn-ghost"><i data-feather="message-circle"></i></a>
  </div>

  <!-- FOOTER -->
  <footer class="mt-10 px-4 md:px-6 py-6 glass shadow-deep text-center text-sm muted">
    &copy; <?php echo $year; ?> RestorativeCare — All rights reserved.
  </footer>

  <!-- Scripts -->
  <script>
    feather.replace();

    // Toggle mobile menu
    document.getElementById('navToggle').addEventListener('click', () => {
      document.getElementById('mobileMenu').classList.toggle('hidden');
    });

    // 3D model switch
    const modelViewer = document.getElementById('modelViewer').querySelector('iframe');
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        if(btn.dataset.model === 'skeleton'){
          modelViewer.src = "https://sketchfab.com/models/f80e51109cf04f38869adb1d0de90daa/embed";
        } else {
          modelViewer.src = "https://sketchfab.com/models/7ea21567ff9942bf9511e2d99efe85d9/embed";
        }
      });
    });

    // Fake live wait time update
    function updateWaitTimes(){
      document.getElementById('wtWalkin').textContent = Math.floor(Math.random()*20+5);
      document.getElementById('wtEr').textContent = Math.floor(Math.random()*5+1);
      document.getElementById('wtOnline').textContent = Math.floor(Math.random()*15+2);
      document.getElementById('avgReply').textContent = Math.floor(Math.random()*10+5) + " min";
      document.getElementById('queueNow').textContent = Math.floor(Math.random()*10+1) + " in queue";
    }
    setInterval(updateWaitTimes, 10000);
    updateWaitTimes();

    // Body map click => select department
    document.querySelectorAll('.hotspot').forEach(hs => {
      hs.addEventListener('click', () => {
        document.getElementById('deptSelect').value = hs.dataset.dept;
        document.getElementById('mapHint').textContent = "Selected: " + hs.dataset.tag;
      });
    });

    // AI suggest (mock)
    document.getElementById('aiSuggest').addEventListener('click', () => {
      document.getElementById('symptoms').value = "I am experiencing mild chest pain and shortness of breath for the past 3 hours.";
    });

    // FAQ search
    document.getElementById('faqSearch').addEventListener('input', e => {
      const term = e.target.value.toLowerCase();
      document.querySelectorAll('#faqList > div').forEach(div => {
        div.style.display = div.textContent.toLowerCase().includes(term) ? 'block' : 'none';
      });
    });

    // Fake submit
    function fakeSubmit(){
      const toast = document.getElementById('formToast');
      toast.textContent = "✅ Your message has been sent. We'll respond shortly.";
      toast.classList.remove('hidden');
      toast.classList.add('text-green-600');
    }
  </script>
</body>
</html>
