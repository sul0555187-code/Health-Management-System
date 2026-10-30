<?php
session_start();

// إزالة جميع بيانات الجلسة
$_SESSION = [];

// تدمير الجلسة نهائيًا
session_destroy();

// إعادة التوجيه لصفحة تسجيل الدخول
header("Location: login.php");
exit;
?>