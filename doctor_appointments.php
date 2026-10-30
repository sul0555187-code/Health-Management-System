<?php
session_start();
require 'config/config.php';
require 'config/doctor_data.php';

// الحصول على فلاتر GET
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$filterDateRaw = isset($_GET['date']) ? trim($_GET['date']) : '';
$filterDate = '';
if (!empty($filterDateRaw)) {
    $ts = strtotime($filterDateRaw);
    if ($ts !== false) {
        $filterDate = date('Y-m-d', $ts);
    }
}

// بناء استعلام جلب المواعيد مع الفلاتر
$sql = "
  SELECT a.*, u.username AS patient_name, u.email AS patient_email 
  FROM appointments a
  JOIN users u ON a.user_id = u.id
  WHERE a.doctor_id = ?
";
$params = [$doctor_id];

if ($filterStatus && in_array($filterStatus, ['Pending', 'Confirmed', 'Cancelled', 'Completed', 'In-Progress'], true)) {
    $sql .= " AND a.status = ?";
    $params[] = $filterStatus;
}
if ($filterDate) {
    $sql .= " AND a.appointment_date = ?";
    $params[] = $filterDate;
}

$sql .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Doctor Appointments | Health Centers & Hospitals</title>
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
          <a href="doctor_dashboard.php" class="logo">
            <img src="../logo/logo.jpg" width="80" height="80" alt="logo" />
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="doctor_dashboard.php">Dashboard</a></li>
              <li><a href="about_us.php">About Us</a></li>
              <li><a href="doctor_appointments.php" class="active">My Appointments</a></li>
              <li><a href="doctor_patients.php">My Patients</a></li>
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
          <h1>All Appointments</h1>

          <?php if (!empty($_SESSION['message'])): ?>
            <div class="message"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
          <?php endif; ?>

          <!-- Filter form (GET) -->
          <form method="GET" class="filter-form" style="margin-bottom:15px;">
            <label>
              Status:
              <select name="status" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="Pending" <?= $filterStatus === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Confirmed" <?= $filterStatus === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="Cancelled" <?= $filterStatus === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                <option value="Completed" <?= $filterStatus === 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option value="In-Progress" <?= $filterStatus === 'In-Progress' ? 'selected' : '' ?>>In-Progress</option>
              </select>
            </label>

            <label>
              Date:
              <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>" onchange="this.form.submit()" />
            </label>

            <button type="submit" class="btn-secondary" style="margin-left:8px;">Filter</button>
            <a href="doctor_appointments.php" class="btn" style="margin-left:8px;">Clear</a>
          </form>

          <?php if (count($appointments) === 0): ?>
            <p class="no-appointments">No appointments found.</p>
          <?php else: ?>
            <table class="appointments-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Patient</th>
                  <th>Email</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Notes</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($appointments as $index => $app): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($app['patient_name']) ?></td>
                    <td><?= htmlspecialchars($app['patient_email']) ?></td>
                    <td><?= htmlspecialchars($app['appointment_date']) ?></td>
                    <td><?= htmlspecialchars(substr($app['appointment_time'], 0, 5)) ?></td>
                    <td><?= htmlspecialchars($app['notes'] ?: '-') ?></td>
                    <td class="status <?= htmlspecialchars($app['status']) ?>"><?= htmlspecialchars($app['status']) ?></td>        
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