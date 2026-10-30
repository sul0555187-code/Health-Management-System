<?php
session_start();
require 'config/config.php';
require 'config/doctor_data.php';

$appointment_id = (int)$_POST['appointment_id'];
$patient_id = (int)$_POST['patient_id'];

// ✅ حدّث حالة الموعد إلى in_progress
$update = $pdo->prepare("UPDATE appointments SET status = 'Completed' WHERE id = ? AND doctor_id = ?");
$update->execute([$appointment_id, $doctor_id]);

// ✅ أضف إشعار للمريض
$message = "Your doctor has started the video consultation.";
$link = "user_video.php?appointment_id=" . $appointment_id;
$stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
$stmt->execute([$patient_id, $message, $link]);

// ✅ إعادة التوجيه لصفحة الفيديو الخاصة بالطبيب
header("Location: doctor_video.php?appointment_id=$appointment_id");
exit();
?>