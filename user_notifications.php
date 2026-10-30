<?php
session_start();
require 'config/config.php';
require 'config/user_data.php';

// ✅ جلب الإشعارات من قاعدة البيانات
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ تعيين الإشعارات كمقروءة
$pdo->prepare("UPDATE notifications SET status='read' WHERE user_id=?")->execute([$user_id]);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Notifications | Health Centers & Hospitals</title>
    <link rel="icon" href="../logo/logo.jpg" type="image/x-icon" />
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
              <li><a href="user_notifications.php" class="active">🔔 Notifications (<?= $notifCount ?>)</a></li>
              <li><a href="user_dashboard.php">Dashboard</a></li>
              <li><a href="doctors.php">Doctors</a></li>
              <li><a href="user_profile.php">Profile</a></li>
              <li><a href="user_appointments.php">My Appointments</a></li>
              <li><a href="contact_us.php">Contact Us</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <!-- Main Content -->
        <main class="dashboard">
          <h1>🔔 My Notifications</h1>

          <?php if (!$notifications): ?>
            <p class="no-appointments">You have no notifications.</p>
          <?php else: ?>
            <div class="notif-container">
              <?php foreach ($notifications as $n): ?>
                <div class="notif-card <?= $n['status'] === 'unread' ? 'unread' : '' ?>">
                  <div class="notif-icon">
                    <i class="fa-solid fa-bell"></i>
                  </div>
                  <div class="notif-details">
                    <p class="notif-message"><?= htmlspecialchars($n['message']) ?></p>
                    <?php if (!empty($n['link'])): ?>
                      <a href="<?= htmlspecialchars($n['link']) ?>" class="btn btn-small">
                        <i class="fa-solid fa-arrow-right"></i> Open
                      </a>
                    <?php endif; ?>
                    <small class="notif-date">🕒 <?= htmlspecialchars($n['created_at']) ?></small>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <a href="user_dashboard.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Back
          </a>
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