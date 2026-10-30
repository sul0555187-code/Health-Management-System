<?php
session_start();
require 'config/config.php';
require 'config/admin_data.php';

/* ✅ Update appointment status directly in same page */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {

  $appointment_id = (int)$_POST['appointment_id'];
  $status = trim($_POST['status']);

  $allowed = ['Pending', 'Confirmed', 'Cancelled', 'Completed', 'In-Progress'];

  if (!in_array($status, $allowed)) {
    die("Invalid status!");
  }

  // ✅ تأكد من حالة الموعد الحالية
  $stmtCheck = $pdo->prepare("SELECT status FROM appointments WHERE id=?");
  $stmtCheck->execute([$appointment_id]);
  $current = $stmtCheck->fetchColumn();

  if ($current !== 'In-Progress' && $current !== 'Completed') {
    $stmt = $pdo->prepare("UPDATE appointments SET status=? WHERE id=?");
    $stmt->execute([$status, $appointment_id]);
    $_SESSION['message'] = "✅ Appointment status updated!";
  } else {
    $_SESSION['message'] = "⚠️ Cannot modify appointments that are In-Progress or Completed!";
  }

  header("Location: admin_appointments.php");
  exit();
}

/* ✅ Fetch all appointments */
$stmt = $pdo->query("
  SELECT a.id, a.appointment_date, a.appointment_time, a.status,
         d.username AS doctor_name, u.username AS patient_name
  FROM appointments a
  JOIN users d ON a.doctor_id = d.id
  JOIN users u ON a.user_id = u.id
  ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Appointments | Health Centers & Hospitals</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="icon" href="../logo/logo.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>  
      /* تصميم القائمة المنسدلة في جدول المواعيد */
      .appointments-table select {
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        background-color: #f9f9f9;
        color: #333;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      /* تأثير عند التركيز على القائمة */
      .appointments-table select:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        background-color: #fff;
      }
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
              <li><a href="admin_dashboard.php">Dashboard</a></li>
              <li><a href="admin_doctors.php">Doctors</a></li>
              <li><a href="admin_users.php">Patients</a></li>
              <li><a href="admin_appointments.php" class="active">Appointments</a></li>
              <li><a href="admin_messages.php">Messages</a></li>
              <li><a href="admin_profile.php">Profile</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <main class="dashboard">
          <h1>All Appointments</h1>

          <?php if (!empty($_SESSION['message'])): ?>
            <div class="alert success"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
          <?php endif; ?>

          <table class="appointments-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Doctor</th>
                <th>Patient</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($appointments as $i => $a): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                <td><?= htmlspecialchars($a['patient_name']) ?></td>
                <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                <td><?= htmlspecialchars(substr($a['appointment_time'],0,5)) ?></td>
                <td class="status <?= htmlspecialchars($a['status']) ?>">
                    <?php if ($a['status'] === 'Pending'): ?>
                      <span class="status-badge Pending"><i class="fa-solid fa-hourglass-half"></i> Pending</span>
                    <?php elseif ($a['status'] === 'Confirmed'): ?>
                      <span class="status-badge Confirmed"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                    <?php elseif ($a['status'] === 'Completed'): ?>
                      <span class="status-badge Completed"><i class="fa-solid fa-star completed-icon"></i> Completed</span>
                    <?php elseif ($a['status'] === 'In-Progress'): ?>
                      <span class="status-badge In-Progress"><i class="fa-solid fa-spinner fa-spin"></i> In-Progress</span>
                    <?php elseif ($a['status'] === 'Cancelled'): ?>
                      <span class="status-badge Cancelled"><i class="fa-solid fa-circle-xmark"></i> Cancelled</span>
                    <?php endif; ?>
                  </td>
                <td class="status <?= htmlspecialchars(strtolower($a['status'])) ?>">
                  <?php if ($a['status'] !== 'In-Progress' && $a['status'] !== 'Completed'): ?>
                    <form method="POST" style="margin:0;">
                      <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                      <select name="status" onchange="this.form.submit()">
                        <?php 
                          $options = ['Pending','Confirmed','Cancelled','In-Progress','Completed'];
                          foreach ($options as $opt): 
                            $sel = $a['status'] === $opt ? 'selected' : '';
                        ?>
                          <option value="<?= $opt ?>" <?= $sel ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                      </select>
                    </form>
                  <?php else: ?>
                    <?= htmlspecialchars($a['status']) ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </main>
      </div>
    </div>

    <script src="../js/transition.js"></script>
  </body>
</html>
