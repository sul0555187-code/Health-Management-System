<?php
session_start();
require 'config/config.php'; // ملف الاتصال بقاعدة البيانات

// ✅ لو المستخدم مسجل دخول فعلًا، نحوله حسب نوعه
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'doctor') {
        header("Location: doctor_dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    }
}

// جلب بيانات الأطباء
$stmt = $pdo->query("SELECT * FROM doctors");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Doctors | Health Centers & Hospitals</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- CSS -->
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
              <li><a href="index.php">Home</a></li>
              <li><a href="about_us.php">About Us</a></li>
              <li><a href="doctors.php" class="active">Doctors</a></li>
              <li><a href="user_appointments.php">My Appointments</a></li>
              <li><a href="contact_us.php">Contact Us</a></li>

              <?php if (isset($_SESSION['user_id'])): ?>
                <!-- المستخدم مسجل دخول -->
                <li><a href="logout.php" class="register">Logout</a></li>
              <?php else: ?>
                <!-- المستخدم غير مسجل -->
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="register">Register Now</a></li>
              <?php endif; ?>

              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>
        <!-- header end -->

        <!-- Doctors Section -->
        <main class="doctors">
          <h1>Meet Our Doctors</h1>
          <div class="doctors-grid">
            <?php foreach ($doctors as $doctor): ?>
              <?php
                // ✅ تحديد الصورة الصحيحة أو الصورة الافتراضية
                $imagePath = (!empty($doctor['image']) && file_exists('../' . $doctor['image']))
                  ? '../' . $doctor['image']
                  : '../img/doctors/default-doctor.png';
              ?>
              <div class="doctor-card">
                <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" 
                    alt="<?= htmlspecialchars($doctor['name'] ?? 'Doctor', ENT_QUOTES, 'UTF-8') ?>" />

                <h3><?= htmlspecialchars($doctor['name'] ?? 'Dr. Unknown', ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($doctor['specialization'] ?? 'General Practitioner', ENT_QUOTES, 'UTF-8') ?></p>

                <!-- ✅ عرض سعر الكشف -->
                <?php if (!empty($doctor['fee'])): ?>
                  <p class="doctor-fee">
                    <?= htmlspecialchars($doctor['fee'], ENT_QUOTES, 'UTF-8') ?> ﷼
                  </p>
                <?php else: ?>
                  <p class="doctor-fee">💰 Fee: Not specified</p>
                <?php endif; ?>

                <a href="user_book_appointment.php?doctor_id=<?= $doctor['id'] ?>" class="btn">Book Appointment</a>
              </div>
            <?php endforeach; ?>
          </div>
        </main>

        <!-- footer -->
        <footer>
          <p>&copy; 2025 Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <script src="../js/transition.js"></script>
  </body>
</html>