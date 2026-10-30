<?php
session_start();
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $subject = trim($_POST['subject']);
  $message = trim($_POST['message']);

  if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
    $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $message]);

    $_SESSION['msg_success'] = "✅ Message sent successfully!";
  } else {
    $_SESSION['msg_error'] = "⚠️ Please fill in all fields!";
  }

  header("Location: contact_us.php");
  exit;
} else {
  header("Location: contact_us.php");
  exit;
}
?>