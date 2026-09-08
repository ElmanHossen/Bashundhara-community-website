<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireRole('community_user');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
  <h1>Change Password</h1>
  <p class="subtitle">Update your account password.</p>

  <div id="passwordAlert"></div>

  <form id="changePasswordForm">
    <label>Current Password</label>
    <input type="password" name="current_password" id="current_password" required>

    <label>New Password</label>
    <input type="password" name="new_password" id="new_password" minlength="6" required>

    <label>Confirm New Password</label>
    <input type="password" name="confirm_new_password" id="confirm_new_password" minlength="6" required>

    <button type="submit" class="btn btn-primary" id="submitBtn">Update Password</button>
  </form>

  <p style="margin-top:14px;">
    <a href="/WebTech/community/profile.php" class="btn btn-outline">Back to Profile</a>
  </p>
</div>

<script src="/WebTech/assets/js/change_password.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
