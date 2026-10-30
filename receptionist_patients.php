<?php
session_start();
require 'config/config.php';
require 'config/receptionist_data.php';

// إضافة / تعديل / حذف مريض
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // إضافة
  if (isset($_POST['add'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password'] ?: 'password');
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $dob = $_POST['dob'] ?: null;           // تاريخ الميلاد
    $gender = $_POST['gender'] ?: null;     // الجنس

    $stmt = $pdo->prepare("
        INSERT INTO users 
        (username, email, password, address, phone, dob, gender, role, created_at, image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'user', NOW(), 'img/users/default-user.png')
    ");
    $stmt->execute([$username, $email, $password, $address, $phone, $dob, $gender]);

    $_SESSION['message'] = "✅ Patient added.";
    header("Location: receptionist_patients.php");
    exit();
}

  // تعديل
  if (isset($_POST['edit'])) {
    $uid = (int)$_POST['patient_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $pdo->prepare("UPDATE users SET username=?, email=?, phone=?, address=? WHERE id=? AND role='user'")
        ->execute([$username, $email, $phone, $address, $uid]);
    $_SESSION['message'] = "✅ Patient updated.";
    header("Location: receptionist_patients.php"); exit();
  }
}

// جلب المرضى
$patients = $pdo->query("SELECT id, username, email, phone, address FROM users WHERE role='user' ORDER BY username DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Patients</title>
<link rel="stylesheet" href="../css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  .section-card {
  background: #fff;
  padding: 22px;
  border-radius: 10px;
  margin-bottom: 25px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.07);
}

.section-card h3 {
  margin-bottom: 15px;
  font-size: 21px;
  font-weight: 600;
}

.patient-form {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 15px;
}

.input-box {
  position: relative;
}

.input-box input {
  width: 100%;
  padding: 12px 42px 12px 12px;
  border: 1px solid #ccc;
  border-radius: 7px;
  font-size: 14px;
  background: #fff;
  transition: 0.25s;
}

.input-box input:focus {
  border-color: #1e90ff;
  box-shadow: 0 0 6px rgba(30,144,255,0.3);
}

.input-box i {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #666;
  font-size: 15px;
  pointer-events: none;
}

.btn {
  padding: 12px;
  font-size: 16px;
  border-radius: 7px;
  max-width: 160px;
  max-height: 45px;
}
.input-box select {
  width: 100%;
  padding: 12px 42px 12px 12px;
  border: 1px solid #ccc;
  border-radius: 7px;
  font-size: 14px;
  background: #fff;
  appearance: none; /* لإخفاء السهم الافتراضي */
}

.input-box select:focus {
  border-color: #1e90ff;
  box-shadow: 0 0 6px rgba(30,144,255,0.3);
}
</style>
</head>
<body>
  <div class="home-section">
    <div class="effects"><div class="bg-effect"></div></div>
    <div class="container">
      <!-- header start -->
      <header class="header">
        <a href="receptionist_dashboard.php" class="logo" aria-label="Health Management Logo">
          <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
        </a>
        <nav class="links" aria-label="Main Navigation">
          <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
          <ul class="nav-bar">
            <li><a href="receptionist_dashboard.php">Dashboard</a></li>
            <li><a href="receptionist_appointments.php">Manage Appointments</a></li>
            <li><a href="receptionist_today.php">Today's Appointments</a></li>
            <li><a href="receptionist_patients.php" class="active">Patients</a></li>
            <li><a href="receptionist_doctors_schedule.php">Doctors Schedule</a></li>
            <li><a href="logout.php" class="register">Logout</a></li>
            <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
          </ul>
        </nav>
      </header>

      <main class="dashboard">
        <h1>Add New Patient</h1>
        <?php if (!empty($_SESSION['message'])): ?><div class="alert success"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div><?php endif; ?>

        <!-- Add patient -->
        <section class="section-card">
          <form method="POST" class="patient-form">
            <div class="input-box">
              <input type="text" name="username" placeholder="Patient Name" required>
              <i class="fa-solid fa-user"></i>
            </div>

            <div class="input-box">
              <input type="email" name="email" placeholder="Email" required>
              <i class="fa-solid fa-envelope"></i>
            </div>

            <div class="input-box">
              <input type="password" name="password" placeholder="Password">
              <i class="fa-solid fa-lock"></i>
            </div>

            <div class="input-box">
              <input type="text" name="address" placeholder="Address">
              <i class="fa-solid fa-location-dot"></i>
            </div>

            <div class="input-box">
              <input type="text" name="phone" placeholder="Phone">
              <i class="fa-solid fa-phone"></i>
            </div>

            <div class="input-box">
              <input type="date" name="dob" placeholder="Date of Birth">
              <i class="fa-solid fa-calendar"></i>
            </div>

            <div class="input-box">
              <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
              <i class="fa-solid fa-venus-mars"></i>
            </div>

            <button type="submit" name="add" class="btn">Add Patient</button>
          </form>
        </section>

        <!-- Patients table -->
        <table class="appointments-table">
          <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach($patients as $i=>$p): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($p['username']) ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= htmlspecialchars($p['phone'] ?: '-') ?></td>
                <td><?= htmlspecialchars($p['address'] ?: '-') ?></td>
                <td>
                  <button class="btnn" onclick="toggleEdit(<?= $p['id'] ?>)"><i class="fa-solid fa-pen-to-square"></i></button>

                  <div id="edit-<?= $p['id'] ?>" style="display:none; margin-top:6px;">
                    <form method="POST">
                      <input type="hidden" name="patient_id" value="<?= $p['id'] ?>">
                      <input type="text" name="username" value="<?= htmlspecialchars($p['username']) ?>">
                      <input type="email" name="email" value="<?= htmlspecialchars($p['email']) ?>">
                      <input type="text" name="phone" value="<?= htmlspecialchars($p['phone']) ?>">
                      <input type="text" name="address" value="<?= htmlspecialchars($p['address']) ?>">
                      <button type="submit" name="edit" class="btnn btn-confirm">Save</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </main>

      <!-- Footer -->
        <footer>
          <p>&copy; <time datetime="2025">2025</time> Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>
    </div>
  </div>

<script>
function toggleEdit(id){
  const el=document.getElementById('edit-'+id);
  if(!el) return;
  el.style.display = el.style.display==='none' ? 'block' : 'none';
}
</script>


  <script src="../js/transition.js"></script>
</body>
</html>