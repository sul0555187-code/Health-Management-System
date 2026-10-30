<?php
session_start();
require 'config/config.php';
require 'config/user_data.php';

// ✅ جلب doctor_id من الرابط
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
if ($doctor_id <= 0) {
    echo "<script>
        alert('Please choose doctor to continue!');
        window.location.href = 'doctors.php';
    </script>";
    exit;
}

// ✅ جلب بيانات الطبيب من جدول doctors (بما فيهم fee)
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    echo "<script>
        alert('❌ Doctor not found.');
        window.location.href = 'doctors.php';
    </script>";
    exit;
}

// ✅ استخدام user_id للطبيب كـ doctor_id في appointments
$doctor_user_id = $doctor['user_id'];
$doctor_fee = $doctor['fee'] ?? 0;

// ✅ جلب كل المواعيد المحجوزة للطبيب
$stmtBooked = $pdo->prepare("
    SELECT appointment_date, appointment_time
    FROM appointments
    WHERE doctor_id = ? AND status != 'Cancelled'
");
$stmtBooked->execute([$doctor_user_id]);
$bookedData = [];
while ($row = $stmtBooked->fetch(PDO::FETCH_ASSOC)) {
    $bookedData[$row['appointment_date']][] = substr($row['appointment_time'],0,5);
}

// ✅ معالجة الحجز
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    date_default_timezone_set('Asia/Riyadh');

    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $notes = htmlspecialchars(trim($_POST['notes']));

    if (!empty($date) && !empty($time)) {
        $today = new DateTime();
        $appointmentDateTime = new DateTime("$date $time");
        $maxDate = new DateTime('+1 month');

        if ($appointmentDateTime < $today) {
            $error = "⚠️ You cannot book an appointment in the past.";
        } elseif ($appointmentDateTime > $maxDate) {
            $error = "⚠️ You can only book appointments within the next month!";
        } else {
            // ✅ التحقق من وجود موعد آخر للمريض في نفس اليوم والوقت (مع أي دكتور)
            $checkConflict = $pdo->prepare("
                SELECT COUNT(*) FROM appointments
                WHERE user_id = ? AND appointment_date = ? AND appointment_time = ? AND status NOT IN ('cancelled', 'rejected')
            ");
            $checkConflict->execute([$user_id, $date, $time]);
            $hasConflict = $checkConflict->fetchColumn();

            // ✅ التحقق من وجود موعد لنفس الدكتور في نفس اليوم (بدون تكرار للمريض)
            $checkUser = $pdo->prepare("
                SELECT COUNT(*) FROM appointments 
                WHERE doctor_id = ? AND appointment_date = ? AND user_id = ? AND status != 'cancelled'
            ");
            $checkUser->execute([$doctor_user_id, $date, $user_id]);
            $userHasAppointment = $checkUser->fetchColumn();

            // ✅ التحقق من وقت الحجز لنفس الدكتور (أي مريض آخر)
            $checkTime = $pdo->prepare("
                SELECT COUNT(*) FROM appointments 
                WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'
            ");
            $checkTime->execute([$doctor_user_id, $date, $time]);
            $timeTaken = $checkTime->fetchColumn();

            // ✅ رسائل التحقق
            if ($hasConflict > 0) {
                $error = "⚠️ You already have another appointment at this time with another doctor.";
            } elseif ($userHasAppointment > 0) {
                $error = "⚠️ You already have an appointment with this doctor on this day.";
            } elseif ($timeTaken > 0) {
                $error = "⚠️ This time slot is already booked. Please choose another time.";
            } else {
                // ✅ إدراج الموعد مع إضافة السعر
                $insert = $pdo->prepare("
                    INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, notes, fee)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insert->execute([$user_id, $doctor_user_id, $date, $time, $notes, $doctor_fee]);
                $success = "✅ Appointment booked successfully!";
            }
        }
    } else {
        $error = "⚠️ Please select date and time.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Book Appointment | Health Centers & Hospitals</title>
    <link rel="icon" href="../logo/logo.jpg" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
              <li><a href="user_notifications.php">🔔 Notifications (<?= $notifCount ?>)</a></li>
              <li><a href="user_dashboard.php">Dashboard</a></li>
              <li><a href="doctors.php">Doctors</a></li>
              <li><a href="user_profile.php">Profile</a></li>
              <li><a href="user_book_appointment.php?doctor_id=<?= $doctor['id'] ?>" class="active">Book Appointment</a></li>
              <li><a href="contact_us.php">Contact Us</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <!-- Booking Form -->
        <main class="wrapper">
          <h1>Book with <?= htmlspecialchars($doctor['name']) ?></h1>

          <?php if (!empty($success)): ?>
            <div class="message success">
              <?= $success ?>
              <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
            </div>
          <?php elseif (!empty($error)): ?>
            <div class="message error">
              <?= $error ?>
              <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
            </div>
          <?php endif; ?>

          <form method="POST">
            <div class="input-box">
              <input type="text" value="<?= htmlspecialchars($doctor['specialization']) ?>" disabled />
              <i class="fa-solid fa-user-doctor"></i>
            </div>

            <div class="input-box">
              <input 
                type="date" 
                name="appointment_date" 
                id="appointment_date"
                required 
                min="<?= date('Y-m-d') ?>" 
                max="<?= date('Y-m-d', strtotime('+1 month')) ?>" 
              />
              <i class="fa-solid fa-calendar-days"></i>
            </div>

            <div class="input-box">
              <select name="appointment_time" id="appointment_time" class="styled-select" required>
                <option value="" disabled selected>Select Time</option>
              </select>
              <i class="fa-solid fa-clock"></i>
            </div>

            <div class="input-box textarea-box">
              <textarea name="notes" placeholder="Add any notes (optional)" rows="4"></textarea>
              <i class="fa-solid fa-note-sticky"></i>
            </div>

            <button type="submit" class="btn">Confirm Booking</button>
          </form>
        </main>

        <!-- Footer -->
        <footer>
          <p>&copy; 2025 Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <script src="../js/transition.js"></script>

    <!-- JS لتحديث الأوقات -->
    <script>
      const availableTimes = [];
      let start = new Date('1970-01-01T08:00:00');
      let end = new Date('1970-01-01T16:00:00');
      while(start <= end) {
        let h = start.getHours().toString().padStart(2,'0');
        let m = start.getMinutes().toString().padStart(2,'0');
        availableTimes.push(`${h}:${m}`);
        start.setMinutes(start.getMinutes() + 30);
      }

      const bookedSlots = <?= json_encode($bookedData); ?>;

      const dateInput = document.getElementById('appointment_date');
      const timeSelect = document.getElementById('appointment_time');

      dateInput.addEventListener('change', () => {
        timeSelect.innerHTML = '<option value="" disabled selected>Select Time</option>';
        const date = dateInput.value;
        const booked = bookedSlots[date] || [];
        availableTimes.forEach(t => {
          const option = document.createElement('option');
          option.value = t;
          option.textContent = t + (booked.includes(t) ? ' (Booked)' : '');
          if(booked.includes(t)) {
            option.disabled = true;
            option.style.background = '#ccc';
            option.style.color = '#666';
          }
          timeSelect.appendChild(option);
        });
      });
    </script>
  </body>
</html>
