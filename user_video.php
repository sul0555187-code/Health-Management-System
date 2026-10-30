<?php
session_start();
require 'config/config.php';
require 'config/user_data.php';

$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;
$stmt = $pdo->prepare("
  SELECT a.*, u.username AS doctor_name
  FROM appointments a
  JOIN users u ON a.doctor_id = u.id
  WHERE a.id = ? AND a.user_id = ?
");
$stmt->execute([$appointment_id, $user_id]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
  die("❌ Invalid or unauthorized appointment.");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Video Consultation | Health Centers & Hospitals</title>
    <link rel="icon" href="../logo/logo.jpg" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/styles.css" />
    <style>
      .btn { padding: 10px 20px; background-color: #28be97; border: none; border-radius: 8px; color: #fff; cursor: pointer; }
      .btn-secondary { background-color: #e04e4e; }
    </style>
  </head>

  <body>
    <div class="home-section">
      <div class="effects"><div class="bg-effect"></div></div>
      <div class="container">

        <!-- header start -->
        <header class="header">
          <a href="user_dashboard.php" class="logo">
            <img src="../logo/logo.jpg" alt="Health Centers Logo" />
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="user_notifications.php">🔔 Notifications (<?= $notifCount ?>)</a></li>
              <li><a href="user_dashboard.php">Dashboard</a></li>
              <li><a href="doctors.php">Doctors</a></li>
              <li><a href="user_profile.php">Profile</a></li>
              <li><a href="user_appointments.php">My Appointments</a></li>
              <li><a href="user_video.php?appointment_id=<?= $appointment_id ?>" class="active">Video Call</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <main class="dashboard">
          <h1>🎥 Video Consultation</h1>
          <p class="video-intro">
            You are now joining your live session with <strong>Dr. <?= htmlspecialchars($appointment['doctor_name']); ?></strong>.
          </p>

          <div class="video-wrapper">
            <video id="localVideo" autoplay muted></video>
            <video id="remoteVideo" autoplay></video>
          </div>

          <div class="video-controls">
            <button id="startVideo" class="btn"><i class="fa-solid fa-video"></i> Join Call</button>
            <button id="endVideo" class="btn btn-secondary"><i class="fa-solid fa-phone-slash"></i> Leave Call</button>
          </div>

          <a href="user_dashboard.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
          </a>
        </main>

        <!-- Footer -->
        <footer>
          <p>&copy; <time datetime="2025">2025</time> Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>

    <script>
      // ✅ Firebase Configuration
      const firebaseConfig = {
        apiKey: "AIzaSyAGLwOrE0S94RhQBbfgp4VpEPd_5OYBWdM",
        authDomain: "health-management-af1e5.firebaseapp.com",
        databaseURL: "https://health-management-af1e5-default-rtdb.firebaseio.com",
        projectId: "health-management-af1e5",
        storageBucket: "health-management-af1e5.firebasestorage.app",
        messagingSenderId: "85133896271",
        appId: "1:85133896271:web:1fd0519844b66d86c5583a"
      };
      firebase.initializeApp(firebaseConfig);
      const db = firebase.database();

      const appointmentId = "<?= $appointment_id ?>";
      const servers = { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] };
      const pc = new RTCPeerConnection(servers);
      const localVideo = document.getElementById('localVideo');
      const remoteVideo = document.getElementById('remoteVideo');
      let localStream;

      pc.ontrack = e => remoteVideo.srcObject = e.streams[0];
      pc.onicecandidate = e => e.candidate && db.ref(`calls/${appointmentId}/patient`).push(e.candidate.toJSON());

      document.getElementById('startVideo').onclick = async () => {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        localVideo.srcObject = localStream;
        localStream.getTracks().forEach(track => pc.addTrack(track, localStream));

        const offerSnapshot = await db.ref(`calls/${appointmentId}/offer`).get();
        if (!offerSnapshot.exists()) {
          alert("⚠️ Doctor has not started the call yet.");
          return;
        }

        await pc.setRemoteDescription(new RTCSessionDescription(offerSnapshot.val()));
        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);
        db.ref(`calls/${appointmentId}/answer`).set(answer);

        db.ref(`calls/${appointmentId}/doctor`).on("child_added", snap => {
          pc.addIceCandidate(new RTCIceCandidate(snap.val()));
        });
      };

      document.getElementById('endVideo').onclick = () => {
        if (localStream) localStream.getTracks().forEach(t => t.stop());
        pc.close();
        alert("You left the call.");
      };
    </script>

    <script src="../js/transition.js"></script>
  </body>
</html>