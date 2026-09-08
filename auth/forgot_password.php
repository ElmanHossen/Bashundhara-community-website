<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
// No login required - this page must be reachable by logged-out visitors

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
  <h1>Forgot Password</h1>
  <p class="subtitle">Enter your account email and we'll generate a reset link.</p>

  <div id="resetAlert"></div>

  <form id="forgotPasswordForm">
    <label>Email</label>
    <input type="email" name="email" id="email" required>

    <button type="submit" class="btn btn-primary" id="submitBtn">Send Reset Link</button>
  </form>

  <p class="muted" style="margin-top:14px;">
    <a href="/WebTech/auth/login.php">Back to Login</a>
  </p>
</div>

<script src="/WebTech/assets/js/forgot_password.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
