<?php
// connect.php — RestorativeCare Video/Audio Chat
// Integrates with existing RestorativeCare platform

$year = date('Y');
if (!function_exists('esc')) {
  function esc($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// DB connection
$DB_HOST = 'localhost';
$DB_PORT = '3306';
$DB_USER = 'root';  // change if different
$DB_PASS = '';      // change if you set password
$DB_NAME = 'restorativecare';

$mysqli = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if (!$mysqli) {
    http_response_code(500);
    die("Database connection failed: " . esc(mysqli_connect_error()));
}
$mysqli->set_charset('utf8mb4');

// Check if user is logged in
session_start();
$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// For demo purposes, if no user is logged in, use a dummy patient
if (!$currentUser) {
  $query = "SELECT patients.id, users.name FROM patients JOIN users ON patients.user_id = users.id LIMIT 1";
  $result = $mysqli->query($query);
  if ($result && $row = $result->fetch_assoc()) {
    $currentUser = [
      'id' => $row['id'],
      'name' => $row['name'],
      'role' => 'patient'
    ];
  } else {
    // Fallback to guest
    $currentUser = [
      'id' => 0,
      'name' => 'Guest User',
      'role' => 'patient'
    ];
  }
}

// Get available doctors for consultation
$doctors = [];
$query = "SELECT doctors.id, users.name, doctors.specialty FROM doctors JOIN users ON doctors.user_id = users.id WHERE doctors.is_available = 1 LIMIT 10";
$result = $mysqli->query($query);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Connect to Doctor - RestorativeCare</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Matching existing RestorativeCare styles */
    :root {
      --primary: #0ea5e9;
      --secondary: #0284c7;
      --accent: #0ea5e9;
      --text: #0f172a;
      --text-muted: #64748b;
      --bg: #ffffff;
      --bg-offset: #f8fafc;
    }
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      color: var(--text);
      background: var(--bg);
    }
    .glass {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(10px);
      border-radius: 0.75rem;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .shadow-deep {
      box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
    }
    .muted {
      color: var(--text-muted);
    }
    .btn-primary {
      background: var(--primary);
      color: white;
      padding: 0.5rem 1.5rem;
      border-radius: 0.5rem;
      font-weight: 600;
      transition: all 0.2s;
    }
    .btn-primary:hover {
      background: var(--secondary);
    }
    .btn-secondary {
      background: #f1f5f9;
      color: var(--text);
      padding: 0.5rem 1.5rem;
      border-radius: 0.5rem;
      font-weight: 600;
      transition: all 0.2s;
    }
    .btn-secondary:hover {
      background: #e2e8f0;
    }
    .video-container {
      aspect-ratio: 16/9;
      background: #0f172a;
      border-radius: 0.75rem;
      overflow: hidden;
    }
    #local-video {
      width: 150px;
      height: 150px;
      position: absolute;
      bottom: 20px;
      right: 20px;
      border-radius: 8px;
      border: 2px solid white;
      z-index: 10;
    }
    .controls {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 12px;
      z-index: 10;
    }
    .control-btn {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s;
    }
    .control-btn:hover {
      background: rgba(255, 255, 255, 0.3);
    }
    .control-btn.active {
      background: var(--primary);
    }
    .control-btn.end-call {
      background: #ef4444;
    }
    .control-btn.end-call:hover {
      background: #dc2626;
    }
    .subtitles {
      position: absolute;
      bottom: 80px;
      left: 0;
      right: 0;
      text-align: center;
      color: white;
      background: rgba(0, 0, 0, 0.5);
      padding: 10px;
      z-index: 5;
      min-height: 60px;
    }
    .status-indicator {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 5px;
    }
    .status-available {
      background: #10b981;
    }
    .status-busy {
      background: #f59e0b;
    }
    .status-offline {
      background: #6b7280;
    }
    .connection-indicator {
      position: absolute;
      top: 20px;
      right: 20px;
      background: rgba(0, 0, 0, 0.5);
      color: white;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      z-index: 10;
    }
    .chat-panel {
      display: none;
      background: white;
      border-radius: 0.75rem;
      box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
      height: 100%;
      overflow-y: auto;
    }
    .chat-message {
      padding: 10px;
      margin: 10px;
      border-radius: 8px;
    }
    .chat-message.sent {
      background: #e2e8f0;
      margin-left: 50px;
    }
    .chat-message.received {
      background: #f0f9ff;
      margin-right: 50px;
    }
  </style>
</head>
<body>
  <!-- Top Navigation -->
  <header class="py-4 px-6 shadow-sm">
    <div class="container mx-auto flex justify-between items-center">
      <a href="index.php" class="text-2xl font-bold text-cyan-600">RestorativeCare</a>
      <nav class="hidden md:flex space-x-6">
        <a href="index.php" class="font-medium hover:text-cyan-600">Home</a>
        <a href="dashboard.php" class="font-medium hover:text-cyan-600">Dashboard</a>
        <a href="admit.php" class="font-medium hover:text-cyan-600">Admit Patient</a>
        <a href="notifications.php" class="font-medium hover:text-cyan-600">Notifications</a>
        <a href="connect.php" class="font-medium text-cyan-600">Connect to Doctor</a>
        <a href="contact.php" class="font-medium hover:text-cyan-600">Contact</a>
      </nav>
      <div class="flex items-center">
        <span class="mr-2"><?php echo esc($currentUser['name']); ?></span>
        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
          <span class="text-sm font-medium"><?php echo substr(esc($currentUser['name']), 0, 1); ?></span>
        </div>
      </div>
    </div>
  </header>

  <main class="container mx-auto px-4 py-6">
    <div class="text-center mb-10">
      <h1 class="text-3xl md:text-4xl font-extrabold">Connect to Doctor</h1>
      <p class="muted mt-3 max-w-3xl mx-auto">
        Instant video or audio consultations with your healthcare providers.
        Includes real-time subtitles and automatically adapts to your connection quality.
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Doctors Panel -->
      <div id="doctors-panel" class="glass p-6 shadow-deep">
        <h2 class="text-xl font-bold mb-4">Available Doctors</h2>
        <?php if (empty($doctors)): ?>
          <p class="muted">No doctors are currently available. Please check back later.</p>
        <?php else: ?>
          <div class="space-y-4">
            <?php foreach ($doctors as $doctor): ?>
              <div class="doctor-card p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition" 
                   data-doctor-id="<?php echo esc($doctor['id']); ?>"
                   data-doctor-name="<?php echo esc($doctor['name']); ?>">
                <div class="flex items-center">
                  <div class="flex-shrink-0 mr-3">
                    <div class="w-12 h-12 rounded-full bg-cyan-100 flex items-center justify-center">
                      <span class="text-lg font-medium text-cyan-600">
                        <?php echo substr(esc($doctor['name']), 0, 1); ?>
                      </span>
                    </div>
                  </div>
                  <div>
                    <h3 class="font-medium"><?php echo esc($doctor['name']); ?></h3>
                    <p class="text-sm muted"><?php echo esc($doctor['specialty']); ?></p>
                    <div class="flex items-center mt-1">
                      <span class="status-indicator status-available"></span>
                      <span class="text-sm">Available Now</span>
                    </div>
                  </div>
                </div>
                <div class="mt-3 flex space-x-2">
                  <button class="start-video-call btn-primary text-sm py-1 px-3 flex items-center">
                    <i class="fas fa-video mr-1"></i> Video Call
                  </button>
                  <button class="start-audio-call btn-secondary text-sm py-1 px-3 flex items-center">
                    <i class="fas fa-phone-alt mr-1"></i> Audio Only
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Call Panel -->
      <div id="call-panel" class="col-span-2 hidden">
        <div class="relative video-container">
          <video id="remote-video" autoplay playsinline class="w-full h-full object-cover"></video>
          <video id="local-video" autoplay playsinline muted></video>
          
          <div class="connection-indicator">
            <i class="fas fa-wifi mr-1"></i> <span id="connection-quality">Excellent</span>
          </div>

          <div id="subtitles" class="subtitles hidden">
            <p id="subtitle-text">Subtitles will appear here during the call...</p>
          </div>
          
          <div class="controls">
            <div id="mic-btn" class="control-btn active">
              <i class="fas fa-microphone text-white"></i>
            </div>
            <div id="camera-btn" class="control-btn active">
              <i class="fas fa-video text-white"></i>
            </div>
            <div id="subtitle-btn" class="control-btn">
              <i class="fas fa-closed-captioning text-white"></i>
            </div>
            <div id="chat-btn" class="control-btn">
              <i class="fas fa-comment-alt text-white"></i>
            </div>
            <div id="end-call-btn" class="control-btn end-call">
              <i class="fas fa-phone-slash text-white"></i>
            </div>
          </div>
        </div>

        <div class="mt-4 glass p-4 shadow-deep">
          <h3 id="call-status" class="font-medium">Starting call...</h3>
          <p id="call-details" class="text-sm muted mt-1">Connecting to secure server...</p>
        </div>
      </div>
      
      <!-- Chat Panel -->
      <div id="chat-panel" class="col-span-2 hidden glass p-4 shadow-deep">
        <div class="flex flex-col h-full">
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-medium">Chat with <span id="chat-doctor-name"></span></h3>
            <button id="close-chat-btn" class="text-sm text-gray-500">
              <i class="fas fa-times"></i> Close
            </button>
          </div>
          <div id="chat-messages" class="flex-grow overflow-y-auto mb-4 h-96">
            <div class="chat-message received">
              <div class="text-sm text-gray-500 mb-1">Dr. System</div>
              Welcome to secure chat. Messages are not stored and will be lost when the call ends.
            </div>
          </div>
          <div class="flex">
            <input type="text" id="chat-input" placeholder="Type a message..." 
                   class="flex-grow border border-gray-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
            <button id="send-chat-btn" class="bg-cyan-600 text-white px-4 py-2 rounded-r-lg hover:bg-cyan-700">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Call Details/Preparation Area -->
    <div id="call-preparation" class="glass p-6 shadow-deep hidden">
      <h2 class="text-xl font-bold mb-4">Prepare for Your Call</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="font-medium mb-2">Video Preview</h3>
          <div class="bg-gray-900 rounded-lg overflow-hidden">
            <video id="preview-video" autoplay playsinline muted class="w-full h-48 object-cover"></video>
          </div>
          <div class="mt-3 flex space-x-3">
            <button id="preview-mic-btn" class="btn-secondary text-sm py-1 px-3 flex items-center">
              <i class="fas fa-microphone mr-1"></i> Test Microphone
            </button>
            <button id="preview-camera-btn" class="btn-secondary text-sm py-1 px-3 flex items-center">
              <i class="fas fa-video mr-1"></i> Switch Camera
            </button>
          </div>
        </div>
        <div>
          <h3 class="font-medium mb-2">Call Details</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm muted mb-1">Doctor</label>
              <div id="selected-doctor" class="font-medium">Not selected</div>
            </div>
            <div>
              <label class="block text-sm muted mb-1">Call Type</label>
              <div id="selected-call-type" class="font-medium">Not selected</div>
            </div>
            <div>
              <label for="call-reason" class="block text-sm muted mb-1">Reason for Call (optional)</label>
              <textarea id="call-reason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"></textarea>
            </div>
            <div class="pt-4 flex space-x-3">
              <button id="start-call-btn" class="btn-primary">Start Call</button>
              <button id="cancel-call-btn" class="btn-secondary">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/socket.io-client@4.6.1/dist/socket.io.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // DOM Elements
      const doctorsPanel = document.getElementById('doctors-panel');
      const callPanel = document.getElementById('call-panel');
      const callPreparation = document.getElementById('call-preparation');
      const chatPanel = document.getElementById('chat-panel');
      
      const previewVideo = document.getElementById('preview-video');
      const localVideo = document.getElementById('local-video');
      const remoteVideo = document.getElementById('remote-video');
      
      const selectedDoctor = document.getElementById('selected-doctor');
      const selectedCallType = document.getElementById('selected-call-type');
      const callReason = document.getElementById('call-reason');
      
      const startCallBtn = document.getElementById('start-call-btn');
      const cancelCallBtn = document.getElementById('cancel-call-btn');
      const endCallBtn = document.getElementById('end-call-btn');
      
      const micBtn = document.getElementById('mic-btn');
      const cameraBtn = document.getElementById('camera-btn');
      const subtitleBtn = document.getElementById('subtitle-btn');
      const chatBtn = document.getElementById('chat-btn');
      
      const subtitles = document.getElementById('subtitles');
      const subtitleText = document.getElementById('subtitle-text');
      
      const connectionQuality = document.getElementById('connection-quality');
      const callStatus = document.getElementById('call-status');
      const callDetails = document.getElementById('call-details');

      const chatMessages = document.getElementById('chat-messages');
      const chatInput = document.getElementById('chat-input');
      const sendChatBtn = document.getElementById('send-chat-btn');
      const closeChatBtn = document.getElementById('close-chat-btn');
      const chatDoctorName = document.getElementById('chat-doctor-name');
      
      // WebRTC variables
      let localStream = null;
      let peerConnection = null;
      let selectedDoctorId = null;
      let selectedDoctorName = null;
      let callType = null;
      let socket = null;
      let speechRecognition = null;
      let isCallActive = false;
      let isMicEnabled = true;
      let isCameraEnabled = true;
      let isSubtitlesEnabled = false;
      let networkQuality = 'excellent'; // excellent, good, poor
      
      // Initialize speech recognition if available
      function initSpeechRecognition() {
        if ('webkitSpeechRecognition' in window) {
          speechRecognition = new webkitSpeechRecognition();
          speechRecognition.continuous = true;
          speechRecognition.interimResults = true;
          speechRecognition.lang = 'en-US'; // Default language
          
          speechRecognition.onresult = function(event) {
            let interimTranscript = '';
            let finalTranscript = '';
            
            for (let i = event.resultIndex; i < event.results.length; ++i) {
              if (event.results[i].isFinal) {
                finalTranscript += event.results[i][0].transcript;
              } else {
                interimTranscript += event.results[i][0].transcript;
              }
            }
            
            if (finalTranscript || interimTranscript) {
              subtitleText.textContent = finalTranscript || interimTranscript;
              
              // Send subtitle to remote peer
              if (peerConnection && finalTranscript) {
                sendDataChannelMessage({
                  type: 'subtitle',
                  text: finalTranscript
                });
              }
            }
          };
          
          return true;
        }
        return false;
      }
      
      // Initialize WebSocket connection
      function initSocket() {
        // In a real implementation, you would connect to your WebSocket server
        // For demo purposes, we'll simulate the connection
        console.log('Initializing WebSocket connection...');
        
        // Simulated socket for demo purposes
        // In a real implementation, use:
        // socket = io('https://your-websocket-server.com');
        socket = {
          emit: function(event, data) {
            console.log('Socket emit:', event, data);
            
            // Simulate server responses
            if (event === 'join') {
              setTimeout(() => {
                simulateServerMessage('joined', { userId: '<?php echo $currentUser['id']; ?>' });
              }, 500);
            } else if (event === 'call-user') {
              setTimeout(() => {
                simulateServerMessage('call-made', {
                  offer: 'simulated-sdp-offer',
                  caller: '<?php echo $currentUser['id']; ?>'
                });
                callStatus.textContent = 'Call Connected';
                callDetails.textContent = `In call with ${selectedDoctorName}`;
                isCallActive = true;
              }, 1500);
            } else if (event === 'make-answer') {
              setTimeout(() => {
                simulateServerMessage('answer-made', {
                  answer: 'simulated-sdp-answer',
                  answerer: selectedDoctorId
                });
              }, 1000);
            } else if (event === 'ice-candidate') {
              // Simulate ICE candidate exchange
            }
          },
          on: function(event, callback) {
            // Store callbacks for simulation
            socketCallbacks[event] = callback;
          }
        };
        
        return true;
      }
      
      // For demo: simulate server messages
      const socketCallbacks = {};
      function simulateServerMessage(event, data) {
        if (socketCallbacks[event]) {
          socketCallbacks[event](data);
        }
      }
      
      // Initialize data channel for chat and subtitles
      let dataChannel = null;
      
      function initPeerConnection() {
        const configuration = {
          iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
          ]
        };
        
        peerConnection = new RTCPeerConnection(configuration);
        
        // Set up data channel for chat and subtitles
        dataChannel = peerConnection.createDataChannel('chat');
        dataChannel.onopen = handleDataChannelOpen;
        dataChannel.onmessage = handleDataChannelMessage;
        
        peerConnection.ondatachannel = event => {
          const receivedChannel = event.channel;
          receivedChannel.onopen = handleDataChannelOpen;
          receivedChannel.onmessage = handleDataChannelMessage;
          dataChannel = receivedChannel;
        };
        
        // Add local stream tracks to peer connection
        if (localStream) {
          localStream.getTracks().forEach(track => {
            peerConnection.addTrack(track, localStream);
          });
        }
        
        // Handle incoming remote stream
        peerConnection.ontrack = event => {
          remoteVideo.srcObject = event.streams[0];
        };
        
        // Handle ICE candidates
        peerConnection.onicecandidate = event => {
          if (event.candidate) {
            socket.emit('ice-candidate', {
              candidate: event.candidate,
              to: selectedDoctorId
            });
          }
        };
        
        // Monitor connection state
        peerConnection.onconnectionstatechange = () => {
          console.log('Connection state:', peerConnection.connectionState);
          if (peerConnection.connectionState === 'connected') {
            callStatus.textContent = 'Call Connected';
            callDetails.textContent = `In call with ${selectedDoctorName}`;
          } else if (peerConnection.connectionState === 'disconnected' || 
                     peerConnection.connectionState === 'failed' ||
                     peerConnection.connectionState === 'closed') {
            endCall();
          }
        };
        
        // Monitor ICE connection state
        peerConnection.oniceconnectionstatechange = () => {
          console.log('ICE connection state:', peerConnection.iceConnectionState);
          if (peerConnection.iceConnectionState === 'disconnected' ||
              peerConnection.iceConnectionState === 'failed') {
            updateNetworkQuality('poor');
          } else if (peerConnection.iceConnectionState === 'connected') {
            updateNetworkQuality('good');
          } else if (peerConnection.iceConnectionState === 'completed') {
            updateNetworkQuality('excellent');
          }
        };
        
        return true;
      }
      
      function handleDataChannelOpen() {
        console.log('Data channel opened');
      }
      
      function handleDataChannelMessage(event) {
        const message = JSON.parse(event.data);
        
        if (message.type === 'chat') {
          addChatMessage(message.text, false);
        } else if (message.type === 'subtitle') {
          if (isSubtitlesEnabled) {
            subtitleText.textContent = message.text;
          }
        }
      }
      
      function sendDataChannelMessage(message) {
        if (dataChannel && dataChannel.readyState === 'open') {
          dataChannel.send(JSON.stringify(message));
        }
      }
      
      // Update network quality indicator
      function updateNetworkQuality(quality) {
        networkQuality = quality;
        connectionQuality.textContent = quality.charAt(0).toUpperCase() + quality.slice(1);
        
        // If network is poor, switch to audio-only if currently in video mode
        if (quality === 'poor' && callType === 'video' && isCameraEnabled) {
          alert('Network quality is poor. Switching to audio-only mode to improve call quality.');
          disableVideo();
        }
      }
      
      // Get user media (camera and microphone)
      async function getMedia(video = true) {
        try {
          const constraints = {
            audio: true,
            video: video ? { width: 1280, height: 720 } : false
          };
          
          const stream = await navigator.mediaDevices.getUserMedia(constraints);
          return stream;
        } catch (err) {
          console.error('Error accessing media devices:', err);
          alert('Could not access camera or microphone. Please check your device permissions.');
          return null;
        }
      }
      
      // Start call preparation
      async function prepareCall(doctorId, doctorName, type) {
        selectedDoctorId = doctorId;
        selectedDoctorName = doctorName;
        callType = type;
        
        // Show call preparation panel
        doctorsPanel.classList.add('hidden');
        callPreparation.classList.remove('hidden');
        
        // Update call details
        selectedDoctor.textContent = doctorName;
        selectedCallType.textContent = type === 'video' ? 'Video Call' : 'Audio Call';
        
        // Initialize preview video
        try {
          localStream = await getMedia(type === 'video');
          if (localStream) {
            previewVideo.srcObject = localStream;
          }
        } catch (err) {
          console.error('Error getting local stream:', err);
          alert('Could not access your camera or microphone. Please check your device permissions.');
        }
      }
      
      // Start the actual call
      function startCall() {
        if (!selectedDoctorId || !localStream) {
          alert('Cannot start call. Please make sure your camera and microphone are working.');
          return;
        }
        
        // Show call panel, hide preparation
        callPreparation.classList.add('hidden');
        callPanel.classList.remove('hidden');
        
        // Set local video
        localVideo.srcObject = localStream;
        
        // Initialize WebRTC
        initSocket();
        initPeerConnection();
        
        // Join signaling server room
        socket.emit('join', {
          userId: '<?php echo $currentUser['id']; ?>',
          userName: '<?php echo $currentUser['name']; ?>',
          userRole: '<?php echo $currentUser['role']; ?>'
        });
        
        // Set up socket event handlers
        socket.on('joined', handleUserJoined);
        socket.on('call-made', handleCallMade);
        socket.on('answer-made', handleAnswerMade);
        socket.on('ice-candidate', handleIceCandidate);
        socket.on('call-ended', handleCallEnded);
        
        // Update call status
        callStatus.textContent = 'Calling...';
        callDetails.textContent = `Waiting for ${selectedDoctorName} to join...`;
        
        // In a real implementation, you would send an offer to the doctor here
        socket.emit('call-user', {
          to: selectedDoctorId,
          offer: 'simulated-sdp-offer',
          callType: callType,
          reason: callReason.value
        });
      }

      // Cancel call preparation
      function cancelCall() {
        callPreparation.classList.add('hidden');
        doctorsPanel.classList.remove('hidden');
        if (localStream) {
          localStream.getTracks().forEach(track => track.stop());
          localStream = null;
        }
        previewVideo.srcObject = null;
      }

      // End the call
      function endCall() {
        isCallActive = false;
        callPanel.classList.add('hidden');
        chatPanel.classList.add('hidden');
        doctorsPanel.classList.remove('hidden');
        if (peerConnection) {
          peerConnection.close();
          peerConnection = null;
        }
        if (localStream) {
          localStream.getTracks().forEach(track => track.stop());
          localStream = null;
        }
        localVideo.srcObject = null;
        remoteVideo.srcObject = null;
        callStatus.textContent = 'Call ended.';
        callDetails.textContent = '';
      }

      // Handle user joined
      function handleUserJoined(data) {
        // In a real implementation, you would create and send an offer
        // For demo, do nothing
      }

      // Handle call made (offer received)
      function handleCallMade(data) {
        // In a real implementation, you would set remote description and create answer
        // For demo, simulate answer
        socket.emit('make-answer', {
          to: selectedDoctorId,
          answer: 'simulated-sdp-answer'
        });
      }

      // Handle answer made
      function handleAnswerMade(data) {
        // In a real implementation, you would set remote description
        // For demo, do nothing
      }

      // Handle ICE candidate
      function handleIceCandidate(data) {
        // In a real implementation, you would add ICE candidate
        // For demo, do nothing
      }

      // Handle call ended
      function handleCallEnded() {
        endCall();
      }

      // UI event listeners
      document.querySelectorAll('.start-video-call').forEach(btn => {
        btn.addEventListener('click', function(e) {
          const card = e.target.closest('.doctor-card');
          prepareCall(card.dataset.doctorId, card.dataset.doctorName, 'video');
        });
      });
      document.querySelectorAll('.start-audio-call').forEach(btn => {
        btn.addEventListener('click', function(e) {
          const card = e.target.closest('.doctor-card');
          prepareCall(card.dataset.doctorId, card.dataset.doctorName, 'audio');
        });
      });
      startCallBtn.addEventListener('click', startCall);
      cancelCallBtn.addEventListener('click', cancelCall);
      if (endCallBtn) endCallBtn.addEventListener('click', endCall);

      // Chat panel logic
      chatBtn.addEventListener('click', function() {
        chatPanel.classList.remove('hidden');
        chatDoctorName.textContent = selectedDoctorName;
      });
      closeChatBtn.addEventListener('click', function() {
        chatPanel.classList.add('hidden');
      });
      sendChatBtn.addEventListener('click', function() {
        const text = chatInput.value.trim();
        if (text) {
          addChatMessage(text, true);
          sendDataChannelMessage({ type: 'chat', text });
          chatInput.value = '';
        }
      });
      chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          sendChatBtn.click();
        }
      });

      // Add chat message to UI
      function addChatMessage(text, sent) {
        const div = document.createElement('div');
        div.className = 'chat-message ' + (sent ? 'sent' : 'received');
        div.textContent = text;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      }

      // Mic/camera/subtitle controls
      micBtn.addEventListener('click', function() {
        isMicEnabled = !isMicEnabled;
        if (localStream) {
          localStream.getAudioTracks().forEach(track => track.enabled = isMicEnabled);
        }
        micBtn.classList.toggle('active', isMicEnabled);
      });
      cameraBtn.addEventListener('click', function() {
        isCameraEnabled = !isCameraEnabled;
        if (localStream) {
          localStream.getVideoTracks().forEach(track => track.enabled = isCameraEnabled);
        }
        cameraBtn.classList.toggle('active', isCameraEnabled);
      });
      subtitleBtn.addEventListener('click', function() {
        isSubtitlesEnabled = !isSubtitlesEnabled;
        subtitles.classList.toggle('hidden', !isSubtitlesEnabled);
        subtitleBtn.classList.toggle('active', isSubtitlesEnabled);
        if (isSubtitlesEnabled) {
          initSpeechRecognition();
        } else if (speechRecognition) {
          speechRecognition.stop();
        }
      });

      // Utility: disable video (switch to audio-only)
      function disableVideo() {
        isCameraEnabled = false;
        if (localStream) {
          localStream.getVideoTracks().forEach(track => track.enabled = false);
        }
        cameraBtn.classList.remove('active');
        selectedCallType.textContent = 'Audio Call';
      }
    });
  </script>
</body>
</html>