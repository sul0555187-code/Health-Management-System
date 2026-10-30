<?php
session_start();
require 'config/config.php';
require 'config/receptionist_data.php';

// إحصائيات بسيطة
$totalToday = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status != 'Cancelled'");
$totalToday->execute();
$totalToday = $totalToday->fetchColumn();

$totalUpcoming = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date > CURDATE() AND status != 'Cancelled'");
$totalUpcoming->execute();
$totalUpcoming = $totalUpcoming->fetchColumn();

$totalPatients = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role='user'");
$totalPatients->execute();
$totalPatients = $totalPatients->fetchColumn();

$recent = $pdo->prepare("
  SELECT a.id, a.appointment_date, a.appointment_time, a.status,
         d.name AS doctor_name, u.username AS patient_name
  FROM appointments a
  JOIN doctors d ON a.doctor_id = d.user_id
  JOIN users u ON a.user_id = u.id
  ORDER BY a.appointment_date ASC, a.appointment_time ASC
  LIMIT 8
");
$recent->execute();
$recent = $recent->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Receptionist Dashboard</title>
<link rel="icon" href="../logo/logo.jpg">
<link rel="stylesheet" href="../css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="home-section">
    <div class="effects"><div class="bg-effect"></div></div>
    <div class="container">
      <!-- header start -->
      <header class="header">
        <a href="receptionist_dashboard.php" class="logo" aria-label="Health Management Logo">
          <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
        </a>
        <nav class="links" aria-label="Main Navigation">
          <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
          <ul class="nav-bar">
            <li><a href="receptionist_dashboard.php" class="active">Dashboard</a></li>
            <li><a href="receptionist_appointments.php">Manage Appointments</a></li>
            <li><a href="receptionist_today.php">Today's Appointments</a></li>
            <li><a href="receptionist_patients.php">Patients</a></li>
            <li><a href="receptionist_doctors_schedule.php">Doctors Schedule</a></li>
            <li><a href="logout.php" class="register">Logout</a></li>
            <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
          </ul>
        </nav>
      </header>

      <main class="dashboard">
        <h1>Welcome, Receptionist 👋</h1>
        <p>Overview & quick actions.</p>

        <div class="stats-box" style="margin-top:30px;">
          <div class="card">
            <i class="fa-solid fa-calendar-day"></i>
            <h2><?= (int)$totalToday ?></h2>
            <p>Appointments Today</p>
          </div>
          <div class="card">
            <i class="fa-solid fa-calendar-check"></i>
            <h2><?= (int)$totalUpcoming ?></h2>
            <p>Upcoming Appointments</p>
          </div>
          <div class="card">
            <i class="fa-solid fa-users"></i>
            <h2><?= (int)$totalPatients ?></h2>
            <p>Total Patients</p>
          </div>
        </div>

        <h3 style="margin-top:40px;">Recent Appointments</h3>
        <?php if ($recent): ?>
          <table class="appointments-table" style="margin-top:12px;">
            <thead><tr><th>#</th><th>Doctor</th><th>Patient</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach($recent as $i=>$r): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= htmlspecialchars($r['doctor_name']) ?></td>
                  <td><?= htmlspecialchars($r['patient_name']) ?></td>
                  <td><?= htmlspecialchars($r['appointment_date']) ?></td>
                  <td><?= htmlspecialchars(substr($r['appointment_time'],0,5)) ?></td>
                  <td class="status <?= htmlspecialchars(strtolower($r['status'])) ?>"><?= htmlspecialchars($r['status']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No recent appointments found.</p>
        <?php endif; ?>

        <div class="actions" style="margin-top:30px;">
          <a href="receptionist_today.php" class="btn"><i class="fa-solid fa-calendar-plus"></i> Today's Appointments</a>
          <a href="receptionist_appointments.php" class="btn"><i class="fa-solid fa-list"></i> Manage Appointments</a>
        </div>
      </main>

<!-- Footer -->
        <footer>
          <p>&copy; <time datetime="2025">2025</time> Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>    </div>
  </div>

  <script src="../js/transition.js"></script>
</body>
</html>
