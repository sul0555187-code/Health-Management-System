<?php
session_start();
require 'config/config.php';
require 'config/doctor_data.php';

// ✅ جلب قائمة المرضى الذين لديهم مواعيد مع الطبيب
$stmt = $pdo->prepare("
  SELECT 
    u.id AS patient_id,
    u.username AS patient_name,
    u.email,
    u.phone,
    COUNT(a.id) AS total_visits,
    MAX(a.appointment_date) AS last_visit
  FROM appointments a
  JOIN users u ON a.user_id = u.id
  WHERE a.doctor_id = ?
  GROUP BY u.id, u.username, u.email, u.phone
  ORDER BY last_visit DESC
");
$stmt->execute([$doctor_id]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Patients | Health Centers & Hospitals</title>
    <link rel="icon" href="../logo/logo.jpg" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/styles.css" />
  </head>
  
  <body>
    <div class="home-section">
      <div class="effects"><div class="bg-effect"></div></div>
      <div class="container">

        <!-- Header -->
        <header class="header">
          <a href="doctor_dashboard.php" class="logo"><img src="../logo/logo.jpg" alt="logo" /></a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="doctor_dashboard.php">Dashboard</a></li>
              <li><a href="about_us.php">About Us</a></li>
              <li><a href="doctor_appointments.php">My Appointments</a></li>
              <li><a href="doctor_patients.php" class="active">My Patients</a></li>
              <li><a href="doctor_profile.php">Profile</a></li>
              <li><a href="contact_us.php">Contact Us</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <!-- Main Content -->
        <main class="dashboard">
          <h1>My Patients</h1>

          <?php if (count($patients) === 0): ?>
            <p class="no-appointments">You have no patients yet.</p>
          <?php else: ?>
            <table class="patients-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Patient Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Total Visits</th>
                  <th>Last Visit</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($patients as $index => $p): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($p['patient_name']) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= htmlspecialchars($p['phone'] ?: '-') ?></td>
                    <td><?= (int)$p['total_visits'] ?></td>
                    <td><?= htmlspecialchars($p['last_visit']) ?></td>
                    <td>
                      <a href="doctor_patient_details.php?id=<?= (int)$p['patient_id'] ?>" class="btn-view">
                        <i class="fa-solid fa-eye"></i> View
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </main>

        <!-- Footer -->
        <footer>
          <p>&copy; 2025 Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <script src="../js/transition.js"></script>
  </body>
</html>