<?php
session_start();
require 'config/config.php';
require 'config/admin_data.php';

// ✅ بحث عن المرضى
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search) {
  $stmt = $pdo->prepare("SELECT id, username, email, phone, address FROM users WHERE role='user' AND (username LIKE ? OR email LIKE ?) ORDER BY id ASC");
  $stmt->execute(["%$search%", "%$search%"]);
} else {
  $stmt = $pdo->query("SELECT id, username, email, phone, address FROM users WHERE role='user' ORDER BY id ASC");
}
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ حذف مريض
if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];

    // ✅ استرجاع الصورة قبل الحذف
    $stmt = $pdo->prepare("SELECT image FROM users WHERE id=? AND role='user'");
    $stmt->execute([$delete_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && !empty($user['image'])) {

        // ✅ لا تحذف الصورة الافتراضية
        if ($user['image'] !== "img/users/default-user.png" && file_exists("../" . $user['image'])) {
            unlink("../" . $user['image']);
        }
    }

    // ✅ حذف أي سجلات مرتبطة — اختياري حسب التصميم
    // $pdo->prepare("DELETE FROM appointments WHERE user_id=?")->execute([$delete_id]);

    // ✅ حذف المستخدم
    $pdo->prepare("DELETE FROM users WHERE id=? AND role='user'")->execute([$delete_id]);

    $_SESSION['message'] = "🗑️ User deleted successfully.";
    header("Location: admin_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Patients | Health Centers & Hospitals</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="icon" href="../logo/logo.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  </head>

  <body>
    <div class="home-section">
      <div class="effects"><div class="bg-effect"></div></div>
      <div class="container">

        <!-- Header -->
        <header class="header">
          <a href="admin_dashboard.php" class="logo" aria-label="Health Management Logo">
            <img src="../logo/logo.jpg" alt="Health Centers Logo" loading="lazy"/>
          </a>
          <nav class="links" aria-label="Main Navigation">
            <button class="hamburger-btn outer-shadow hover-in-shadow" aria-label="Open Menu"><span></span></button>
            <ul class="nav-bar">
              <li><a href="admin_dashboard.php">Dashboard</a></li>
              <li><a href="admin_doctors.php">Doctors</a></li>
              <li><a href="admin_users.php" class="active">Patients</a></li>
              <li><a href="admin_appointments.php">Appointments</a></li>
              <li><a href="admin_messages.php">Messages</a></li>
              <li><a href="admin_profile.php">Profile</a></li>
              <li><a href="logout.php" class="register">Logout</a></li>
              <button class="close-navBar outer-shadow hover-in-shadow" aria-label="Close Menu">×</button>
              <div class="copy-right"><p>&copy; Health Centers & Hospitals Platform</p></div>
            </ul>
          </nav>
        </header>

        <main class="dashboard">
          <h1>Manage Patients</h1>

          <?php if (!empty($_SESSION['message'])): ?>
            <div class="alert success"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
          <?php endif; ?>

          <!-- Search bar -->
          <form method="GET" class="search-bar" style="margin-bottom: 20px;">
            <div class="input-box" style="position:relative; flex:1;">
              <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>" style="width:100%; padding:10px 40px 10px 10px; border-radius:8px; border:1px solid #ccc;" />
              <i class="fa-solid fa-search" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); color:#888;"></i>
            </div>
            <?php if ($search): ?>
              <br>
              <a href="admin_users.php" class="btn" style="background-color:#aaa;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            <?php endif; ?>
          </form>

          <?php if (!$patients): ?>
            <p>No patients found.</p>
          <?php else: ?>
            <table class="appointments-table">
              <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Actions</th></tr>
              </thead>
              <tbody>
                <?php foreach ($patients as $i => $u): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= htmlspecialchars($u['username']) ?></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><?= htmlspecialchars($u['phone'] ?: '-') ?></td>
                  <td><?= htmlspecialchars($u['address'] ?: '-') ?></td>
                  <td class="actions">
                    <div class="action-buttons">
                      <!-- تعديل -->
                      <a href="admin_edit_user.php?id=<?= $u['id'] ?>" class="btnn btn-confirm">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                      </a>

                      <!-- حذف -->
                      <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btnn btn-cancel">
                          <i class="fa-solid fa-trash"></i> Delete
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
          
          <div class="actions extra-actions">
            <a href="admin_add_user.php" class="btn"><i class="fa-solid fa-user-plus"></i> Add New User</a>
            <a href="admin_dashboard.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
          </div>
        </main>

        <!-- Footer -->
        <footer>
          <p>&copy; 2025 Health Centers & Hospitals Platform. All rights reserved.</p>
          <p>&nbsp;</p>
        </footer>
      </div>
    </div>

    <script src="../js/transition.js"></script>
  </body>
</html>