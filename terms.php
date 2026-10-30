<?php
session_start();
require_once 'config/config.php';

// ✅ لو المستخدم مسجل دخول فعلًا، نحوله للصفحة المناسبة حسب دوره (مستخدم، طبيب، مسؤول)
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'user') {
        header("Location: user_dashboard.php");
    } elseif ($_SESSION['role'] === 'doctor') {
        header("Location: doctor_dashboard.php");
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- character encoding -->
    <meta charset="UTF-8" />
    <!-- responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- SEO -->
    <title>Terms & Conditions | Health Management System</title>
    <meta name="description" content="Read the Terms and Conditions of using our Health Management System platform." />
    <meta name="author" content="Health Centers & Hospitals Platform" />

    <!-- favicon -->
    <link rel="icon" href="../logo/logo.jpg" type="image/x-icon" />

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
            <li><a href="About_Us.php">About Us</a></li>
            <li><a href="user_dashboard.php">Doctors</a></li>
            <li><a href="terms.php" class="active">Terms & Conditions</a></li>
            <li><a href="contact_us.php">Contact Us</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php" class="register">Register Now</a></li>
            <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
            <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
          </ul>
        </nav>
      </header>
        <!-- header end -->

        <!-- Terms & Conditions content -->
        <section class="about-section">
          <div class="about-text">
            <h1>Terms & Conditions</h1><br>
            <p>
              Welcome to <strong>Health Centers & Hospitals Platform</strong>.  
              By accessing or using our services, you agree to comply with the following Terms and Conditions.  
              Please read them carefully before using the website or booking any healthcare service.
            </p>

            <h3>1. Acceptance of Terms</h3>
            <p>
              By registering, logging in, or using any part of the platform, you confirm that you have read and agree to these Terms and Conditions.
            </p>

            <h3>2. User Responsibilities</h3>
            <ul>
              <li><i class="fa-solid fa-check" style="color: #28be97;"></i> You agree to provide accurate personal and medical information.</li>
              <li><i class="fa-solid fa-check" style="color: #28be97;"></i> You are responsible for maintaining the confidentiality of your login credentials.</li>
              <li><i class="fa-solid fa-check" style="color: #28be97;"></i> Misuse of the platform or providing false data may result in account suspension.</li>
            </ul>

            <h3><br>3. Appointment and Payment Policy</h3>
            <p>
              All appointments are subject to doctor availability.  
              Payments made through the platform are processed securely via third-party gateways.  
              Refunds, if applicable, will follow the center’s refund policy.
            </p>

            <h3>4. Data Privacy</h3>
            <p>
              We take your privacy seriously.  
              All user data, including medical and payment information, is encrypted and stored securely.  
              We do not share your data with third parties without consent, except as required by law.
            </p>

            <h3>5. Limitation of Liability</h3>
            <p>
              The platform acts as a facilitator between patients and healthcare providers.  
              We are not liable for any medical decisions, treatment outcomes, or communication errors between users and doctors.
            </p>

            <h3>6. Modifications</h3>
            <p>
              We reserve the right to modify or update these terms at any time.  
              Continued use of the platform after any changes constitutes your acceptance of the new Terms.
            </p>

            <h3>7. Contact Us</h3>
            <p>
              For questions or complaints regarding these Terms and Conditions,  
              please contact us via our <a href="contact_us.php" style="color:#28be97;">Contact Page</a>.
            </p>
          </div>

          <div class="about-img">
            <img src="../img/icons/terms-health.png" alt="Terms and Conditions Image" />
          </div>
        </section>

        <!-- footer -->
        <footer>
          <p>&copy; <time datetime="2025">2025</time> Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <script src="../js/transition.js"></script>
  </body>
</html>