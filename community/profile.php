<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireRole('community_user');

// Always load using the logged-in user's own ID from the session -
// never from a URL parameter or form field, so there's no way to
// view/edit someone else's profile.
$stmt = $pdo->prepare("SELECT name, email, role, created_at FROM users WHERE user_id = ?");
$stmt->execute([currentUserId()]);
$user = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
  <h1>My Profile</h1>
  <p class="subtitle">View and update your account information.</p>

  <div id="profileAlert"></div>

  <form id="profileForm">
    <label>Full Name</label>
    <input type="text" name="name" id="name" maxlength="100"
           value="<?php echo htmlspecialchars($user['name']); ?>" required>

    <label>Email</label>
    <input type="email" name="email" id="email" maxlength="150"
           value="<?php echo htmlspecialchars($user['email']); ?>" required>

    <label>Role</label>
    <input type="text" value="Community User" disabled style="background:#f3f4f6;color:#6b7280;">

    <label>Member Since</label>
    <input type="text" value="<?php echo date('d M Y', strtotime($user['created_at'])); ?>"
           disabled style="background:#f3f4f6;color:#6b7280;">

    <button type="submit" class="btn btn-primary" id="submitBtn">Save Changes</button>
  </form>

  <p style="margin-top:14px;">
    <a href="/WebTech/auth/change_password.php" class="btn btn-outline">Change Password</a>
  </p>
</div>

<script src="/WebTech/assets/js/profile.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
