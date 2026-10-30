<?php
session_start();
require 'config/config.php';

// ✅ التحقق من صلاحيات المستخدم
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
  header("Location: login.php");
  exit();
}

// لو مش مستخدم عادي (user)
if ($_SESSION['role'] !== 'user') {
  header("Location: logout.php");
  exit();
}

$user_id = (int) $_SESSION['user_id'];

// التحقق من إرسال appointment_id
if (!isset($_POST['appointment_id'])) {
    $_SESSION['message'] = "No appointment selected for payment.";
    header("Location: user_appointments.php");
    exit();
}

$appointment_id = (int) $_POST['appointment_id'];

// جلب بيانات الموعد مع المبلغ حسب الدكتور
$stmt = $pdo->prepare("
    SELECT a.*, d.fee 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.user_id
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->execute([$appointment_id, $user_id]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    $_SESSION['message'] = "Appointment not found.";
    header("Location: user_appointments.php");
    exit();
}

// المبلغ حسب الدكتور
$amount = $appointment['fee'];

// التحقق أن الحالة Confirmed
if ($appointment['status'] !== 'Confirmed') {
    $_SESSION['message'] = "This appointment cannot be paid.";
    header("Location: user_appointments.php");
    exit();
}

// التحقق من اختيار وسيلة الدفع
if (!isset($_POST['method']) || empty($_POST['method'])) {
    $_SESSION['message'] = "Please select a payment method.";
    header("Location: user_payment.php?appointment_id={$appointment_id}");
    exit();
}

$method = trim($_POST['method']);

// التحقق إذا تم الدفع بالفعل لمنع التكرار
$checkPayment = $pdo->prepare("SELECT * FROM payments WHERE appointment_id = ? AND user_id = ?");
$checkPayment->execute([$appointment_id, $user_id]);
$existingPayment = $checkPayment->fetch();

if ($existingPayment) {
    $_SESSION['message'] = "Payment has already been made for this appointment, Please Contact with Support!";
    header("Location: user_appointments.php");
    exit();
}

// إضافة سجل الدفع مع حفظ method
$insert = $pdo->prepare("
    INSERT INTO payments (appointment_id, user_id, amount, method, status) 
    VALUES (?, ?, ?, ?, 'Paid')
");
$insert->execute([$appointment_id, $user_id, $amount, $method]);

// تحديث حالة الموعد إلى In-Progress بعد الدفع
$update = $pdo->prepare("UPDATE appointments SET status = 'In-Progress' WHERE id = ? AND user_id = ?");
$update->execute([$appointment_id, $user_id]);

// إعادة التوجيه مع رسالة نجاح (سطرين)
$_SESSION['message'] = "Payment completed successfully. Your appointment is now In-Progress.";
header("Location: user_appointments.php");
exit();
?>