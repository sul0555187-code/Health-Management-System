<?php
session_start();
require 'config/config.php';
require 'config/receptionist_data.php';

// جلب الأطباء و أوقاتهم المشغولة خلال الأسبوع القادم
$doctors = $pdo->query("SELECT id, name, user_id FROM doctors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$weekStart = date('Y-m-d');
$weekEnd = date('Y-m-d', strtotime('+7 days'));

$schedules = [];
foreach($doctors as $d) {
  // استخدام JOIN لجلب اسم المريض
  $stmt = $pdo->prepare("
    SELECT a.appointment_date, a.appointment_time, a.status, u.username 
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.doctor_id = ? AND a.appointment_date BETWEEN ? AND ?
    ORDER BY a.appointment_date, a.appointment_time
  ");
  $stmt->execute([$d['user_id'], $weekStart, $weekEnd]);
  $schedules[$d['user_id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Doctors Schedule</title>
<link rel="stylesheet" href="../css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  .card {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }
  .card h3 {
    margin-bottom: 15px;
    font-size: 20px;
    font-weight: 600;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  table th, table td {
    padding: 10px 12px;
    border: 1px solid #ddd;
    text-align: left;
    font-size: 14px;
  }
  table th {
    background: #f5f5f5;
  }
  table tr:nth-child(even) {
    background: #fafafa;
  }
  .status {
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 500;
    text-align: center;
    display: inline-block;
    min-width: 80px;
  }
  .status.booked {
    background: #1e90ff;
    color: #fff;
  }
  .status.cancelled {
    background: #ff4d4f;
    color: #fff;
  }
  .status.completed {
    background: #52c41a;
    color: #fff;
  }
</style>
</head>
<body>
  <div class="home-section">
    <div class="effects"><div class="bg-effect"></div></div>
    <div class="container">
      <!-- header start -->
      <header class="header">
        <a href="receptionist_dashboard.php" class="logo"><img src="../logo/logo.jpg" width="80"></a>
        <nav class="links">
          <ul class="nav-bar">
            <li><a href="receptionist_dashboard.php">Dashboard</a></li>
            <li><a href="receptionist_appointments.php">Manage Appointments</a></li>
            <li><a href="receptionist_today.php">Today's Appointments</a></li>
            <li><a href="receptionist_patients.php">Patients</a></li>
            <li><a href="receptionist_doctors_schedule.php" class="active">Doctors Schedule</a></li>
            <li><a href="logout.php" class="register">Logout</a></li>
            <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
          </ul>
        </nav>
      </header>

      <main class="dashboard">
        <h1>Doctors Schedule (Next 7 Days)</h1>

        <?php foreach($doctors as $d): ?>
          <div class="card">
            <h3><?= htmlspecialchars($d['name']) ?></h3>
            <?php $rows = $schedules[$d['user_id']] ?? []; ?>
            <?php if (!$rows): ?>
              <p>No bookings in next 7 days.</p>
            <?php else: ?>
              <table>
                <thead>
                  <tr>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($rows as $r): ?>
                    <tr>
                      <td><?= htmlspecialchars($r['username']) ?></td>
                      <td><?= htmlspecialchars($r['appointment_date']) ?></td>
                      <td><?= htmlspecialchars(substr($r['appointment_time'],0,5)) ?></td>
                      <td>
                        <span class="status <?= strtolower($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

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