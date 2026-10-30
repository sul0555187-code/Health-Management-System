<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Meta -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- SEO -->
    <title>Contact Us | Health Centers & Hospitals</title>
    <meta name="description" content="Get in touch with Health Centers & Hospitals Platform for inquiries or support." />

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
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
              <li><a href="about_us.php">About us</a></li>
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
              <li><a href="contact_us.php" class="active">Contact Us</a></li>

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

        <!-- Contact Section -->
        <main class="wrapper">
          <h1>Contact Us</h1>
          <p class="contact-intro">
            We’d love to hear from you! Please fill out the form below or use our contact details.
          </p>
          <?php if (isset($_SESSION['msg_success'])): ?>
            <p class="alert success"><?= $_SESSION['msg_success']; ?></p>
            <?php unset($_SESSION['msg_success']); ?>
          <?php elseif (isset($_SESSION['msg_error'])): ?>
            <p class="alert error"><?= $_SESSION['msg_error']; ?></p>
            <?php unset($_SESSION['msg_error']); ?>
          <?php endif; ?>

          <form action="send_message.php" method="POST">
            <div class="input-box">
              <input type="text" name="name" placeholder="Full Name" required />
              <i class="fa-solid fa-user"></i>
            </div>

            <div class="input-box">
              <input type="email" name="email" placeholder="Email Address" required />
              <i class="fa-solid fa-envelope"></i>
            </div>

            <div class="input-box">
              <input type="text" name="subject" placeholder="Subject" required />
              <i class="fa-solid fa-heading"></i>
            </div>

            <div class="input-box textarea-box">
              <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
              <i class="fa-solid fa-message"></i>
            </div>

            <button type="submit" class="btn">Send Message</button>
          </form>

          <!-- Contact Info -->
          <div class="contact-info">
            <p><i class="fa-solid fa-phone"></i> +966 50 123 4567</p>
            <p><i class="fa-solid fa-envelope"></i> support@healthplatform.com</p>
            <p><i class="fa-solid fa-location-dot"></i> Riyadh, Saudi Arabia</p>
          </div>
        </main>
          <!-- Contact Section End -->

        <footer>
          <p>&copy; 2025 Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <script src="../js/transition.js"></script>
  </body>
</html>