<?php
session_start();
require 'config/config.php';
require 'config/user_data.php';

$file = isset($_GET['file']) ? $_GET['file'] : '';
$return = isset($_GET['return']) ? urldecode($_GET['return']) : 'user_appointments.php';

// تحقق من ملكية الملف
$stmt = $pdo->prepare("
    SELECT 1 FROM appointments a
    JOIN doctor_notes n ON n.patient_id = a.user_id AND n.doctor_id = a.doctor_id
    WHERE a.user_id = ? AND n.prescription_image = ?
");
$stmt->execute([$user_id, $file]);
if (!$stmt->fetch()) {
    echo "<script>
        alert('⚠️ You do not have permission to download this file.');
        window.location.href = 'user_dashboard.php';
    </script>";
    exit;
}

$_SESSION['message'] = "✅ Prescription downloaded successfully!";
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Downloading...</title>
    <script>
      window.onload = function() {
        const link = document.createElement('a');
        link.href = 'user_force_download.php?file=<?= urlencode($file) ?>';
        link.download = '';
        link.target = '_blank';
        link.click();
        setTimeout(() => {
          window.location.href = '<?= $return ?>';
        }, 1000);
      };
    </script>
    <style>
      body {
        background: #f8f9fa;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        font-family: sans-serif;
        color: #333;
      }
    </style>
  </head>
  
  <body>
    <h2>Downloading Prescription... ⏳</h2>
  </body>
</html>