<?php
// schedule.php - Interactive Scheduling & Booking Wizard (static demo)
// No DB/auth. Single-file demo with animated timeline and booking flow.
$hospitalName = 'RestorativeCare';
$todayLabel = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Schedule & Booking — <?php echo htmlspecialchars($hospitalName); ?> (Demo)</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <style>
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
      font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;
      background: radial-gradient(circle at 10% 10%, #f5fcff 0%, #ffffff 35%);
      color:#052026;
      -webkit-font-smoothing:antialiased;
    }

    /* Glass card */
    .glass { background: var(--glass); border:1px solid var(--glass-border); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-radius:12px; }
    .card-deep { box-shadow: 0 12px 30px rgba(6,20,28,0.06); transition: transform .18s, box-shadow .18s; }
    .card-deep:hover { transform: translateY(-6px); box-shadow: 0 26px 48px rgba(6,20,28,0.12); }

    /* Timeline specific */
    .timeline-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom:18px; }
    .timeline { display:flex; gap:18px; align-items:stretch; padding:18px; transform-style:preserve-3d; perspective:1200px; }
    .event-card { min-width:320px; max-width:360px; padding:16px; border-radius:14px; color: #fff; position:relative; cursor:pointer; transform-origin:center; transition: transform .25s ease, box-shadow .25s ease; }
    .event-card:hover { transform: translateY(-10px) rotateX(2deg) rotateY(0.5deg); box-shadow: 0 30px 60px rgba(6,20,28,0.12); }
    .ev-time { font-weight:700; font-size:0.9rem; opacity:0.95; }
    .ev-title { font-size:1.05rem; font-weight:700; margin-top:6px; }
    .ev-sub { font-size:0.85rem; opacity:0.92; margin-top:6px; }

    /* Category colors */
    .cat-consult { background: linear-gradient(135deg,#60a5fa,#06b6d4); }
    .cat-therapy { background: linear-gradient(135deg,#34d399,#10b981); }
    .cat-surgery { background: linear-gradient(135deg,#fb7185,#f97316); }
    .cat-follow { background: linear-gradient(135deg,#fbbf24,#f97316); }

    /* booking FAB */
    .fab { position:fixed; right:20px; bottom:20px; width:68px; height:68px; border-radius:999px; display:flex; align-items:center; justify-content:center; color:white; font-size:28px; z-index:80;
           box-shadow:0 12px 36px rgba(6,20,28,0.18); }

    /* Wizard modal */
    #wizardModal { position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:90; background:rgba(2,6,23,0.35); }
    .wizard { width:100%; max-width:760px; background:linear-gradient(180deg, rgba(255,255,255,0.98), rgba(250,250,250,0.95)); border-radius:14px; padding:20px; }
    .wizard .step { display:none; }
    .wizard .step.active { display:block; }

    /* progress bullets */
    .bullets { display:flex; gap:8px; align-items:center; }
    .bullet { width:10px; height:10px; border-radius:999px; background:#e6edf6; }
    .bullet.active { background:var(--accent); box-shadow: 0 4px 10px rgba(6,182,212,0.18); }

    /* small helpers */
    .muted { color:var(--muted); }
    .tiny { font-size:12px; color:var(--muted); }

    /* timeline empty state */
    .empty-state { display:flex; gap:14px; align-items:center; justify-content:center; padding:40px; border-radius:12px; background:linear-gradient(180deg,#ffffff,#f8fbff); }
  </style>
</head>
<body class="p-6">

  <!-- NAV -->
  <header class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
      <div class="text-2xl font-bold text-cyan-600"><?php echo htmlspecialchars($hospitalName); ?></div>
      <div class="text-sm muted">— Intelligent Scheduling</div>
    </div>
    <div class="flex items-center gap-3">
      <a href="index.php" class="text-sm text-gray-600 hover:text-cyan-600">Home</a>
      <a href="dashboard.php" class="text-sm text-gray-600 hover:text-cyan-600">Dashboard</a>
    </div>
  </header>

  <!-- Intro -->
  <section class="glass p-6 rounded-xl card-deep mb-6">
    <div class="flex items-start justify-between gap-6">
      <div>
        <h1 class="text-2xl font-extrabold">Patient Timeline & Booking</h1>
        <p class="muted mt-2">A focused timeline view showing upcoming patient activity. Book new appointments with a guided wizard — collects who is booking and required details.</p>
      </div>
      <div class="text-right">
        <div class="tiny">Today</div>
        <div class="text-lg font-semibold"><?php echo date('M d, Y'); ?></div>
      </div>
    </div>
  </section>

  <!-- Controls -->
  <div class="flex items-center gap-3 mb-4">
    <div class="glass p-3 rounded-lg flex items-center gap-3">
      <label class="tiny mr-2">Filter</label>
      <select id="filterDept" class="p-2 border rounded">
        <option value="all">All Departments</option>
        <option value="ortho">Orthopedics</option>
        <option value="psych">Psychiatry</option>
        <option value="physio">Physiotherapy</option>
      </select>
      <select id="filterType" class="p-2 border rounded">
        <option value="all">All Types</option>
        <option value="consult">Consultation</option>
        <option value="therapy">Therapy</option>
        <option value="surgery">Surgery</option>
        <option value="follow">Follow-up</option>
      </select>
    </div>

    <div class="ml-auto flex items-center gap-2">
      <button id="toggleCalendar" class="px-3 py-2 rounded glass">Calendar View</button>
      <button id="toggleList" class="px-3 py-2 rounded glass">List View</button>
    </div>
  </div>

  <!-- Horizontal Timeline -->
  <div class="glass rounded-xl p-4 mb-6">
    <div class="timeline-wrap">
      <div id="timeline" class="timeline">
        <!-- Event cards will be injected here -->
      </div>
    </div>

    <!-- empty state (hidden when events exist) -->
    <div id="empty" class="hidden mt-4">
      <div class="empty-state">
        <div>
          <div class="text-lg font-semibold">No upcoming events</div>
          <div class="muted mt-2">Click "Book" to create appointments and populate the timeline.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Secondary area: quick overview -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="glass p-4 rounded-xl card-deep">
      <div class="flex items-center justify-between">
        <div>
          <div class="tiny">Next Appointment</div>
          <div class="font-semibold mt-2" id="nextApptTitle">—</div>
          <div class="tiny muted" id="nextApptTime">—</div>
        </div>
        <div>
          <div class="tiny">Progress</div>
          <div class="text-lg font-semibold mt-2">45%</div>
        </div>
      </div>
    </div>

    <div class="glass p-4 rounded-xl card-deep">
      <div>
        <div class="tiny">Available Doctors</div>
        <div class="mt-3 flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-cyan-400 flex items-center justify-center text-white">M</div>
          <div>
            <div class="font-semibold">Dr. Mehta</div>
            <div class="tiny muted">Orthopedics</div>
          </div>
        </div>
      </div>
    </div>

    <div class="glass p-4 rounded-xl card-deep">
      <div>
        <div class="tiny">Booking Queue</div>
        <div class="mt-3 text-sm" id="queueCount">3 upcoming</div>
      </div>
    </div>
  </div>

  <!-- Floating Book Appointment Button -->
  <button id="bookBtn" class="fab btn-primary glass" style="right:22px; bottom:22px;">＋</button>

  <!-- WIZARD MODAL (animated) -->
  <div id="wizardModal" aria-hidden="true">
    <div class="wizard animate__animated animate__fadeInUp">
      <div class="flex items-center justify-between mb-4">
        <div>
          <div class="text-sm muted">New Booking</div>
          <div class="text-lg font-semibold">Book Appointment</div>
        </div>
        <div class="bullets" id="wizardBullets"></div>
      </div>

      <!-- Steps -->
      <div id="stepContainer">
        <!-- Step 1: Type -->
        <div class="step active" data-step="1">
          <div class="mb-2 tiny">Step 1 — Select appointment type</div>
          <div class="grid grid-cols-2 gap-3">
            <button class="type-btn glass p-4 text-left" data-type="consult">
              <div class="font-semibold">Consultation</div>
              <div class="tiny muted">Doctor review</div>
            </button>
            <button class="type-btn glass p-4 text-left" data-type="therapy">
              <div class="font-semibold">Therapy</div>
              <div class="tiny muted">Counseling / Rehab</div>
            </button>
            <button class="type-btn glass p-4 text-left" data-type="surgery">
              <div class="font-semibold">Surgery Prep</div>
              <div class="tiny muted">Operation scheduling</div>
            </button>
            <button class="type-btn glass p-4 text-left" data-type="follow">
              <div class="font-semibold">Follow-up</div>
              <div class="tiny muted">Post-discharge check</div>
            </button>
          </div>
        </div>

        <!-- Step 2: Select Provider -->
        <div class="step" data-step="2">
          <div class="mb-2 tiny">Step 2 — Choose provider</div>
          <div class="grid grid-cols-1 gap-3" id="providerList">
            <!-- providers injected here -->
          </div>
        </div>

        <!-- Step 3: Date & Time -->
        <div class="step" data-step="3">
          <div class="mb-2 tiny">Step 3 — Choose date & time</div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="tiny">Select date</label>
              <input id="bookDate" type="date" class="w-full p-2 border rounded" />
            </div>
            <div>
              <label class="tiny">Select time</label>
              <select id="bookTime" class="w-full p-2 border rounded"></select>
            </div>
          </div>
        </div>

        <!-- Step 4: Booking details (who is booking) -->
        <div class="step" data-step="4">
          <div class="mb-2 tiny">Step 4 — Who is booking?</div>
          <div class="grid grid-cols-1 gap-3">
            <input id="patientName" class="w-full p-2 border rounded" placeholder="Patient full name" value="Riya Patel" />
            <input id="bookedBy" class="w-full p-2 border rounded" placeholder="Booked by (name)" />
            <select id="relation" class="w-full p-2 border rounded">
              <option value="self">Self</option>
              <option value="family">Family member</option>
              <option value="staff">Hospital staff</option>
            </select>
            <input id="contactPhone" class="w-full p-2 border rounded" placeholder="Contact phone" />
            <input id="contactEmail" class="w-full p-2 border rounded" placeholder="Email (optional)" />
            <textarea id="specialInstructions" class="w-full p-2 border rounded" placeholder="Special instructions (wheelchair, interpreter, payment notes)"></textarea>
          </div>
        </div>

        <!-- Step 5: Confirm -->
        <div class="step" data-step="5">
          <div class="mb-2 tiny">Step 5 — Confirm booking</div>
          <div class="glass p-4 rounded">
            <div class="text-sm tiny">Review details below and confirm booking.</div>
            <div class="mt-3">
              <div><strong>Type:</strong> <span id="confirmType"></span></div>
              <div><strong>Provider:</strong> <span id="confirmProvider"></span></div>
              <div><strong>Date & Time:</strong> <span id="confirmDateTime"></span></div>
              <div><strong>Patient:</strong> <span id="confirmPatient"></span></div>
              <div><strong>Booked by:</strong> <span id="confirmBookedBy"></span></div>
              <div><strong>Contact:</strong> <span id="confirmContact"></span></div>
              <div class="mt-2"><strong>Notes:</strong> <div id="confirmNotes" class="tiny muted"></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Wizard footer -->
      <div class="flex items-center justify-between mt-4">
        <div>
          <button id="wizardPrev" class="px-3 py-2 rounded glass">Back</button>
        </div>
        <div class="flex items-center gap-2">
          <button id="wizardCancel" class="px-3 py-2 rounded border">Cancel</button>
          <button id="wizardNext" class="px-3 py-2 rounded btn-primary">Next</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Success Toast (hidden initially) -->
  <div id="toast" style="position:fixed; right:20px; bottom:100px; display:none; z-index:95;">
    <div class="glass p-4 rounded-xl card-deep animate__animated animate__zoomIn">
      <div class="font-semibold">Booking Confirmed</div>
      <div class="tiny muted" id="toastMsg">Details saved (demo).</div>
    </div>
  </div>

<script>
/* ===========================
   Static demo data & helpers
   =========================== */
const demoProviders = [
  { id: 'p1', name: 'Dr. Mehta', dept:'ortho', title:'Orthopedics', avatar: 'M' },
  { id: 'p2', name: 'Dr. Cohen', dept:'psych', title:'Psychiatry', avatar: 'C' },
  { id: 'p3', name: 'Ms. Reddy', dept:'physio', title:'Physiotherapist', avatar: 'R' }
];

// static timeline events (demo)
let timelineEvents = [
  { id: 'e1', type:'consult', title:'Consult: Riya Patel', provider:'Dr. Mehta', providerId:'p1', dept:'ortho', datetime: '2025-08-14T09:30', status:'Upcoming' },
  { id: 'e2', type:'therapy', title:'Physio: Amit Kumar', provider:'Ms. Reddy', providerId:'p3', dept:'physio', datetime: '2025-08-15T11:00', status:'Upcoming' },
  { id: 'e3', type:'follow', title:'Follow-up: Riya Patel', provider:'Dr. Cohen', providerId:'p2', dept:'psych', datetime: '2025-08-17T14:00', status:'Upcoming' }
];

// mapping for type => look
const typeMeta = {
  consult: { label:'Consultation', cls:'cat-consult', icon:'💬' },
  therapy: { label:'Therapy', cls:'cat-therapy', icon:'🧑‍⚕️' },
  surgery: { label:'Surgery', cls:'cat-surgery', icon:'🔪' },
  follow: { label:'Follow-up', cls:'cat-follow', icon:'📞' }
};

/* ===========================
   DOM refs
   =========================== */
const timelineEl = document.getElementById('timeline');
const emptyEl = document.getElementById('empty');
const nextApptTitle = document.getElementById('nextApptTitle');
const nextApptTime = document.getElementById('nextApptTime');
const queueCount = document.getElementById('queueCount');
const filterDept = document.getElementById('filterDept');
const filterType = document.getElementById('filterType');

/* ===========================
   Render timeline
   =========================== */
function renderTimeline(){
  // apply filters
  const deptFilter = filterDept.value;
  const typeFilter = filterType.value;
  timelineEl.innerHTML = '';
  const visible = timelineEvents.filter(ev=>{
    if (deptFilter !== 'all' && ev.dept !== deptFilter) return false;
    if (typeFilter !== 'all' && ev.type !== typeFilter) return false;
    return true;
  });

  if (visible.length === 0) {
    emptyEl.classList.remove('hidden');
  } else {
    emptyEl.classList.add('hidden');
  }

  // sort by datetime
  visible.sort((a,b)=> new Date(a.datetime) - new Date(b.datetime));

  visible.forEach(ev=>{
    const meta = typeMeta[ev.type] || {};
    const card = document.createElement('div');
    card.className = `event-card ${meta.cls} card-deep reveal`;
    card.dataset.id = ev.id;
    // content
    const dt = new Date(ev.datetime);
    const timeLabel = dt.toLocaleString(undefined, { month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
    card.innerHTML = `
      <div class="ev-time">${meta.icon || '📌'} ${timeLabel}</div>
      <div class="ev-title">${ev.title}</div>
      <div class="ev-sub">${ev.provider} • ${meta.label}</div>
      <div class="mt-3 tiny">Status: ${ev.status}</div>
      <div style="position:absolute; right:10px; bottom:10px; font-size:12px; opacity:0.95">${ev.dept.toUpperCase()}</div>
    `;
    card.addEventListener('click', ()=> showEventDetails(ev));
    timelineEl.appendChild(card);
  });

  // quick stats
  const upcoming = timelineEvents.filter(e => new Date(e.datetime) >= new Date()).length;
  queueCount.innerText = `${upcoming} upcoming`;
  setNextAppointment();
  startRevealObserver(); // re-bind reveals for new cards
}

/* show details (mock) */
function showEventDetails(ev){
  alert(`${ev.title}\nProvider: ${ev.provider}\nDate: ${new Date(ev.datetime).toLocaleString()}\nStatus: ${ev.status}\n\n(Detail view - demo)`);
}

function setNextAppointment(){
  const future = timelineEvents.filter(e => new Date(e.datetime) >= new Date()).sort((a,b)=> new Date(a.datetime) - new Date(b.datetime));
  if (future.length) {
    nextApptTitle.innerText = future[0].title;
    nextApptTime.innerText = new Date(future[0].datetime).toLocaleString();
  } else {
    nextApptTitle.innerText = 'No upcoming';
    nextApptTime.innerText = '—';
  }
}

/* initial render */
renderTimeline();

/* Filters */
filterDept.addEventListener('change', renderTimeline);
filterType.addEventListener('change', renderTimeline);

/* Reveal helper using IntersectionObserver */
let revealObserver = null;
function startRevealObserver(){
  const reveals = document.querySelectorAll('.reveal');
  if (revealObserver) revealObserver.disconnect();
  revealObserver = new IntersectionObserver((entries)=>{
    entries.forEach(en=>{
      if (en.isIntersecting) {
        en.target.classList.add('visible','animate__animated','animate__fadeInUp');
      }
    });
  }, { threshold: 0.12 });
  reveals.forEach(r => revealObserver.observe(r));
}
startRevealObserver();

/* Parallax subtle mouse effect on timeline */
timelineEl.parentElement.addEventListener('mousemove', (e)=>{
  const rect = timelineEl.getBoundingClientRect();
  const cx = rect.left + rect.width / 2;
  const cy = rect.top + rect.height / 2;
  const dx = (e.clientX - cx) / cx;
  const dy = (e.clientY - cy) / cy;
  document.querySelectorAll('.event-card').forEach((card,i)=>{
    const depth = 0.02 + (i % 5) * 0.01;
    card.style.transform = `translate3d(${dx * depth * -40}px, ${dy * depth * -18}px, 0) rotateX(${dy*depth*3}deg) rotateY(${dx*depth*6}deg)`;
  });
});

/* ===========================
   Booking Wizard
   =========================== */
const wizardModal = document.getElementById('wizardModal');
const stepContainer = document.getElementById('stepContainer');
const wizardBullets = document.getElementById('wizardBullets');
const wizardNext = document.getElementById('wizardNext');
const wizardPrev = document.getElementById('wizardPrev');
const wizardCancel = document.getElementById('wizardCancel');
const bookBtn = document.getElementById('bookBtn');

let currentStep = 1;
const totalSteps = 5;
let bookingDraft = {
  type: null, providerId: null, providerName: null, date: null, time: null,
  patientName: 'Riya Patel', bookedBy: '', relation: 'self', phone: '', email:'', notes:''
};

// populate bullets
function renderBullets(){
  wizardBullets.innerHTML = '';
  for (let i=1;i<=totalSteps;i++){
    const b = document.createElement('div');
    b.className = 'bullet' + (i===currentStep?' active':'');
    wizardBullets.appendChild(b);
  }
}
renderBullets();

function openWizard(){
  wizardModal.style.display = 'flex';
  currentStep = 1;
  bookingDraft = { type:null, providerId:null, providerName:null, date:null, time:null, patientName:'Riya Patel', bookedBy:'', relation:'self', phone:'', email:'', notes:'' };
  showStep(currentStep);
  renderProvidersList(); // step 2 content
  populateTimeOptions(); // step 3
}
bookBtn.addEventListener('click', openWizard);
wizardCancel.addEventListener('click', closeWizard);
document.getElementById('wizardCancel').addEventListener('click', closeWizard);
wizardPrev.addEventListener('click', ()=> {
  if (currentStep>1) showStep(currentStep-1);
});
wizardNext.addEventListener('click', ()=> {
  if (currentStep < totalSteps) {
    // validate step before moving
    if (!validateStep(currentStep)) return;
    showStep(currentStep+1);
  } else {
    // finalize booking
    if (!validateStep(currentStep)) return;
    finalizeBooking();
  }
});

function closeWizard(){
  wizardModal.style.display = 'none';
}

/* show step */
function showStep(n){
  // hide all
  document.querySelectorAll('.wizard .step').forEach(s => s.classList.remove('active'));
  const el = document.querySelector('.wizard .step[data-step="'+n+'"]');
  if (el) el.classList.add('active');
  currentStep = n;
  renderBullets();

  // special actions
  if (n === 2) renderProvidersList();
  if (n === 3) populateTimeOptions();
  if (n === 5) populateConfirm();
  // update wizard Next text
  wizardNext.innerText = (n === totalSteps) ? 'Confirm' : 'Next';
}

/* Step validation (simple) */
function validateStep(n){
  if (n===1) {
    if (!bookingDraft.type) { alert('Please select an appointment type.'); return false; }
  }
  if (n===2) {
    if (!bookingDraft.providerId) { alert('Please choose a provider.'); return false; }
  }
  if (n===3) {
    const date = document.getElementById('bookDate').value;
    const time = document.getElementById('bookTime').value;
    if (!date || !time) { alert('Please select date and time.'); return false; }
    bookingDraft.date = date;
    bookingDraft.time = time;
  }
  if (n===4) {
    const pname = document.getElementById('patientName').value.trim();
    const bookedBy = document.getElementById('bookedBy').value.trim();
    const phone = document.getElementById('contactPhone').value.trim();
    if (!pname || !bookedBy || !phone) { alert('Please fill patient name, booked by and contact phone.'); return false; }
    bookingDraft.patientName = pname;
    bookingDraft.bookedBy = bookedBy;
    bookingDraft.relation = document.getElementById('relation').value;
    bookingDraft.phone = phone;
    bookingDraft.email = document.getElementById('contactEmail').value.trim();
    bookingDraft.notes = document.getElementById('specialInstructions').value.trim();
  }
  return true;
}

/* Step 1: type buttons */
document.querySelectorAll('.type-btn').forEach(btn => {
  btn.addEventListener('click', ()=> {
    document.querySelectorAll('.type-btn').forEach(b=> b.classList.remove('ring-2','ring-cyan-400'));
    btn.classList.add('ring-2','ring-cyan-400');
    bookingDraft.type = btn.dataset.type;
  });
});

/* Step 2: provider list */
function renderProvidersList(){
  const el = document.getElementById('providerList');
  el.innerHTML = '';
  demoProviders.forEach(p=>{
    const row = document.createElement('div');
    row.className = 'glass p-3 rounded flex items-center gap-3 cursor-pointer';
    row.innerHTML = `<div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-400 to-cyan-400 text-white flex items-center justify-center">${p.avatar}</div>
                     <div><div class="font-semibold">${p.name}</div><div class="tiny muted">${p.title}</div></div>`;
    row.addEventListener('click', ()=> {
      document.querySelectorAll('#providerList .glass').forEach(n=> n.classList.remove('ring-2','ring-cyan-400'));
      row.classList.add('ring-2','ring-cyan-400');
      bookingDraft.providerId = p.id;
      bookingDraft.providerName = p.name;
    });
    el.appendChild(row);
  });
}

/* Step 3: time select helper */
function populateTimeOptions(){
  const select = document.getElementById('bookTime');
  select.innerHTML = '';
  const times = ['09:00','09:30','10:00','10:30','11:00','11:30','13:00','13:30','14:00','14:30','15:00','15:30'];
  times.forEach(t=>{
    const o = document.createElement('option');
    o.value = t;
    o.textContent = t;
    select.appendChild(o);
  });
  // set default date to today
  const dateIn = document.getElementById('bookDate');
  if (!dateIn.value) dateIn.value = '<?php echo date("Y-m-d"); ?>';
}

/* Step 5 confirmation populate */
function populateConfirm(){
  document.getElementById('confirmType').innerText = typeMetaLabel(bookingDraft.type);
  document.getElementById('confirmProvider').innerText = bookingDraft.providerName || '—';
  document.getElementById('confirmDateTime').innerText = (bookingDraft.date && bookingDraft.time) ? (bookingDraft.date + ' ' + bookingDraft.time) : '—';
  document.getElementById('confirmPatient').innerText = bookingDraft.patientName;
  document.getElementById('confirmBookedBy').innerText = bookingDraft.bookedBy;
  document.getElementById('confirmContact').innerText = (bookingDraft.phone || '') + (bookingDraft.email ? (' • ' + bookingDraft.email) : '');
  document.getElementById('confirmNotes').innerText = bookingDraft.notes || '—';
}

function typeMetaLabel(t){
  return (typeMeta[t] && typeMeta[t].label) ? typeMeta[t].label : (t || '—');
}

/* finalize booking (demo) */
function finalizeBooking(){
  // create a timeline event object from bookingDraft
  const dtStr = (bookingDraft.date || '<?php echo date("Y-m-d"); ?>') + 'T' + (bookingDraft.time || '09:00');
  const newEv = {
    id: 'ev' + Math.random().toString(36).slice(2,9),
    type: bookingDraft.type || 'consult',
    title: `${typeMetaLabel(bookingDraft.type)}: ${bookingDraft.patientName}`,
    provider: bookingDraft.providerName || 'TBD',
    providerId: bookingDraft.providerId || null,
    dept: (bookingDraft.providerId && demoProviders.find(p=>p.id===bookingDraft.providerId)?.dept) || 'ortho',
    datetime: dtStr,
    status: 'Upcoming'
  };
  timelineEvents.push(newEv);
  renderTimeline();
  closeWizard();
  showToast('Booking confirmed for ' + bookingDraft.patientName + ' on ' + (bookingDraft.date || '') + ' ' + (bookingDraft.time || ''));
}

/* toast */
function showToast(msg){
  const t = document.getElementById('toast');
  document.getElementById('toastMsg').innerText = msg;
  t.style.display = 'block';
  setTimeout(()=> {
    t.style.display = 'none';
  }, 3000);
}

/* Pre-fill step 2/3 elements on wizard open */
document.getElementById('bookBtn').addEventListener('click', ()=> {
  openWizard();
});
function openWizard(){
  wizardModal.style.display = 'flex';
  showStep(1);
  renderProvidersList();
  populateTimeOptions();
}

/* close on outside click */
wizardModal.addEventListener('click', (e)=>{
  if (e.target === wizardModal) closeWizard();
});

/* allow Esc to close */
window.addEventListener('keydown', (e)=> { if (e.key === 'Escape') closeWizard(); });

/* prebind confirm element ids used in step 5 */
(function bindConfirmIds(){
  // create placeholders in DOM if not present
  const ids = ['confirmType','confirmProvider','confirmDateTime','confirmPatient','confirmBookedBy','confirmContact','confirmNotes'];
  ids.forEach(id => {
    if (!document.getElementById(id)) {
      const el = document.createElement('span'); el.id = id; el.innerText = '—';
      document.body.appendChild(el); el.style.display='none';
    }
  });
})();

/* toggle list/calendar (static demo toggles a small alert) */
document.getElementById('toggleCalendar').addEventListener('click', ()=> {
  alert('Calendar view (demo) — would show compact monthly calendar. This demo focuses on Timeline + Booking wizard.');
});
document.getElementById('toggleList').addEventListener('click', ()=> {
  alert('List view toggled (demo). Timeline remains the main interactive view.');
});

/* Initialize a few UI values */
renderTimeline();

</script>
</body>
</html>
