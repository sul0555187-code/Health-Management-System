<?php
session_start();
require 'config/config.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        // التحقق من وجود البريد في قاعدة البيانات
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            // هنا ممكن تضيف الكود الفعلي لإرسال رابط إعادة التعيين بالبريد
            $message = "<p class='message success'>✅ A password reset link has been sent to <strong>$email</strong></p>";
        } else {
            $message = "<p class='message error'>❌ This email address is not registered in our system!</p>";
        }
    } else {
        $message = "<p class='message error'>❌ Please enter a valid email address.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- meta -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password | Health Management System</title>
    <meta name="description" content="Reset your password easily by entering your registered email address." />
    <meta name="author" content="Health Centers & Hospitals Platform" />

    <!-- favicon -->
    <link rel="icon" href="../logo/logo.jpg" type="image/x-icon" />

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- CSS -->
    <link rel="stylesheet" href="../css/styles.css" />

    <style>
      input[ type="email"] {width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 20px; font-size: 16px; }
      button { background-color: #28be97; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease; }
      button:hover { background-color: #24a985; }
      .message { margin-top: 15px; font-weight: 500; }
      .message.success { color: #2e9b66; }
      .message.error { color: #e04e4e; }
    </style>
  </head>

  <body>
    <div class="home-section">
      <div class="effects"><div class="bg-effect"></div></div>
      <div class="container">

        <!-- header start -->
        <header class="header">
          <a href="index.php" class="logo">
            <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="index.php">Home</a></li>
              <li><a href="about_us.php">About Us</a></li>
              <li><a href="doctors.php">Doctors</a></li>
              <li><a href="forgot_password.php" class="active">Forgot Password</a></li>
              <li><a href="contact_us.php">Contact Us</a></li>
              <li><a href="login.php">Login</a></li>
              <li><a href="register.php" class="register">Register Now</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>
        <!-- header end -->

        <!-- Forgot Password Form -->
        <div class="form-container">
          <h1>Forgot Password</h1><br>
          <p>Please enter your registered email address below.  
             We’ll send you a link to reset your password.</p>

          <form method="POST" action="">
            <input type="email" name="email" placeholder="Enter your email address" required />
            <button type="submit"><i class="fa-solid fa-paper-plane"></i> Send Reset Link</button>
          </form>

          <?php if (!empty($message)) echo $message; ?>
        </div>

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