<?php
// mental-health.php - Full static demo (single-file)
// No DB/auth/localStorage — purely static demo data for hackathon presentation.
$patientName = 'Riya Patel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Mental Health — RestorativeCare (Demo)</title>

  <!-- Tailwind (CDN quick) -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    /* -------------------------
       Visual theme & layout
       ------------------------- */
    :root{
      --accent: #06b6d4;
      --accent-2: #0891b2;
      --glass: rgba(255,255,255,0.10);
      --glass-border: rgba(255,255,255,0.16);
      --muted: #6b7280;
      --bg: #f5fcff;
    }
    html,body{height:100%}
    body{
      margin:0;
      font-family: Inter,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;
      background: radial-gradient(circle at 10% 10%, #f5fcff 0%, #ffffff 35%);
      color:#052026;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }
    /* Glass card */
    .glass { background: var(--glass); border: 1px solid var(--glass-border); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-radius:12px; }
    .card-deep { box-shadow: 0 12px 30px rgba(6,20,28,0.06); transition: transform .18s, box-shadow .18s; }
    .card-deep:hover { transform: translateY(-6px); box-shadow: 0 26px 48px rgba(6,20,28,0.12); }

    /* Parallax / layers */
    .parallax { perspective: 1200px; transform-style: preserve-3d; }
    .layer { transform-origin: center; transition: transform 0.45s cubic-bezier(.2,.9,.3,1); will-change: transform; }

    /* Reveal animations */
    .reveal { opacity: 0; transform: translateY(18px); transition: all .7s cubic-bezier(.2,.9,.3,1); }
    .reveal.visible { opacity: 1; transform: translateY(0) }

    /* Heatmap */
    .heatday { width:28px; height:28px; border-radius:6px; display:inline-block; margin:3px; background:#f3f4f6; cursor:pointer; transition: transform .12s ease, box-shadow .12s ease; }
    .heatday:hover { transform: translateY(-6px); box-shadow: 0 14px 30px rgba(6,20,28,0.06); }
    .heat-low { background: #fde68a; }   /* low mood */
    .heat-med { background: #fca5a5; }   /* medium */
    .heat-high { background: #86efac; }  /* high mood */

    /* Mood picker */
    .mood-btn { width:58px; height:58px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; font-size:24px; cursor:pointer; transition: transform .12s ease, box-shadow .12s ease; }
    .mood-btn:hover { transform: translateY(-6px); box-shadow: 0 14px 30px rgba(6,20,28,0.08); }

    /* Sliders */
    .slider-thumb { accent-color: var(--accent); }

    /* Orb */
    .orb { width:220px; height:220px; border-radius:50%; background: radial-gradient(circle at 30% 30%, rgba(6,182,212,0.28), rgba(8,145,178,0.18)); filter: drop-shadow(0 20px 40px rgba(6,182,212,0.08)); display:flex; align-items:center; justify-content:center; }

    /* Layout */
    @media (min-width:1024px) {
      .layout-grid { display:grid; grid-template-columns: 1fr 420px; gap:28px; align-items:start; }
    }

    .muted { color: var(--muted); }
    .btn-primary { background: linear-gradient(90deg, var(--accent), var(--accent-2)); color: white; }
    .btn-ghost { background: white; color: var(--accent); border-radius: 10px; border:1px solid rgba(0,0,0,0.04); }

    /* small responsive tweaks */
    @media (max-width: 640px) {
      .orb { width:140px; height:140px; }
    }
  </style>
</head>
<body class="p-6">

  <!-- NAV -->
  <header class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
      <div class="text-2xl font-bold text-cyan-600">RestorativeCare</div>
      <div class="text-sm text-gray-500">— Mental Health</div>
    </div>
    <div class="flex items-center gap-3">
      <a href="index.php" class="text-sm text-gray-600 hover:text-cyan-600">Home</a>
      <a href="dashboard.php" class="text-sm text-gray-600 hover:text-cyan-600">Dashboard</a>
      <a href="mental-health.php" class="px-3 py-2 rounded-lg bg-cyan-500 text-white">Check In</a>
    </div>
  </header>

  <!-- HERO with orb and trend -->
  <section class="mb-8 relative overflow-hidden">
    <div class="parallax">
      <div class="layer reveal" data-depth="0.02">
        <div class="glass p-6 rounded-xl card-deep grid md:grid-cols-2 gap-6">
          <div>
            <h1 class="text-3xl md:text-4xl font-extrabold">Mental Well-being Check-in</h1>
            <p class="text-gray-600 mt-2">Daily check-ins help detect trends early. Log mood, stress, sleep and get coping suggestions.</p>

            <div class="mt-6 flex items-center gap-4">
              <div class="orb" id="orb3d" title="Mood orb (visual)">
                <div id="orbText" class="text-white font-bold text-xl">😊</div>
              </div>

              <div>
                <div class="text-sm muted">Patient</div>
                <div class="font-semibold"><?php echo htmlspecialchars($patientName); ?></div>
                <div class="text-sm muted mt-2">Today</div>
                <div id="todayMood" class="text-2xl font-semibold mt-1">😊 Happy</div>
                <div class="text-sm muted mt-1">Quick take: <span id="quickTake">Positive</span></div>
              </div>
            </div>
          </div>

          <div class="flex flex-col justify-center">
            <div class="text-sm muted">Recent trend</div>
            <canvas id="trendChart" height="160" class="mt-3"></canvas>
            <div class="text-xs muted mt-2">Mood over the last 14 days — higher is better.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- decorative glow -->
    <div style="position:absolute; right:-80px; top:-120px; width:420px; height:420px; background: radial-gradient(circle at 30% 30%, rgba(6,182,212,0.12), transparent); border-radius:50%; filter: blur(40px); transform: rotate(10deg);"></div>
  </section>

  <!-- MAIN GRID -->
  <main class="layout-grid">
    <!-- LEFT: check-in & history -->
    <section class="glass rounded-xl p-6 card-deep reveal layer" data-depth="0.02">

      <div class="flex items-start justify-between">
        <div>
          <h2 class="text-xl font-semibold">Daily Check-in</h2>
          <p class="text-sm muted mt-1">Static demo data. Use controls to interact visually (no data saved).</p>
        </div>
        <div class="text-sm muted">Session demo</div>
      </div>

      <!-- Mood picker -->
      <div class="mt-6">
        <div class="text-sm muted mb-2">How do you feel right now?</div>
        <div class="flex gap-3">
          <button class="mood-btn glass" onclick="uiPickMood(5)" title="Very happy">😄</button>
          <button class="mood-btn glass" onclick="uiPickMood(4)" title="Happy">🙂</button>
          <button class="mood-btn glass" onclick="uiPickMood(3)" title="Neutral">😐</button>
          <button class="mood-btn glass" onclick="uiPickMood(2)" title="Sad">☹️</button>
          <button class="mood-btn glass" onclick="uiPickMood(1)" title="Very sad">😭</button>
        </div>
      </div>

      <!-- sliders -->
      <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass p-4 rounded-lg">
          <div class="text-sm muted">Stress</div>
          <input id="stress" type="range" min="0" max="10" value="4" class="w-full slider-thumb" oninput="document.getElementById('stressVal').innerText=this.value" />
          <div class="text-sm mt-2">Level: <span id="stressVal">4</span>/10</div>
        </div>
        <div class="glass p-4 rounded-lg">
          <div class="text-sm muted">Energy</div>
          <input id="energy" type="range" min="0" max="10" value="7" class="w-full slider-thumb" oninput="document.getElementById('energyVal').innerText=this.value" />
          <div class="text-sm mt-2">Level: <span id="energyVal">7</span>/10</div>
        </div>
        <div class="glass p-4 rounded-lg">
          <div class="text-sm muted">Sleep (hours)</div>
          <input id="sleep" type="number" min="0" max="24" value="7" class="w-full p-2 rounded-md border border-white/10 bg-white/5" onchange="updateSleepQuality()" />
          <div class="text-sm mt-2">Quality: <span id="sleepQuality">Good</span></div>
        </div>
      </div>

      <!-- quick journal -->
      <div class="mt-6">
        <label class="text-sm muted">Short journal (optional)</label>
        <textarea id="journal" rows="4" class="w-full mt-2 p-3 rounded-lg border border-white/10 bg-white/5" placeholder="How was your day? Any triggers or wins?"></textarea>
        <div class="flex items-center justify-between mt-3">
          <div class="text-xs muted">This demo analyzes sentiment with simple rules.</div>
          <div class="flex gap-2">
            <button id="suggestBtn" class="px-3 py-2 rounded-lg btn-ghost" onclick="fillSuggestion()">Suggest text</button>
            <button id="saveBtn" class="px-4 py-2 rounded-lg btn-primary" onclick="demoSave()">Save (demo)</button>
          </div>
        </div>
      </div>

      <!-- analytics: heatmap & suggestions -->
      <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="glass p-4 rounded-lg">
          <div class="text-sm muted">Mood calendar (14 days)</div>
          <div id="heatmap" class="mt-3"></div>
        </div>
        <div class="glass p-4 rounded-lg">
          <div class="text-sm muted">AI suggestions</div>
          <div id="suggestions" class="mt-3 space-y-2 text-sm">
            <!-- static initial suggestions -->
          </div>
        </div>
      </div>

      <!-- recent check-ins list -->
      <div class="mt-6">
        <h3 class="text-sm font-semibold">Recent Check-ins</h3>
        <div id="history" class="mt-3 space-y-2 text-sm muted"></div>
      </div>
    </section>

    <!-- RIGHT: insights, mini charts, breathing -->
    <aside class="space-y-6">
      <div class="glass p-4 rounded-xl card-deep reveal layer" data-depth="0.06">
        <h4 class="font-semibold">Mood Trend</h4>
        <canvas id="miniTrend" height="140" class="mt-3"></canvas>
        <div class="text-xs muted mt-2">7-day mood (static demo)</div>
      </div>

      <div class="glass p-4 rounded-xl card-deep reveal layer" data-depth="0.09">
        <h4 class="font-semibold">Stress Gauge</h4>
        <div class="mt-3 flex items-center gap-3">
          <div style="flex:1">
            <div class="h-3 w-full bg-white/8 rounded-full overflow-hidden"><div id="stressBar" style="width:40%" class="h-3 bg-gradient-to-r from-yellow-400 to-red-400"></div></div>
            <div class="text-xs muted mt-2">Current: <span id="stressBig">4</span>/10</div>
          </div>
          <div class="text-lg font-semibold">4</div>
        </div>
      </div>

      <div class="glass p-4 rounded-xl card-deep reveal layer" data-depth="0.03">
        <h4 class="font-semibold">Mindful Break</h4>
        <p class="text-sm muted mt-2">Try a quick guided breathing exercise — 30s demo.</p>
        <button id="startBreath" class="mt-3 px-4 py-2 rounded-lg btn-primary">Start</button>
      </div>
    </aside>
  </main>

  <footer class="mt-8 text-center text-xs text-gray-500">&copy; <?php echo date('Y'); ?> RestorativeCare — Mental Health (Demo)</footer>

  <!-- FULL JAVASCRIPT (static data + UI) -->
  <script>
  /****************************************************************
   * Static demo mental-health UI script
   * - No localStorage / no DB
   * - All data hardcoded below so page displays immediately
   ****************************************************************/

  // --------- Static demo arrays (14 days) ----------
  const demoLabels = ["Day 1","Day 2","Day 3","Day 4","Day 5","Day 6","Day 7",
                      "Day 8","Day 9","Day 10","Day 11","Day 12","Day 13","Day 14"];
  // mood scale 1..5 (1 low, 5 high)
  const demoMood =    [5,4,4,3,5,4,5,4,4,3,4,5,5,4];
  // stress 0..10
  const demoStress =  [3,4,5,4,3,4,3,4,5,4,3,4,3,4];
  const demoEnergy =  [7,6,6,5,7,6,7,6,7,6,6,7,7,6];
  const demoSleep =   [7,6,8,6,7,7,8,6,7,6,7,8,7,7];

  // ---------- DOM elements ----------
  const orbText = document.getElementById('orbText');
  const todayMoodEl = document.getElementById('todayMood');
  const quickTakeEl = document.getElementById('quickTake');
  const heatmapEl = document.getElementById('heatmap');
  const suggestionsEl = document.getElementById('suggestions');
  const historyEl = document.getElementById('history');
  const stressBar = document.getElementById('stressBar');
  const stressBig = document.getElementById('stressBig');

  // initial static "today" values (choose last day of arrays)
  const todayIndex = demoMood.length - 1;
  const today = {
    mood: demoMood[todayIndex],
    stress: demoStress[todayIndex],
    energy: demoEnergy[todayIndex],
    sleep: demoSleep[todayIndex],
    journal: "Had a good physiotherapy session, feeling hopeful."
  };

  // helper: mood label / emoji
  function moodEmoji(m){
    if (m>=5) return '😁';
    if (m==4) return '🙂';
    if (m==3) return '😐';
    if (m==2) return '☹️';
    return '😢';
  }
  function moodLabel(m){
    if (m>=5) return 'Very good';
    if (m==4) return 'Good';
    if (m==3) return 'Okay';
    if (m==2) return 'Low';
    return 'Very low';
  }

  // ---------- Render initial "today" UI ----------
  function renderToday(){
    orbText.innerText = moodEmoji(today.mood);
    todayMoodEl.innerText = moodLabel(today.mood);
    quickTakeEl.innerText = (today.journal ? simpleSentimentPreview(today.journal) : 'No journal');
    stressBar.style.width = Math.min(100, Math.round((today.stress/10)*100)) + '%';
    stressBig.innerText = today.stress;
  }

  // ---------- Build charts (Chart.js) ----------
  // Trend chart (14 days)
  const trendCtx = document.getElementById('trendChart').getContext('2d');
  const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
      labels: demoLabels,
      datasets: [{
        label: 'Mood',
        data: demoMood,
        borderColor: '#06b6d4',
        backgroundColor: 'rgba(6,182,212,0.12)',
        tension: 0.36,
        fill: true,
        pointRadius: 4
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        y: { min: 0, max: 6, ticks: { stepSize: 1 } },
        x: { display: false }
      }
    }
  });

  // Mini 7-day bar
  const miniCtx = document.getElementById('miniTrend').getContext('2d');
  const miniChart = new Chart(miniCtx, {
    type: 'bar',
    data: {
      labels: demoLabels.slice(-7),
      datasets: [{ data: demoMood.slice(-7), backgroundColor: '#06b6d4' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { display: false }, x: { display: false } } }
  });

  // ---------- Heatmap (static) ----------
  function buildHeatmap(){
    heatmapEl.innerHTML = '';
    for (let i=0;i<demoMood.length;i++){
      const m = demoMood[i];
      const node = document.createElement('div');
      node.className = 'heatday ' + (m>=4 ? 'heat-high' : (m==3 ? 'heat-med' : 'heat-low'));
      node.title = `${demoLabels[i]} — Mood ${m} • Stress ${demoStress[i]}`;
      node.addEventListener('click', ()=> {
        alert(`${demoLabels[i]}\nMood: ${m}\nStress: ${demoStress[i]}\nEnergy: ${demoEnergy[i]}\nSleep: ${demoSleep[i]}h`);
      });
      heatmapEl.appendChild(node);
    }
  }

  // ---------- Suggestions logic (mocked) ----------
  function simpleSentimentScore(text){
    if (!text) return 0;
    const t = text.toLowerCase();
    const pos = ['good','great','happy','better','improved','grateful','hopeful','relieved','ok','okay','fine','productive','peace','good'];
    const neg = ['sad','depressed','anxious','anxiety','angry','hate','worse','terrible','lonely','tired','pain','scared','fear'];
    let s = 0;
    pos.forEach(w=> { if (t.indexOf(w) !== -1) s++; });
    neg.forEach(w=> { if (t.indexOf(w) !== -1) s--; });
    return s;
  }
  function simpleSentimentPreview(text){
    const sc = simpleSentimentScore(text);
    if (sc > 0) return 'Positive';
    if (sc === 0) return 'Neutral';
    return 'Negative';
  }

  function generateStaticSuggestions(){
    // use today's mood/stress and sentiment to pick suggestions
    const list = [];
    if (today.mood <= 2 || today.stress >= 8) {
      list.push('Contact counselor / helpline — consider booking an urgent check-in.');
      list.push('Try 3 minutes of paced breathing (4s in, 6s out).');
      list.push('If severe, visit nearest ER or call emergency services.');
    } else {
      if (simpleSentimentScore(today.journal) < 0) list.push('Write a gratitude list: 3 things you’re thankful for today.');
      list.push('Take a 10–15 minute walk in sunlight — mood booster.');
      list.push('Try a short guided meditation (5 minutes).');
    }
    return list;
  }

  // ---------- History list (static) ----------
  function buildHistory(){
    historyEl.innerHTML = '';
    for (let i=demoMood.length-1;i>=Math.max(0,demoMood.length-7);i--){
      const d = demoLabels[i];
      const m = demoMood[i];
      const st = demoStress[i];
      const node = document.createElement('div');
      node.className = 'flex items-center justify-between p-2 glass rounded-md';
      node.innerHTML = `<div><div class="font-semibold">${d}</div><div class="text-xs muted">Mood ${m} • Stress ${st} • Sleep ${demoSleep[i]}h</div></div><div class="text-xl">${moodEmoji(m)}</div>`;
      historyEl.appendChild(node);
    }
  }

  // ---------- UI pick mood (visual only) ----------
  function uiPickMood(m){
    // update orb + today label + quick take
    orbText.innerText = moodEmoji(m);
    todayMoodEl.innerText = moodLabel(m);
    quickTakeEl.innerText = (m >=4 ? 'Positive' : (m==3 ? 'Neutral' : 'Needs attention'));
    // small pulse animation
    const el = document.getElementById('orb3d');
    el.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.06)' }, { transform: 'scale(1)' }], { duration: 420 });
  }

  // ---------- Demo save (visual feedback only) ----------
  function demoSave(){
    const j = document.getElementById('journal').value;
    const s = document.getElementById('stress').value;
    const e = document.getElementById('energy').value;
    const sl = document.getElementById('sleep').value;
    // update "today" preview visually only
    today.journal = j || today.journal;
    today.stress = parseInt(s);
    today.energy = parseInt(e);
    today.sleep = parseInt(sl);
    renderToday();
    // show suggestions based on current values
    const sug = generateStaticSuggestions();
    suggestionsEl.innerHTML = sug.map(x=> `<div class="glass p-2 rounded-md">${x}</div>`).join('');
    // feedback
    const btn = document.getElementById('saveBtn');
    btn.innerText = 'Saved';
    setTimeout(()=> btn.innerText = 'Save (demo)', 900);
  }

  // suggestion filler quick text
  function fillSuggestion(){
    document.getElementById('journal').value = 'Today I felt better after a short walk and chatting with a friend.';
  }

  // ---------- Breathing exercise (demo) ----------
  document.getElementById('startBreath').addEventListener('click', ()=> {
    const btn = document.getElementById('startBreath');
    btn.disabled = true;
    let t = 30;
    const orig = btn.innerText;
    btn.innerText = `Breathing... ${t}s`;
    const iv = setInterval(()=> {
      t--;
      btn.innerText = `Breathing... ${t}s`;
      if (t <= 0) { clearInterval(iv); btn.disabled = false; btn.innerText = orig; alert('Breathing exercise finished (demo).'); }
    }, 1000);
  });

  // ---------- Support functions ----------
  function updateSleepQuality(){
    const val = parseInt(document.getElementById('sleep').value || 0);
    const el = document.getElementById('sleepQuality');
    el.innerText = (val >= 7 ? 'Good' : (val >=5 ? 'Okay' : 'Poor'));
  }

  // ---------- Parallax mouse subtle movement ----------
  document.querySelectorAll('.layer').forEach(node => node.style.willChange = 'transform');
  window.addEventListener('mousemove', (e)=>{
    const cx = window.innerWidth/2, cy = window.innerHeight/2;
    const dx = (e.clientX - cx)/cx, dy = (e.clientY - cy)/cy;
    document.querySelectorAll('.layer').forEach(el=>{
      const depth = parseFloat(el.dataset.depth || 0.02);
      el.style.transform = `translate3d(${dx * depth * -40}px, ${dy * depth * -24}px, 0) rotateX(${dy * depth * 6}deg) rotateY(${dx * depth * 10}deg)`;
    });
  });

  // ---------- Scroll reveal (IntersectionObserver) ----------
  const reveals = document.querySelectorAll('.reveal');
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible','animate__animated','animate__fadeInUp');
      }
    });
  }, { threshold: 0.12 });
  reveals.forEach(r => io.observe(r));

  // ---------- Initial render ----------
  (function init(){
    renderToday();
    buildHeatmap();
    buildHistory();
    // initial suggestions
    const sug = generateStaticSuggestions();
    suggestionsEl.innerHTML = sug.map(x=> `<div class="glass p-2 rounded-md">${x}</div>`).join('');
    // initial trend & mini charts already rendered
  })();

  // ---------- chart responsiveness ----------
  window.addEventListener('resize', ()=> { trendChart.resize(); miniChart.resize(); });

  </script>
</body>
</html>
