<?php
// admit.php - Admit Patient demo page
// If you have auth.php with require_auth(), keep below lines; otherwise remove them.
if (file_exists(__DIR__ . '/auth.php')) {
    require_once __DIR__ . '/auth.php';
    if (function_exists('require_auth')) require_auth();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admit Patient — RestorativeCare</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- QRCode.js (for QR generation) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

  <!-- jsPDF (for PDF generation) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

  <style>
    :root{
      --glass: rgba(255,255,255,0.12);
      --glass-border: rgba(255,255,255,0.18);
      --accent: #06b6d4;
    }
    body { font-family: 'Inter',sans-serif; background: radial-gradient(circle at 10% 10%, #f0fbff 0%, #ffffff 30%); color:#071326; }
    .glass { background: var(--glass); border:1px solid var(--glass-border); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); }
    .tilt { transform-style: preserve-3d; transition: transform .18s ease-out; will-change: transform; }
    .float-slow { animation: floatY 5s ease-in-out infinite; }
    @keyframes floatY { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-8px) } }

    .reveal { opacity: 0; transform: translateY(12px); transition: all .6s cubic-bezier(.2,.9,.3,1); }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    /* drag drop */
    .dropzone { border: 2px dashed rgba(255,255,255,0.08); padding: 18px; border-radius: 12px; text-align:center; background: linear-gradient(180deg, rgba(255,255,255,0.02), transparent); }

    /* progress steps */
    .step-dot { width: 14px; height: 14px; border-radius: 999px; background: rgba(255,255,255,0.08); display:inline-block; box-shadow: 0 4px 12px rgba(6,182,212,0.06); }
    .step-dot.active { background: var(--accent); box-shadow: 0 10px 24px rgba(6,182,212,0.18); }

    /* small */
    input::placeholder { color: rgba(7,19,38,0.35); }
    @media (min-width: 1024px) { .layout-grid { display:grid; grid-template-columns: 1fr 420px; gap:24px; align-items:start; } }
  </style>
</head>
<body class="p-6">

  <!-- header -->
  <header class="flex justify-between items-center mb-6">
    <div class="flex items-center gap-3">
      <div class="text-2xl font-bold text-cyan-600">RestorativeCare</div>
      <div class="text-sm text-gray-500">— Admit Patient</div>
    </div>
    <div>
      <a href="index.php" class="text-sm text-gray-600 hover:text-cyan-600 mr-4">Home</a>
      <a href="dashboard.php" class="px-3 py-2 bg-cyan-500 text-white rounded-lg hover:bg-cyan-600">Dashboard</a>
    </div>
  </header>

  <main class="layout-grid">
    <!-- left: wizard -->
    <section class="glass rounded-xl p-6 tilt card-deep reveal">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-2xl font-semibold">Admit New Patient</h2>
          <div class="text-xs text-gray-500">Fast admission wizard — demo mode</div>
        </div>
        <div class="text-sm">
          <span class="step-dot active" id="dot1"></span>
          <span class="step-dot" id="dot2"></span>
          <span class="step-dot" id="dot3"></span>
          <span class="step-dot" id="dot4"></span>
        </div>
      </div>

      <!-- progress bar -->
      <div class="w-full bg-white/10 rounded-full h-2 overflow-hidden mb-6">
        <div id="progressBar" class="h-2 bg-gradient-to-r from-cyan-400 to-cyan-600" style="width:25%"></div>
      </div>

      <!-- steps -->
      <form id="admitForm" onsubmit="return false;">
        <!-- STEP 1: patient details -->
         
<div>
  <label class="block text-sm text-gray-600">Blood Type</label>
  <select id="bloodType" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" required>
    <option value="">Select</option>
    <option>A+</option><option>A-</option>
    <option>B+</option><option>B-</option>
    <option>O+</option><option>O-</option>
    <option>AB+</option><option>AB-</option>
  </select>
</div>

        <div class="step" data-step="1">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm text-gray-600">Full name</label>
              <input id="fullName" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" placeholder="e.g., Riya Patel" required>
            </div>
            <div>
              <label class="block text-sm text-gray-600">Date of birth</label>
              <input id="dob" type="date" class="w-full p-3 rounded-lg border border-white/10 bg-white/5">
            </div>
            <div>
              <label class="block text-sm text-gray-600">Gender</label>
              <select id="gender" class="w-full p-3 rounded-lg border border-white/10 bg-white/5">
                <option>Female</option><option>Male</option><option>Other</option>
              </select>
            </div>
            <div>
              <label class="block text-sm text-gray-600">Phone</label>
              <input id="phone" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" placeholder="+91 98xxxxxxxx">
            </div>
            <div>
  <label class="block text-sm text-gray-600">National ID</label>
  <input id="nationalId" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" placeholder="Aadhar / Passport" required>
</div>
<div>
  <label class="block text-sm text-gray-600">Emergency Contact Name</label>
  <input id="emergencyName" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" placeholder="e.g., Raj Patel" required>
</div>
<div>
  <label class="block text-sm text-gray-600">Emergency Contact Relationship</label>
  <input id="emergencyRelation" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" placeholder="e.g., Father, Friend" required>
</div>
<div>
  <label class="block text-sm text-gray-600">Emergency Contact Phone</label>
  <input id="emergencyPhone" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" placeholder="+91 98xxxxxxxx" required>
</div>

            <!-- photo upload -->
            <div class="md:col-span-2 mt-2">
              <label class="block text-sm text-gray-600 mb-2">Photo / ID (drag & drop)</label>
              <div id="dropzone" class="dropzone glass" ondragover="event.preventDefault()" ondrop="handleDrop(event)">
                <div id="dropInner">
                  <p class="text-sm text-gray-500">Drop image here or <button type="button" id="pickBtn" class="text-cyan-500 underline">choose file</button></p>
                  <input id="fileInput" type="file" accept="image/*" class="hidden" />
                </div>
                <div id="previewWrap" class="hidden mt-3"></div>
              </div>
            </div>

            <!-- voice input -->
            <div class="md:col-span-2 mt-2 flex gap-3 items-center">
              <button type="button" id="voiceBtn" class="px-4 py-2 bg-cyan-500 text-white rounded-lg">🎤 Voice Input</button>
              <div id="voiceStatus" class="text-sm text-gray-500">Say "Name is Riya Patel" or "Age 32"</div>
            </div>

            <!-- AI autofill -->
            <div class="md:col-span-2 mt-2 flex justify-end">
              <button type="button" id="autofillBtn" class="px-4 py-2 bg-white text-cyan-600 rounded-lg border border-white/20">Suggest (AI)</button>
            </div>
          </div>
        </div>

        <!-- STEP 2: medical history -->
        <div class="step hidden" data-step="2">
          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="block text-sm text-gray-600">Known Conditions</label>
              <input id="conditions" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" placeholder="e.g., Diabetes, Hypertension">
            </div>
            <div>
              <label class="block text-sm text-gray-600">Allergies</label>
              <input id="allergies" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" placeholder="e.g., Penicillin">
            </div>
            <div>
              <label class="block text-sm text-gray-600">Current Medications</label>
              <input id="medications" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" placeholder="e.g., Metformin 500mg">
            </div>
            <div>
              <label class="block text-sm text-gray-600">Notes for staff</label>
              <textarea id="notes" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" rows="4" placeholder="Any special instructions..."></textarea>
            </div>
          </div>
        </div>

        <!-- STEP 3: admission details -->
        <div class="step hidden" data-step="3">
          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="block text-sm text-gray-600">Admit Date & Time</label>
              <input id="admitDate" type="datetime-local" class="w-full p-3 rounded-lg border border-white/10 bg-white/5">
            </div>

            <div>
              <label class="block text-sm text-gray-600">Admitting Doctor</label>
              <select id="doctor" class="w-full p-3 rounded-lg border border-white/10 bg-white/5">
                <option>Dr. Mehta — Orthopedics</option>
                <option>Dr. Cohen — Rehabilitation</option>
                <option>Dr. Singh — General Medicine</option>
              </select>
            </div>
            <div>
  <label class="block text-sm text-gray-600">Insurance Provider</label>
  <input id="insuranceProvider" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" placeholder="e.g., Star Health" required>
</div>
<div>
  <label class="block text-sm text-gray-600">Policy Number</label>
  <input id="policyNumber" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" placeholder="e.g., POL123456" required>
</div>
<div>
  <label class="block text-sm text-gray-600">Coverage Type</label>
  <select id="coverageType" class="w-full p-3 rounded-lg border border-white/10 bg-white/5">
    <option>Full</option>
    <option>Partial</option>
    <option>None</option>
  </select>
</div>


            <!-- ward availability mock -->
            <div>
              <label class="block text-sm text-gray-600">Ward / Room (select available)</label>
              <div class="grid grid-cols-3 gap-3 mt-2">
                <button type="button" class="wardOption glass p-3 rounded-lg" data-bed="Ward A - Bed 12">A - Bed 12</button>
                <button type="button" class="wardOption glass p-3 rounded-lg" data-bed="Ward A - Bed 14">A - Bed 14</button>
                <button type="button" class="wardOption glass p-3 rounded-lg" data-bed="Ward B - Bed 03">B - Bed 03</button>
                <button type="button" class="wardOption glass p-3 rounded-lg" data-bed="ICU - Bed 01">ICU - Bed 01</button>
                <button type="button" class="wardOption glass p-3 rounded-lg" data-bed="Ward C - Bed 09">C - Bed 09</button>
                <button type="button" class="wardOption glass p-3 rounded-lg" data-bed="Ward C - Bed 10">C - Bed 10</button>
              </div>
              <div id="selectedBed" class="text-sm text-gray-500 mt-2">No bed selected</div>
            </div>
          </div>
        </div>
<div>
  <label class="block text-sm text-gray-600">Reason for Admission</label>
  <textarea id="admissionReason" class="w-full p-3 rounded-lg border border-white/10 bg-white/5" rows="3" placeholder="Brief description of condition..." required></textarea>
</div>
<div>
  <label class="block text-sm text-gray-600">Admission Priority</label>
  <select id="priority" class="w-full p-3 rounded-lg border border-white/10 bg-white/5">
    <option>Emergency</option>
    <option>Urgent</option>
    <option>Routine</option>
  </select>
</div>

        <!-- STEP 4: review & confirm -->
         <div class="mt-4">
  <label class="inline-flex items-center">
    <input type="checkbox" id="consent" class="form-checkbox text-cyan-500" required>
    <span class="ml-2 text-sm text-gray-600">I consent to treatment and data storage for medical purposes.</span>
  </label>
</div>

        <div class="step hidden" data-step="4">
          <div>
            <h3 class="font-semibold mb-3">Review & Confirm</h3>
            <div id="reviewBox" class="glass p-4 rounded-lg"></div>
            <div class="mt-4 flex gap-3">
              <button id="confirmBtn" class="px-4 py-2 bg-cyan-500 text-white rounded-lg">Admit Patient</button>
              <button id="downloadPdfBtn" class="px-4 py-2 bg-white text-cyan-600 rounded-lg border border-white/20">Download PDF</button>
            </div>
          </div>
        </div>

        <!-- navigation -->
        <div class="mt-6 flex justify-between">
          <button type="button" id="prevBtn" class="px-4 py-2 bg-white text-gray-700 rounded-lg border border-white/10 hidden">Previous</button>
          <div class="flex gap-3">
            <button type="button" id="nextBtn" class="px-6 py-2 bg-cyan-500 text-white rounded-lg">Next</button>
          </div>
        </div>
      </form>
    </section>

   <!-- right: preview, QR, tips -->
<aside class="space-y-4">
  <div class="glass rounded-xl p-4 reveal tilt float-slow">
    <h4 class="font-semibold mb-2">Ward Selection</h4>

    <!-- Ward Selector -->
    <label for="wardSelect" class="block text-sm font-medium text-gray-600 mb-1">Select Ward</label>
    <select id="wardSelect" class="w-full p-2 border rounded-md text-sm mb-3">
      <option value="">-- Choose Ward --</option>
      <option value="A">Ward A</option>
      <option value="B">Ward B</option>
      <option value="C">Ward C</option>
    </select>

    <!-- Bed Selector -->
    <label for="bedSelect" class="block text-sm font-medium text-gray-600 mb-1">Select Bed</label>
    <select id="bedSelect" class="w-full p-2 border rounded-md text-sm mb-3">
      <option value="">-- Choose Bed --</option>
      <option value="1">Bed 1</option>
      <option value="2">Bed 2</option>
      <option value="3">Bed 3</option>
      <option value="4">Bed 4</option>
      <option value="5">Bed 5</option>
    </select>

    <!-- Live Preview -->
    <div id="miniPreview" class="text-sm text-gray-700">
      Complete steps to see preview.
    </div>
  </div>
</aside>

<script>
  const wardSelect = document.getElementById('wardSelect');
  const bedSelect = document.getElementById('bedSelect');
  const miniPreview = document.getElementById('miniPreview');

  function updatePreview() {
    if (wardSelect.value && bedSelect.value) {
      miniPreview.textContent = `Selected: Ward ${wardSelect.value} - Bed ${bedSelect.value}`;
    } else {
      miniPreview.textContent = "Complete steps to see preview.";
    }
  }

  wardSelect.addEventListener('change', updatePreview);
  bedSelect.addEventListener('change', updatePreview);
</script>

  </main>

  <footer class="mt-6 text-center text-xs text-gray-500">&copy; <?php echo date('Y'); ?> RestorativeCare — Demo</footer>

  <!-- Scripts -->
  <script>
    // Wizard logic
    const steps = Array.from(document.querySelectorAll('.step'));
    let current = 0;
    const progressBar = document.getElementById('progressBar');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const dots = [document.getElementById('dot1'),document.getElementById('dot2'),document.getElementById('dot3'),document.getElementById('dot4')];

    function showStep(i) {
      steps.forEach(s => s.classList.add('hidden'));
      const step = steps[i];
      step.classList.remove('hidden');
      // progress
      const pct = ((i+1)/steps.length)*100;
      progressBar.style.width = pct + '%';
      // dots
      dots.forEach((d,idx)=> d.classList.toggle('active', idx <= i));
      prevBtn.classList.toggle('hidden', i===0);
      nextBtn.innerText = (i === steps.length-1) ? 'Finish' : 'Next';
      // set mini preview
      buildMiniPreview();
    }
    showStep(0);

    nextBtn.addEventListener('click', () => {
      if (current < steps.length - 1) {
        current++;
        showStep(current);
      } else {
        // finish = go to review step (already step 4); we can auto-scroll
        alert('Review the details and click Admit Patient.');
      }
    });
    prevBtn.addEventListener('click', () => {
      if (current > 0) { current--; showStep(current); }
    });

    // File upload & drag/drop
    const fileInput = document.getElementById('fileInput');
    const pickBtn = document.getElementById('pickBtn');
    const dropzone = document.getElementById('dropzone');
    const previewWrap = document.getElementById('previewWrap');
    let uploadedFile = null;
    pickBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => handleFiles(e.target.files));
    function handleDrop(ev) {
      ev.preventDefault();
      const f = ev.dataTransfer.files;
      handleFiles(f);
    }
    function handleFiles(files) {
      if (!files[0]) return;
      const file = files[0];
      if (!file.type.startsWith('image/')) return alert('Please upload an image file');
      uploadedFile = file;
      const reader = new FileReader();
      reader.onload = (e) => {
        previewWrap.innerHTML = `<img src="${e.target.result}" class="w-32 h-32 object-cover rounded-md mx-auto">`;
        previewWrap.classList.remove('hidden');
      };
      reader.readAsDataURL(file);
    }

    // Voice input (Web Speech API)
    const voiceBtn = document.getElementById('voiceBtn');
    const voiceStatus = document.getElementById('voiceStatus');
    let recognition;
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      recognition = new SpeechRecognition();
      recognition.lang = 'en-IN';
      recognition.interimResults = false;
      recognition.onresult = (e) => {
        const text = e.results[0][0].transcript;
        voiceStatus.innerText = 'Heard: ' + text;
        parseVoiceFill(text);
      };
      recognition.onend = () => voiceStatus.innerText = 'Voice input stopped.';
    } else {
      voiceBtn.disabled = true;
      voiceStatus.innerText = 'Voice not supported in this browser.';
    }
    voiceBtn.addEventListener('click', () => {
      if (recognition) {
        recognition.start();
        voiceStatus.innerText = 'Listening... say "Name is Riya Patel" or "Phone is 98..."';
      }
    });
    function parseVoiceFill(text) {
      text = text.toLowerCase();
      if (text.includes('name')) {
        const m = text.split('name').pop().replace('is','').trim();
        if (m) document.getElementById('fullName').value = capitalizeWords(m);
      } else if (text.match(/phone|mobile/)) {
        const num = text.replace(/\D/g,'').slice(-10);
        if (num) document.getElementById('phone').value = '+91 ' + num;
      } else if (text.includes('age')) {
        // set dob approx (not implementing complex)
      }
    }
    function capitalizeWords(s){ return s.replace(/\b\w/g, c => c.toUpperCase()); }

    // AI autofill (mock)
    document.getElementById('autofillBtn').addEventListener('click', () => {
      const demo = {
        fullName: 'Riya Patel',
        dob: '1990-08-14',
        gender: 'Female',
        phone: '+91 9812345678',
        conditions: 'Hypertension',
        allergies: 'None',
        medications: 'Amlodipine 5mg',
        notes: 'Requires wheelchair assistance'
      };
      document.getElementById('fullName').value = demo.fullName;
      document.getElementById('dob').value = demo.dob;
      document.getElementById('gender').value = demo.gender;
      document.getElementById('phone').value = demo.phone;
      document.getElementById('conditions').value = demo.conditions;
      document.getElementById('allergies').value = demo.allergies;
      document.getElementById('medications').value = demo.medications;
      document.getElementById('notes').value = demo.notes;
      buildMiniPreview();
    });

    // Ward selection
    document.querySelectorAll('.wardOption').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.wardOption').forEach(b=> b.classList.remove('ring-2','ring-cyan-400'));
        btn.classList.add('ring-2','ring-cyan-400');
        document.getElementById('selectedBed').innerText = 'Selected: ' + btn.dataset.bed;
      });
    });

    // build mini preview & review
    function buildMiniPreview(){
      const name = document.getElementById('fullName').value || '—';
      const dob = document.getElementById('dob').value || '—';
      const phone = document.getElementById('phone').value || '—';
      const bed = document.getElementById('selectedBed').innerText || '—';
      document.getElementById('miniPreview').innerHTML = `<div class="flex items-center gap-3"><div class="w-12 h-12 bg-cyan-400 rounded-md flex items-center justify-center text-white font-bold">${name.split(' ').map(x=>x[0]).slice(0,2).join('')}</div><div><div class="font-semibold">${name}</div><div class="text-xs text-gray-500">${dob} • ${phone}</div><div class="text-xs text-gray-500 mt-1">${bed}</div></div></div>`;
      // review box
      const review = `
        <div><strong>Name:</strong> ${name}</div>
        <div><strong>DOB:</strong> ${dob}</div>
        <div><strong>Phone:</strong> ${phone}</div>
        <div><strong>Conditions:</strong> ${document.getElementById('conditions').value || '—'}</div>
        <div><strong>Allergies:</strong> ${document.getElementById('allergies').value || '—'}</div>
        <div><strong>Doctor:</strong> ${document.getElementById('doctor').value || '—'}</div>
        <div><strong>Bed:</strong> ${document.getElementById('selectedBed').innerText || '—'}</div>
        <div><strong>Blood Type:</strong> ${document.getElementById('bloodType').value || '—'}</div>
<div><strong>National ID:</strong> ${document.getElementById('nationalId').value || '—'}</div>
<div><strong>Emergency Contact:</strong> ${document.getElementById('emergencyName').value || '—'} (${document.getElementById('emergencyRelation').value || '—'}) — ${document.getElementById('emergencyPhone').value || '—'}</div>
<div><strong>Insurance:</strong> ${document.getElementById('insuranceProvider').value || '—'} (Policy: ${document.getElementById('policyNumber').value || '—'}) — ${document.getElementById('coverageType').value || '—'}</div>
<div><strong>Reason for Admission:</strong> ${document.getElementById('admissionReason').value || '—'}</div>
<div><strong>Priority:</strong> ${document.getElementById('priority').value || '—'}</div>

      `;
      document.getElementById('reviewBox').innerHTML = review;
    }

    // Confirm / Admit (mock)
    document.getElementById('confirmBtn').addEventListener('click', () => {
      // create a mock patient id
      const id = 'P' + Date.now().toString().slice(-6);
      // show QR
      const qrWrap = document.getElementById('qrcode'); qrWrap.innerHTML = '';
      const q = new QRCode(qrWrap, { text: location.origin + '/dashboard.php?patient=' + id, width: 140, height: 140 });
      document.getElementById('qrText').innerText = 'Patient ID: ' + id;
      // success toast
      alert('Patient admitted (demo). QR and PDF are available.');
    });

    // PDF generation (jsPDF)
    document.getElementById('downloadPdfBtn').addEventListener('click', async () => {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      const name = document.getElementById('fullName').value || '—';
      const dob = document.getElementById('dob').value || '—';
      const phone = document.getElementById('phone').value || '—';
      const conditions = document.getElementById('conditions').value || '—';
      const doctor = document.getElementById('doctor').value || '—';
      const bed = document.getElementById('selectedBed').innerText || '—';
      doc.setFontSize(16); doc.text('Admission Summary', 14, 20);
      doc.setFontSize(11);
      doc.text(`Name: ${name}`, 14, 36);
      doc.text(`DOB: ${dob}`, 14, 44);
      doc.text(`Phone: ${phone}`, 14, 52);
      doc.text(`Conditions: ${conditions}`, 14, 60);
      doc.text(`Doctor: ${doctor}`, 14, 68);
      doc.text(`Bed: ${bed}`, 14, 76);
      // attach QR as text link
      doc.setTextColor(6,182,212);
      doc.text('Access Dashboard: (scan QR on screen)', 14, 92);
      doc.save('admission_' + (name.replace(/\s+/g,'_') || 'patient') + '.pdf');
    });

    // Scroll reveal
    const reveals = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible','animate__animated','animate__fadeInUp'); });
    }, { threshold: 0.12 });
    reveals.forEach(r => io.observe(r));

    // 3D tilt on left card
    const tiltCard = document.querySelector('.tilt');
    if (tiltCard) {
      tiltCard.addEventListener('mousemove', (e) => {
        const r = tiltCard.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width;
        const y = (e.clientY - r.top) / r.height;
        const rx = (y - 0.5) * 8; const ry = (x - 0.5) * 12;
        tiltCard.style.transform = `perspective(900px) rotateX(${rx}deg) rotateY(${ry}deg)`;
      });
      tiltCard.addEventListener('mouseleave', () => tiltCard.style.transform = 'none');
    }

    // keep preview updated as user types
    ['fullName','dob','phone','conditions','allergies','medications','doctor'].forEach(id=>{
      const el = document.getElementById(id);
      if (el) el.addEventListener('input', buildMiniPreview);
    });

    // initial preview
    buildMiniPreview();
  </script>
</body>
</html>
