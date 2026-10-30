<?php
session_start();
require 'config/config.php';
require 'config/admin_data.php';

// 🧮 حساب الإحصائيات
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM users WHERE role='doctor'")->fetchColumn();
$totalPatients = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$totalMessages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();

// 📊 بيانات المواعيد الشهرية للسنة الحالية
$appointments = $pdo->query("
    SELECT MONTH(appointment_date) as month, COUNT(*) as total
    FROM appointments
    WHERE YEAR(appointment_date) = YEAR(CURDATE())
    GROUP BY MONTH(appointment_date)
")->fetchAll(PDO::FETCH_ASSOC);

$appointmentsLabels = [];
$appointmentsData = [];
for($i=1;$i<=12;$i++){
    $appointmentsLabels[] = date('M', mktime(0,0,0,$i,1));
    $found = false;
    foreach($appointments as $a){
        if($a['month'] == $i){
            $appointmentsData[] = $a['total'];
            $found = true;
            break;
        }
    }
    if(!$found) $appointmentsData[] = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard | Health Centers & Hospitals</title>
    <link rel="icon" href="../logo/logo.jpg" />
    <link rel="stylesheet" href="../css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      .stats-box {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 40px;
      }
      .card {
        background: var(--card-bg);
        padding: 25px;
        text-align: center;
        border-radius: 12px;
        box-shadow: var(--shadow);
        transition: transform 0.2s ease;
      }
      .card:hover { transform: translateY(-5px); }
      .card i { font-size: 40px; margin-bottom: 15px; }
      .card h2 { font-size: 35px; margin: 10px 0; font-weight: 700; }
      .card p { font-size: 18px; opacity: 0.8; }
      .actions { margin-top: 50px; display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; }
      .chart-container { max-width: 800px; margin: 50px auto; }
    </style>
  </head>
  
  <body>
    <div class="home-section">
      <div class="effects"><div class="bg-effect"></div></div>
      <div class="container">

        <!-- Header -->
        <header class="header">
          <a href="admin_dashboard.php" class="logo" aria-label="Health Management Logo">
            <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
              <li><a href="admin_doctors.php">Doctors</a></li>
              <li><a href="admin_users.php">Patients</a></li>
              <li><a href="admin_appointments.php">Appointments</a></li>
              <li><a href="admin_messages.php">Messages</a></li>
              <li><a href="admin_profile.php">Profile</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <!-- Dashboard Content -->
        <main class="dashboard">
          <h1>Welcome, Admin 👋</h1>
          <p>Here’s an overview of the platform activity.</p>

          <!-- Stats Cards -->
          <div class="stats-box">
            <div class="card">
              <i class="fa-solid fa-user-md"></i>
              <h2><?= $totalDoctors ?></h2>
              <p>Total Doctors</p>
            </div>
            <div class="card">
              <i class="fa-solid fa-users"></i>
              <h2><?= $totalPatients ?></h2>
              <p>Total Patients</p>
            </div>
            <div class="card">
              <i class="fa-solid fa-calendar-check"></i>
              <h2><?= $totalAppointments ?></h2>
              <p>Total Appointments</p>
            </div>
            <div class="card">
              <i class="fa-solid fa-envelope"></i>
              <h2><?= $totalMessages ?></h2>
              <p>Messages Received</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="actions extra-actions">
            <a href="admin_doctors.php" class="btn"><i class="fa-solid fa-user-md"></i> Manage Doctors</a>
            <a href="admin_users.php" class="btn"><i class="fa-solid fa-users"></i> Manage Patients</a>
            <a href="admin_appointments.php" class="btn"><i class="fa-solid fa-calendar-days"></i> Manage Appointments</a>
          </div>

          <!-- Chart -->
          <div class="chart-container">
            <canvas id="appointmentsChart"></canvas>
          </div>

        </main>

        <!-- Footer -->
        <footer>
          <p>&copy; 2025 Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <script>
      // Chart.js
      const ctx = document.getElementById('appointmentsChart').getContext('2d');
      const appointmentsChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: <?= json_encode($appointmentsLabels) ?>,
          datasets: [{
            label: 'Appointments per Month',
            data: <?= json_encode($appointmentsData) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: true },
            title: { display: true, text: 'Appointments Statistics' }
          },
          scales: { y: { beginAtZero: true } }
        }
      });
    </script>
    <script src="../js/transition.js"></script>
  </body>
</html>