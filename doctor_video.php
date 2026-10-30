<?php
session_start();
require 'config/config.php';
require 'config/doctor_data.php';

$doctorName = $_SESSION['doctor_name'] ?? "Doctor";
$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Video Consultation | Doctor Dashboard</title>
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

        <!-- Header -->
        <header class="header">
          <a href="doctor_dashboard.php" class="logo">
            <img src="../logo/logo.jpg" width="80" height="80" alt="logo">
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="doctor_dashboard.php">Dashboard</a></li>
              <li><a href="doctor_video.php" class="active">Video</a></li>
              <li><a href="doctor_appointments.php">My Appointments</a></li>
              <li><a href="doctor_patients.php">My Patients</a></li>
              <li><a href="doctor_profile.php">Profile</a></li>
              <li><a href="contact_us.php">Contact Us</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <div class="video-section">
          <h1>💻 Video Consultation Session</h1>
          <p class="video-intro">
            Hello <strong><?php echo htmlspecialchars($doctorName); ?></strong> — start a live video or audio call with your patient directly through the system.
          </p>

          <div class="video-wrapper">
            <video id="localVideo" autoplay muted></video>
            <video id="remoteVideo" autoplay></video>
          </div>

          <div class="video-controls">
            <button id="startCall" class="btn"><i class="fa-solid fa-video"></i> Start Call</button>
            <button id="shareScreen" class="btn"><i class="fa-solid fa-desktop"></i> Share Screen</button>
            <button id="muteAudio" class="btn btn-secondary"><i class="fa-solid fa-microphone-slash"></i> Mute</button>
            <button id="endCall" class="btn btn-secondary"><i class="fa-solid fa-phone-slash"></i> End Call</button>
          </div>
        </div>

          <!-- Footer -->
        <footer>
          <p>&copy; 2025 Health Centers & Hospitals Platform. All rights reserved.</p>
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

      const appointmentId = "<?php echo $appointment_id; ?>";
      const servers = { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] };
      const pc = new RTCPeerConnection(servers);
      const localVideo = document.getElementById('localVideo');
      const remoteVideo = document.getElementById('remoteVideo');
      let localStream;

      pc.ontrack = e => remoteVideo.srcObject = e.streams[0];
      pc.onicecandidate = e => e.candidate && db.ref(`calls/${appointmentId}/doctor`).push(e.candidate.toJSON());

      document.getElementById('startCall').onclick = async () => {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        localVideo.srcObject = localStream;
        localStream.getTracks().forEach(track => pc.addTrack(track, localStream));

        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        db.ref(`calls/${appointmentId}/offer`).set(offer);

        db.ref(`calls/${appointmentId}/answer`).on("value", async snap => {
          if (snap.exists() && !pc.currentRemoteDescription) {
            await pc.setRemoteDescription(new RTCSessionDescription(snap.val()));
          }
        });

        db.ref(`calls/${appointmentId}/patient`).on("child_added", snap => {
          pc.addIceCandidate(new RTCIceCandidate(snap.val()));
        });
      };
      // ✅ Mute / Unmute
      const muteBtn = document.getElementById('muteAudio');
      let isMuted = false;
      muteBtn.onclick = () => {
        if (!localStream) return;
        localStream.getAudioTracks().forEach(track => track.enabled = isMuted);
        isMuted = !isMuted;
        muteBtn.innerHTML = isMuted
          ? '<i class="fa-solid fa-microphone"></i> Unmute'
          : '<i class="fa-solid fa-microphone-slash"></i> Mute';
      };

      // ✅ Share Screen
      const shareScreenBtn = document.getElementById('shareScreen');
      shareScreenBtn.onclick = async () => {
        try {
          const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
          const screenTrack = screenStream.getVideoTracks()[0];

          // استبدال الفيديو الحالي بمصدر الشاشة
          const sender = pc.getSenders().find(s => s.track.kind === 'video');
          sender.replaceTrack(screenTrack);

          // لما المستخدم يوقف المشاركة يرجع الكاميرا
          screenTrack.onended = async () => {
            const camStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            const camTrack = camStream.getVideoTracks()[0];
            sender.replaceTrack(camTrack);
          };

          alert("✅ You are now sharing your screen.");
        } catch (err) {
          console.error("❌ Screen share failed:", err);
        }
      };

      document.getElementById('endCall').onclick = () => {
        if (localStream) localStream.getTracks().forEach(t => t.stop());
        pc.close();
        db.ref(`calls/${appointmentId}`).remove();
        alert("Call ended.");
      };
    </script>

    <script src="../js/transition.js"></script>
  </body>
</html>