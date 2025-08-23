<?php
// chatbot.php
// Database connection
function connectDB() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "restorativecare";
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Function to get code file contents
function getCodeFileContents($filename) {
    // Define allowed paths and extensions for security
    $allowedDirectory = ".";  // Only allow files in current directory
    $allowedExtensions = ['php', 'css', 'html'];

    // Validate file extension
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    if (!in_array($extension, $allowedExtensions)) {
        return "Error: Cannot access files with .$extension extension.";
    }

    // Create safe path (prevent directory traversal)
    $filepath = realpath($allowedDirectory . "/" . $filename);

    // Ensure the file is within allowed directory
    if ($filepath === false || strpos($filepath, $allowedDirectory) !== 0) {
        return "Error: File '$filename' not found.";
    }

    // Read file contents
    if (is_file($filepath)) {
        return file_get_contents($filepath);
    } else {
        return "Error: File '$filename' not found.";
    }
}

// Gemini API function with healthcare context
function gemini_chat($prompt) {
    $api_key = "AIzaSyBUvCFPcW_6k8eDgqxIDqfbtYA8AYes-Sg"; // Replace with your actual API key
    
    // Add the restorative care context to the prompt
    $restrictedContext = "You are a specialized assistant for the Restorative Care application. You can ONLY help with:
    1. Explaining the code files in this application
    2. Answering medical questions related to restorative care
    3. Automating tasks within the application
    For any other topics, politely decline and redirect to these topics.";
    
    $enhancedPrompt = $restrictedContext . " Query: " . $prompt;
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $api_key;
    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $enhancedPrompt]
                ]
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    if ($result === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return "Error contacting Gemini API: " . $error;
    }
    
    $response = json_decode($result, true);
    
    // Parse response
    if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
        return $response['candidates'][0]['content']['parts'][0]['text'];
    } else {
        // Fall back to preset responses if API fails
        $healthcareResponses = [
            "I can help answer your healthcare questions. What specific medical information are you looking for?",
            "As a healthcare assistant, I can provide general medical information, but remember to consult your doctor for personalized advice.",
            "That's a good question about your health. Based on general medical knowledge, here's what I can tell you...",
            "I'm here to help with healthcare questions. For this specific query, you might want to consider...",
            "From a healthcare perspective, it's important to understand that this condition typically..."
        ];
        return $healthcareResponses[array_rand($healthcareResponses)];
    }
}

// Handle API request if this file is called directly
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userMessage = $_POST['message'];
    $response = gemini_chat($userMessage);
    
    // Return response in JSON format
    header('Content-Type: application/json');
    echo json_encode(['reply' => $response]);
    exit;
}
?>