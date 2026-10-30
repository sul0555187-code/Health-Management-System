<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- character encoding -->
    <meta charset="UTF-8" />
    <!-- responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- SEO -->
    <title>About Us | Health Management System</title>
    <meta name="description" content="Learn more about our Health Management System platform, our mission, and how we provide healthcare services online." />
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
          <a href="index.php" class="logo">
            <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="index.php">Home</a></li>
              <li><a href="about_us.php" class="active">About us</a></li>
              <li><a href="doctors.php">Doctors</a></li>
              <?php
              $href = "";

              if (isset($_SESSION['role'])) {
                  if ($_SESSION['role'] == 'doctor') {
                      $href = "doctor_appointments.php";
                  } elseif ($_SESSION['role'] == 'user') {
                      $href = "user_appointments.php";
                  }
              }
              ?>
              <li><a href="<?= $href ?>">My Appointments</a></li>
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

        <!-- About us content -->
        <section class="about-section">
          <div class="about-text">
            <h1>About Us</h1><br>
            <p>
              Our <strong>Health Management System</strong> is designed to simplify and modernize healthcare services.  
              We connect patients with doctors and hospitals in an easy and efficient way, allowing online booking, appointment scheduling, and real-time healthcare access.  
            </p>
            <p>
              Our mission is to provide accessible healthcare for everyone, anywhere, anytime — with a strong focus on efficiency, trust, and patient satisfaction.
            </p>
            <ul>
              <li><i class="fa-solid fa-check" style="color: #28be97;"></i> 24/7 Online Appointment Booking</li>
              <li><i class="fa-solid fa-check" style="color: #28be97;"></i> Trusted and Verified Doctors</li>
              <li><i class="fa-solid fa-check" style="color: #28be97;"></i> Secure and Reliable Platform</li>
            </ul>
          </div>
          <div class="about-img">
            <img src="../img/icons/about-health.png" alt="About Health System" />
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