<?php
// ✅ بدء الجلسة لو مش مفعلة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ استدعاء الاتصال بقاعدة البيانات
require_once __DIR__ . '/config.php';

// ✅ Verify doctor login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = (int) $_SESSION['user_id'];
?>