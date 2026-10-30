<?php
session_start();
require 'config/config.php';
require 'config/receptionist_data.php';

// Check-in / complete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['checkin_id'])) {
    $id = (int)$_POST['checkin_id'];
    $pdo->prepare("UPDATE appointments SET status = 'In-Progress' WHERE id=?")->execute([$id]);
    $_SESSION['message'] = "Patient checked in.";
    header("Location: receptionist_today.php"); exit();
  }
  if (isset($_POST['complete_id'])) {
    $id = (int)$_POST['complete_id'];
    $pdo->prepare("UPDATE appointments SET status = 'Completed' WHERE id=?")->execute([$id]);
    $_SESSION['message'] = "Appointment marked as completed.";
    header("Location: receptionist_today.php"); exit();
  }
}

// جلب مواعيد اليوم
$appointments = $pdo->prepare("
  SELECT a.*, d.name AS doctor_name, u.username AS patient_name
  FROM appointments a
  JOIN doctors d ON a.doctor_id = d.user_id
  JOIN users u ON a.user_id = u.id
  WHERE a.appointment_date = CURDATE()
  ORDER BY a.appointment_time ASC
");
$appointments->execute();
$appointments = $appointments->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Today's Appointments</title>
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
            <li><a href="receptionist_dashboard.php">Dashboard</a></li>
            <li><a href="receptionist_appointments.php">Manage Appointments</a></li>
            <li><a href="receptionist_today.php" class="active">Today's Appointments</a></li>
            <li><a href="receptionist_patients.php">Patients</a></li>
            <li><a href="receptionist_doctors_schedule.php">Doctors Schedule</a></li>
            <li><a href="logout.php" class="register">Logout</a></li>
            <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
          </ul>
        </nav>
      </header>

      <main class="dashboard">
        <h1>Today's Appointments (<?= date('Y-m-d') ?>)</h1>

        <?php if (!empty($_SESSION['message'])): ?>
          <div class="alert success"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <?php if (!$appointments): ?>
          <p>No appointments for today.</p>
        <?php else: ?>
          <table class="appointments-table">
            <thead><tr><th>#</th><th>Time</th><th>Doctor</th><th>Patient</th><th>Notes</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach($appointments as $i=>$a): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= htmlspecialchars(substr($a['appointment_time'],0,5)) ?></td>
                  <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                  <td><?= htmlspecialchars($a['patient_name']) ?></td>
                  <td><?= htmlspecialchars($a['notes'] ?: '-') ?></td>
                  <td class="status <?= htmlspecialchars(strtolower($a['status'])) ?>"><?= htmlspecialchars($a['status']) ?></td>
                  <td>
                    <?php if ($a['status'] === 'Pending' || $a['status'] === 'Confirmed'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="checkin_id" value="<?= $a['id'] ?>">
                        <button class="btnn btn-confirm" type="submit"><i class="fa-solid fa-right-to-bracket"></i> Check-in</button>
                      </form>
                    <?php elseif ($a['status'] === 'In-Progress'): ?>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="complete_id" value="<?= $a['id'] ?>">
                        <button class="btnn btn-confirm" type="submit"><i class="fa-solid fa-circle-check"></i> Complete</button>
                      </form>
                    <?php else: ?>
                      <span style="opacity:0.8;"><?= htmlspecialchars($a['status']) ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </main>

      <!-- Footer -->
        <footer>
          <p>&copy; <time datetime="2025">2025</time> Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>
    </div>
  </div>

  <script src="../js/transition.js"></script>
</body>
</html>