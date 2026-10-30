<?php
// ✅ تشغيل تسجيل الأخطاء
error_reporting(E_ALL);           // فعّل كل أنواع الأخطاء
ini_set('display_errors', 0);     // لا يتم عرض الأخطاء في الصفحة
ini_set('log_errors', 1);         // فعّل تسجيل الأخطاء في ملف
ini_set('error_log', __DIR__ . '/../logs/errors.log');  // المسار الكامل للملف

$host = 'localhost';
$dbname = 'health_management';
$username = 'root';
$password = '';

#PHP Data Objects (PDO) is a database access layer providing a uniform method of access to multiple databases.
#أي خطأ في قاعدة البيانات هيتم التعامل معاه كـ 
#Exception يعني خطأ قابل للالتقاط والتعامل معاه عن طريق 
#try...catch

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password); 
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); #Error Handling Mode
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage()); #عرض رسالة الخطأ في حال فشل الاتصال بقاعدة البيانات
}
?>