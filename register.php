<?php
session_start();
require_once 'config/config.php';

// ✅ لو المستخدم مسجل دخول فعلًا نحوله حسب دوره
if (isset($_SESSION['user_id'])) {
  if ($_SESSION['role'] === 'user') header("Location: user_dashboard.php");
  elseif ($_SESSION['role'] === 'doctor') header("Location: doctor_dashboard.php");
  elseif ($_SESSION['role'] === 'admin') header("Location: admin_dashboard.php");
  exit();
}

// ✅ عند إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm = $_POST['confirm_password'];
  $address = trim($_POST['address']);
  $phone = trim($_POST['country_code']) . ' ' . trim($_POST['phone']);
  $dob = $_POST['dob'];
  $gender = $_POST['gender'];
  $role = $_POST['role'] ?? 'user';

  $specialization = $_POST['specialization'] ?? null;
  $fee = $_POST['fee'] ?? null;

  // ✅ تحقق من البيانات
  if (empty($username) || empty($email) || empty($password) || empty($confirm) || empty($address) || empty($phone) || empty($dob) || empty($gender)) {
  $message = "<p class='alert error'>⚠️ Please fill all required fields.</p>";
  } elseif ($password !== $confirm) {
    $message = "<p class='alert error'>⚠️ Passwords do not match.</p>";
  } elseif (!preg_match('/^[0-9]{8,15}$/', str_replace(' ', '', substr($phone, strpos($phone, ' ') + 1)))) {
  // ✅ تحقق احتياطي في السيرفر لو JavaScript مش شغالة
  $message = "<p class='alert error'>⚠️ Invalid phone number format.</p>";

  } elseif ($role === 'doctor' && (empty($specialization) || empty($fee))) {
    $message = "<p class='alert error'>⚠️ Please enter specialization and fee for doctor registration.</p>";
  } else {
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;

    if ($age < 16) {
      $message = "<p class='alert error'>⚠️ You must be at least 16 years old to register.</p>";
    } else {
      // ✅ تحقق من البريد الإلكتروني
      $check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
      $check->execute([$email]);
      if ($check->rowCount() > 0) {
        $message = "<p class='alert error'>⚠️ This email is already registered.</p>";
      } else {
        // ✅ إضافة المستخدم إلى جدول users
        // ✅ إضافة المستخدم إلى جدول users
        $insert = $pdo->prepare("
          INSERT INTO users (username, email, password, address, phone, dob, gender, role)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->execute([$username, $email, $password, $address, $phone, $dob, $gender, $role]);

        $user_id = $pdo->lastInsertId();

        // ✅ تحديد مجلد الصور والافتراضي حسب الدور
        if ($role === 'doctor') {
          $targetDir = "../img/doctors/";
          $relativeDir = "img/doctors/";
          $defaultImg = "img/doctors/default-doctor.png";
        } else {
          $targetDir = "../img/users/";
          $relativeDir = "img/users/";
          $defaultImg = "img/users/default-user.png";
        }

        $imagePath = $defaultImg;

        // ✅ رفع الصورة إن وجدت
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        // تحديد المجلد حسب الدور
        $folderName = ($role === 'doctor') ? 'doctors' : 'users';
        $targetDir = dirname(__DIR__) . "/img/$folderName/"; // ✅ يصحح المسار مهما كان مكان الملف

        // إنشاء المجلد إذا مش موجود
        if (!is_dir($targetDir)) {
          mkdir($targetDir, 0777, true);
        }

        // تجهيز الاسم والمسار
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $fileName = "{$role}_{$user_id}." . $extension;
        $targetPath = $targetDir . $fileName;
        $dbPath = "img/$folderName/$fileName";

        // رفع الملف فعلاً
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
          $imagePath = $dbPath;
        } else {
          error_log("❌ Failed to move uploaded file for $role (path: $targetPath)");
        }
      }

        // ✅ تحديث جدول users بالصورة (حتى لو افتراضية)
        $update = $pdo->prepare("UPDATE users SET image = ? WHERE id = ?");
        $update->execute([$imagePath, $user_id]);

        // ✅ لو دكتور → نحفظ بياناته في جدول doctors
        if ($role === 'doctor') {
          $doctorName = "Dr. " . $username;
          $insertDoctor = $pdo->prepare("
            INSERT INTO doctors (name, specialization, image, user_id, fee)
            VALUES (?, ?, ?, ?, ?)
          ");
          $insertDoctor->execute([$doctorName, $specialization, $imagePath, $user_id, $fee]);
        }

        $message = "<p class='alert success'>✅ Registration successful! You can now <a href='login.php'>login</a>.</p>";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Register | Health Centers & Hospitals</title>
  <meta name="description" content="Create an account to book appointments and access healthcare services online." />
  <meta name="author" content="Health Centers & Hospitals Platform" />

  <!-- Favicon -->
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
            <li><a href="about_us.php">About Us</a></li>
            <li><a href="doctors.php">Doctors</a></li>
            <li><a href="user_appointments.php">Appointments</a></li>
            <li><a href="contact_us.php">Contact Us</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php" class="register">Register</a></li>
            <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
            <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
          </ul>
        </nav>
      </header>

      <!-- register form -->
      <main>
        <div class="register-wrapper">
          <h1>Create New Account</h1>

          <!-- ✅ عرض رسالة الخطأ أو النجاح -->
          <?php if (isset($message)) echo $message; ?>

          <form action="register.php" method="POST" enctype="multipart/form-data">

            <!-- Profile Image -->
            <div class="input-box profile-upload-box">
              <div class="profile-preview">
                <img id="previewImg" src="../img/users/default-user.png">
              </div>
              <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(event)" />
            </div>

            <!-- Username + Email -->
            <div class="register-form-row">
              <div class="input-box">
                <input type="text" name="username" placeholder="Username" autocomplete="off" required />
                <i class="fa-solid fa-user"></i>
              </div>
              <div class="input-box">
                <input type="email" name="email" placeholder="Email Address" required />
                <i class="fa-solid fa-envelope"></i>
              </div>
            </div>

            <!-- Password -->
            <div class="input-box">
              <input type="password" id="password" name="password" placeholder="Password" required />
              <span class="toggle-password" onclick="togglePassword('password', this)">
                <i class="fa-solid fa-eye"></i>
              </span>
            </div>

            <!-- Confirm Password -->
            <div class="input-box">
              <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required />
              <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
                <i class="fa-solid fa-eye"></i>
              </span>
            </div>

            <!-- Address -->
            <div class="input-box">
              <input type="text" name="address" placeholder="Address" required />
              <i class="fa-solid fa-location-dot"></i>
            </div>

            <!-- Phone with Country Code -->
            <div class="input-box phone-box">
              <div class="phone-field">
                <select id="countryCode" name="country_code" required>
                  <option value="+966" selected>🇸🇦 +966 (Saudi Arabia)</option>
                  <option value="+20">🇪🇬 +20 (Egypt)</option>
                  <option value="+971">🇦🇪 +971 (UAE)</option>
                  <option value="+962">🇯🇴 +962 (Jordan)</option>
                  <option value="+212">🇲🇦 +212 (Morocco)</option>
                </select>
                <input type="text" id="phone" name="phone" placeholder="Phone Number" required />
                <i class="fa-solid fa-phone"></i>
              </div>
              <small class="phone-hint">Phone number must be 8–15 digits.</small>
            </div>

            <!-- Date of Birth + Gender -->
            <div class="register-form-row">
              <div class="input-box">
                <input type="date" name="dob" required />
                <i class="fa-solid fa-calendar-days"></i>
              </div>

              <div class="input-box select-box">
                <select name="gender" required>
                  <option value="" disabled selected>Select Gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
                <i class="fa-solid fa-venus-mars"></i>
              </div>
            </div>

            <!-- 🩺 نوع الحساب -->
            <div class="input-box select-box">
              <select name="role" id="role" required onchange="toggleDoctorFields()">
                <option value="user" selected>Normal User</option>
                <option value="doctor">Doctor</option>
              </select>
              <i class="fa-solid fa-user-md"></i>
            </div>

            <!-- 🩺 حقول الأطباء (مخفية افتراضيًا) -->
            <div id="doctorFields" style="display:none;">
              <div class="input-box">
                <input type="text" name="specialization" placeholder="Specialization" />
                <i class="fa-solid fa-stethoscope"></i>
              </div>

              <div class="input-box">
                <input type="number" name="fee" placeholder="Consultation Fee (SAR)" min="0" />
                <i class="fa-solid fa-money-bill"></i>
              </div>
            </div>

            <div class="remember-forgot">
              <label>
                <input type="checkbox" required /> I agree to the 
                <a href="terms.php" target="_blank">Terms & Conditions</a>
              </label>
            </div>

            <button type="submit" class="btn">Register</button>

            <div class="register-link">
              <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
          </form>
        </div>
      </main>

      <!-- footer -->
      <footer>
        <p>&copy; <time datetime="2025">2025</time> Health Centers & Hospitals Platform. All rights reserved.</p>
        <p>&nbsp;</p>
      </footer>
    </div>
  </div>

  <script src="../js/register_validation.js"></script>

  <!-- Password Toggle Script -->
  <script src="../js/auth.js"></script>
  <script src="../js/transition.js"></script>
  <script src="../js/preview_img.js"></script>
  <script src="../js/doctor_register.js"></script>
</body>
</html>