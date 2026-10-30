<?php
session_start();

// لو المستخدم مسجل دخول
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'user':
            header("Location: user_dashboard.php");
            exit();
        case 'doctor':
            header("Location: doctor_dashboard.php");
            exit();
        case 'admin':
            header("Location: admin_dashboard.php");
            exit();
        default:
            // لو الدور غير معروف، ممكن نعيده للصفحة الرئيسية
            session_destroy();
            header("Location: login.php");
            exit();
    }
}

// لو مش مسجل دخول، الكود يوصل هنا ويفتح الصفحة عادي
?>


<!DOCTYPE html>                                                                     <!-- الصفحة مكتوبة بلغة HTML5-->
<html lang="en"> 
  <head>
    <meta charset="UTF-8" />                                                        <!--UTF-8، يدعم كل اللغات (بما فيها العربية)-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />                         <!--جعل المتصفح يستخدم أحدث وضع عرض-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />        <!--يجعل الصفحة متجاوبة (Responsive) على شاشات الموبايل والتابلت-->
    <title>Health Management System | Book Doctors & Appointments Online</title>

    <meta name="description" content="Book doctors, schedule appointments, and access healthcare services online." />
    <meta name="author" content="Health Centers & Hospitals Platform" />

    <!--أيقونة التبويب (favicon) التي تظهر بجانب اسم الموقع في المتصفح-->
    <link rel="icon" href="../img/healthService.png" type="image/x-icon" />         
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <!--يستورد مكتبة Font Awesome لاستخدام الأيقونات (زي أيقونة المستخدم، الهاتف--> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/styles.css" />
  </head>

  <body>
    <div class="home-section">
      <div class="effects"><div class="bg-effect"></div></div>
      <div class="container">
        
        <!-- header start -->
        <div class="header"> 
          <a href="index.php" class="logo" aria-label="Health Management Logo">
            <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
          </a>

          <div class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
              <ul class="nav-bar">
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="about_us.php">About Us</a></li>
                <li><a href="doctors.php">Doctors</a></li>
                <li><a href="user_appointments.php">Appointments</a></li>
                <li><a href="contact_us.php">Contact Us</a></li>

                <?php if (isset($_SESSION['user_id'])): ?> <!-- لو المستخدم مسجل دخول --> <!--Alternative Syntax-->
                  <li><a href="logout.php" class="register">Logout</a></li>
                <?php else: ?>
                  <li><a href="login.php">Login</a></li>
                  <li><a href="register.php" class="register">Register Now</a></li>
                <?php endif; ?>

              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </div>
        </div>

        <!-- main content -->
        <div class="index">
          <div class="index-text">
            <h1>
              Book Now!!
              <span>Best Healthcare services. Anytime, anywhere...</span>
            </h1>
            <p>
              Our Health Management System simplifies healthcare services by allowing patients to book medical appointments online.
              Browse doctors, select your preferred time and confirm your appointment. All in just a few clicks.
              Accessible, fast and efficient healthcare for everyone.
            </p>
            <button><a href="doctors.php">Book Appointment</a></button>
          </div>

          <div class="index-img">
            <div class="left-box"> 
              <div class="icon"><i class="fa-solid fa-user-doctor"></i></div>
              <div>
                <h3>100+</h3>
                <span>Specialized Doctors</span>
              </div>
            </div>
            <div class="img">
              <img src="../img/icons/healthService.png" alt="index-img" />
            </div>
            <div class="right-box">
              <p><i class="fa-solid fa-check"></i><span>Online Booking</span></p>
              <p><i class="fa-solid fa-check"></i><span>Expert Doctors</span></p>
              <p><i class="fa-solid fa-check"></i><span>Best Service</span></p>
            </div>
          </div>
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

<!--ALTER TABLE users AUTO_INCREMENT = 1;--> 