<?php
// blog.php — RestorativeCare Blog (Premium) — single-file drop-in
// Keep this with index.php, about.php, contact.php, etc.
$year = date('Y');

/**
 * Demo posts — replace with DB/content later.
 * Each post includes a compact schema used by the UI.
 */
$posts = [
  [
    "id" => "p-heart-001",
    "title" => "Heart-Healthy Morning Routines That Actually Work",
    "type" => "Wellness Tip",
    "category" => "Cardiology",
    "difficulty" => "Easy",
    "words" => 980,
    "doctor" => ["name" => "Dr. Aisha Verma", "role" => "Cardiologist", "avatar" => "https://i.pravatar.cc/150?img=32"],
    "tags" => ["cholesterol","bp","walking","sleep"],
    "summary" => "A gentle blueprint for mornings that support a resilient heart: sleep hygiene, hydration, light mobility, and medication adherence.",
    "video" => "https://www.youtube.com/embed/2V-20Qe4M8Y",
    "cover" => "https://images.unsplash.com/photo-1516542076529-1ea3854896e1?q=80&w=1400&auto=format&fit=crop",
    "chart" => [72,84,91,105,120], // sample “adherence improvement” data
    "testimony" => ["quote" => "After following this plan, my morning BP dropped by 8 points in 3 weeks.", "name" => "Nina P.", "age" => 42]
  ],
  [
    "id" => "p-brain-002",
    "title" => "Migraine Myths Debunked: What Triggers Really Mean",
    "type" => "Research",
    "category" => "Neurology",
    "difficulty" => "Medium",
    "words" => 1400,
    "doctor" => ["name" => "Dr. Leo Park", "role" => "Neurologist", "avatar" => "https://i.pravatar.cc/150?img=12"],
    "tags" => ["migraine","caffeine","hydration","screen-time"],
    "summary" => "We examine evidence behind common migraine triggers and how to build a practical avoidance plan without over-restricting life.",
    "video" => "https://www.youtube.com/embed/1APwq1df6Mw",
    "cover" => "https://images.unsplash.com/photo-1512069772995-ec65ed45afd6?q=80&w=1400&auto=format&fit=crop",
    "chart" => [5,7,6,9,12],
    "testimony" => ["quote" => "Understanding my real triggers halved my episodes.", "name" => "Mark J.", "age" => 35]
  ],
  [
    "id" => "p-skin-003",
    "title" => "Sunscreen Science: SPF, UVA/UVB & Daily Habits",
    "type" => "Guideline",
    "category" => "Dermatology",
    "difficulty" => "Easy",
    "words" => 820,
    "doctor" => ["name" => "Dr. Sofia Martinez", "role" => "Dermatologist", "avatar" => "https://i.pravatar.cc/150?img=5"],
    "tags" => ["spf","uva","uvb","hyperpigmentation"],
    "summary" => "How to pick the right SPF, why broad-spectrum matters, and the 2-finger rule that keeps skin protected every day.",
    "video" => "https://www.youtube.com/embed/fxs90A9Kq6o",
    "cover" => "https://images.unsplash.com/photo-1512499617640-c2f999098c9a?q=80&w=1400&auto=format&fit=crop",
    "chart" => [10,18,26,34,45],
    "testimony" => ["quote" => "My melasma is calmer since adopting daily SPF.", "name" => "Priya R.", "age" => 29]
  ],
  [
    "id" => "p-gut-004",
    "title" => "The 3–3–3 Gut Reset (Simple, Safe, Evidence-Informed)",
    "type" => "Wellness Tip",
    "category" => "Gastroenterology",
    "difficulty" => "Easy",
    "words" => 1100,
    "doctor" => ["name" => "Dr. Evan Cole", "role" => "Gastroenterologist", "avatar" => "https://i.pravatar.cc/150?img=20"],
    "tags" => ["fiber","hydration","fermented","prebiotic","probiotic"],
    "summary" => "Three days of gentle changes to fiber, fluids, and fermented foods that can help reset regularity and comfort.",
    "video" => "https://www.youtube.com/embed/3GwjfUFyY6M",
    "cover" => "https://images.unsplash.com/photo-1551218808-94e220e084d2?q=80&w=1400&auto=format&fit=crop",
    "chart" => [2,3,5,7,11],
    "testimony" => ["quote" => "Bloating way down after day two. Kept the habit.", "name" => "Olivia T.", "age" => 31]
  ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Blog — RestorativeCare</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">

  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>

  <style>
    :root{
      --bg1:#f0faff; --bg2:#ffffff; --ink:#052026; --muted:#6b7280;
      --accent:#06b6d4; --accent-2:#0ea5b7; --glass:rgba(255,255,255,0.15);
      --glass-border:rgba(255,255,255,0.25); --shadow:rgba(6,20,28,0.18);
    }
    body{ font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:var(--ink);
      background: radial-gradient(circle at -10% -10%, var(--bg1), var(--bg2) 45%); overflow-x:hidden; }
    .glass{ background:var(--glass); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);
      border:1px solid var(--glass-border); border-radius:14px; }
    .btn{ display:inline-flex; align-items:center; justify-content:center; gap:.5rem; padding:.8rem 1.05rem; border-radius:12px;
      font-weight:800; cursor:pointer; transition:transform .15s ease, box-shadow .15s ease; box-shadow:0 10px 30px rgba(6,182,212,.10); user-select:none;}
    .btn-primary{ color:white; background:linear-gradient(90deg,var(--accent),var(--accent-2)); }
    .btn-ghost{ color:var(--accent-2); background:rgba(255,255,255,.92); border:1px solid rgba(6,182,212,.15); }
    .btn:hover{ transform:translateY(-2px); }
    .link{ color:var(--accent-2); font-weight:700 } .muted{ color:var(--muted) } .shadow-deep{ box-shadow:0 30px 80px rgba(6,20,28,0.08) }
    .chip{ display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; background:#f0fdff; color:#0b5661; font-weight:800; font-size:.75rem }
    .nav-wrap{ position:sticky; top:12px; z-index:50 } .nav{ display:flex; align-items:center; justify-content:space-between; gap:16px; }
    .nav-brand{ display:flex; align-items:center; gap:12px; font-weight:900; font-size:1.45rem; color:var(--accent-2) }
    .nav-brand-badge{ width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:900;
      background:linear-gradient(135deg,#60a5fa,var(--accent)); box-shadow:0 12px 26px rgba(6,182,212,.22) }
    .nav-links a{ padding:.55rem .9rem; border-radius:12px; font-weight:700; color:#0f3540; }
    .nav-links a:hover{ background:rgba(6,182,212,.08); transform:translateY(-2px) }

    /* Hero */
    .hero{ position:relative; overflow:hidden }
    .hero::before{
      content:""; position:absolute; inset:-20%; background:
        radial-gradient(60% 60% at 20% 20%, rgba(14,165,183,.25), transparent 60%),
        radial-gradient(50% 50% at 80% 0%, rgba(99,102,241,.20), transparent 60%),
        radial-gradient(40% 40% at 50% 100%, rgba(6,182,212,.18), transparent 60%);
      filter: blur(40px);
      animation: floatBg 14s ease-in-out infinite alternate;
    }
    @keyframes floatBg { from { transform: translateY(-10px)} to { transform: translateY(10px)} }

    /* Blog grid */
    .ribbon{ position:absolute; top:12px; left:-6px; background:linear-gradient(90deg,#60a5fa,var(--accent)); color:#fff; padding:6px 10px;
      font-weight:900; border-radius:10px; box-shadow:0 10px 20px rgba(6,182,212,.2)}
    .ribbon::after{ content:""; position:absolute; right:-8px; top:0; border-top:16px solid #3fb6d0; border-right:8px solid transparent; }
    .badge-spec{ position:absolute; bottom:-4px; right:-4px; background:linear-gradient(135deg,#f59e0b,#ef4444); color:#fff; border-radius:999px;
      font-size:10px; padding:2px 6px; font-weight:900; box-shadow:0 8px 18px rgba(239,68,68,.25)}
    .card{ background:linear-gradient(180deg,rgba(255,255,255,.96),rgba(255,255,255,.88)); border:1px solid rgba(6,182,212,.12); border-radius:16px; overflow:hidden;
      transition:transform .18s ease, box-shadow .18s ease; position:relative }
    .card:hover{ transform:translateY(-4px); box-shadow:0 24px 60px rgba(6,20,28,.12) }
    .cat-Cardiology{ --cat:#ef4444 } .cat-Neurology{ --cat:#6366f1 } .cat-Dermatology{ --cat:#22c55e } .cat-Gastroenterology{ --cat:#06b6d4 }
    .cat-pill{ display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; border:1px solid color-mix(in oklab, var(--cat) 35%, #ffffff);
      background: color-mix(in oklab, var(--cat) 14%, #ffffff); color: color-mix(in oklab, var(--cat) 80%, #062128); font-weight:800; font-size:.7rem }

    /* Ticker */
    .ticker{ overflow:hidden; position:relative; }
    .ticker-track{ display:flex; gap:14px; white-space:nowrap; animation: scroll 22s linear infinite }
    @keyframes scroll{ from{ transform:translateX(0)} to{ transform:translateX(-50%)} }

    /* Tooltip glossary */
    .term{ border-bottom:2px dotted rgba(6,182,212,.6); cursor:help; position:relative }
    .term:hover .tip{ opacity:1; transform:translateY(0) }
    .tip{ position:absolute; left:0; top:120%; background:#032026; color:#e6fffb; padding:.55rem .7rem; border-radius:10px; font-size:.75rem;
      white-space:nowrap; opacity:0; transform:translateY(6px); transition: all .12s ease; z-index:5 }

    /* Reader badges */
    .badge{ border-radius:999px; padding:.4rem .7rem; font-weight:900; font-size:.7rem; background:#ecfeff; border:1px solid rgba(6,182,212,.18); color:#075e69 }

    /* Modal */
    .modal{ position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(5,32,38,.55); z-index:70 }
    .modal .panel{ background:#fff; border-radius:16px; width:min(920px,96vw); padding:0; overflow:hidden; box-shadow:0 40px 120px rgba(3,18,22,.35)}
    .modal.show{ display:flex }

    /* Mini world map card (fixed size) */
    .world-card{ width:100%; max-width:520px; height:300px; border-radius:14px; overflow:hidden; border:1px solid rgba(6,182,212,.15); background:#fff }
    .world-map{ width:100%; height:100% }

    /* Audio read button */
    .tts{ position:relative }
    .tts.playing::after{ content:""; position:absolute; inset:-6px; border-radius:12px; border:2px dashed rgba(6,182,212,.5); animation: dash 1s linear infinite }
    @keyframes dash{ to{ stroke-dashoffset:100 } }

    /* Footer grid */
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
        <a href="about.php">About</a>
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
      <a href="features.php" class="block p-2 rounded hover:bg-cyan-50">Features</a>
      <a href="about.php" class="block p-2 rounded hover:bg-cyan-50">About</a>
      <a href="blog.php" class="block p-2 rounded hover:bg-cyan-50">Blog</a>
      <a href="contact.php" class="block p-2 rounded hover:bg-cyan-50">Contact</a>
      <a href="dashboard.php" class="block p-2 rounded hover:bg-cyan-50">Dashboard</a>
      <a href="admit.php" class="block p-2 rounded hover:bg-cyan-50">Admit Patient</a>
    </div>
  </div>

  <!-- HERO -->
  <header class="px-4 md:px-6 mt-6">
    <div class="hero glass p-5 md:p-7 shadow-deep">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="max-w-2xl">
          <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">Expert voices • Real impact</div>
          <h1 class="text-3xl md:text-5xl font-extrabold mt-1">RestorativeCare Blog</h1>
          <p class="muted mt-2">
            Research, wellness, and practical guidance from our clinicians. Low noise, high clarity.
            Hover <span class="term">A1c<span class="tip">Average blood sugar (≈3 months)</span></span>, <span class="term">SPF<span class="tip">Sun Protection Factor</span></span>, and more for instant glossaries.
          </p>

          <!-- Trending tags ticker (Feature #7) -->
          <div class="ticker mt-4 p-2 rounded-lg bg-white/80 border border-cyan-100">
            <div class="ticker-track" id="tickerTrack" aria-label="Trending health tags">
              <?php
                $allTags = array_unique(array_merge(...array_map(fn($p) => $p['tags'], $posts)));
                $loop = array_merge($allTags, $allTags); // make it long for scroll
                foreach($loop as $tg): ?>
                  <span class="pill">#<?= htmlspecialchars($tg) ?></span>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="chip"><i data-feather="star" class="w-4 h-4"></i> Hand-checked by specialists</span>
            <span class="chip"><i data-feather="headphones" class="w-4 h-4"></i> Audio read-aloud on every post</span>
            <span class="chip"><i data-feather="activity" class="w-4 h-4"></i> Live readers now</span>
          </div>
        </div>

        <!-- Mini global heatmap of most-read topics (Feature #20) -->
        <div class="world-card">
          <svg viewBox="0 0 800 460" class="world-map" id="worldMap" role="img" aria-label="Most-read topics worldwide">
            <rect width="800" height="460" fill="#ecfeff"/>
            <!-- super-simplified continents (abstract blobs) -->
            <g id="regions" fill="#c7f9ff" stroke="#8ee5f3" stroke-width="1">
              <path id="na" d="M80,110 C150,60 270,70 330,120 C370,150 320,220 260,230 C210,240 140,210 100,170 Z"/>
              <path id="sa" d="M320,260 C360,260 380,320 360,360 C340,400 300,420 280,380 C260,340 280,280 320,260 Z"/>
              <path id="eu" d="M420,110 C470,90 520,110 560,140 C530,160 470,170 440,150 Z"/>
              <path id="af" d="M500,180 C560,170 620,210 610,280 C600,350 530,360 490,320 C460,290 470,210 500,180 Z"/>
              <path id="as" d="M580,120 C660,120 720,160 740,210 C710,240 630,240 600,210 C580,190 560,140 580,120 Z"/>
              <path id="au" d="M650,340 C700,330 740,350 730,390 C700,410 660,410 640,380 Z"/>
            </g>
            <!-- legend -->
            <g font-size="12" font-weight="800" fill="#075e69">
              <rect x="16" y="392" width="16" height="10" fill="#dffbff" stroke="#8ee5f3"/>
              <text x="38" y="401">Low</text>
              <rect x="86" y="392" width="16" height="10" fill="#9eefff" stroke="#8ee5f3"/>
              <text x="108" y="401">Medium</text>
              <rect x="186" y="392" width="16" height="10" fill="#43d6f0" stroke="#8ee5f3"/>
              <text x="208" y="401">High</text>
              <text x="16" y="420">Live topic interest heatmap</text>
            </g>
          </svg>
        </div>
      </div>
    </div>
  </header>

  <!-- BLOG GRID -->
  <main class="px-4 md:px-6 mt-6 space-y-6">

    <!-- Top Doctors Leaderboard (Feature #22) + Reader Badges (Feature #23) -->
    <section class="glass p-5 shadow-deep">
      <div class="flex items-center justify-between gap-3">
        <div>
          <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">Community</div>
          <h2 class="text-2xl font-extrabold">Top Doctors • Your Badges</h2>
        </div>
        <div class="text-right">
          <div class="text-sm muted">Reading points: <span id="readPoints">0</span></div>
          <div id="badgeRack" class="mt-1 flex flex-wrap gap-1 justify-end"></div>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3" id="leaderboard">
        <!-- Populated by JS -->
      </div>
    </section>

    <!-- Posts -->
    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <?php foreach($posts as $p): ?>
        <article class="card cat-<?= htmlspecialchars($p['category']) ?>" data-postid="<?= $p['id'] ?>">
          <div class="ribbon"><?= htmlspecialchars($p['type']) ?></div>
          <div class="relative">
            <img src="<?= htmlspecialchars($p['cover']) ?>" alt="" class="w-full h-44 object-cover">
            <!-- Doctor avatar + specialty badge (Features #2 & #3 extension) -->
            <div class="absolute -bottom-5 left-4 flex items-center gap-2">
              <div class="relative">
                <img src="<?= htmlspecialchars($p['doctor']['avatar']) ?>" class="w-12 h-12 rounded-full border-2 border-white shadow"/>
                <span class="badge-spec"><?= htmlspecialchars($p['doctor']['role']) ?></span>
              </div>
              <span class="cat-pill"><i data-feather="tag" class="w-3 h-3"></i> <?= htmlspecialchars($p['category']) ?></span>
            </div>
          </div>

          <div class="p-4 pt-7">
            <h3 class="font-extrabold text-lg"><?= htmlspecialchars($p['title']) ?></h3>

            <!-- Reading time & difficulty (Feature #8) -->
            <div class="mt-1 text-xs muted">
              <span data-words="<?= $p['words'] ?>" class="readingTime"></span> • Difficulty: <strong><?= htmlspecialchars($p['difficulty']) ?></strong>
              • <span class="liveReaders" data-base="<?= rand(12,85) ?>">0</span> reading now
            </div>

            <p class="muted mt-2 text-sm"><?= htmlspecialchars($p['summary']) ?></p>

            <!-- Infographic mini chart (Feature #13) -->
            <canvas aria-label="Infographic" class="w-full mt-3 rounded border border-cyan-100 bg-white" height="90"
                    data-points="<?= implode(',', $p['chart']) ?>"></canvas>

            <!-- Patient story (Feature #15) -->
            <blockquote class="mt-3 p-3 rounded-lg bg-cyan-50/60 border border-cyan-100 text-sm">
              “<?= htmlspecialchars($p['testimony']['quote']) ?>”
              <span class="block text-xs mt-1 muted">— <?= htmlspecialchars($p['testimony']['name']) ?>, <?= (int)$p['testimony']['age'] ?></span>
            </blockquote>

            <!-- Tags -->
            <div class="mt-3 flex flex-wrap gap-2">
              <?php foreach($p['tags'] as $tg): ?>
                <span class="pill">#<?= htmlspecialchars($tg) ?></span>
              <?php endforeach; ?>
            </div>

            <!-- Actions: audio read, share, bookmark, upvote, ask doctor -->
            <div class="mt-4 flex flex-wrap items-center gap-2">
              <button class="btn btn-ghost tts" data-say="<?= htmlspecialchars($p['summary']) ?>"><i data-feather="headphones"></i> Listen</button>
              <button class="btn btn-ghost shareBtn" data-url="<?= 'https://' . $_SERVER['HTTP_HOST'] . '/blog.php#' . $p['id'] ?>"><i data-feather="share-2"></i> Share</button>
              <button class="btn btn-ghost bookmarkBtn" data-id="<?= $p['id'] ?>"><i data-feather="bookmark"></i> Save</button>
              <button class="btn btn-ghost upvoteBtn" data-id="<?= $p['id'] ?>"><i data-feather="thumbs-up"></i> <span class="upvotes">0</span></button>
              <a class="btn btn-primary" href="contact.php?subject=Question%20about%3A%20<?= urlencode($p['title']) ?>"><i data-feather="message-circle"></i> Ask the Doctor</a>
            </div>

            <!-- Doctor short video intro modal trigger (Feature #12) -->
            <button class="mt-3 link text-sm openVideo" data-video="<?= htmlspecialchars($p['video']) ?>"><i data-feather="play-circle" class="w-4 h-4"></i> Watch 60s intro</button>

            <!-- “You may also be at risk” (Feature #11) -->
            <div class="mt-3 p-3 rounded-lg bg-white/80 border border-cyan-100 text-xs">
              <div class="font-extrabold mb-1">You may also be at risk for:</div>
              <ul class="list-disc pl-5 muted">
                <?php
                  $risk = [
                    "Cardiology" => ["High blood pressure", "Elevated LDL", "Sedentary lifestyle"],
                    "Neurology" => ["Screen overuse fatigue", "Dehydration headaches", "Irregular sleep"],
                    "Dermatology" => ["UV overexposure", "Hyperpigmentation", "Barrier damage"],
                    "Gastroenterology" => ["Low fiber intake", "Inadequate hydration", "Inconsistent meals"]
                  ];
                  foreach(($risk[$p['category']] ?? ["General stress"]) as $r){
                    echo "<li>".htmlspecialchars($r)."</li>";
                  }
                ?>
              </ul>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </section>

    <!-- Reader Mood Poll (Feature #9) -->
    <section class="glass p-5 shadow-deep">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm uppercase tracking-widest text-cyan-600 font-extrabold">How did this content make you feel?</div>
          <h2 class="text-2xl font-extrabold">Reader Mood Poll</h2>
        </div>
        <div class="text-sm muted">Live results</div>
      </div>
      <div class="mt-3 flex items-center gap-2 flex-wrap">
        <button class="btn btn-ghost moodBtn" data-mood="calm">😌 Calmer</button>
        <button class="btn btn-ghost moodBtn" data-mood="informed">🧠 Informed</button>
        <button class="btn btn-ghost moodBtn" data-mood="motivated">⚡ Motivated</button>
        <button class="btn btn-ghost moodBtn" data-mood="concerned">🤔 Concerned</button>
      </div>
      <div id="moodResults" class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm"></div>
    </section>

  </main>

  <!-- VIDEO MODAL -->
  <div class="modal" id="videoModal" aria-hidden="true" aria-label="Doctor intro video">
    <div class="panel">
      <div class="flex items-center justify-between p-3 border-b border-cyan-100">
        <strong>Doctor Intro</strong>
        <button class="btn btn-ghost" id="closeVideo"><i data-feather="x"></i> Close</button>
      </div>
      <div class="relative" style="padding-top:56.25%">
        <iframe id="videoFrame" src="" allow="autoplay; encrypted-media" allowfullscreen
                class="absolute inset-0 w-full h-full border-0"></iframe>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="px-4 md:px-6 mt-8 mb-10">
    <div class="glass p-5 shadow-deep">
      <div class="foot">
        <div>
          <div class="flex items-center gap-2 font-extrabold">
            <div class="nav-brand-badge">RC</div> RestorativeCare
          </div>
          <p class="muted mt-2 text-sm">Patient-first health platform. Calm tech, human care.</p>
          <div class="mt-2 flex gap-2">
            <a href="about.php" class="btn btn-ghost">About</a>
            <a href="contact.php" class="btn btn-primary">Contact</a>
          </div>
        </div>
        <div>
          <div class="font-extrabold mb-2">Blog</div>
          <div class="space-y-1 text-sm">
            <a class="link" href="#top">Latest posts</a><br/>
            <a class="link" href="#tags">Popular tags</a><br/>
            <a class="link" href="contact.php?subject=Pitch%20a%20topic">Pitch a topic</a>
          </div>
        </div>
        <div>
          <div class="font-extrabold mb-2">Legal</div>
          <div class="space-y-1 text-sm">
            <a class="link" href="#">Privacy</a><br/>
            <a class="link" href="#">Terms</a>
          </div>
        </div>
        <div>
          <div class="font-extrabold mb-2">Status</div>
          <div class="text-sm muted">© <?= $year ?> RestorativeCare</div>
        </div>
      </div>
    </div>
  </footer>

  <!-- SCRIPTS -->
  <script>
    feather.replace();

    // Mobile menu
    document.getElementById('navToggle')?.addEventListener('click',()=>{
      document.getElementById('mobileMenu').classList.toggle('hidden');
    });

    // Reading time estimator (Feature #8)
    document.querySelectorAll('.readingTime').forEach(el=>{
      const words = +el.dataset.words || 600;
      const mins = Math.max(1, Math.round(words / 200));
      el.textContent = `${mins} min read`;
    });

    // Live readers counter (Feature #19)
    function wiggle(base){ return Math.max(1, base + Math.round((Math.random()-0.5)*6)); }
    function tickReaders(){
      document.querySelectorAll('.liveReaders').forEach(el=>{
        const base = +el.dataset.base || 12;
        el.textContent = wiggle(base);
      });
    }
    tickReaders(); setInterval(tickReaders, 5000);

    // Infographic mini charts (Feature #13) — tiny canvas bars, no external libs
    document.querySelectorAll('canvas[data-points]').forEach(cv=>{
      const pts = cv.dataset.points.split(',').map(Number);
      const ctx = cv.getContext('2d');
      const W = cv.width = cv.clientWidth, H = cv.height;
      ctx.clearRect(0,0,W,H);
      const max = Math.max(...pts) || 1;
      const pad = 16, bw = (W - pad*2) / (pts.length*1.5);
      pts.forEach((v,i)=>{
        const x = pad + i * bw * 1.5;
        const h = (v/max) * (H - pad*2);
        ctx.fillStyle = '#06b6d4'; ctx.strokeStyle = '#0ea5b7';
        ctx.fillRect(x, H-pad-h, bw, h);
        ctx.strokeRect(x, H-pad-h, bw, h);
      });
      // baseline
      ctx.strokeStyle = '#bae6fd'; ctx.beginPath(); ctx.moveTo(12, H-14); ctx.lineTo(W-12, H-14); ctx.stroke();
    });

    // Audio read-aloud (Feature #14)
    const synth = window.speechSynthesis;
    document.querySelectorAll('.tts').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        if(!synth) return;
        // Stop if already playing
        if(synth.speaking){ synth.cancel(); document.querySelectorAll('.tts').forEach(b=>b.classList.remove('playing')); return; }
        const u = new SpeechSynthesisUtterance(btn.dataset.say);
        btn.classList.add('playing');
        u.onend = ()=> btn.classList.remove('playing');
        u.rate = 1; u.pitch = 1; u.lang = 'en-US';
        synth.speak(u);
      });
    });

    // Share buttons (Features #16, #17, #18)
    function shareTo(platform, url, text=''){
      const encodedUrl = encodeURIComponent(url), encodedText = encodeURIComponent(text);
      const routes = {
        whatsapp: `https://wa.me/?text=${encodedText}%20${encodedUrl}`,
        x: `https://twitter.com/intent/tweet?text=${encodedText}&url=${encodedUrl}`,
        linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`
      };
      window.open(routes[platform], '_blank','noopener');
    }
    document.querySelectorAll('.shareBtn').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const url = btn.dataset.url;
        const selection = window.getSelection()?.toString().trim();
        const text = selection || 'Great read from RestorativeCare';
        if(navigator.share){
          navigator.share({title:'RestorativeCare Blog', text, url}).catch(()=>{});
        } else {
          // Quick chooser
          const choice = prompt('Type: whatsapp / x / linkedin', 'whatsapp');
          if(choice) shareTo(choice.toLowerCase(), url, text);
        }
      });
    });

    // Bookmark + Upvote (Features #21)
    const LS_BOOK = 'rc_blog_bookmarks', LS_UP = 'rc_blog_upvotes', LS_POINTS='rc_read_points', LS_MOOD='rc_mood_poll';
    const bookmarks = new Set(JSON.parse(localStorage.getItem(LS_BOOK) || '[]'));
    const upvotes = JSON.parse(localStorage.getItem(LS_UP) || '{}');

    document.querySelectorAll('.bookmarkBtn').forEach(btn=>{
      const id = btn.dataset.id;
      function render(){ btn.classList.toggle('btn-primary', bookmarks.has(id)); }
      render();
      btn.addEventListener('click', ()=>{
        if(bookmarks.has(id)){ bookmarks.delete(id); } else { bookmarks.add(id); addPoints(2); }
        localStorage.setItem(LS_BOOK, JSON.stringify([...bookmarks]));
        render();
      });
    });

    document.querySelectorAll('.upvoteBtn').forEach(btn=>{
      const id = btn.dataset.id; const span = btn.querySelector('.upvotes');
      span.textContent = upvotes[id] || 0;
      btn.addEventListener('click', ()=>{
        upvotes[id] = (upvotes[id]||0)+1; span.textContent = upvotes[id];
        localStorage.setItem(LS_UP, JSON.stringify(upvotes)); addPoints(1);
      });
    });

    // Reader points & badges (Feature #23)
    function getPoints(){ return +localStorage.getItem(LS_POINTS) || 0 }
    function setPoints(v){ localStorage.setItem(LS_POINTS, v) }
    function addPoints(v){ setPoints(getPoints()+v); renderPoints() }
    function renderPoints(){
      const pts = getPoints();
      document.getElementById('readPoints').textContent = pts;
      const rack = document.getElementById('badgeRack'); rack.innerHTML='';
      const tiers = [
        {min:0, label:'New Reader'}, {min:10,label:'Wellness Explorer'},
        {min:25,label:'Heart Health Champion'}, {min:50,label:'Care Advocate'}
      ];
      tiers.filter(t=>pts>=t.min).forEach(t=>{
        const span = document.createElement('span'); span.className='badge'; span.textContent=t.label; rack.appendChild(span);
      });
    }
    renderPoints();

    // Leaderboard (Feature #22) — demo data
    const leaders = [
      {name:'Dr. Aisha Verma', role:'Cardiology', reads: 12980},
      {name:'Dr. Leo Park', role:'Neurology', reads: 10440},
      {name:'Dr. Sofia Martinez', role:'Dermatology', reads: 9988},
      {name:'Dr. Evan Cole', role:'Gastroenterology', reads: 8620},
    ];
    const lb = document.getElementById('leaderboard');
    leaders.forEach((d,i)=>{
      const el = document.createElement('div');
      el.className='p-3 rounded-xl bg-white/85 border border-cyan-100';
      el.innerHTML = `<div class="flex items-center justify-between"><div class="font-extrabold">${i+1}. ${d.name}</div><span class="pill">${d.role}</span></div>
                      <div class="muted text-sm mt-1">${d.reads.toLocaleString()} reads</div>`;
      lb.appendChild(el);
    });

    // Mood Poll (Feature #9)
    const moodMap = JSON.parse(localStorage.getItem(LS_MOOD) || '{"calm":0,"informed":0,"motivated":0,"concerned":0}');
    function renderMood(){
      const box = document.getElementById('moodResults'); box.innerHTML='';
      Object.entries(moodMap).forEach(([k,v])=>{
        const wrap = document.createElement('div');
        wrap.className='p-3 rounded-xl bg-white/85 border border-cyan-100';
        wrap.innerHTML = `<div class="font-extrabold capitalize">${k}</div><div class="mt-1 h-2 rounded bg-cyan-50"><div style="width:${Math.min(100,v)}%" class="h-2 rounded" aria-hidden="true"></div></div><div class="text-xs muted mt-1">${v} votes</div>`;
        box.appendChild(wrap);
      });
    }
    renderMood();
    document.querySelectorAll('.moodBtn').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const k = btn.dataset.mood; moodMap[k]=(moodMap[k]||0)+1; addPoints(1);
        localStorage.setItem(LS_MOOD, JSON.stringify(moodMap)); renderMood();
      });
    });

    // Video modal (Feature #12)
    const modal = document.getElementById('videoModal'), frame = document.getElementById('videoFrame');
    document.querySelectorAll('.openVideo').forEach(b=>{
      b.addEventListener('click', ()=>{
        frame.src = b.dataset.video + '?autoplay=1';
        modal.classList.add('show'); modal.setAttribute('aria-hidden','false');
      });
    });
    document.getElementById('closeVideo').addEventListener('click', ()=>{
      frame.src=''; modal.classList.remove('show'); modal.setAttribute('aria-hidden','true');
    });
    modal.addEventListener('click', e=>{ if(e.target===modal){ document.getElementById('closeVideo').click(); } });

    // Mini world heatmap coloring (Feature #20) — simple random weights per region
    (function colorMap(){
      const levels = ['#dffbff','#9eefff','#43d6f0'];
      ['na','sa','eu','af','as','au'].forEach(id=>{
        const w = Math.random();
        const color = w < .34 ? levels[0] : (w < .7 ? levels[1] : levels[2]);
        const el = document.getElementById(id);
        if(el){ el.setAttribute('fill', color); }
      });
    })();

    // Auto-award “New Reader” points on first visit
    if(!localStorage.getItem('rc_blog_first')){
      addPoints(3); localStorage.setItem('rc_blog_first','1');
    }
  </script>
</body>
</html>
