<?php
// ✅ بدء الجلسة لو مش مفعلة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ استدعاء الاتصال بقاعدة البيانات
require_once __DIR__ . '/config.php';

// ✅ التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// لو مش (admin)
if ($_SESSION['role'] !== 'admin') {
  header("Location: logout.php");
  exit();
}
?>