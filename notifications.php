<?php
// notifications.php
// Futuristic, patient-centric Notification Center (static demo)
// Drop into your demo folder and open via HTTP (e.g., http://localhost/notifications.php)

$patientName = 'Riya Patel';
$year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Notifications — RestorativeCare (Demo)</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Animate.css (micro-animations) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Chart.js (for mini charts) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Anime.js for fancy animations -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

  <style>
    :root{
      --bg: #f5fcff;
      --accent: #06b6d4;
      --accent-2: #0891b2;
      --glass: rgba(255,255,255,0.10);
      --glass-border: rgba(255,255,255,0.14);
      --muted: #6b7280;
    }
    html,body{height:100%}
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: radial-gradient(circle at 10% 10%, #eefcff 0%, #ffffff 40%);
      color: #052026;
      -webkit-font-smoothing:antialiased;
    }

    /* glass card */
    .glass { background: var(--glass); border:1px solid var(--glass-border); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-radius:12px; }
    .card-deep { box-shadow: 0 18px 40px rgba(6,20,28,0.06); transition: transform .18s, box-shadow .18s; }
    .card-deep:hover { transform: translateY(-6px); box-shadow: 0 36px 60px rgba(6,20,28,0.10); }

    /* notifications center layout */
    .layout { display:grid; grid-template-columns: 360px 1fr 360px; gap:20px; align-items:start; padding:12px; }
    @media (max-width: 1100px){
      .layout { grid-template-columns: 1fr; }
    }

    /* notification card */
    .notif { border-radius:12px; padding:14px; display:flex; gap:12px; align-items:flex-start; position:relative; overflow:hidden; }
    .notif .dot { width:12px;height:12px;border-radius:999px; margin-top:6px; flex-shrink:0; box-shadow:0 6px 18px rgba(0,0,0,0.06); }
    .notif .content { flex:1; }
    .notif .title { font-weight:700; }
    .notif .meta { font-size:12px; color:var(--muted); margin-top:6px; }

    /* urgency colors & soft glows */
    .u-critical { background: linear-gradient(135deg, rgba(251,113,133,0.95), rgba(249,115,22,0.92)); color: white; }
    .u-warning  { background: linear-gradient(135deg, rgba(251,191,36,0.95), rgba(250,204,21,0.9)); color: rgba(6,6,6,0.92); }
    .u-remind   { background: linear-gradient(135deg, rgba(96,165,250,0.95), rgba(6,182,212,0.9)); color: white; }
    .u-info     { background: linear-gradient(135deg, rgba(99,102,241,0.95), rgba(168,85,247,0.9)); color: white; }
    .u-positive { background: linear-gradient(135deg, rgba(34,197,94,0.92), rgba(16,185,129,0.9)); color: white; }

    /* small UI */
    .muted { color:var(--muted); }
    .pill { padding:6px 10px; border-radius:999px; font-weight:600; font-size:13px; }
    .btn { padding:8px 12px; border-radius:10px; cursor:pointer; }
    .btn-primary { background: linear-gradient(90deg,var(--accent),var(--accent-2)); color:white; box-shadow:0 10px 30px rgba(6,182,212,0.08); }
    .btn-ghost { background:white; border:1px solid rgba(6,182,212,0.08); color:var(--accent); }

    /* health points badge */
    .hp-badge { display:flex; gap:8px; align-items:center; padding:8px; border-radius:12px; background:linear-gradient(90deg,#ffffff,#f7ffff); }
    .hp-val { font-weight:700; color:var(--accent-2); font-size:18px; }

    /* timeline / visual feed */
    .feed { display:flex; flex-direction:column; gap:12px; }

    /* breathe-first overlay */
    #breatheOverlay {
      position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:80;
      background: linear-gradient(180deg, rgba(2,6,23,0.45), rgba(2,6,23,0.25));
    }
    .breath-circle { width:160px; height:160px; border-radius:999px; display:flex; align-items:center; justify-content:center; background: radial-gradient(circle at 30% 30%, rgba(6,182,212,0.12), rgba(6,182,212,0.06)); box-shadow:0 40px 80px rgba(6,182,212,0.06); }

    /* small responsive tweaks */
    @media (max-width:700px){
      .layout { gap:12px; padding:10px; }
      .hp-badge { display:none; }
    }
    /* tooltip */
    .tooltip { position:relative; }
    .tooltip:hover::after { content: attr(data-tip); position:absolute; left:50%; transform:translateX(-50%); top:-36px; background:rgba(6,20,28,0.9); color:white; padding:6px 10px; border-radius:8px; font-size:12px; white-space:nowrap; z-index:60; }
  </style>
</head>
<body>

  <!-- Top nav / mini header -->
  <header class="flex items-center justify-between p-4 mb-4">
    <div class="flex items-center gap-4">
      <div class="text-2xl font-bold text-cyan-600">RestorativeCare</div>
      <div class="muted">— Notifications</div>
    </div>

    <div class="flex items-center gap-3">
      <!-- theme selector -->
      <select id="themeSelect" class="p-2 rounded border">
        <option value="calm">Calm</option>
        <option value="nature">Nature</option>
        <option value="space">Space</option>
        <option value="minimal">Minimal</option>
      </select>

      <!-- voice playback -->
      <button class="btn btn-ghost" id="voicePlayBtn" title="Read important notifications aloud">🔊 Voice Play</button>

      <!-- mood-aware toggle (simulate wearable) -->
      <label class="flex items-center gap-2 muted">
        <input type="checkbox" id="moodAware" /> Mood-aware
      </label>

      <!-- health points -->
      <div class="hp-badge">
        <div class="text-xs muted">Health Points</div>
        <div class="hp-val" id="hpVal">230</div>
      </div>
    </div>
  </header>

  <!-- Main layout -->
  <main class="layout">

    <!-- LEFT: controls & quick digest -->
    <aside class="glass p-4 card-deep">
      <div class="flex items-center justify-between mb-3">
        <div>
          <div class="text-sm muted">Hello</div>
          <div class="font-semibold"><?php echo htmlspecialchars($patientName); ?></div>
        </div>
        <div class="tiny muted">ID: P100001</div>
      </div>

      <!-- AI Assistant Avatar -->
      <div class="glass p-3 rounded-lg mb-4" id="aiAssistant">
        <div class="flex items-center gap-3">
          <div id="avatar" aria-hidden="true" style="width:64px;height:64px;border-radius:999px;background:linear-gradient(135deg,#60a5fa,#06b6d4);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:22px;">RC</div>
          <div>
            <div class="font-semibold">Wellness Assistant</div>
            <div class="muted text-sm">“Hi Riya — I’ll keep your notifications calm and helpful.”</div>
          </div>
        </div>
        <div class="mt-3 flex gap-2">
          <button id="avatarSpeak" class="btn btn-ghost">Speak Latest</button>
          <button id="avatarBreathe" class="btn">Breathe</button>
        </div>
      </div>

      <!-- Digest (AI Health Summary) -->
      <div class="glass p-3 rounded-lg mb-4">
        <div class="flex items-center justify-between">
          <div class="font-semibold">Quick Digest</div>
          <div class="tiny muted">Today</div>
        </div>
        <div class="mt-3 text-sm" id="digestText">
          You have 3 upcoming items today. No critical alerts at this time. Medication adherence is steady.
        </div>
        <div class="mt-3">
          <button id="refreshDigest" class="btn btn-primary">Refresh Digest</button>
        </div>
      </div>

      <!-- Gamification / progress -->
      <div class="glass p-3 rounded-lg mb-4">
        <div class="flex items-center justify-between">
          <div>
            <div class="tiny muted">Streak</div>
            <div class="font-semibold">Medication: 12 days</div>
          </div>
          <div>
            <div class="tiny muted">Badge</div>
            <div class="font-semibold">Consistent Care</div>
          </div>
        </div>
      </div>

      <!-- Family shadow toggle -->
      <div class="glass p-3 rounded-lg">
        <div class="flex items-center justify-between">
          <div class="tiny muted">Family Alerts</div>
          <label class="inline-flex items-center">
            <input type="checkbox" id="familyToggle" />
            <span class="ml-2 muted">Enabled</span>
          </label>
        </div>
        <div class="tiny muted mt-2">When enabled, trusted guardians receive mirrored critical alerts.</div>
      </div>
    </aside>

    <!-- CENTER: immersive timeline feed -->
    <section class="glass p-4 card-deep" aria-live="polite">
      <div class="flex items-center justify-between mb-3">
        <div class="font-semibold text-lg">Health Timeline</div>
        <div class="flex items-center gap-2">
          <button id="sortBtn" class="btn btn-ghost">Sort: Urgency</button>
          <button id="clusterBtn" class="btn btn-ghost">Cluster</button>
        </div>
      </div>

      <!-- timeline / feed -->
      <div id="feed" class="feed" role="list">
        <!-- JS injects notification cards here -->
      </div>

      <!-- Calendar overlay toggle -->
      <div class="mt-4 flex items-center gap-3">
        <button id="calendarOverlay" class="btn btn-ghost">Calendar Overlay</button>
        <button id="snoozeAI" class="btn">Smart Snooze</button>
      </div>
    </section>

    <!-- RIGHT: details, mini charts, explain & interaction -->
    <aside class="glass p-4 card-deep">
      <div class="flex items-center justify-between mb-3">
        <div class="font-semibold">Details</div>
        <div class="tiny muted">Tap a notification</div>
      </div>

      <div id="detailPane" class="glass p-3 rounded-lg mb-4" style="min-height:220px;">
        <div class="muted">Select a notification to see details — chart previews, doctor voice notes, explainers, and actions appear here.</div>
      </div>

      <!-- Explain this quick tool -->
      <div class="glass p-3 rounded-lg mb-4">
        <div class="font-semibold tiny">Explain This</div>
        <div class="muted text-sm mt-2">Select a notification and click <strong>Explain</strong> to see plain-language medical explanation.</div>
        <div class="mt-3">
          <button id="explainBtn" class="btn btn-primary">Explain Selected</button>
        </div>
      </div>

      <!-- empathy rating -->
      <div class="glass p-3 rounded-lg">
        <div class="tiny muted">How did this message feel?</div>
        <div class="mt-2 flex gap-2" id="empathyBtns">
          <button class="pill" data-empathy="1">Too Blunt</button>
          <button class="pill" data-empathy="2">Neutral</button>
          <button class="pill" data-empathy="3">Comforting</button>
        </div>
      </div>
    </aside>
  </main>

  <!-- breathe overlay (for Breathe First Mode) -->
  <div id="breatheOverlay" aria-hidden="true">
    <div class="breath-circle" role="dialog" aria-modal="true" aria-label="Calming breath">
      <svg id="breathSVG" viewBox="0 0 120 120" width="140" height="140">
        <defs>
          <linearGradient id="g1" x1="0" x2="1">
            <stop offset="0" stop-color="#06b6d4" stop-opacity="0.35"/>
            <stop offset="1" stop-color="#0891b2" stop-opacity="0.25"/>
          </linearGradient>
        </defs>
        <circle cx="60" cy="60" r="44" fill="url(#g1)" />
      </svg>
    </div>
  </div>

  <!-- Explain modal -->
  <div id="explainModal" style="display:none; position:fixed; inset:0; align-items:center; justify-content:center; background:rgba(2,6,23,0.4); z-index:80;">
    <div style="background:white; padding:18px; border-radius:12px; width:clamp(300px,60%,700px);">
      <div class="flex items-center justify-between">
        <div class="font-semibold">Explain Medical Term</div>
        <button onclick="closeExplain()">✕</button>
      </div>
      <div id="explainBody" class="mt-3 text-sm muted"></div>
    </div>
  </div>

  <footer class="text-center text-xs muted p-4">&copy; <?php echo $year; ?> RestorativeCare — Demo</footer>

<script>
/* ============================
   Static demo notification dataset
   Each notification includes:
   id, type, tone, title, message, datetime, urgency (0-100), cluster (optional), doctorNote (opt)
   ============================ */
const notifications = [
  { id: 'n1', type:'lab', tone:'critical', title:'Critical Lab Result', msg:'Potassium level critically low (2.9 mmol/L). Immediate action required.', datetime:'2025-08-14T09:12', urgency:95, cluster:'labs', doctorNoteAudio:null, dataPeek:{label:'Potassium', values:[4.2,4.0,3.8,3.5,2.9]} },
  { id: 'n2', type:'reminder', tone:'remind', title:'Medication Reminder', msg:'Cefixime — take 1 tablet now.', datetime:'2025-08-14T08:00', urgency:45, cluster:'meds' },
  { id: 'n3', type:'appt', tone:'info', title:'Appointment Tomorrow', msg:'Orthopedics OPD — Aug 15, 2025 • 10:30 AM with Dr. Mehta.', datetime:'2025-08-15T10:30', urgency:30, cluster:'appts', doctorNoteAudio:'https://cdn.pixabay.com/download/audio/2022/03/15/audio_6d746a98b2.mp3?filename=beep-01-47023.mp3' },
  { id: 'n4', type:'check', tone:'positive', title:'Good Progress', msg:'Your physiotherapy notes show improved range of motion — keep it up!', datetime:'2025-08-13T18:00', urgency:10 },
  { id: 'n5', type:'message', tone:'warning', title:'Message from Care Team', msg:'Please confirm availability for discharge planning meeting.', datetime:'2025-08-13T11:00', urgency:55, cluster:'messages' },
  { id: 'n6', type:'med', tone:'remind', title:'Medication Cluster', msg:'You have 3 medication reminders today — tap to view them grouped.', datetime:'2025-08-14T07:00', urgency:40, cluster:'meds' },
  { id: 'n7', type:'nudge', tone:'positive', title:'Milestone', msg:'30 days medication adherence — reward unlocked!', datetime:'2025-08-12T09:00', urgency:5 },
  { id: 'n8', type:'alert', tone:'critical', title:'Missed Dose Alert', msg:'A recent critical dose was missed. Please contact your care team.', datetime:'2025-08-14T06:45', urgency:85 }
];

// UI state
let selectedNotificationId = null;
let healthPoints = 230;
let clusterOpen = false;
let currentSort = 'urgency'; // or 'progress' 'time'
let moodAware = false;

/* Utility: format datetime nicely */
function niceDateTime(dt){
  const d = new Date(dt);
  return d.toLocaleString(undefined, { month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
}

/* Map tone to class & color */
function toneClass(t){
  if (t === 'critical' || t === 'alert' || t === 'lab') return 'u-critical';
  if (t === 'warning') return 'u-warning';
  if (t === 'remind' || t === 'med') return 'u-remind';
  if (t === 'info' || t === 'appt') return 'u-info';
  if (t === 'positive' || t === 'nudge') return 'u-positive';
  return 'u-info';
}

/* Accessibility - announce (aria-live) */
function announce(text){
  const live = document.createElement('div');
  live.setAttribute('aria-live','polite');
  live.className = 'sr-only';
  live.innerText = text;
  document.body.appendChild(live);
  setTimeout(()=> document.body.removeChild(live), 2000);
}

/* Build feed */
function buildFeed(data = notifications){
  const feed = document.getElementById('feed');
  feed.innerHTML = '';
  const list = [...data];

  // sorting
  if (currentSort === 'urgency') list.sort((a,b)=> b.urgency - a.urgency);
  if (currentSort === 'time') list.sort((a,b)=> new Date(b.datetime) - new Date(a.datetime));
  // progress-based sorting (example): keep positive/next-step first
  if (currentSort === 'progress') list.sort((a,b)=> (a.tone==='positive'? -1:1) - (b.tone==='positive'? -1:1));

  // cluster: group by cluster key if enabled
  if (clusterOpen){
    const grouped = {};
    list.forEach(n => {
      const key = n.cluster || '_nocluster_' + n.id;
      if (!grouped[key]) grouped[key] = [];
      grouped[key].push(n);
    });
    for (const k of Object.keys(grouped)){
      if (grouped[k].length > 1){
        // cluster card
        const ccard = document.createElement('div');
        ccard.className = 'glass p-3 rounded-lg card-deep';
        ccard.innerHTML = <div class="font-semibold">${grouped[k].length} related items</div><div class="muted text-sm mt-1">Tap to expand</div>;
        ccard.addEventListener('click', ()=> {
          // expand cluster into individual cards (simple toggle)
          grouped[k].forEach(n => insertNotifCard(n, feed));
        });
        feed.appendChild(ccard);
      } else {
        insertNotifCard(grouped[k][0], feed);
      }
    }
    return;
  }

  // normal render
  list.forEach(n => insertNotifCard(n, feed));
}

/* Insert individual notification card into container (feed) */
function insertNotifCard(n, container){
  const card = document.createElement('div');
  card.className = notif ${toneClass(n.tone)} card-deep;
  card.setAttribute('role','article');
  card.setAttribute('tabindex','0');
  card.id = 'card-'+n.id;

  // dot color
  const dot = document.createElement('div');
  dot.className = 'dot';
  // small color indicator (use subtle shadow)
  dot.style.background = 'rgba(255,255,255,0.9)';
  dot.style.boxShadow = '0 6px 16px rgba(0,0,0,0.08)';

  // content
  const content = document.createElement('div');
  content.className = 'content';
  const title = document.createElement('div');
  title.className = 'title';
  title.innerText = n.title;
  const msg = document.createElement('div');
  msg.className = 'text-sm';
  msg.innerText = n.msg;
  const meta = document.createElement('div');
  meta.className = 'meta';
  meta.innerText = ${niceDateTime(n.datetime)} • Urgency ${n.urgency}%;

  // side actions
  const actions = document.createElement('div');
  actions.style.display = 'flex';
  actions.style.flexDirection = 'column';
  actions.style.gap = '8px';
  actions.style.marginLeft = '12px';

  const ackBtn = document.createElement('button');
  ackBtn.className = 'btn btn-ghost';
  ackBtn.innerText = 'Got it';
  ackBtn.onclick = (e) => { e.stopPropagation(); acknowledge(n.id); };

  const snoozeBtn = document.createElement('button');
  snoozeBtn.className = 'btn';
  snoozeBtn.innerText = 'Snooze';
  snoozeBtn.onclick = (e)=> { e.stopPropagation(); smartSnooze(n.id); };

  const explainBtn = document.createElement('button');
  explainBtn.className = 'btn btn-ghost tooltip';
  explainBtn.innerText = 'Explain';
  explainBtn.setAttribute('data-tip','Explain this term in plain language');
  explainBtn.onclick = (e) => { e.stopPropagation(); showExplain(n); };

  actions.appendChild(ackBtn);
  actions.appendChild(snoozeBtn);
  actions.appendChild(explainBtn);

  // attach mini visual peek for lab items
  if (n.dataPeek){
    const canvas = document.createElement('canvas');
    canvas.width = 220; canvas.height = 60;
    canvas.className = 'mt-2';
    // render sparkline
    setTimeout(()=> {
      new Chart(canvas.getContext('2d'), {
        type:'line',
        data:{ labels: n.dataPeek.values.map((_,i)=>i+1), datasets:[{ data: n.dataPeek.values, borderColor:'rgba(255,255,255,0.9)', backgroundColor:'rgba(255,255,255,0.08)', tension:0.3, pointRadius:0 }]},
        options:{ plugins:{ legend:{display:false}}, scales:{ x:{display:false}, y:{display:false} }, elements:{ line:{borderWidth:2} } }
      });
    }, 40);
    content.appendChild(canvas);
  }

  // doctor voice note icon
  if (n.doctorNoteAudio){
    const audioBtn = document.createElement('button');
    audioBtn.className = 'btn btn-ghost';
    audioBtn.innerText = 'Doctor Note ▶';
    audioBtn.onclick = (e)=> {
      e.stopPropagation();
      playDoctorNote(n);
    };
    actions.appendChild(audioBtn);
  }

  content.appendChild(title);
  content.appendChild(msg);
  content.appendChild(meta);

  card.appendChild(dot);
  card.appendChild(content);
  card.appendChild(actions);

  // click selects notification
  card.addEventListener('click', ()=> selectNotification(n.id));
  card.addEventListener('keydown', (e)=> { if (e.key === 'Enter') selectNotification(n.id); });

  container.appendChild(card);
}
/* Acknowledge action (smart) */
function acknowledge(id){
  // simple: remove from list visually and award points
  const idx = notifications.findIndex(n=>n.id===id);
  if (idx === -1) return;
  const n = notifications[idx];
  // award points for acknowledging non-critica
    // ---------- CONTINUATION FROM acknowledge() ----------
  // Acknowledge action (smart) - continued
  if (n.urgency >= 80) {
    // For very urgent items, show a calming "Breathe First" overlay before removing
    showBreatheOverlay(() => {
      // remove notification after breathe overlay ends
      notifications.splice(idx, 1);
      buildFeed();
      awardPoints(10);
      announce('Critical notification acknowledged. Care team notified (demo).');
      showToast('Acknowledged. Care team notified (demo).');
    });
  } else {
    // Non-critical: remove instantly and award small points
    notifications.splice(idx, 1);
    buildFeed();
    awardPoints(5);
    announce('Notification acknowledged.');
    showToast('Acknowledged (demo).');
  }
}

/* Smart snooze (demo) - postpones non-critical notifications by 30 minutes (visual only) */
function smartSnooze(id){
  const idx = notifications.findIndex(n => n.id === id);
  if (idx === -1) return;
  const n = notifications[idx];
  // If critical, recommend contacting care team
  if (n.urgency >= 80) {
    const ok = confirm('This notification appears critical. Are you sure you want to snooze it?');
    if (!ok) return;
  }
  // Postpone 30 minutes (demo: just update datetime)
  const d = new Date(n.datetime);
  d.setMinutes(d.getMinutes() + 30);
  n.datetime = d.toISOString();
  buildFeed();
  showToast('Snoozed for 30 minutes (demo).');
}

/* Explain modal - map simple explanations for common clinical terms */
function showExplain(n){
  const body = document.getElementById('explainBody');
  // simple rule-based explanation
  if (n.type === 'lab' && n.dataPeek && n.dataPeek.label) {
    body.innerHTML = `<strong>${n.dataPeek.label}</strong> — lab values vary over time. A value lower than normal may need immediate attention. In your case, the latest value is ${n.dataPeek.values.slice(-1)[0]}. If unsure, contact your care team.`;
  } else if (n.title.toLowerCase().includes('potassium')) {
    body.innerHTML = `<strong>Potassium (K)</strong> is an electrolyte important for heart and muscle function. Low levels can be serious and may cause weakness or heart rhythm changes. Your team will advise next steps.`;
  } else if (n.type === 'med' || n.type === 'reminder') {
    body.innerHTML = `This is a medication reminder. It helps keep your treatment consistent. If you have side-effects or can't take the medicine, please contact your care team.`;
  } else {
    body.innerHTML = `This alert contains information from your care team. If something is unclear, press "Ask a doctor" or call the hospital.`;
  }
  document.getElementById('explainModal').style.display = 'flex';
}

/* close explain modal */
function closeExplain(){
  document.getElementById('explainModal').style.display = 'none';
}

/* Play doctor audio note (demo) */
function playDoctorNote(n){
  if (!n.doctorNoteAudio) return;
  // simple audio player
  const audio = new Audio(n.doctorNoteAudio);
  audio.play().catch(() => showToast('Unable to play audio in this environment.'));
  showToast('Playing doctor note (demo).');
}

/* Select notification - show details in right pane */
function selectNotification(id){
  selectedNotificationId = id;
  const n = notifications.find(x => x.id === id);
  const pane = document.getElementById('detailPane');
  if (!n) {
    pane.innerHTML = '<div class="muted">Select a notification to see details.</div>';
    return;
  }
  // Build detail HTML
  let html = `<div class="font-semibold">${n.title}</div>`;
  html += `<div class="muted text-sm">${niceDateTime(n.datetime)} • Urgency ${n.urgency}%</div>`;
  html += `<div class="mt-3">${n.msg}</div>`;
  if (n.dataPeek) {
    html += `<div class="mt-3 tiny muted">Quick data peek:</div><canvas id="detailChart" width="320" height="100"></canvas>`;
  }
  if (n.doctorNoteAudio) {
    html += `<div class="mt-3"><button class="btn btn-ghost" id="playDocNoteBtn">Play Doctor Note</button></div>`;
  }
  // action buttons
  html += `<div class="mt-4 flex gap-2"><button class="btn btn-primary" id="askDocBtn">Ask a doctor</button><button class="btn" id="remindLaterBtn">Remind me later</button></div>`;
  pane.innerHTML = html;

  // if dataPeek chart required, render it
  if (n.dataPeek) {
    const ctx = document.getElementById('detailChart').getContext('2d');
    new Chart(ctx, {
      type:'line',
      data: {
        labels: n.dataPeek.values.map((_,i)=>i+1),
        datasets: [{ data: n.dataPeek.values, borderColor:'#06b6d4', backgroundColor:'rgba(6,182,212,0.12)', tension:0.3 }]
      },
      options: { plugins:{ legend:{display:false}}, scales:{ x:{display:false} } }
    });
  }

  // bind doctor note play
  if (n.doctorNoteAudio) {
    document.getElementById('playDocNoteBtn').addEventListener('click', () => playDoctorNote(n));
  }

  // bind ask doctor and remind
  document.getElementById('askDocBtn').addEventListener('click', ()=> {
    showToast('Request sent to care team (demo).');
  });
  document.getElementById('remindLaterBtn').addEventListener('click', ()=> {
    smartSnooze(id);
  });
}

/* Award health points (visual) */
function awardPoints(points){
  healthPoints += points;
  const el = document.getElementById('hpVal');
  el.innerText = healthPoints;
  // small pulse animation
  anime({
    targets: '#hpVal',
    scale: [1,1.2,1],
    duration: 700,
    easing: 'easeInOutQuad'
  });
}

/* Breathe First Mode overlay - show then callback */
function showBreatheOverlay(callback){
  const overlay = document.getElementById('breatheOverlay');
  overlay.style.display = 'flex';
  // animate circle with anime.js - breathe in/out twice
  anime({
    targets: '#breathSVG circle',
    r: [30, 52, 30],
    opacity: [0.6,0.95,0.6],
    duration: 4000,
    easing: 'easeInOutSine',
    complete: () => {
      overlay.style.display = 'none';
      if (typeof callback === 'function') callback();
    }
  });
}

/* Smart snooze AI button (global) */
document.getElementById('snoozeAI').addEventListener('click', ()=> {
  showToast('Smart snooze suggests: after lunch (1:30 PM). (demo)');
});

/* Cluster toggle */
document.getElementById('clusterBtn').addEventListener('click', ()=> {
  clusterOpen = !clusterOpen;
  document.getElementById('clusterBtn').innerText = clusterOpen ? 'Uncluster' : 'Cluster';
  buildFeed();
});

/* Sort toggle */
document.getElementById('sortBtn').addEventListener('click', ()=> {
  if (currentSort === 'urgency') currentSort = 'time';
  else if (currentSort === 'time') currentSort = 'progress';
  else currentSort = 'urgency';
  document.getElementById('sortBtn').innerText = `Sort: ${currentSort.charAt(0).toUpperCase() + currentSort.slice(1)}`;
  buildFeed();
});

/* Theme selector */
document.getElementById('themeSelect').addEventListener('change', (e)=> {
  const v = e.target.value;
  if (v === 'calm') document.body.style.background = 'radial-gradient(circle at 10% 10%, #eefcff 0%, #ffffff 40%)';
  if (v === 'nature') document.body.style.background = 'radial-gradient(circle at 10% 10%, #f0fff4 0%, #ffffff 40%)';
  if (v === 'space') document.body.style.background = 'radial-gradient(circle at 10% 10%, #f3f5ff 0%, #ffffff 40%)';
  if (v === 'minimal') document.body.style.background = '#ffffff';
});

/* Voice playback of top 3 important notifications */
document.getElementById('voicePlayBtn').addEventListener('click', avatarSpeak);
document.getElementById('avatarSpeak').addEventListener('click', avatarSpeak);

function avatarSpeak(){
  // pick top 3 by urgency
  const top = [...notifications].sort((a,b)=> b.urgency - a.urgency).slice(0,3);
  const text = top.map(t => `${t.title}. ${t.msg}`).join(' Next: ');
  if ('speechSynthesis' in window) {
    const u = new SpeechSynthesisUtterance(`Hello ${<?php echo json_encode($patientName); ?>}. ${text}`);
    u.lang = 'en-US';
    u.rate = 0.95;
    speechSynthesis.speak(u);
    showToast('Reading important notifications (demo).');
  } else {
    showToast('Speech synthesis not supported in this browser.');
  }
}

/* Avatar breathe (visual) */
document.getElementById('avatarBreathe').addEventListener('click', ()=> {
  showBreatheOverlay(()=> showToast('Breathing session finished (demo).'));
});

/* Refresh digest (mock AI summary) */
document.getElementById('refreshDigest').addEventListener('click', ()=> {
  const r = Math.random();
  let text = 'You have 3 upcoming items today. No critical alerts.';
  if (r > 0.6) text = 'One critical lab needs attention — contact orthopedics.';
  if (r > 0.9) text = 'Great progress: physiotherapy range improved.';
  document.getElementById('digestText').innerText = text;
  showToast('Digest refreshed (demo).');
});

/* Calendar overlay (demo) */
document.getElementById('calendarOverlay').addEventListener('click', ()=> {
  showToast('Calendar overlay would appear here (demo).');
});

/* family toggle */
document.getElementById('familyToggle').addEventListener('change', (e)=> {
  showToast(`Family notifications ${e.target.checked ? 'enabled' : 'disabled'} (demo).`);
});

/* Explain Selected */
document.getElementById('explainBtn').addEventListener('click', ()=> {
  if (!selectedNotificationId) { showToast('Select a notification first.'); return; }
  const n = notifications.find(x => x.id === selectedNotificationId);
  if (n) showExplain(n);
});

/* Empathy rating buttons */
document.querySelectorAll('#empathyBtns .pill').forEach(btn => {
  btn.addEventListener('click', ()=> {
    const v = btn.getAttribute('data-empathy');
    showToast('Thanks for the feedback (demo).');
    // in a real app we would send to server/AI model
  });
});

/* Simple toast */
function showToast(msg){
  let t = document.getElementById('rcToast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'rcToast';
    t.style.position = 'fixed';
    t.style.right = '20px';
    t.style.bottom = '20px';
    t.style.zIndex = 200;
    document.body.appendChild(t);
  }
  t.innerHTML = `<div class="glass p-3 rounded-lg card-deep animate__animated animate__fadeInUp">${msg}</div>`;
  setTimeout(()=> { if (t) t.innerHTML = ''; }, 2600);
}

/* Play a doctor audio note (fallback if CORS blocked) handled above */

/* Initial render */
document.addEventListener('DOMContentLoaded', ()=> {
  buildFeed();
  // announce initial state for screen readers
  announce('You have ' + notifications.length + ' notifications. ' + notifications.filter(n=>n.urgency>=80).length + ' critical.');
});

/* Expose some functions to console for demo debugging */
window._rc = { buildFeed, notifications, acknowledge, smartSnooze, showExplain };

</script>
</body>
</html>
