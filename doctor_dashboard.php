<?php
session_start();
require 'config/config.php';
require 'config/doctor_data.php';

// ✅ Ensure doctor exists
$checkUser = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'doctor'");
$checkUser->execute([$doctor_id]);
if ($checkUser->rowCount() === 0) {
    session_destroy();
    header("Location: login.php");
    exit();
}
// ✅ Handle Confirm / Cancel actions from same page
if (isset($_POST['action_type'], $_POST['appointment_id'])) {
    $id = (int) $_POST['appointment_id'];
    $action = $_POST['action_type'];

    if ($action === 'Cancel') {
        $update = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND doctor_id = ?");
        $update->execute([$id, $doctor_id]);

        $message = "Appointment cancelled successfully.";
        $messageType = "error"; // 🟡 لون أصفر

    } elseif ($action === 'Confirm') {
        $update = $pdo->prepare("UPDATE appointments SET status = 'Confirmed' WHERE id = ? AND doctor_id = ?");
        $update->execute([$id, $doctor_id]);

        $message = "Appointment confirmed successfully.";
        $messageType = "success"; // ✅ لون أخضر

    } else {
        $message = "Invalid action request.";
        $messageType = "error"; // 🔴 لون أحمر
    }
}

// ✅ Statistics - Upcoming Appointments only
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM appointments 
    WHERE doctor_id = ?
      AND appointment_date >= CURDATE()
      AND status != 'Cancelled'
");
$stmt->execute([$doctor_id]);
$totalAppointments = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments 
    WHERE doctor_id = ? AND status = 'Confirmed'
");
$stmt->execute([$doctor_id]);
$totalConfirmed = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT user_id) FROM appointments 
    WHERE doctor_id = ?
");
$stmt->execute([$doctor_id]);
$totalPatients = $stmt->fetchColumn();

// ✅ Upcoming appointments
$stmt = $pdo->prepare("
  SELECT a.*, u.username AS patient_name 
  FROM appointments a
  JOIN users u ON a.user_id = u.id
  WHERE a.doctor_id = ?
    AND a.appointment_date >= CURDATE()
    AND a.status != 'Cancelled'
  ORDER BY a.appointment_date ASC, a.appointment_time ASC
  LIMIT 10
");

$stmt->execute([$doctor_id]);
$upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard | Health Centers & Hospitals</title>
    <link rel="icon" href="../logo/logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
  </head>

  <body>
    <div class="home-section">
      <div class="effects"><div class="bg-effect"></div></div>
      <div class="container">

        <!-- Header -->
        <header class="header">
          <a href="index.php" class="logo">
            <img src="../logo/logo.jpg" width="80" height="80" alt="logo">
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="doctor_dashboard.php" class="active">Dashboard</a></li>
              <li><a href="about_us.php">About Us</a></li>
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

        <!-- Dashboard Content -->
        <main class="dashboard">
          <h1>Welcome, Dr. <?= htmlspecialchars($_SESSION['user_name']); ?> 👋</h1>

          <?php if (!empty($message)): ?>
            <div class="alert <?= htmlspecialchars($messageType) ?>">
              <?= htmlspecialchars($message) ?>
            </div>
          <?php endif; ?>


          <!-- Statistics -->
          <div class="stats-box">
            <div class="card">
              <i class="fa-solid fa-calendar-check"></i>
              <h2><?= $totalAppointments ?></h2>
              <p>Upcoming Appointments</p>
            </div>

            <div class="card">
              <i class="fa-solid fa-user-check"></i>
              <h2><?= $totalConfirmed ?></h2>
              <p>Confirmed Appointments</p>
            </div>

            <div class="card">
              <i class="fa-solid fa-users"></i>
              <h2><?= $totalPatients ?></h2>
              <p>Total Patients</p>
            </div>
          </div>

          <h2>Next Appointments</h2><br>

          <?php if (count($upcoming) === 0): ?>
            <p class="no-appointments">You have no upcoming appointments.</p>
          <?php else: ?>
            <table class="appointments-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Patient</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Notes</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($upcoming as $index => $app): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($app['patient_name']) ?></td>
                    <td><?= htmlspecialchars($app['appointment_date']) ?></td>
                    <td><?= htmlspecialchars(substr($app['appointment_time'], 0, 5)) ?></td>
                    <td><?= htmlspecialchars($app['notes'] ?: '-') ?></td>
                    <td class="status <?= htmlspecialchars($app['status']) ?>">
                      <?= htmlspecialchars(ucfirst($app['status'])) ?>
                    </td>

                    <td class="actions">
                      <div class="action-buttons">
                        <?php
                          // 🟢 In-Progress => Start Video
                          if ($app['status'] === 'In-Progress'): ?>
                            <form method="POST" action="doctor_start_video.php" style="display:inline;">
                              <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                              <input type="hidden" name="patient_id" value="<?= $app['user_id'] ?>">
                              <button type="submit" class="btn btn-video">
                                <i class="fa-solid fa-video"></i> Start Video
                              </button>
                            </form>

                        <?php
                          // 🟠 Confirmed => Wait for Payment
                          elseif ($app['status'] === 'Confirmed'): ?>
                            <button type="button" class="btnn btn-wait" disabled>
                              <i class="fa-solid fa-clock"></i> Wait for Payment
                            </button>

                        <?php
                          // 🩺 Completed => View Details
                          elseif ($app['status'] === 'Completed'): ?>
                            <form method="GET" action="doctor_patient_details.php" style="display:inline;">
                              <input type="hidden" name="id" value="<?= $app['user_id'] ?>">
                              <button type="submit" class="btn-view">
                                <i class="fa-solid fa-user"></i> View Details
                              </button>
                            </form>

                        <?php
                          // 🟡 Pending => Confirm
                          elseif ($app['status'] === 'Pending'): ?>
                            <form method="POST" style="display:inline;">
                              <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                              <input type="hidden" name="action_type" value="Confirm">
                              <button type="submit" class="btnn btn-confirm" onclick="return confirm('Confirm this appointment?');">
                                <i class="fa-solid fa-check"></i> Confirm
                              </button>
                            </form>
                        <?php endif; ?>

                        <?php
                          // ❌ Cancel button يظهر فقط لو Pending
                          if ($app['status'] == 'Pending'): ?>
                            <form method="POST" style="display:inline;">
                              <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                              <input type="hidden" name="action_type" value="Cancel">
                              <button type="submit" class="btnn btn-cancel" onclick="return confirm('Cancel this appointment?');">
                                <i class="fa-solid fa-xmark"></i> Cancel
                              </button>
                            </form>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>

          <div class="actions extra-actions">
            <a href="doctor_appointments.php" class="btn">
              <i class="fa-solid fa-calendar"></i> View All Appointments
            </a>
            <a href="doctor_profile.php" class="btn">
              <i class="fa-solid fa-user"></i> Edit Profile
            </a>
          </div>
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