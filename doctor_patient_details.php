<?php
session_start();
require 'config/config.php';
require 'config/doctor_data.php';

$patient_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($patient_id <= 0) die("Invalid patient ID.");

/* حذف ملاحظة */
if (isset($_POST['delete_note'])) {
  $note_id = (int)$_POST['note_id'];
  $stmt = $pdo->prepare("SELECT prescription_image FROM doctor_notes WHERE id=? AND doctor_id=?");
  $stmt->execute([$note_id, $doctor_id]);
  $note = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($note) {
    if (!empty($note['prescription_image']) && file_exists("../" . $note['prescription_image'])) {
      unlink("../" . $note['prescription_image']);
    }
    $pdo->prepare("DELETE FROM doctor_notes WHERE id=? AND doctor_id=?")->execute([$note_id, $doctor_id]);
    $_SESSION['message'] = "🗑️ Note Deleted Successfully.";
  }

  header("Location: doctor_patient_details.php?id=$patient_id");
  exit();
}

/* تعديل ملاحظة */
if (isset($_POST['edit_note'])) {
  $note_id = (int)$_POST['note_id'];
  $new_note = trim($_POST['note']);
  $new_image = null;

  $stmt = $pdo->prepare("SELECT prescription_image FROM doctor_notes WHERE id=? AND doctor_id=?");
  $stmt->execute([$note_id, $doctor_id]);
  $old = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($old) {
    if (!empty($_FILES['prescription']['name'])) {
      $targetDir = "../img/prescriptions/";
      if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

      $ext = strtolower(pathinfo($_FILES["prescription"]["name"], PATHINFO_EXTENSION));
      $safeUsername = preg_replace("/[^a-zA-Z0-9_-]/", "_", $doctor_id . "_" . time());
      $fileName = "Prescription_" . $safeUsername . "." . $ext;
      $targetFilePath = $targetDir . $fileName;
      $allowed = ['jpg','jpeg','png','pdf'];

      if (in_array($ext, $allowed)) {
        move_uploaded_file($_FILES["prescription"]["tmp_name"], $targetFilePath);
        $new_image = "img/prescriptions/" . $fileName;

        if (!empty($old['prescription_image']) && file_exists("../" . $old['prescription_image'])) {
          unlink("../" . $old['prescription_image']);
        }
      }
    } else {
      $new_image = $old['prescription_image'];
    }

    $pdo->prepare("UPDATE doctor_notes SET note=?, prescription_image=? WHERE id=? AND doctor_id=?")
        ->execute([$new_note, $new_image, $note_id, $doctor_id]);
    $_SESSION['message'] = "✅ Prescription Updated Successfully!";
  }

  header("Location: doctor_patient_details.php?id=$patient_id");
  exit();
}

/* بيانات المريض + المواعيد + الملاحظات */
$stmt = $pdo->prepare("SELECT username, email, phone FROM users WHERE id=?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.notes, u.username
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.doctor_id=? AND a.user_id=? AND a.status='Completed'
    ORDER BY a.appointment_date ASC
");
$stmt->execute([$doctor_id, $patient_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
  $note = trim($_POST['note']);
  $prescription_image = null;
  $appointment_id = !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null;

  if (empty($appointment_id)) {
      $_SESSION['message'] = "⚠️ Please select a completed appointment before adding a prescription.";
      header("Location: doctor_patient_details.php?id=$patient_id");
      exit();
  }

  if (!empty($_FILES['prescription']['name'])) {
    $targetDir = "../img/prescriptions/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $ext = strtolower(pathinfo($_FILES["prescription"]["name"], PATHINFO_EXTENSION));
    $safeUsername = preg_replace("/[^a-zA-Z0-9_-]/", "_", $patient['username']);
    $fileName = "Prescription_" . $safeUsername . "_" . time() . "." . $ext;
    $targetFilePath = $targetDir . $fileName;
    $allowed = ['jpg','jpeg','png','pdf'];

    if (in_array($ext, $allowed)) {
        move_uploaded_file($_FILES["prescription"]["tmp_name"], $targetFilePath);
        $prescription_image = "img/prescriptions/" . $fileName;
    }
  }

  $check = $pdo->prepare("SELECT id FROM appointments WHERE id=? AND doctor_id=? AND status='Completed'");
  $check->execute([$appointment_id, $doctor_id]);
  if (!$check->fetch()) {
      $_SESSION['message'] = "⚠️ You can only add prescriptions for completed appointments.";
      header("Location: doctor_patient_details.php?id=$patient_id");
      exit();
  }

  $exists = $pdo->prepare("SELECT id FROM doctor_notes WHERE appointment_id=? AND doctor_id=?");
  $exists->execute([$appointment_id, $doctor_id]);
  if ($exists->fetch()) {
      $_SESSION['message'] = "⚠️ A prescription for this appointment already exists.";
      header("Location: doctor_patient_details.php?id=$patient_id");
      exit();
  }

  $pdo->prepare("INSERT INTO doctor_notes (doctor_id, patient_id, appointment_id, note, prescription_image)
      VALUES (?, ?, ?, ?, ?)")->execute([$doctor_id, $patient_id, $appointment_id, $note, $prescription_image]);

  $_SESSION['message'] = "✅ Prescription Added Successfully!";
  header("Location: doctor_patient_details.php?id=$patient_id");
  exit();
}

$stmt = $pdo->prepare("
  SELECT n.id, n.note, n.prescription_image, n.created_at, a.appointment_date, a.appointment_time
  FROM doctor_notes n
  LEFT JOIN appointments a ON n.appointment_id = a.id
  WHERE n.doctor_id=? AND n.patient_id=?
  ORDER BY n.created_at DESC
");
$stmt->execute([$doctor_id, $patient_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Patient Details | Health Centers & Hospitals</title>
    <link rel="icon" href="../logo/logo.jpg" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/styles.css" />
  </head>

  <body>
    <div class="home-section">
      <div class="effects"><div class="bg-effect"></div></div>
      <div class="container">
        <header class="header">
          <a href="doctor_dashboard.php" class="logo"><img src="../logo/logo.jpg" alt="logo" /></a>
          <nav class="links" aria-label="Main Navigation">
            <ul class="nav-bar">
              <li><a href="doctor_dashboard.php">Dashboard</a></li>
              <li><a href="about_us.php">About Us</a></li>
              <li><a href="doctor_appointments.php">My Appointments</a></li>
              <li><a href="doctor_patients.php" class="active">My Patients</a></li>
              <li><a href="doctor_profile.php">Profile</a></li>
              <li><a href="contact_us.php">Contact Us</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <main class="dashboard">
          <h1>Patient Details</h1>

          <?php if (!empty($_SESSION['message'])): ?>
            <div class="message"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
          <?php endif; ?>

          <div class="patient-info">
            <h2><?= htmlspecialchars($patient['username']) ?></h2>
            <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($patient['phone'] ?: '-') ?></p>
          </div><br>

          <h2 style="color:#28be97;">Patient Completed Appointments</h2>
          <?php if (!$appointments): ?>
            <p style="color:#b33;"><br>⚠️ You can only add prescriptions for completed appointments. <br> No completed appointments found.</p>
          <?php else: ?>
            <table class="appointments-table">
              <thead><tr><th>#</th><th>Date</th><th>Time</th><th>Status</th><th>Notes</th></tr></thead>
              <tbody>
                <?php foreach ($appointments as $i => $a): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                  <td><?= htmlspecialchars(substr($a['appointment_time'],0,5)) ?></td>
                  <td class="status <?= htmlspecialchars($a['status']) ?>"><?= htmlspecialchars($a['status']) ?></td>
                  <td><?= htmlspecialchars($a['notes'] ?: '-') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>

          <br><br>
          <?php if ($appointments): ?>
            <div class="notes-section">
              <h2 style="color:#28be97;">Add New Prescription</h2><br>
              <form method="POST" enctype="multipart/form-data">
                <label><strong>Select Completed Appointment:</strong></label>
                <select name="appointment_id" required>
                  <option value="">-- Select Appointment --</option>
                  <?php foreach ($appointments as $app): ?>
                    <option value="<?= htmlspecialchars($app['id']) ?>">
                       <?= htmlspecialchars($app['username']) ?>
                      (<?= htmlspecialchars($app['appointment_date']) ?>) (<?= htmlspecialchars(substr($app['appointment_time'],0,5)) ?>)
                    </option>
                  <?php endforeach; ?>
                </select><br><br>

                <textarea name="note" placeholder="Write your prescription..." required></textarea>
                <input type="file" name="prescription" accept=".jpg,.jpeg,.png,.pdf">
                <button type="submit" name="add_note" class="btn-add"><i class="fa fa-plus"></i> Add Prescription</button>
              </form>
            </div><br>
          <?php endif; ?>

          <h2 style="color:#28be97;">Doctor Notes & Prescriptions</h2><br>

          <?php if (!$notes): ?>
            <p>No notes yet.</p>
          <?php else: ?>
          <div class="notes-grid">
            <?php foreach ($notes as $n): ?>
              <div class="note-card">
                <p><?= nl2br(htmlspecialchars($n['note'])) ?></p>
                <small>🕒 <?= htmlspecialchars($n['created_at']) ?></small>
                <?php if ($n['appointment_date']): ?>
                  <small>📅 Linked to: <?= htmlspecialchars($n['appointment_date']) ?> (<?= htmlspecialchars(substr($n['appointment_time'],0,5)) ?>)</small>
                <?php endif; ?>

                <?php if ($n['prescription_image']): ?>
                <div class="prescription-preview">
                  <a href="../<?= htmlspecialchars($n['prescription_image']) ?>" target="_blank">
                    <?php if (preg_match('/\.pdf$/i', $n['prescription_image'])): ?>
                      <div class="pdf-box">📄 PDF File</div>
                    <?php else: ?>
                      <img src="../<?= htmlspecialchars($n['prescription_image']) ?>" alt="Prescription">
                    <?php endif; ?>
                  </a>
                  <a href="doctor_download_prescription.php?file=<?= urlencode($n['prescription_image']) ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-download"><i class="fa fa-download"></i> Download</a>
                </div>
                <?php endif; ?>

                <div class="note-actions">
                  <button type="button" onclick="toggleEdit(<?= $n['id'] ?>)" class="btn-edit"><i class="fa fa-pen"></i> Edit</button>
                  <form method="POST" onsubmit="return confirm('Delete this note?');">
                    <input type="hidden" name="note_id" value="<?= (int)$n['id'] ?>">
                    <button type="submit" name="delete_note" class="btn-delete"><i class="fa fa-trash"></i> Delete</button>
                  </form>
                </div>

                <form method="POST" enctype="multipart/form-data" class="edit-form" id="edit-form-<?= $n['id'] ?>">
                  <input type="hidden" name="note_id" value="<?= (int)$n['id'] ?>">
                  <textarea name="note" required><?= htmlspecialchars($n['note']) ?></textarea>
                  <input type="file" name="prescription" accept=".jpg,.jpeg,.png,.pdf">
                  <button type="submit" name="edit_note" class="btn-save"><i class="fa fa-floppy-disk"></i> Save</button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <a href="doctor_patients.php" class="btn-back"><i class="fa fa-arrow-left"></i> Back</a>
        </main>

        <footer>
          <p>&copy; 2025 Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <button class="dark-toggle" id="darkToggle"><i class="fa fa-moon"></i></button>
    <script>
      function toggleEdit(id){
        const form=document.getElementById("edit-form-"+id);
        form.style.display=(form.style.display==="none"||form.style.display==="")?"block":"none";
      }
    </script>
    <script src="../js/transition.js"></script>
  </body>
</html>