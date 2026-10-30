<?php
session_start();
require 'config/config.php';
require 'config/admin_data.php';

$message = "";

// قواعد أرقام الهاتف لكل دولة
$phoneRules = [
    "+966" => ["digits" => 9, "name" => "Saudi Arabia"],
    "+20"  => ["digits" => 10, "name" => "Egypt"],
    "+971" => ["digits" => 9, "name" => "UAE"],
    "+962" => ["digits" => 9, "name" => "Jordan"],
    "+212" => ["digits" => 9, "name" => "Morocco"]
];

// ✅ إضافة طبيب جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username       = trim($_POST['username']);
    $email          = trim($_POST['email']);
    $password       = trim($_POST['password']);
    $confirm        = trim($_POST['confirm_password']);
    $phone          = trim($_POST['phone']);
    $country_code   = $_POST['country_code'];
    $address        = trim($_POST['address']);
    $dob            = trim($_POST['dob']);
    $gender         = trim($_POST['gender']);
    $specialization = trim($_POST['specialization']);
    $fee            = trim($_POST['fee']);

    // ✅ التحقق من البيانات المطلوبة
    if (empty($username) || empty($email) || empty($password) || empty($confirm) ||
        empty($phone) || empty($address) || empty($dob) || empty($gender) ||
        empty($specialization) || empty($fee)) {
        $message = "<p class='alert error'>⚠️ Please fill all required fields.</p>";
    }
    elseif ($password !== $confirm) {
        $message = "<p class='alert error'>⚠️ Passwords do not match.</p>";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p class='alert error'>⚠️ Invalid email format.</p>";
    }
    else {
        // ✅ التحقق من العمر ≥16 سنة
        $birthDate = new DateTime($dob);
        $today     = new DateTime();
        $age       = $today->diff($birthDate)->y;

        if ($age < 16) {
            $message = "<p class='alert error'>⚠️ Doctor must be at least 16 years old.</p>";
        }
        else {
            // ✅ التحقق من رقم الهاتف حسب الدولة
            $requiredDigits = $phoneRules[$country_code]["digits"] ?? 8;
            if (!preg_match('/^[0-9]{'.$requiredDigits.'}$/', $phone)) {
                $message = "<p class='alert error'>⚠️ Phone number for ".$phoneRules[$country_code]["name"]." must be $requiredDigits digits.</p>";
            }
            else {
                // ✅ التحقق من البريد الإلكتروني المكرر
                $check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $check->execute([$email]);
                if ($check->rowCount() > 0) {
                    $message = "<p class='alert error'>⚠️ This email is already registered.</p>";
                }
                else {
                    // ✅ الصورة الافتراضية
                    $imagePath = "img/doctors/default-doctor.png";

                    // ✅ إدراج المستخدم أولاً في جدول users بالصورة الافتراضية
                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, email, password, address, phone, dob, gender, role, created_at, image)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'doctor', NOW(), ?)
                    ");
                    $stmt->execute([$username, $email, $password, $address, $phone, $dob, $gender, $imagePath]);
                    $user_id = $pdo->lastInsertId();

                    // ✅ لو فيه صورة مرفوعة، ارفعها بعد معرفة $user_id
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $fileName = "doctor_{$user_id}." . $ext;

                    // مسار مطلق على السيرفر
                    $targetDir = __DIR__ . "/../img/doctors/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

                    $targetFile = $targetDir . $fileName;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        // مسار النسبي لحفظه في قاعدة البيانات
                        $imagePath = "img/doctors/" . $fileName;

                        // تحديث جدول users
                        $updateUser = $pdo->prepare("UPDATE users SET image=? WHERE id=?");
                        $updateUser->execute([$imagePath, $user_id]);
                    } else {
                        error_log("❌ Failed to move uploaded file to $targetFile");
                    }
                }


                    // ✅ إدراج بيانات الطبيب في جدول doctors
                    $doctorName = "Dr. " . $username;
                    $stmt2 = $pdo->prepare("
                        INSERT INTO doctors (name, specialization, image, user_id, fee)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt2->execute([$doctorName, $specialization, $imagePath, $user_id, $fee]);

                    $_SESSION['message'] = "✅ Doctor added successfully!";
                    header("Location: admin_doctors.php");
                    exit();
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Doctor | Health Centers & Hospitals</title>
    <link rel="icon" href="../logo/logo.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
  </head>

  <body>
    <div class="home-section">
      <div class="effects"><div class="bg-effect"></div></div>
      <div class="container">

        <!-- Header -->
        <header class="header">
          <a href="admin_dashboard.php" class="logo" aria-label="Health Management Logo">
            <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="admin_dashboard.php">Dashboard</a></li>
              <li><a href="admin_doctors.php" class="active">Doctors</a></li>
              <li><a href="admin_users.php">Patients</a></li>
              <li><a href="admin_appointments.php">Appointments</a></li>
              <li><a href="admin_messages.php">Messages</a></li>
              <li><a href="admin_profile.php">Profile</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <main>
          <div class="register-wrapper">
            <h1>Add New Doctor</h1><br>
            <?php if (!empty($message)) echo $message; ?>
            <?php if (!empty($_SESSION['message'])) { echo "<p class='alert success'>".$_SESSION['message']."</p>"; unset($_SESSION['message']); } ?>

            <form action="admin_add_doctor.php" method="POST" enctype="multipart/form-data">

              <!-- Profile Image -->
              <div class="input-box profile-upload-box">
                <div class="profile-preview">
                  <img id="previewImg" src="../img/doctors/default-doctor.png" alt="preview">
                </div>
                <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(event)" />
              </div>

              <div class="register-form-row">
                <div class="input-box">
                  <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required />
                  <i class="fa-solid fa-user"></i>
                </div>
                <div class="input-box">
                  <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
                  <i class="fa-solid fa-envelope"></i>
                </div>
              </div>

              <div class="input-box">
                <input type="password" id="password" name="password" placeholder="Password" required />
                <span class="toggle-password" onclick="togglePassword('password', this)"><i class="fa-solid fa-eye"></i></span>
              </div>

              <div class="input-box">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required />
                <span class="toggle-password" onclick="togglePassword('confirm_password', this)"><i class="fa-solid fa-eye"></i></span>
              </div>

              <div class="input-box">
                <input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required />
                <i class="fa-solid fa-location-dot"></i>
              </div>

              <div class="input-box phone-box">
                <div class="phone-field">
                  <select id="countryCode" name="country_code" required>
                    <option value="+966" selected>🇸🇦 +966 (Saudi Arabia)</option>
                    <option value="+20">🇪🇬 +20 (Egypt)</option>
                    <option value="+971">🇦🇪 +971 (UAE)</option>
                    <option value="+962">🇯🇴 +962 (Jordan)</option>
                    <option value="+212">🇲🇦 +212 (Morocco)</option>
                  </select>
                  <input type="text" id="phone" name="phone" placeholder="Phone Number" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required />
                  <i class="fa-solid fa-phone"></i>
                </div>
                <small class="phone-hint">Phone number must be correct per country.</small>
              </div>

              <div class="register-form-row">
                <div class="input-box">
                  <input type="date" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required />
                  <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div class="input-box select-box">
                  <select name="gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="male" <?= (($_POST['gender'] ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= (($_POST['gender'] ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
                  </select>
                  <i class="fa-solid fa-venus-mars"></i>
                </div>
              </div>

              <div id="doctorFields">
                <div class="input-box">
                  <input type="text" name="specialization" placeholder="Specialization" value="<?= htmlspecialchars($_POST['specialization'] ?? '') ?>" required />
                  <i class="fa-solid fa-stethoscope"></i>
                </div>
                <div class="input-box">
                  <input type="number" name="fee" placeholder="Consultation Fee (SAR)" min="0" value="<?= htmlspecialchars($_POST['fee'] ?? '') ?>" required />
                  <i class="fa-solid fa-money-bill"></i>
                </div>
              </div>

              <button type="submit" class="btn">Add Doctor</button>
            </form>
          </div>
        </main>

        <footer>
          <p>&copy; <time datetime="2025">2025</time> Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <script src="../js/transition.js"></script>
    <script src="../js/preview_img.js"></script>
    <script src="../js/validation.js"></script>
  </body>
</html>