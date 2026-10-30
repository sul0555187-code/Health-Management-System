<?php
session_start();
require 'config/config.php';
require 'config/admin_data.php';

$admin_id = (int)$_SESSION['user_id'];

// ✅ جلب بيانات الأدمن من قاعدة البيانات
$stmt = $pdo->prepare("SELECT username, email, phone, address FROM users WHERE id=? AND role='admin'");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Admin not found.");
}

// ✅ تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);

    if ($username === '' || $email === '') {
        $_SESSION['message'] = "⚠️ Please fill in all required fields.";
        header("Location: admin_profile.php");
        exit();
    }

    // ✅ منع الإيميل المكرر
    $check = $pdo->prepare("SELECT id FROM users WHERE email=? AND id<>?");
    $check->execute([$email, $admin_id]);
    if ($check->rowCount() > 0) {
        $_SESSION['message'] = "❌ Email is already used!";
        header("Location: admin_profile.php");
        exit();
    }

    // ✅ تنفيذ عملية التحديث
    $update = $pdo->prepare("UPDATE users SET username=?, email=?, phone=?, address=? WHERE id=?");
    $update->execute([$username, $email, $phone, $address, $admin_id]);

    $_SESSION['message'] = "✅ Profile updated successfully!";
    header("Location: admin_profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | Health Centers & Hospitals</title>

    <link rel="icon" href="../logo/logo.jpg">
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
              <li><a href="admin_appointments.php">Appointments</a></li>
              <li><a href="admin_messages.php">Messages</a></li>
              <li><a href="admin_profile.php" class="active">Profile</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <!-- Main -->
        <main class="profile-section">
          <h1>Admin Profile</h1>

          <?php if (!empty($_SESSION['message'])): ?>
            <div class="message">
              <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
            </div>
          <?php endif; ?>

          <form method="POST" class="profile-form">

            <div class="input-box">
              <label>Username</label>
              <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>
            </div>

            <div class="input-box">
              <label>Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
            </div>

            <div class="input-box">
              <label>Phone</label>
              <input type="text" name="phone" value="<?= htmlspecialchars($admin['phone']) ?>">
            </div>

            <div class="input-box">
              <label>Address</label>
              <input type="text" name="address" value="<?= htmlspecialchars($admin['address']) ?>">
            </div>

            <button type="submit" class="btn-update">
              <i class="fa-solid fa-save"></i> Save Changes
            </button>

          </form>
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