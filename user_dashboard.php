<?php
session_start();
require 'config/config.php';
require 'config/user_data.php';

// 🔹 تجهيز استعلام SQL للحصول على بيانات المواعيد الخاصة بالمستخدم الحالي
$appointments_stmt = $pdo->prepare("
  SELECT 
    a.id,                                                   -- رقم الموعد (المفتاح الأساسي من جدول appointments)
    d.name AS doctor_name,                                  -- اسم الطبيب (من جدول doctors)
    d.specialization,                                       -- تخصص الطبيب
    a.appointment_date,                                     -- تاريخ الموعد
    a.appointment_time,
    a.notes,
    a.status,                                               -- حالة الموعد (مثلاً: مؤكد، ملغي، بانتظار)
    a.fee AS doctor_fee
    FROM appointments a                                     -- الجدول الرئيسي للمواعيد
  JOIN doctors d ON a.doctor_id = d.user_id                      -- ربط جدول المواعيد بجدول الأطباء لجلب بيانات الطبيب
  WHERE a.user_id = ? AND a.appointment_date >= CURDATE()
  AND a.status != 'Cancelled'                               -- تحديد المستخدم الحالي (يتم تمرير الـ user_id كقيمة لاحقًا)
  ORDER BY a.appointment_date ASC                           -- ترتيب المواعيد تصاعديًا حسب التاريخ (الأقرب أولًا)
");

$appointments_stmt->execute([$_SESSION['user_id']]);
$appointments = $appointments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Dashboard | Health Centers & Hospitals</title>
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
          <a href="index.php" class="logo" aria-label="Health Management Logo">
            <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="user_notifications.php">🔔 Notifications (<?= $notifCount ?>)</a></li>
              <li><a href="user_dashboard.php" class="active">Dashboard</a></li>
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

        <!-- Main Dashboard -->
        <main class="patient-dashboard">
          <h1>Welcome, <?= htmlspecialchars($user['username']) ?> 👋</h1>
          <div class="user-profile-pic">
            <?php if (!empty($user['image']) && file_exists('../' . $user['image'])): ?>
              <img src="../<?= htmlspecialchars($user['image']); ?>" alt="Profile Picture" class="profile-image1">
            <?php else: ?>
              <img src="../img/users/default-user.png" alt="Default Profile" class="profile-image1">
            <?php endif; ?>
          </div>

          <p class="welcome-text">Manage your appointments and book new visits with our specialists.</p>

          <div class="doctor-buttons">
            <a href="doctors.php" class="btn">Book Appointment</a>
            <a href="user_appointments.php" class="btn btn-secondary">View My Appointments</a>
          </div><br><br><br>

          <h2>Next Appointments</h2>
          <?php if ($appointments): ?>
            <table class="appointments-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Doctor</th>
                  <th>Specialization</th>
                  <th>Fee (SAR)</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Notes</th>
                  <th>Status</th>
                </tr>
              </thead>

              <tbody>
                <?php foreach ($appointments as $index => $a): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                    <td><?= htmlspecialchars($a['specialization']) ?></td>
                    <td><?= htmlspecialchars($a['doctor_fee']) ?></td>
                    <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                    <td><?= htmlspecialchars($a['appointment_time']) ?></td>
                    <td><?= htmlspecialchars($a['notes']) ?></td>
                    <td class="status <?= htmlspecialchars(strtolower($a['status'])) ?>">
                      <?= htmlspecialchars(ucfirst($a['status'])) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            
          <?php else: ?>
            <p class="no-appointments">No appointments found. <a href="doctors.php">Book one now!</a></p><br>
          <?php endif; 
          ?>
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