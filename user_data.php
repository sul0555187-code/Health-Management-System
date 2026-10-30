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

// لو مش مستخدم عادي (user)
if ($_SESSION['role'] !== 'user') {
  header("Location: logout.php");
  exit();
}

// ✅ تعريف المتغيرات العامة
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// ✅ جلب بيانات المستخدم الأساسية
$stmt = $pdo->prepare("SELECT id, username, email, password, address, phone, image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ جلب عدد الإشعارات غير المقروءة
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'unread'");
$stmt->execute([$user_id]);
$notifCount = $stmt->fetchColumn();
?>