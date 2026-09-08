<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
// No login required - reached via the token link, not a login session

$token = $_GET['token'] ?? '';
$validToken = false;

if ($token !== '') {
    $tokenHash = hash('sha256', $token);

    $stmt = $pdo->prepare(
        "SELECT reset_id FROM password_resets
         WHERE token_hash = ? AND used = 0 AND expires_at > NOW()"
    );
    $stmt->execute([$tokenHash]);
    $validToken = (bool) $stmt->fetch();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
  <h1>Reset Password</h1>

  <?php if (!$validToken): ?>
    <p class="alert alert-error">
        This reset link is invalid or has expired. Please request a new one.
    </p>
    <p><a href="/WebTech/auth/forgot_password.php" class="btn btn-outline">Request New Link</a></p>

  <?php else: ?>
    <p class="subtitle">Enter a new password for your account.</p>

    <div id="resetAlert"></div>

    <form id="resetPasswordForm">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

      <label>New Password</label>
      <input type="password" name="new_password" id="new_password" minlength="6" required>

      <label>Confirm New Password</label>
      <input type="password" name="confirm_new_password" id="confirm_new_password" minlength="6" required>

      <button type="submit" class="btn btn-primary" id="submitBtn">Reset Password</button>
    </form>
  <?php endif; ?>
</div>

<script src="/WebTech/assets/js/reset_password.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
