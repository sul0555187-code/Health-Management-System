<?php
session_start();
require 'config/config.php';
require 'config/doctor_data.php';

// ✅ جلب بيانات الطبيب من الجدولين (users + doctors) باستخدام user_id
$stmt = $pdo->prepare("
  SELECT 
    u.id, u.username, u.email, u.password, u.phone, 
    d.specialization, d.image, d.fee
  FROM users u
  LEFT JOIN doctors d ON d.user_id = u.id
  WHERE u.id = ?
");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
  die("Doctor not found in database.");
}

// ✅ تحديث بيانات الطبيب
if (isset($_POST['update'])) {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $specialization = trim($_POST['specialization']);
  $fee = trim($_POST['fee']);
  $password = !empty($_POST['password'])
    ? $_POST['password'] 
    : $doctor['password'];

  // ✅ تحديث الصورة إن وُجدت
  $image = $doctor['image'];
  if (!empty($_FILES['image']['name'])) {
    $targetDir = "../img/doctors/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $isDefault = empty($doctor['image']) || $doctor['image'] === "img/doctors/default-doctor.png";
    $oldImagePath = "../" . $doctor['image'];

    // 🔹 لو الصورة قديمة ومش الافتراضية → استخدم نفس الاسم واحذف القديمة
    if (!$isDefault && file_exists($oldImagePath)) {
      $fileName = basename($doctor['image']);
      unlink($oldImagePath); // حذف القديمة
    } else {
      // 🔹 أول مرة يرفع فيها أو كانت الصورة الافتراضية → اسم جديد حسب user_id
      $fileExt = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
      $fileName = "doctor_" . $doctor_id . "." . $fileExt;
    }

    $targetFilePath = $targetDir . $fileName;
    $relativePath = "img/doctors/" . $fileName;

    // 🔹 رفع الصورة الجديدة بنفس الاسم المحدد
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
      $image = $relativePath;
    }
  }

  // ✅ تحديث جدول users
  $updateUser = $pdo->prepare("UPDATE users SET username=?, email=?, password=?, phone=?, image=? WHERE id=?");
  $updateUser->execute([$username, $email, $password, $phone, $image, $doctor_id]);

  // ✅ تحديث جدول doctors
  $updateDoctor = $pdo->prepare("UPDATE doctors SET specialization=?, fee=?, image=? WHERE user_id=?");
  $updateDoctor->execute([$specialization, $fee, $image, $doctor_id]);

  $_SESSION['message'] = "Profile updated successfully!";
  header("Location: doctor_profile.php");
  exit();
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Doctor Profile | Health Centers & Hospitals</title>
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
          <a href="doctor_dashboard.php" class="logo">
            <img src="../logo/logo.jpg" width="80" height="80" alt="logo" />
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="doctor_dashboard.php">Dashboard</a></li>
              <li><a href="about_us.php">About Us</a></li>
              <li><a href="doctor_appointments.php">My Appointments</a></li>
              <li><a href="doctor_patients.php">My Patients</a></li>
              <li><a href="doctor_profile.php" class="active">Profile</a></li>
              <li><a href="contact_us.php">Contact Us</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <!-- Main Section -->
        <main class="profile-section">
          <h1>Doctor Profile</h1>

          <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data" class="profile-form">
            <div class="profile-image">
              <?php
                // نتحقق من وجود الصورة فعليًا
                $doctorImagePath = (!empty($doctor['image']) && file_exists('../' . $doctor['image']))
                  ? '../' . $doctor['image']
                  : '../img/doctors/default-doctor.png';
              ?>

              <img src="<?= htmlspecialchars($doctorImagePath, ENT_QUOTES, 'UTF-8') ?>" alt="Doctor Image" class="profile-image">
              <input type="file" name="image" accept="image/*">
            </div>

            <div class="input-box">
              <label>Username</label>
              <input type="text" name="username" value="<?= htmlspecialchars($doctor['username'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="input-box">
              <label>Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($doctor['email'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="input-box">
              <label>Phone</label>
              <input type="text" name="phone" value="<?= htmlspecialchars($doctor['phone'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="input-box">
              <label>Specialization</label>
              <input type="text" name="specialization" value="<?= htmlspecialchars($doctor['specialization'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>


            <div class="input-box">
              <label>Consultation Fee</label>
              <input type="number" name="fee" step="0.01" min="0" value="<?= htmlspecialchars($doctor['fee'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="input-box password-box">
              <label>New Password (optional)</label>
              <div class="input-box password-box">
                <input type="password" id="loginPassword" name="password" placeholder="Enter Your New Password" />
                <span onclick="togglePassword('loginPassword', this)">
                  <i class="fa-solid fa-eye"></i>
                </span>
              </div>
            </div>


            <button type="submit" name="update" class="btn-update">Save Changes</button>
          </form>
        </main>

        <!-- Footer -->
        <footer>
          <p>&copy; 2025 Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>
    
    <script src="../js/transition.js"></script>
    <script src="../js/auth.js"></script>
  </body>
</html>