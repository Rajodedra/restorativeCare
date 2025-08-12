<?php
require_once __DIR__.'/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login($_POST['email'], $_POST['password'])) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Restorative Care Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-xl rounded-lg p-8 w-full max-w-md animate__animated animate__fadeIn">
        <!-- Logo / Title -->
        <div class="text-center mb-6">
            <img src="logo.png" alt="Logo" class="mx-auto w-20 h-20 mb-4 animate__animated animate__pulse animate__infinite" />
            <h1 class="text-3xl font-bold text-blue-700">Restorative Care Portal</h1>
            <p class="text-gray-500 text-sm mt-1">Enhancing patient experience in restorative healthcare</p>
        </div>
        
        <!-- Error message -->
        <?php if (!empty($error)) : ?>
            <p class="text-red-500 text-center mb-4"><?= $error ?></p>
        <?php endif; ?>
        
        <!-- Login form -->
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" required placeholder="you@example.com" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow-md transition duration-200">Login</button>
        </form>
        
        <!-- Footer -->
        <p class="text-xs text-gray-400 text-center mt-6">&copy; <?= date('Y') ?> Restorative Care. All rights reserved.</p>
    </div>
</body>
</html>