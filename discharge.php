<?php
// discharge.php - Redesigned, animated, single-patient demo discharge page
// No auth, no DB. Just drop into your demo folder and open in browser.

// Static demo patient (change these values as needed)
$patientId = 'P100001';
$patientName = 'Riya Patel';
$admitDate = '2025-08-06 09:40';
$dischargeDate = date('Y-m-d H:i');
$attending = 'Dr. Mehta';
$dashboardLink = "http://localhost/dashboard.php?patient=" . urlencode($patientId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Discharge — RestorativeCare (Demo)</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- jsPDF (umd) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <!-- QRCode.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

  <style>
    :root{
      --accent: #06b6d4;
      --accent-2: #0891b2;
      --glass: rgba(255,255,255,0.12);
      --glass-border: rgba(255,255,255,0.18);
    }
    html,body{height:100%}
    body{
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: radial-gradient(ellipse at 10% 10%, #f0fbff 0%, #ffffff 35%);
      color:#06202b;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }

    /* glass card */
    .glass { background: var(--glass); border:1px solid var(--glass-border); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-radius:12px; }
    .card-deep { box-shadow: 0 14px 32px rgba(6,20,28,0.08); transition: transform .18s ease, box-shadow .18s ease; }
    .card-deep:hover{ transform: translateY(-6px); box-shadow: 0 26px 48px rgba(6,20,28,0.12); }

    /* parallax layers */
    .parallax { perspective: 1000px; transform-style: preserve-3d; }
    .layer { transform-origin: center; transition: transform .45s cubic-bezier(.2,.9,.3,1); will-change: transform; }

    /* reveal */
    .reveal { opacity: 0; transform: translateY(18px); transition: all .7s cubic-bezier(.2,.9,.3,1); }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    /* signature */
    #sigCanvas { border-radius:8px; background: rgba(255,255,255,0.04); border: 1px dashed rgba(255,255,255,0.06); width:100%; height:160px; touch-action: none; }

    /* layout */
    @media (min-width: 1024px) {
      .layout { display: grid; grid-template-columns: 1fr 420px; gap:28px; align-items:start; }
    }
    .stat-pill { background: rgba(255,255,255,0.06); padding:8px 12px; border-radius:999px; font-weight:600; color:#083344; }
    .floating-hero { filter: drop-shadow(0 24px 40px rgba(6,182,212,0.08)); border-radius:12px; }
    .btn-primary { background: linear-gradient(90deg,var(--accent),var(--accent-2)); color:white; }
    .btn-ghost { background: white; color: var(--accent); border:1px solid rgba(255,255,255,0.06); }
    /* small visuals */
    .mini-icon { width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent-2));display:flex;align-items:center;justify-content:center;color:white;font-weight:700; }
  </style>
</head>
<body class="p-6">

  <!-- NAV -->
  <header class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
      <div class="text-2xl font-bold text-cyan-600">RestorativeCare</div>
      <div class="text-sm text-gray-500">— Discharge Summary (Demo)</div>
    </div>
    <nav class="flex items-center gap-3">
      <a href="index.php" class="text-sm text-gray-600 hover:text-cyan-600">Home</a>
      <a href="dashboard.php" class="text-sm text-gray-600 hover:text-cyan-600">Dashboard</a>
      <a href="#" id="printBtn" class="px-3 py-2 rounded-lg btn-ghost">Print</a>
    </nav>
  </header>

  <!-- Hero / Intro -->
  <section class="mb-6 grid md:grid-cols-3 gap-6 items-center">
    <div class="md:col-span-2 glass p-6 rounded-xl card-deep parallax reveal layer" data-depth="0.02">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-extrabold">Discharge Summary</h1>
          <p class="text-sm text-gray-500 mt-1">Finalize patient discharge, generate PDFs, and provide clear post-discharge instructions.</p>
        </div>
        <div class="text-right">
          <div class="text-xs text-gray-400">Patient ID</div>
          <div class="font-semibold text-lg"><?php echo $patientId; ?></div>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="glass p-3 rounded-lg">
          <div class="text-xs text-gray-500">Patient</div>
          <div class="font-semibold"><?php echo $patientName; ?></div>
        </div>
        <div class="glass p-3 rounded-lg">
          <div class="text-xs text-gray-500">Admitted</div>
          <div class="font-semibold"><?php echo $admitDate; ?></div>
        </div>
        <div class="glass p-3 rounded-lg">
          <div class="text-xs text-gray-500">Discharged</div>
          <div class="font-semibold"><?php echo $dischargeDate; ?></div>
        </div>
      </div>
    </div>

    <div class="glass p-4 rounded-xl card-deep parallax reveal layer text-center floating-hero" data-depth="0.06">
      <img src="https://cdn-icons-png.flaticon.com/512/2920/2920323.png" alt="AI" class="w-32 mx-auto mb-3">
      <div class="text-sm text-gray-600">Scannable access to patient records</div>
      <div id="qrcode" class="mt-3"></div>
      <a id="dashLink" href="#" class="block mt-3 text-xs text-cyan-600 underline break-words"></a>
    </div>
  </section>

  <!-- Main layout -->
  <main class="layout">
    <!-- left column: clinical + instructions -->
    <section class="glass rounded-xl p-6 card-deep parallax reveal layer" data-depth="0.02">

      <!-- Clinical summary -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold">Clinical Summary</h3>
        <div class="glass p-4 rounded-lg mt-3">
          <p class="text-sm"><strong>Final Diagnosis:</strong> Right femur fracture — postoperative recovery.</p>
          <p class="text-sm mt-2"><strong>Procedures:</strong> ORIF (Aug 9, 2025)</p>
          <p class="text-sm mt-2 text-gray-600"><strong>Hospital Course:</strong> Uneventful. Physiotherapy started. Pain controlled.</p>
        </div>
      </div>

      <!-- Meds & care -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold">Medications & Care Plan</h3>
        <div class="glass p-4 rounded-lg mt-3">
          <ul class="list-disc pl-5 text-sm">
            <li>Paracetamol 500 mg — 1 tab every 8 hours PRN (7 days)</li>
            <li>Cefixime 200 mg — 1 tab daily (5 days)</li>
            <li>Cold pack 15 min × 3/day for surgical site</li>
          </ul>
          <div class="mt-3 text-sm text-gray-600"><strong>Activity:</strong> Partial weight-bearing with walker for 4 weeks.</div>
        </div>
      </div>

      <!-- Follow-ups -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold">Follow-up & Contacts</h3>
        <div class="glass p-4 rounded-lg mt-3">
          <div class="text-sm"><strong>Next Appointment:</strong> Orthopedics OPD — Aug 28, 2025 • 10:30 AM</div>
          <div class="text-sm mt-2"><strong>Attending:</strong> <?php echo $attending; ?></div>
          <div class="mt-3 text-sm text-gray-600">For urgent issues call: +91-80-XXXX-XXXX</div>
        </div>
      </div>

      <!-- Voice playback -->
      <div class="mb-6">
        <button id="playVoice" class="px-4 py-2 rounded-lg btn-primary">🔊 Read Instructions</button>
        <button id="downloadFullPdf" class="ml-3 px-4 py-2 rounded-lg btn-ghost">📄 Download Full Discharge PDF</button>
      </div>

      <!-- Discharge checklist -->
      <div>
        <h3 class="text-lg font-semibold">Discharge Checklist</h3>
        <div class="glass p-4 rounded-lg mt-3">
          <ul class="text-sm space-y-2">
            <li><input type="checkbox" id="chk1" class="mr-2">Discharge summary handed to patient</li>
            <li><input type="checkbox" id="chk2" class="mr-2">Medications explained</li>
            <li><input type="checkbox" id="chk3" class="mr-2">Follow-up scheduled</li>
            <li><input type="checkbox" id="chk4" class="mr-2">All equipment returned</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- right column: billing, invoice, signature -->
    <aside class="space-y-6">
      <!-- billing -->
      <div class="glass rounded-xl p-4 card-deep reveal layer" data-depth="0.05">
        <div class="flex items-center justify-between mb-3">
          <div>
            <h4 class="font-semibold">Billing Summary</h4>
            <div class="text-xs text-gray-500">Invoice # INV-<?php echo substr($patientId, -4); ?></div>
          </div>
          <div class="stat-pill">₹ 60,500</div>
        </div>
        <canvas id="billChart" height="160"></canvas>
        <div class="mt-3 text-sm text-gray-600">
          <div>Room & Board: ₹12,000</div>
          <div>Procedure: ₹45,000</div>
          <div>Medicines & Supplies: ₹3,500</div>
        </div>
        <div class="mt-3 flex gap-2">
          <button id="markPaid" class="px-3 py-2 rounded-lg btn-ghost">Mark Paid</button>
          <button id="downloadInvoice" class="px-3 py-2 rounded-lg btn-primary">Download Invoice</button>
        </div>
      </div>

      <!-- signature -->
      <div class="glass rounded-xl p-4 card-deep reveal layer" data-depth="0.09">
        <h4 class="font-semibold mb-2">Patient / Guardian Signature</h4>
        <canvas id="sigCanvas"></canvas>
        <div class="mt-3 flex gap-2">
          <button id="clearSig" class="px-3 py-2 rounded-lg btn-ghost">Clear</button>
          <button id="saveSig" class="px-3 py-2 rounded-lg btn-primary">Save Signature</button>
        </div>
      </div>

      <!-- finalize -->
      <div class="glass rounded-xl p-4 card-deep reveal layer" data-depth="0.03">
        <h4 class="font-semibold mb-2">Finalize Discharge</h4>
        <div class="text-sm text-gray-600 mb-3">Ensure checklist is complete and patient consent is obtained.</div>
        <label class="inline-flex items-center">
          <input id="confirmFinal" type="checkbox" class="form-checkbox text-cyan-500 mr-2">
          <span class="text-sm">I confirm details & consent (demo)</span>
        </label>
        <div class="mt-4 flex gap-2">
          <button id="finalizeBtn" class="px-3 py-2 rounded-lg btn-primary">Finalize & Generate PDF</button>
          <button id="sendSms" class="px-3 py-2 rounded-lg btn-ghost">Send SMS</button>
        </div>
      </div>
    </aside>
  </main>

  <footer class="mt-8 text-center text-xs text-gray-500">&copy; <?php echo date('Y'); ?> RestorativeCare — Demo</footer>

  <!-- SCRIPTS -->
  <script>
    // ---------- Parallax mouse for layers ----------
    document.querySelectorAll('.layer').forEach(el => el.style.willChange = 'transform');
    window.addEventListener('mousemove', (e) => {
      const cx = window.innerWidth / 2;
      const cy = window.innerHeight / 2;
      const dx = (e.clientX - cx) / cx;
      const dy = (e.clientY - cy) / cy;
      document.querySelectorAll('.layer').forEach(node => {
        const depth = parseFloat(node.dataset.depth || 0.02);
        node.style.transform = `translate3d(${dx * depth * -30}px, ${dy * depth * -15}px, 0) rotateX(${dy * depth * 3}deg) rotateY(${dx * depth * 6}deg)`;
      });
    });

    // ---------- Scroll reveal ----------
    const reveals = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(en => {
        if (en.isIntersecting) en.target.classList.add('visible','animate__animated','animate__fadeInUp');
      });
    }, { threshold: 0.12 });
    reveals.forEach(r => io.observe(r));

    // ---------- Bill chart ----------
    const billCtx = document.getElementById('billChart').getContext('2d');
    const billChart = new Chart(billCtx, {
      type: 'doughnut',
      data: {
        labels: ['Room','Procedure','Medicines'],
        datasets: [{ data: [12000,45000,3500], backgroundColor: ['#60a5fa','#06b6d4','#f97316'] }]
      },
      options: { cutout: '65%', plugins: { legend: { position: 'bottom' } } }
    });

    // ---------- QR ----------
    const dashboardLink = "<?php echo $dashboardLink; ?>";
    new QRCode(document.getElementById('qrcode'), { text: dashboardLink, width: 140, height: 140 });
    document.getElementById('dashLink').href = dashboardLink;
    document.getElementById('dashLink').innerText = dashboardLink;

    // ---------- Signature pad ----------
    const sigCanvas = document.getElementById('sigCanvas');
    function resizeCanvas() {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      const w = sigCanvas.clientWidth;
      const h = sigCanvas.clientHeight;
      sigCanvas.width = w * ratio;
      sigCanvas.height = h * ratio;
      sigCtx.setTransform(ratio, 0, 0, ratio, 0, 0);
    }
    const sigCtx = sigCanvas.getContext('2d');
    sigCtx.strokeStyle = '#06202b';
    sigCtx.lineWidth = 2.2;
    let drawing = false;
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    function pointerPos(evt){
      const rect = sigCanvas.getBoundingClientRect();
      const x = (evt.touches ? evt.touches[0].clientX : evt.clientX) - rect.left;
      const y = (evt.touches ? evt.touches[0].clientY : evt.clientY) - rect.top;
      return {x,y};
    }
    sigCanvas.addEventListener('pointerdown', (e)=> { drawing=true; const p=pointerPos(e); sigCtx.beginPath(); sigCtx.moveTo(p.x,p.y); });
    sigCanvas.addEventListener('pointermove', (e)=> { if(!drawing) return; const p=pointerPos(e); sigCtx.lineTo(p.x,p.y); sigCtx.stroke(); });
    ['pointerup','pointerout','pointercancel'].forEach(ev => sigCanvas.addEventListener(ev, ()=> { drawing=false; sigCtx.closePath(); }));

    document.getElementById('clearSig').addEventListener('click', ()=> { sigCtx.clearRect(0,0,sigCanvas.width,sigCanvas.height); delete window._signedImage; });
    document.getElementById('saveSig').addEventListener('click', ()=> {
      const dataUrl = sigCanvas.toDataURL('image/png');
      window._signedImage = dataUrl;
      alert('Signature saved (demo).');
    });

    // ---------- Voice readout ----------
    document.getElementById('playVoice').addEventListener('click', ()=> {
      const text = `Hello <?php echo addslashes($patientName); ?>. You are discharged on <?php echo addslashes($dischargeDate); ?>. Please follow up on August 28, 2025 at 10:30 AM. Continue medications as prescribed.`;
      if ('speechSynthesis' in window) {
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'en-GB';
        speechSynthesis.speak(u);
      } else alert('Speech synthesis not supported in this browser.');
    });

    // ---------- Download full discharge PDF ----------
    document.getElementById('downloadFullPdf').addEventListener('click', async () => {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF({unit:'pt', format:'a4'});
      doc.setFontSize(18); doc.text('Discharge Summary', 40, 60);
      doc.setFontSize(11);
      doc.text(`Patient: <?php echo addslashes($patientName); ?>`, 40, 100);
      doc.text(`Patient ID: <?php echo addslashes($patientId); ?>`, 40, 118);
      doc.text(`Admitted: <?php echo addslashes($admitDate); ?>`, 40, 136);
      doc.text(`Discharged: <?php echo addslashes($dischargeDate); ?>`, 40, 154);
      doc.text('Final Diagnosis: Right femur fracture', 40, 182);
      doc.text('Follow-up: Orthopedics OPD — Aug 28, 2025 • 10:30 AM', 40, 200);
      doc.setTextColor(6,182,212); doc.text('Online record: ', 40, 230);
      doc.setTextColor(0,0,0); doc.text(dashboardLink, 120, 230, { maxWidth: 420 });
      if (window._signedImage) {
        doc.addImage(window._signedImage, 'PNG', 40, 260, 160, 80);
        doc.text('Patient / Guardian Signature', 40, 360);
      }
      doc.save('discharge_<?php echo addslashes($patientId); ?>.pdf');
    });

    // ---------- Invoice ----------
    document.getElementById('downloadInvoice').addEventListener('click', ()=> {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      doc.setFontSize(16); doc.text('Invoice', 14, 20);
      doc.setFontSize(11);
      doc.text('Patient: <?php echo addslashes($patientName); ?>', 14, 36);
      doc.text('Patient ID: <?php echo addslashes($patientId); ?>', 14, 44);
      doc.text('Total: ₹60,500', 14, 68);
      doc.save('invoice_<?php echo addslashes($patientId); ?>.pdf');
    });

    // ---------- Mark paid / send SMS / finalize ----------
    document.getElementById('markPaid').addEventListener('click', ()=> alert('Marked as paid (demo).'));
    document.getElementById('sendSms').addEventListener('click', ()=> alert('SMS sent to patient (demo).'));
    document.getElementById('finalizeBtn').addEventListener('click', ()=> {
      const ok = document.getElementById('confirmFinal').checked;
      if (!ok) return alert('Please confirm details before finalizing.');
      // Auto-generate discharge PDF and show QR (demo)
      document.getElementById('finalizeBtn').innerText = 'Finalizing...';
      setTimeout(()=> {
        alert('Discharge finalized (demo). PDF generated.');
        document.getElementById('finalizeBtn').innerText = 'Finalize & Generate PDF';
      }, 900);
    });

    // ---------- Print ---------- 
    document.getElementById('printBtn').addEventListener('click', ()=> window.print());
  </script>
</body>
</html>
