<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $errors[] = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT user_id, name, password, role, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($pass, $user['password'])) {
            $errors[] = "Incorrect email or password.";
        } elseif ($user['status'] !== 'active') {
            $errors[] = "This account is suspended. Contact an administrator.";
        } else {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: /WebTech/community/dashboard.php");
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
  <h1>Login</h1>
  <p class="subtitle">Welcome back to NeighbourNet.</p>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?php echo htmlspecialchars($e); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <label>Email</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit" class="btn btn-primary">Login</button>
  </form>

  <p class="muted" style="margin-top:14px;">
    <a href="/WebTech/auth/forgot_password.php">Forgot your password?</a>
  </p>

  <p class="muted" style="margin-top:8px;">
    Don't have an account? <a href="/WebTech/auth/register.php">Create one</a>
  </p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
