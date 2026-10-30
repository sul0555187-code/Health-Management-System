<?php
session_start();
require 'config/config.php';
require 'config/receptionist_data.php';

// معالجة إنشاء/تعديل/حذف وتغيير حالة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // إنشاء حجز جديد
  if (isset($_POST['create'])) {
    $user_id = (int)$_POST['user_id'];
    $doctor_id = (int)$_POST['doctor_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $notes = trim($_POST['notes']);
    $fee = floatval($_POST['fee'] ?? 0);

    $stmt = $pdo->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, notes, fee) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $doctor_id, $date, $time, $notes, $fee]);
    $_SESSION['message'] = "✅ Appointment created.";
    header("Location: receptionist_appointments.php");
    exit();
  }

  // تغيير الحالة
  if (isset($_POST['change_status'])) {
    $aid = (int)$_POST['appointment_id'];
    $status = trim($_POST['status']);
    $allowed = ['Pending','Confirmed','Cancelled','In-Progress','Completed'];
    if (in_array($status, $allowed)) {
      // لا نسمح بتعديل In-Progress أو Completed إلى شيء آخر إلا Cancelled (اختياري) — هنا نسمح بتغيير إذا الحالة الحالية ليست In-Progress/Completed
      $cur = $pdo->prepare("SELECT status FROM appointments WHERE id=?");
      $cur->execute([$aid]);
      $c = $cur->fetchColumn();
      if ($c !== 'In-Progress' && $c !== 'Completed') {
        $pdo->prepare("UPDATE appointments SET status=? WHERE id=?")->execute([$status, $aid]);
        $_SESSION['message'] = "✅ Status updated.";
      } else {
        $_SESSION['message'] = "⚠️ Cannot modify In-Progress/Completed appointments.";
      }
    }
    header("Location: receptionist_appointments.php");
    exit();
  }

  // حذف
  if (isset($_POST['delete'])) {
    $aid = (int)$_POST['delete_id'];
    $pdo->prepare("DELETE FROM appointments WHERE id=?")->execute([$aid]);
    $_SESSION['message'] = "🗑️ Appointment deleted.";
    header("Location: receptionist_appointments.php");
    exit();
  }

  // تعديل (بسيط — تغيّر التاريخ/الوقت/doctor/user/notes)
  if (isset($_POST['edit'])) {
    $aid = (int)$_POST['appointment_id'];
    $user_id = (int)$_POST['user_id'];
    $doctor_id = (int)$_POST['doctor_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $notes = trim($_POST['notes']);
    $pdo->prepare("UPDATE appointments SET user_id=?, doctor_id=?, appointment_date=?, appointment_time=?, notes=? WHERE id=?")
        ->execute([$user_id, $doctor_id, $date, $time, $notes, $aid]);
    $_SESSION['message'] = "✅ Appointment updated.";
    header("Location: receptionist_appointments.php");
    exit();
  }
}

// جلب بيانات للعرض
$appointments = $pdo->query("
  SELECT a.*, d.name AS doctor_name, u.username AS patient_name
  FROM appointments a
  JOIN doctors d ON a.doctor_id = d.user_id
  JOIN users u ON a.user_id = u.id
  ORDER BY a.appointment_date DESC, a.appointment_time DESC
")->fetchAll(PDO::FETCH_ASSOC);

$patients = $pdo->query("SELECT id, username, email FROM users WHERE role='user' ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
$doctors = $pdo->query("SELECT id, name, user_id FROM doctors ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Appointments</title>
<link rel="stylesheet" href="../css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>

.action-select {
  padding: 8px 35px 8px 12px;
  border-radius: 8px;
  border: 1px solid #ccc;
  background: #fff url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path fill="gray" d="M7 10l5 5 5-5z"/></svg>') no-repeat right 10px center;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: .25s ease;
  appearance: none;
}

.action-select:hover {
  border-color: #888;
}

.action-select:focus {
  outline: none;
  border-color: #1e90ff;
  box-shadow: 0 0 5px rgba(30,144,255,0.4);
}

/* ألوان مختلفة حسب الحالة */
.action-select option[value="Pending"] {
  background: #ffeeba;
  color: #8a6d3b;
}

.action-select option[value="Confirmed"] {
  background: #d4edda;
  color: #155724;
}

.action-select option[value="Cancelled"] {
  background: #f8d7da;
  color: #721c24;
}

.action-select option[value="In-Progress"] {
  background: #cce5ff;
  color: #004085;
}

.action-select option[value="Completed"] {
  background: #e2e3e5;
  color: #383d41;
}

/* شكل الفورم */
.action-form {
  margin: 0;
  display: flex;
  align-items: center;
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
            <li><a href="receptionist_appointments.php" class="active">Manage Appointments</a></li>
            <li><a href="receptionist_today.php">Today's Appointments</a></li>
            <li><a href="receptionist_patients.php">Patients</a></li>
            <li><a href="receptionist_doctors_schedule.php">Doctors Schedule</a></li>
            <li><a href="logout.php" class="register">Logout</a></li>
            <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
          </ul>
        </nav>
      </header>

      <main class="dashboard">
        <h1>Manage Appointments</h1>

        <?php if (!empty($_SESSION['message'])): ?>
          <div class="alert success"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <!-- Create new appointment -->
        <section style="margin-bottom:20px;">
          <h3>Create Appointment</h3><br>
          <form method="POST" class="form-card" style="display:flex;gap:10px;flex-wrap:wrap;">
            <select name="user_id" required>
              <option value="">Select Patient</option>
              <?php foreach($patients as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['username']) ?> (<?= htmlspecialchars($p['email']) ?>)</option>
              <?php endforeach; ?>
            </select>

            <select name="doctor_id" required>
              <option value="">Select Doctor</option>
              <?php foreach($doctors as $d): ?>
                <option value="<?= $d['user_id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
              <?php endforeach; ?>
            </select>

            <input type="date" name="appointment_date" required min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+1 month')) ?>">
            <input type="time" name="appointment_time" required>
            <input type="text" name="fee" placeholder="Fee" required>
            <input type="text" name="notes" placeholder="Notes (ptional)">
            <button style="max-width: 100px;" type="submit" name="create" class="btn">Create</button>
          </form>
        </section>

        <!-- Appointments table -->
        <table class="appointments-table">
          <thead><tr><th>#</th><th>Doctor</th><th>Patient</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach($appointments as $i=>$a): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                <td><?= htmlspecialchars($a['patient_name']) ?></td>
                <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                <td><?= htmlspecialchars(substr($a['appointment_time'],0,5)) ?></td>
                <td class="status <?= htmlspecialchars(strtolower($a['status'])) ?>"><?= htmlspecialchars($a['status']) ?></td>
                <td>
                  <!-- تغيير الحالة (قائمة فقط) -->
                  <?php if ($a['status'] !== 'In-Progress' && $a['status'] !== 'Completed'): ?>
                    <form method="POST" style="display:inline;" class="action-form">
                      <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                      <select name="status" class="action-select" onchange="this.form.submit()">
                        <?php foreach(['Pending','Confirmed','Cancelled','In-Progress','Completed'] as $opt): ?>
                          <option value="<?= $opt ?>" <?= $a['status']===$opt ? 'selected':'' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                      </select>
                      <input type="hidden" name="change_status" value="1">
                    </form>
                  <?php else: ?>
                    <?= htmlspecialchars($a['status']) ?>
                  <?php endif; ?>

                  <!-- Edit button opens simple inline edit form (toggle by JS) -->
                  <button class="btnn" onclick="toggleEdit(<?= $a['id'] ?>)"><i class="fa-solid fa-pen-to-square"></i></button>

                  <div id="edit-<?= $a['id'] ?>" style="display:none; margin-top:8px;">
                    <form method="POST">
                      <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                      <select name="user_id" required>
                        <?php foreach($patients as $p): ?>
                          <option value="<?= $p['id'] ?>" <?= $p['id']==$a['user_id']? 'selected':'' ?>><?= htmlspecialchars($p['username']) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <select name="doctor_id" required>
                        <?php foreach($doctors as $d): ?>
                          <option value="<?= $d['user_id'] ?>" <?= $d['user_id']==$a['doctor_id']? 'selected':'' ?>><?= htmlspecialchars($d['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <input type="date" name="appointment_date" value="<?= htmlspecialchars($a['appointment_date']) ?>" required>
                      <input type="time" name="appointment_time" value="<?= htmlspecialchars(substr($a['appointment_time'],0,5)) ?>" required>
                      <input type="text" name="notes" value="<?= htmlspecialchars($a['notes'] ?? '') ?>">
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
  const el = document.getElementById('edit-'+id);
  if(!el) return;
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

  <script src="../js/transition.js"></script>
</body>
</html>