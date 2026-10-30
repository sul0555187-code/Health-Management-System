<?php
session_start();
require 'config/config.php';
require 'config/user_data.php';

if (!$user) {
  die("User not found in database.");
}

// ✅ تحديث بيانات المستخدم
if (isset($_POST['update'])) {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $address = trim($_POST['address']);
  $password = !empty($_POST['password']) ? $_POST['password'] : $user['password'];

// ✅ تحديث الصورة إن وُجدت
$image = $user['image'];
if (!empty($_FILES['image']['name'])) {
  $targetDir = "../img/users/";
  if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

  // 🔹 حذف الصورة القديمة إن وُجدت
  $oldImagePath = "../" . $user['image'];
  if (!empty($user['image']) && file_exists($oldImagePath) && $user['image'] !== "img/users/default-user.png") {
    unlink($oldImagePath);
  }

  // 🔹 تحديد امتداد الصورة الجديد (jpg / png / jpeg)
  $fileExt = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

  // 🔹 اسم الصورة = user + رقم المستخدم + الامتداد
  $fileName = "user_" . $user_id . "." . $fileExt;
  $targetFilePath = $targetDir . $fileName;

  // 🔹 رفع الصورة الجديدة
  if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
    $image = "img/users/" . $fileName;
  }
}

  // ✅ تحديث بيانات المستخدم
  $updateUser = $pdo->prepare("UPDATE users SET username=?, email=?, password=?, address=?, phone=?, image=? WHERE id=?");
  $updateUser->execute([$username, $email, $password, $address, $phone, $image, $user_id]);

  $_SESSION['user_name'] = $username;
  $_SESSION['message'] = "Profile updated successfully!";
  header("Location: user_profile.php");
  exit();
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Profile | Health Centers & Hospitals</title>
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
          <a href="index.php" class="logo" aria-label="Health Management Logo">
            <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="user_notifications.php">🔔 Notifications (<?= $notifCount ?>)</a></li>
              <li><a href="user_dashboard.php">Dashboard</a></li>
              <li><a href="doctors.php">Doctors</a></li>
              <li><a href="user_profile.php" class="active">Profile</a></li>
              <li><a href="user_appointments.php">My Appointments</a></li>
              <li><a href="contact_us.php">Contact Us</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <!-- Main Section -->
        <main class="profile-section">
          <h1>User Profile</h1>

          <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data" class="profile-form">
            <div class="profile-image"> 
              <?php
                // تحديد مسار الصورة
                $userImagePath = !empty($user['image']) && file_exists('../' . $user['image'])
                  ? '../' . $user['image']
                  : '../img/users/default-user.png';
              ?>
              
              <img src="<?= htmlspecialchars($userImagePath, ENT_QUOTES, 'UTF-8') ?>" alt="User Image" class="profile-image1">
              <input type="file" name="image" accept="image/*">
            </div>


            <div class="input-box">
              <label>Username</label>
              <input type="text" name="username" value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="input-box">
              <label>Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="input-box">
              <label>Address</label>
              <input type="text" name="address" value="<?= htmlspecialchars($user['address'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="input-box">
              <label>Phone</label>
              <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="input-box password-box">
              <label>New Password (optional)</label>
              <div class="input-box password-box">
                <input type="password" id="loginPassword" name="password" placeholder="Enter New Password" />
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
          <p>&copy; <time datetime="2025">2025</time> Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <script src="../js/transition.js"></script>
    <script src="../js/auth.js"></script>
  </body>
</html>