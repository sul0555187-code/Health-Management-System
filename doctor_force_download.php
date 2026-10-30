<?php
session_start();
require 'config/config.php';
require 'config/doctor_data.php';

if (!isset($_GET['file'])) {
  die("File not specified.");
}

$file = '../' . $_GET['file'];
$realBase = realpath('../img/prescriptions/');
$realFile = realpath($file);
if (!$realFile || strpos($realFile, $realBase) !== 0 || !file_exists($realFile)) {
  die("Invalid file path.");
}

// إجبار التحميل
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($realFile) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($realFile));
readfile($realFile);
exit;
?>
