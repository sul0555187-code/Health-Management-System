<?php
session_start();
require 'config/config.php';
require 'config/user_data.php';

// التأكد من معرف الموعد
$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;
if ($appointment_id <= 0) {
    echo "<script>
        alert('⚠️ Please Select Prescription!');
        window.location.href = 'user_appointments.php';
    </script>";
    exit;
}

// ✅ جلب كل الروشتات المرتبطة بالموعد
$stmt = $pdo->prepare("
    SELECT 
        n.prescription_image, 
        n.note, 
        n.created_at, 
        d.user_id AS doctor_user_id, 
        d.name AS doctor_name,
        a.appointment_date, 
        a.appointment_time
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.user_id
    LEFT JOIN doctor_notes n 
        ON n.doctor_id = d.user_id 
        AND n.patient_id = a.user_id
        AND n.appointment_id = a.id  -- ✅ الربط بالموعد المحدد
    WHERE a.id = ? 
      AND a.user_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$appointment_id, $user_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Prescription | Health Centers & Hospitals</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/styles.css" />
  </head>

  <body>
    <div class="home-section">
      <div class="effects"><div class="bg-effect"></div></div>
      <div class="container">
        <!-- header start -->
        <header class="header">
          <a href="index.php" class="logo" aria-label="Health Management Logo">
            <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="user_notifications.php">🔔 Notifications (<?= $notifCount ?>)</a></li>
              <li><a href="user_dashboard.php">Dashboard</a></li>
              <li><a href="doctors.php">Doctors</a></li>
              <li><a href="user_profile.php">Profile</a></li>
              <li><a href="user_appointments.php">My Appointments</a></li>
              <li><a href="user_prescription.php?appointment_id=<?= $appointment_id ?>" class="active">My Prescriptions</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <main class="wrapper1">
          <h1>Prescription for Appointment</h1>
          <?php if (!$notes): ?>
            <p>No prescriptions uploaded by doctor.</p>
          <?php else: ?>
            <div class="prescriptions-grid">
              <?php foreach ($notes as $n): ?>
              <div class="prescription-card">
                <p><strong>Doctor Name:</strong> <?= htmlspecialchars($n['doctor_name']) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($n['appointment_date']) ?></p>
                <p><strong>Time:</strong> <?= htmlspecialchars(substr($n['appointment_time'],0,5)) ?></p>
                <p><strong>Doctor Notes:</strong> <?= nl2br(htmlspecialchars($n['note'] ?: '-')) ?></p>

                <?php if (!empty($n['prescription_image'])): ?>
                <div class="prescription-preview">
                  <a href="../<?= htmlspecialchars($n['prescription_image']) ?>" target="_blank">
                    <?php if (preg_match('/\.pdf$/i', $n['prescription_image'])): ?>
                      <div class="pdf-box">📄 PDF File: <?= basename($n['prescription_image']) ?></div>
                    <?php else: ?>
                      <img src="../<?= htmlspecialchars($n['prescription_image']) ?>" alt="Prescription">
                    <?php endif; ?>
                  </a>
                  <a href="user_download_prescription.php?file=<?= urlencode($n['prescription_image']) ?>" class="btn-download">
                    <i class="fa-solid fa-download"></i> Download
                  </a>
                </div>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </main>

        <!-- Footer -->
        <footer>
          <p>&copy; <time datetime="2025">2025</time> Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <script src="../js/transition.js"></script>
  </body>
</html>