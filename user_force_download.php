<?php
session_start();
require 'config/config.php';
require 'config/user_data.php';

// التحقق من وجود الملف المطلوب
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("No file specified.");
}

$file_path = $_GET['file'];

// التحقق من ملكية الملف: يجب أن يكون مرتبط بموعد للمستخدم
$stmt = $pdo->prepare("
    SELECT a.id
    FROM appointments a
    JOIN doctor_notes n ON n.patient_id = a.user_id AND n.doctor_id = a.doctor_id
    WHERE a.user_id = ? AND n.prescription_image = ?
");
$stmt->execute([$user_id, $file_path]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    die("You do not have permission to download this file.");
}

// تحقق من وجود الملف فعليًا
$full_path = "../" . $file_path;
if (!file_exists($full_path)) {
    die("File not found.");
}

// الحصول على نوع الملف
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $full_path);
finfo_close($finfo);

// اسم الملف عند التحميل
$filename = basename($file_path);

// إرسال الرؤوس للتحميل
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($full_path));

// قراءة الملف وإرساله
readfile($full_path);
exit();
?>