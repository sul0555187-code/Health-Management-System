<?php
session_start();
require 'config/config.php';
require 'config/user_data.php';

// الحصول على معرف الموعد من GET
if (!isset($_GET['appointment_id'])) {
    header("Location: user_appointments.php");
    exit();
}

$appointment_id = (int) $_GET['appointment_id'];

// جلب بيانات الموعد
$stmt = $pdo->prepare("
    SELECT a.*, d.name AS doctor_name, d.specialization, d.fee
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.user_id   -- ربط doctor_id (appointments) بـ user_id (doctors)
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->execute([$appointment_id, $user_id]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    $_SESSION['message'] = "Appointment not found.";
    header("Location: user_appointments.php");
    exit();
}

// المبلغ حسب الدكتور
$amount = $appointment['fee'];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment | Health Centers & Hospitals</title>
    <link rel="icon" href="../logo/logo.jpg" type="image/x-icon" />
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
              <li><a href="user_appointments.php">My Appointments</a></li>
              <li><a href="user_payment.php?appointment_id=<?= $appointment_id ?>" class="active">Payment </a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <!-- Main Content -->
        <main class="wrapper1">
          <h1>Payment for Appointment</h1>

          <div class="payment-card">
            <h2>Appointment Details</h2>
            <div class="payment-info">
              <p><strong>Doctor:</strong> <?= htmlspecialchars($appointment['doctor_name']) ?></p>
              <p><strong>Specialization:</strong> <?= htmlspecialchars($appointment['specialization']) ?></p>
              <p><strong>Date:</strong> <?= htmlspecialchars($appointment['appointment_date']) ?></p>
              <p><strong>Time:</strong> <?= htmlspecialchars(substr($appointment['appointment_time'],0,5)) ?></p>
              <p><strong>Status:</strong> <?= htmlspecialchars($appointment['status']) ?></p>
              <p><strong>Amount:</strong> <?= number_format($amount,2) ?> SAR</p>
            </div><br>

            <h3 style="margin-top:18px;">Choose Payment Method</h3>
            <form action="user_process_payment.php" method="POST" id="paymentForm">
              <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
              <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">

              <div class="payment-methods" id="methods" role="radiogroup" aria-label="Payment methods">
                <label class="pm-option" data-value="visa" role="radio" aria-checked="false" tabindex="0">
                  <input type="radio" name="method" value="visa" />
                  <!-- Font Awesome icon for Visa -->
                  <i class="fa-brands fa-cc-visa" aria-hidden="true"></i>
                  <span class="pm-label">Visa</span>
                </label>

                <label class="pm-option" data-value="mastercard" role="radio" aria-checked="false" tabindex="0">
                  <input type="radio" name="method" value="mastercard" />
                  <!-- Font Awesome icon for Mastercard -->
                  <i class="fa-brands fa-cc-mastercard" aria-hidden="true"></i>
                  <span class="pm-label">Mastercard</span>
                </label>

                <label class="pm-option" data-value="paypal" role="radio" aria-checked="false" tabindex="0">
                  <input type="radio" name="method" value="paypal" />
                  <!-- Font Awesome icon for PayPal -->
                  <i class="fa-brands fa-paypal" aria-hidden="true"></i>
                  <span class="pm-label">PayPal</span>
                </label>
                <label class="pm-option" data-value="stcpay" role="radio" aria-checked="false" tabindex="0">
                  <input type="radio" name="method" value="stcpay" />
                  <!-- أيقونة STC Pay -->
                  <img src="../img/icons/stcpay.png" alt="STC Pay" style="height:24px; width:auto;" />
                  <span class="pm-label">STC Pay</span>
                </label>
              </div>


              <div style="margin-top:18px;">
                <button type="submit" class="btn-payment1">
                  <i class="fa-solid fa-credit-card"></i> Pay Now
                </button>
              </div>
            </form>
          </div>
        </main>

        <!-- Footer -->
        <footer>
          <p>&copy; <time datetime="2025">2025</time> Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <script src="../js/transition.js"></script>
    <script src="../js/payment_method.js"></script>
  </body>
</html>