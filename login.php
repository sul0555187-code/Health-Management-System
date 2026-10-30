<?php
session_start();
require 'config/config.php';

// ✅ لو المستخدم مسجل دخول فعلاً، نحوله حسب نوعه
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'user') {
        header("Location: user_dashboard.php");
    } elseif ($_SESSION['role'] === 'doctor') {
        header("Location: doctor_dashboard.php");
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    }
      elseif ($_SESSION['role'] === 'receptionist') {
        header("Location: receptionist_dashboard.php");
    }
    exit();
}

// تشغيل عرض الأخطاء أثناء التطوير
error_reporting(E_ALL);
ini_set('display_errors', 1);

// عند إرسال الفورم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // التحقق من وجود المستخدم سواء بالاسم أو البريد
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE username = :username OR email = :email 
        LIMIT 1
    ");
    $stmt->execute([
        'username' => $username,
        'email' => $username
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($user) {
        if ($password === $user['password']) { 
            // حفظ بيانات الجلسة
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // ✅ التوجيه حسب نوع المستخدم
            if ($user['role'] === 'user') {
                header("Location: user_dashboard.php");
            } elseif ($user['role'] === 'doctor') {
                header("Location: doctor_dashboard.php");
            } elseif ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] === 'receptionist') {
                header("Location: receptionist_dashboard.php");                
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "❌ Incorrect Password";
        }
    } else {
        $error = "⚠️ User not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en"> 
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | Health Management System</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/styles.css" />
  </head>
  <body>
    <div class="home-section">
    <div class="effects">
      <div class="bg-effect"></div>
    </div>
    <div class="container">
      <!-- header -->
      <header class="header">
        <a href="index.php" class="logo" aria-label="Health Management Logo">
          <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
        </a>
        <nav class="links" aria-label="Main Navigation">
          <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
          <ul class="nav-bar">
            <li><a href="index.php">Home</a></li>
            <li><a href="about_us.php">About Us</a></li>
            <li><a href="doctors.php">Doctors</a></li>
            <li><a href="user_appointments.php">My Appointments</a></li>
            <li><a href="contact_us.php">Contact Us</a></li>
            <li><a href="login.php" class="active">Login</a></li>
            <li><a href="register.php" class="register">Register Now</a></li>
            <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
            <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
          </ul>
        </nav>
      </header>

        <div class="wrapper">
          <h1>Login</h1>

          <?php if (!empty($error)): ?>
            <p style="color:red; font-weight:500; margin-bottom:10px;"><?= htmlspecialchars($error) ?></p>
          <?php endif; ?>

          <form action="login.php" method="POST">
            <div class="input-box">
              <input type="text" name="username" placeholder="Enter Username or Email" required />
              <i class="fa-solid fa-user"></i>
            </div>

            <div class="input-box">
              <input type="password" id="loginPassword" name="password" placeholder="Enter Your Password" required autocomplete="current-password" />
              <span onclick="togglePassword('loginPassword', this)">
                <i class="fa-solid fa-eye"></i>
              </span>
            </div>

            <div class="remember-forgot">
              <label><input type="checkbox" /> Remember me</label>
              <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <button type="submit" class="btn">Login</button>

            <div class="register-link">
              <p>Don’t have an account? <a href="register.php">Register Now</a></p>
            </div>
          </form>
        </div>

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