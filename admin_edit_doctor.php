<?php
session_start();
require 'config/config.php';
require 'config/admin_data.php';

// استلام doctor id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب بيانات المستخدم من جدول users
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=? AND role='doctor'");
$stmt->execute([$id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doctor) die("Doctor not found.");

// جلب بيانات الطبيب من جدول doctors
$stmt2 = $pdo->prepare("SELECT * FROM doctors WHERE user_id=?");
$stmt2->execute([$id]);
$doc = $stmt2->fetch(PDO::FETCH_ASSOC);

// عند تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username       = trim($_POST['username']);
  $email          = trim($_POST['email']);
  $phone_raw      = trim($_POST['phone']);
  $country_code   = $_POST['country_code'] ?? '+20';
  $phone          = $country_code . ' ' . $phone_raw;
  $address        = trim($_POST['address']);
  $dob            = $_POST['dob'] ?? null;
  $gender         = $_POST['gender'] ?? null;
  $specialization = trim($_POST['specialization'] ?? '');
  $fee            = trim($_POST['fee'] ?? '');

  if (empty($username) || empty($email) || empty($specialization) || $fee === '') {
    $error = "⚠️ Please fill all required fields.";
  } else {
    // تحديث جدول users
    $pdo->prepare("UPDATE users SET username=?, email=?, phone=?, address=?, dob=?, gender=? WHERE id=? AND role='doctor'")
        ->execute([$username, $email, $phone, $address, $dob, $gender, $id]);

    // رفع الصورة الجديدة وحذف القديمة
    $imagePath = $doctor['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $folder = dirname(__DIR__) . "/img/doctors/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        // حذف الصورة القديمة
        if (!empty($doctor['image']) 
            && $doctor['image'] !== "img/doctors/default-doctor.png"
            && file_exists("../" . $doctor['image'])) 
        {
            unlink("../" . $doctor['image']);
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $safeExt = preg_match('/^(jpg|jpeg|png|gif)$/', $ext) ? $ext : 'png';
        $fileName = "doctor_{$id}." . $safeExt;
        $target = $folder . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = "img/doctors/" . $fileName;
            $pdo->prepare("UPDATE users SET image=? WHERE id=?")->execute([$imagePath, $id]);
        }
    }

    // تحديث جدول doctors
    $pdo->prepare("UPDATE doctors SET name=?, specialization=?, image=?, fee=? WHERE user_id=?")
        ->execute(["Dr. $username", $specialization, $imagePath, $fee, $id]);

    $_SESSION['message'] = "✅ Doctor updated successfully!";
    header("Location: admin_doctors.php");
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor | Health Centers & Hospitals</title>
    <link rel="icon" href="../logo/logo.jpg">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        <!-- Edit Doctor Form -->
        <main>
          <div class="register-wrapper">
            <h1>Edit Doctor</h1><br>
            <?php if (!empty($error)) echo "<p class='alert error'>$error</p>"; ?>
            <?php if (!empty($_SESSION['message'])) { echo "<p class='alert success'>".$_SESSION['message']."</p>"; unset($_SESSION['message']); } ?>
            
            <form action="admin_edit_doctor.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">

              <!-- Profile Image -->
              <div class="input-box profile-upload-box">
                <div class="profile-preview">
                  <?php
                  $imageSrc = (!empty($doctor['image']) && file_exists("../" . $doctor['image'])) 
                              ? "../" . $doctor['image'] 
                              : "../img/doctors/default-doctor.png";
                  ?>
                  <img id="previewImg" src="<?= $imageSrc ?>" alt="Doctor Image" />
                </div>
                <input type="file" name="image" accept="image/*" onchange="previewImage(event)" />
              </div>

              <div class="register-form-row">
                <div class="input-box">
                  <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($doctor['username']) ?>" required />
                  <i class="fa-solid fa-user"></i>
                </div>
                <div class="input-box">
                  <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($doctor['email']) ?>" required />
                  <i class="fa-solid fa-envelope"></i>
                </div>
              </div>

              <div class="input-box">
                <input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($doctor['address']) ?>" />
                <i class="fa-solid fa-location-dot"></i>
              </div>

              <div class="input-box phone-box">
                <div class="phone-field">
                  <?php
                    $phoneParts = explode(' ', $doctor['phone'] ?? '', 2);
                    $country_code = $phoneParts[0] ?? '+20';
                    $phone_number = $phoneParts[1] ?? $phoneParts[0] ?? '';
                  ?>
                  <select name="country_code">
                  <?php
                    $codes = ['+966'=>'🇸🇦 +966','+20'=>'🇪🇬 +20','+971'=>'🇦🇪 +971','+962'=>'🇯🇴 +962','+212'=>'🇲🇦 +212'];
                    foreach($codes as $k=>$v) {
                        echo "<option value='$k' ".($k==$country_code?'selected':'').">$v</option>";
                    }
                  ?>
                  </select>
                  <input type="text" name="phone" value="<?= htmlspecialchars($phone_number) ?>" required />
                  <i class="fa-solid fa-phone"></i>
                </div>
              </div>

              <div class="register-form-row">
                <div class="input-box">
                  <input type="date" name="dob" value="<?= htmlspecialchars($doctor['dob']) ?>" />
                  <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div class="input-box select-box">
                  <select name="gender">
                    <option value="male" <?= ($doctor['gender']=='male')?'selected':'' ?>>Male</option>
                    <option value="female" <?= ($doctor['gender']=='female')?'selected':'' ?>>Female</option>
                  </select>
                  <i class="fa-solid fa-venus-mars"></i>
                </div>
              </div>

              <div id="doctorFields">
                <div class="input-box">
                  <input type="text" name="specialization" placeholder="Specialization" value="<?= htmlspecialchars($doc['specialization'] ?? '') ?>" required />
                  <i class="fa-solid fa-stethoscope"></i>
                </div>
                <div class="input-box">
                  <input type="number" name="fee" placeholder="Consultation Fee (SAR)" min="0" value="<?= htmlspecialchars($doc['fee'] ?? '') ?>" required />
                  <i class="fa-solid fa-money-bill"></i>
                </div>
              </div>

              <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            </form>
          </div>
        </main>

        <footer>
          <p>&copy; 2025 Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>
    
    <script src="../js/validation.js"></script>
    <script src="../js/transition.js"></script>
    <script src="../js/preview_img.js"></script>
  </body>
</html>