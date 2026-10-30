<?php
session_start();
require 'config/config.php';
require 'config/doctor_data.php';

if (!isset($_GET['file'])) {
  die("File not specified.");
}

$file = urlencode($_GET['file']);
$return = isset($_GET['return']) ? urldecode($_GET['return']) : 'doctor_dashboard.php';

// نعرض صفحة بسيطة فيها سكربت التحميل والرجوع
$_SESSION['message'] = "✅ Prescription downloaded successfully!";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Downloading...</title>
    <script>
      // فتح التحميل في نافذة مخفية
      window.onload = function() {
        const link = document.createElement('a');
        link.href = 'doctor_force_download.php?file=<?= $file ?>';
        link.download = '';
        link.target = '_blank';
        link.click();
        // بعد ثانية واحدة يرجع الطبيب للصفحة الأصلية
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