<?php
session_start();
require 'config/config.php';
require 'config/admin_data.php';

// التعامل مع Resolve
if (isset($_GET['resolve_id'])) {
    $resolve_id = (int)$_GET['resolve_id'];
    $pdo->prepare("UPDATE messages SET status='resolved' WHERE id=?")->execute([$resolve_id]);
    header("Location: admin_messages.php");
    exit();
}

$stmt = $pdo->query("SELECT id, name, email, subject, message, created_at, status FROM messages ORDER BY id ASC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Messages | Health Centers & Hospitals</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="icon" href="../logo/logo.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
              <li><a href="admin_messages.php" class="active">Messages</a></li>
              <li><a href="admin_profile.php">Profile</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <main class="dashboard">
          <h1>Contact Messages</h1>
          <?php if (!$messages): ?>
            <p>No messages found.</p>
          <?php else: ?>
            <table class="appointments-table">
              <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Action</th></tr></thead>
              <tbody>
                <?php foreach ($messages as $i => $m): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= htmlspecialchars($m['name']) ?></td>
                  <td><?= htmlspecialchars($m['email']) ?></td>
                  <td><?= htmlspecialchars($m['subject']) ?></td>
                  <td><?= nl2br(htmlspecialchars($m['message'])) ?></td>
                  <td><?= htmlspecialchars($m['created_at']) ?></td>
                <td>
                    <?php if ($m['status'] === 'pending'): ?>
                      <a href="?resolve_id=<?= $m['id'] ?>" class="btn small">Resolve</a>
                    <?php else: ?>
                      <span class="resolved">Resolved</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </main>
      </div>
    </div>

    <script src="../js/transition.js"></script>
  </body>
</html>