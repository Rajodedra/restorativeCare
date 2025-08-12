<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RestorativeCare – Enhancing Patient Experience</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

  <!-- Custom styles -->
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: radial-gradient(circle at top left, #f0faff, #ffffff);
    }
    .glass {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    @keyframes floatGlow {
      0%, 100% { transform: translateY(0) scale(1); box-shadow: 0 0 20px rgba(0, 172, 193, 0.6); }
      50% { transform: translateY(-10px) scale(1.02); box-shadow: 0 0 40px rgba(0, 172, 193, 0.8); }
    }
    .float-glow {
      animation: floatGlow 5s ease-in-out infinite;
    }
  </style>
</head>
<body class="text-gray-800">

<!-- Navbar -->
<header class="flex justify-between items-center p-6 glass rounded-lg mx-4 mt-4 animate__animated animate__fadeInDown">
  <div class="text-2xl font-bold text-cyan-600">RestorativeCare</div>
  <nav class="space-x-6">
    <a href="index.php" class="hover:text-cyan-500">Home</a>
    <a href="features.php" class="hover:text-cyan-500">Features</a>
    <a href="about.php" class="hover:text-cyan-500">About</a>
    <a href="contact.php" class="hover:text-cyan-500">Contact</a>
    <a href="dashboard.php" class="bg-cyan-500 text-white px-4 py-2 rounded-lg hover:bg-cyan-600">Dashboard</a>
  </nav>
</header>

<!-- Hero Section -->
<section class="flex flex-col md:flex-row items-center justify-center p-10">
  <div class="max-w-lg space-y-6 animate__animated animate__fadeInLeft">
    <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">
      A Patient-First, Tech-Enabled<br>
      <span class="text-cyan-500">Restorative Care</span>
    </h1>
    <p class="text-lg text-gray-600">
      Real-time updates, seamless scheduling, and holistic well-being support to empower every step of the recovery journey.
    </p>
    <div class="space-x-4">
      <a href="features.php" class="px-6 py-3 bg-cyan-500 text-white rounded-lg shadow-lg hover:bg-cyan-600 transition">Explore Features</a>
      <a href="contact.php" class="px-6 py-3 bg-white border border-cyan-500 text-cyan-500 rounded-lg hover:bg-cyan-50 transition">Get in Touch</a>
    </div>
  </div>
  <div class="mt-10 md:mt-0 md:ml-10 animate__animated animate__fadeInRight float-glow rounded-full p-4">
    <img src="https://cdn-icons-png.flaticon.com/512/2920/2920323.png" alt="Healthcare AI" class="w-72 drop-shadow-lg">
  </div>
</section>

<!-- Features Section -->
<section id="features" class="p-10 bg-gradient-to-br from-white to-cyan-50 animate__animated animate__fadeInUp">
  <h2 class="text-3xl font-bold text-center mb-8">Our Key Features</h2>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
    <a href="dashboard.php" class="block glass p-6 rounded-xl hover:scale-105 transition transform duration-300">
      <img src="https://cdn-icons-png.flaticon.com/512/3209/3209265.png" class="w-16 mb-4" alt="">
      <h3 class="font-semibold text-xl mb-2">Digital Dashboard</h3>
      <p>Track treatment progress, medication, and doctor updates in one place.</p>
    </a>
    <a href="schedule.php" class="block glass p-6 rounded-xl hover:scale-105 transition transform duration-300">
      <img src="https://cdn-icons-png.flaticon.com/512/1048/1048943.png" class="w-16 mb-4" alt="">
      <h3 class="font-semibold text-xl mb-2">Smart Scheduling</h3>
      <p>Book and manage appointments with real-time doctor availability.</p>
    </a>
    <a href="mental-health.php" class="block glass p-6 rounded-xl hover:scale-105 transition transform duration-300">
      <img src="https://cdn-icons-png.flaticon.com/512/942/942748.png" class="w-16 mb-4" alt="">
      <h3 class="font-semibold text-xl mb-2">Mental Health Support</h3>
      <p>Access mood tracking, therapy resources, and emotional guidance.</p>
    </a>
    <!-- Add inside your Features grid in index.php -->
<a href="admit.php" class="block glass p-6 rounded-xl hover:scale-105 transition transform duration-300">
  <img src="https://cdn-icons-png.flaticon.com/512/2966/2966486.png" class="w-16 mb-4" alt="">
  <h3 class="font-semibold text-xl mb-2">Admit Patient</h3>
  <p>Quickly admit a new patient and create their profile in the system.</p>
</a>

  </div>
</section>

<!-- How It Works Section -->
<section class="py-16 bg-gradient-to-br from-cyan-50 to-white animate__animated animate__fadeInUp">
  <h2 class="text-3xl font-bold text-center mb-12">How It Works</h2>
  <div class="relative max-w-4xl mx-auto">
    <div class="mb-8 flex items-center w-full">
      <div class="w-10 h-10 bg-cyan-500 text-white rounded-full flex items-center justify-center font-bold z-10">1</div>
      <div class="ml-4 glass p-4 rounded-lg w-full">
        <h3 class="font-semibold">Sign In or Create Profile</h3>
        <p class="text-gray-600">Start your journey by securely logging in or creating a new patient profile.</p>
      </div>
    </div>
    <div class="mb-8 flex items-center w-full">
      <div class="w-10 h-10 bg-cyan-500 text-white rounded-full flex items-center justify-center font-bold z-10">2</div>
      <div class="ml-4 glass p-4 rounded-lg w-full">
        <h3 class="font-semibold">View Your Dashboard</h3>
        <p class="text-gray-600">See your treatment plan, upcoming appointments, and progress in real-time.</p>
      </div>
    </div>
    <div class="flex items-center w-full">
      <div class="w-10 h-10 bg-cyan-500 text-white rounded-full flex items-center justify-center font-bold z-10">3</div>
      <div class="ml-4 glass p-4 rounded-lg w-full">
        <h3 class="font-semibold">Stay on Track</h3>
        <p class="text-gray-600">Receive automated reminders, mental health check-ins, and discharge toolkits.</p>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials Section -->
<section class="py-16 bg-white animate__animated animate__fadeInUp">
  <h2 class="text-3xl font-bold text-center mb-12">What Our Patients Say</h2>
  <div class="max-w-5xl mx-auto grid md:grid-cols-3 gap-8">
    <div class="glass p-6 rounded-lg shadow-md hover:scale-105 transition">
      <p class="text-gray-700 mb-4">“This platform helped me recover faster and feel supported every step of the way.”</p>
      <div class="flex items-center space-x-4">
        <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-12 h-12 rounded-full" alt="">
        <span class="font-semibold">Priya Sharma</span>
      </div>
    </div>
    <div class="glass p-6 rounded-lg shadow-md hover:scale-105 transition">
      <p class="text-gray-700 mb-4">“The real-time updates and easy scheduling were a game changer for my care.”</p>
      <div class="flex items-center space-x-4">
        <img src="https://randomuser.me/api/portraits/men/35.jpg" class="w-12 h-12 rounded-full" alt="">
        <span class="font-semibold">David Cohen</span>
      </div>
    </div>
    <div class="glass p-6 rounded-lg shadow-md hover:scale-105 transition">
      <p class="text-gray-700 mb-4">“I loved the mental health tools — they kept me motivated during recovery.”</p>
      <div class="flex items-center space-x-4">
        <img src="https://randomuser.me/api/portraits/women/12.jpg" class="w-12 h-12 rounded-full" alt="">
        <span class="font-semibold">Sara Levy</span>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-center animate__animated animate__fadeInUp">
  <h2 class="text-4xl font-bold mb-6">Ready to Transform Your Recovery Journey?</h2>
  <p class="mb-8 text-lg max-w-2xl mx-auto">Join thousands of patients who have experienced faster, more supported recoveries with our tech-enabled restorative care platform.</p>
  <a href="dashboard.php" class="px-8 py-4 bg-white text-cyan-600 rounded-lg font-semibold hover:bg-gray-100 transition">Get Started Now</a>
</section>

<!-- Footer -->
<footer class="text-center p-6 mt-10 text-gray-500">
  &copy; <?php echo date("Y"); ?> RestorativeCare – All rights reserved.
</footer>

</body>
</html>