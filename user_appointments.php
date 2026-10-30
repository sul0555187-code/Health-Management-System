<?php
session_start();
require 'config/config.php';
require 'config/user_data.php';

// ✅ إلغاء الموعد عند الضغط على الزر
if (isset($_POST['cancel_id'])) {
    $cancel_id = $_POST['cancel_id'];

    $cancel = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND user_id = ?");
    $cancel->execute([$cancel_id, $user_id]);

    $_SESSION['message'] = "Appointment has been cancelled successfully";
    header("Location: user_appointments.php");
    exit();
}

// ✅ جلب مواعيد المستخدم مع بيانات الطبيب
$stmt = $pdo->prepare("
    SELECT a.*, d.name AS doctor_name, d.specialization, d.fee
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.user_id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Appointments | Health Centers & Hospitals</title>
  <link rel="icon" href="../logo/logo.jpg" type="image/x-icon" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="../css/styles.css" />
</head>

<body>
  <div class="home-section">
    <div class="effects"><div class="bg-effect"></div></div>
    <div class="container">

      <!-- Header -->
      <header class="header">
        <a href="user_dashboard.php" class="logo">
          <img src="../logo/logo.jpg" alt="Health Centers Logo" />
        </a>
        <nav class="links" aria-label="Main Navigation">
          <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
          <ul class="nav-bar">
            <li><a href="user_notifications.php">🔔 Notifications (<?= $notifCount ?>)</a></li>
            <li><a href="user_dashboard.php">Dashboard</a></li>
            <li><a href="doctors.php">Doctors</a></li>
            <li><a href="user_profile.php">Profile</a></li>
            <li><a href="user_appointments.php" class="active">My Appointments</a></li>
            <li><a href="contact_us.php">Contact Us</a></li>
            <li><a href="logout.php" class="register">Logout</a></li>
            <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
            <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
          </ul>
        </nav>
      </header>

      <!-- Main Section -->
      <main class="wrapper1">
        <h1>My Appointments</h1>

        <div class="actions">
          <a href="doctors.php" class="btn-book">
            <i class="fa-solid fa-calendar-plus"></i> Book New Appointment
          </a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
          <div class="message_payment"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <?php if (count($appointments) === 0): ?>
          <p class="no-appointments">You have no appointments yet.</p>
        <?php else: ?>
          <!-- ✅ جدول للحواسيب -->
          <div class="table-wrapper">
            <table class="appointments-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Doctor</th>
                  <th>Specialization</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Notes</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($appointments as $index => $app): ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($app['doctor_name']) ?></td>
                  <td><?= htmlspecialchars($app['specialization']) ?></td>
                  <td><?= htmlspecialchars($app['appointment_date']) ?></td>
                  <td><?= htmlspecialchars(substr($app['appointment_time'], 0, 5)) ?></td>
                  <td><?= htmlspecialchars($app['notes'] ?: '-') ?></td>
                  <td class="status <?= htmlspecialchars($app['status']) ?>">
                    <?php if ($app['status'] === 'Pending'): ?>
                      <span class="status-badge Pending"><i class="fa-solid fa-hourglass-half"></i> Pending</span>
                    <?php elseif ($app['status'] === 'Confirmed'): ?>
                      <span class="status-badge Confirmed"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                    <?php elseif ($app['status'] === 'Completed'): ?>
                      <span class="status-badge Completed"><i class="fa-solid fa-star completed-icon"></i> Completed</span>
                    <?php elseif ($app['status'] === 'In-Progress'): ?>
                      <span class="status-badge In-Progress"><i class="fa-solid fa-spinner fa-spin"></i> In-Progress</span>
                    <?php elseif ($app['status'] === 'Cancelled'): ?>
                      <span class="status-badge Cancelled"><i class="fa-solid fa-circle-xmark"></i> Cancelled</span>
                    <?php endif; ?>
                  </td>

                  <td>
                    <?php if ($app['status'] === 'Pending'): ?>
                      <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                        <input type="hidden" name="cancel_id" value="<?= $app['id'] ?>">
                        <button type="submit" class="btn-cancel"><i class="fa-solid fa-times"></i> Cancel</button>
                      </form>

                    <?php elseif ($app['status'] === 'In-Progress'): ?>
                      <span class="inprogress-text"><i class="fa-solid fa-spinner fa-spin"></i> In-Progress</span>

                    <?php elseif ($app['status'] === 'Completed'): ?>
                      <form method="GET" action="user_prescription.php">
                        <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                        <button type="submit" class="btn-view">
                          <i class="fa-solid fa-file-medical"></i> View Prescription
                        </button>
                      </form>

                    <?php elseif ($app['status'] === 'Cancelled'): ?>
                      <span class="cancelled-text"><i class="fa-solid fa-ban"></i> Cancelled</span>

                    <?php elseif ($app['status'] === 'Confirmed'): ?>
                      <form action="user_payment.php" method="GET">
                        <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                        <button type="submit" class="btn-payment">
                          <i class="fa-solid fa-credit-card"></i> Payment
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- ✅ كروت للموبايل -->
          <?php foreach ($appointments as $index => $app): ?>
            <div class="appointment-card">
              <h3><?= htmlspecialchars($app['doctor_name']) ?></h3>
              <p><strong>Specialization:</strong> <?= htmlspecialchars($app['specialization']) ?></p>
              <p><strong>Date:</strong> <?= htmlspecialchars($app['appointment_date']) ?></p>
              <p><strong>Time:</strong> <?= htmlspecialchars(substr($app['appointment_time'], 0, 5)) ?></p>
              <p><strong>Notes:</strong> <?= htmlspecialchars($app['notes'] ?: '-') ?></p>
              <p><strong>Status:</strong>
                <?php if ($app['status'] === 'Pending'): ?>
                  <span class="status-badge Pending"><i class="fa-solid fa-hourglass-half"></i> Pending</span>
                <?php elseif ($app['status'] === 'Confirmed'): ?>
                  <span class="status-badge Confirmed"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                <?php elseif ($app['status'] === 'Completed'): ?>
                  <span class="status-badge Completed"><i class="fa-solid fa-star completed-icon"></i> Completed</span>
                <?php elseif ($app['status'] === 'In-Progress'): ?>
                  <span class="status-badge In-Progress"><i class="fa-solid fa-spinner fa-spin"></i> In-Progress</span>
                <?php elseif ($app['status'] === 'Cancelled'): ?>
                  <span class="status-badge Cancelled"><i class="fa-solid fa-circle-xmark"></i> Cancelled</span>
                <?php endif; ?>
              </p>

              <div class="actions">
                <?php if ($app['status'] === 'Pending'): ?>
                  <form method="POST" onsubmit="return confirm('Cancel this appointment?');">
                    <input type="hidden" name="cancel_id" value="<?= $app['id'] ?>">
                    <button type="submit" class="btn-cancel"><i class="fa-solid fa-times"></i> Cancel</button>
                  </form>

                <?php elseif ($app['status'] === 'Completed'): ?>
                  <form method="GET" action="user_prescription.php">
                    <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                    <button type="submit" class="btn-view">
                      <i class="fa-solid fa-file-medical"></i> View Prescription
                    </button>
                  </form>

                <?php elseif ($app['status'] === 'Confirmed'): ?>
                  <form action="user_payment.php" method="GET">
                    <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                    <button type="submit" class="btn-payment">
                      <i class="fa-solid fa-credit-card"></i> Payment
                    </button>
                  </form>

                <?php elseif ($app['status'] === 'In-Progress'): ?>
                  <span class="inprogress-text"><i class="fa-solid fa-spinner fa-spin"></i> In-Progress</span>

                <?php elseif ($app['status'] === 'Cancelled'): ?>
                  <span class="cancelled-text"><i class="fa-solid fa-ban"></i> Cancelled</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
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