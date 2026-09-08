# NeighbourNet (Bashundhara Community) — CommunityUser Module
## Complete Source Code Backup

This document contains the complete, final source code for every file in the
CommunityUser module, exactly as implemented and tested. Generated as a
full backup/reference — no functionality was changed in producing this file.

---

## 1. Project Folder / File Tree

```
WebTech/
├── index.php
├── auth/
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   ├── forgot_password.php
│   ├── reset_password.php
│   ├── change_password.php
│   └── ajax/
│       ├── request_reset.php
│       ├── reset_password.php
│       └── change_password.php
├── community/
│   ├── create_post.php
│   ├── edit_post.php
│   ├── notices.php
│   ├── events.php
│   ├── search.php
│   ├── profile.php
│   ├── dashboard.php
│   └── ajax/
│       ├── create_post.php
│       ├── edit_post.php
│       ├── delete_post.php
│       ├── toggle_like.php
│       ├── add_comment.php
│       ├── get_comments.php
│       ├── report_content.php
│       ├── get_notices.php
│       ├── get_events.php
│       ├── search.php
│       └── update_profile.php
├── config/
│   ├── db.php
│   └── session.php
├── includes/
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── create_post.js
│       ├── edit_post.js
│       ├── delete_post.js
│       ├── like_post.js
│       ├── comment_post.js
│       ├── report_content.js
│       ├── notices.js
│       ├── events.js
│       ├── search.js
│       ├── profile.js
│       ├── change_password.js
│       ├── forgot_password.js
│       └── reset_password.js
├── uploads/
│   └── posts/          (image uploads land here - starts empty)
└── sql/
    ├── schema.sql                        (master schema - fresh installs only)
    ├── migration_2_add_likes.sql
    ├── migration_3_add_comments.sql
    ├── migration_4_add_reports.sql
    ├── migration_5_add_notices.sql
    ├── migration_6_add_events.sql
    ├── migration_7_add_businesses.sql
    └── migration_8_add_password_resets.sql
```

---

## 2. Full Source Code

### `index.php`

```php
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="hero">
  <h1>Everything in your neighbourhood, in one place.</h1>
  <p>Connect with neighbours, share updates, and stay informed — Bashundhara Community.</p>

  <?php if (isLoggedIn()): ?>
    <a href="/WebTech/community/create_post.php" class="btn btn-primary">Create a Post</a>
  <?php else: ?>
    <a href="/WebTech/auth/register.php" class="btn btn-primary">Join the Community</a>
    <a href="/WebTech/auth/login.php" class="btn btn-outline">Login</a>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
```

---

### `auth/login.php`

```php
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
```

---

### `auth/logout.php`

```php
<?php
require_once __DIR__ . '/../config/session.php';

$_SESSION = [];
session_destroy();

header("Location: /WebTech/index.php");
exit;
```

---

### `auth/register.php`

```php
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = "Please enter your full name.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (strlen($pass) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($pass !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // check email not already used (prepared statement)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "An account with this email already exists.";
        }
    }

    if (empty($errors)) {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role, status)
             VALUES (?, ?, ?, 'community_user', 'active')"
        );
        $stmt->execute([$name, $email, $hashed]);
        $success = true;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
  <h1>Create Account</h1>
  <p class="subtitle">Join your neighbourhood community.</p>

  <?php if ($success): ?>
    <p class="alert alert-success">
        Account created successfully! You can now <a href="/WebTech/auth/login.php">login</a>.
    </p>
  <?php else: ?>

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
      <label>Full Name</label>
      <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>

      <label>Email</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <label>Confirm Password</label>
      <input type="password" name="confirm_password" required>

      <button type="submit" class="btn btn-primary">Register</button>
    </form>

    <p class="muted" style="margin-top:14px;">
      Already have an account? <a href="/WebTech/auth/login.php">Login here</a>
    </p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```

---

### `auth/forgot_password.php`

```php
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
```

---

### `auth/reset_password.php`

```php
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
```

---

### `auth/change_password.php`

```php
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
```

---

### `auth/ajax/request_reset.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/forgot_password.js using fetch().
//
// IMPORTANT (student-project note): this XAMPP setup has no configured
// mail server, so instead of emailing the reset link, this endpoint
// returns it directly in the JSON response for the demo to work end
// to end. In a real deployment, you would send `reset_link` via
// mail()/PHPMailer instead of returning it to the browser, and this
// response would just say "check your email" with no link included.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please enter a valid email address.';
    echo json_encode($response);
    exit;
}

// This message is always the same regardless of whether the account
// exists, to avoid revealing which emails are registered.
$genericMessage = 'If an account with that email exists, a password reset link has been generated below.';

// Only generate a real token for CommunityUser accounts - this keeps
// password reset contained to the CommunityUser module, since Admin
// and BusinessPerson aren't built yet.
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND role = 'community_user' AND status = 'active'");
$stmt->execute([$email]);
$user = $stmt->fetch();

$resetLink = null;

if ($user) {
    // Generate a random token, store only its hash (never the raw
    // token) in the database - same principle as password hashing.
    $token     = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    $insert = $pdo->prepare(
        "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)"
    );
    $insert->execute([$user['user_id'], $tokenHash, $expiresAt]);

    $resetLink = "http://localhost/WebTech/auth/reset_password.php?token=" . $token;
}

$response['success']    = true;
$response['message']    = $genericMessage;
$response['reset_link'] = $resetLink; // null if no matching CommunityUser account

echo json_encode($response);
```

---

### `auth/ajax/reset_password.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/reset_password.js using fetch().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => [], 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$token           = $_POST['token'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_new_password'] ?? '';
$errors = [];

if ($token === '') {
    $errors[] = "Invalid reset link.";
}
if (strlen($newPassword) < 6) {
    $errors[] = "New password must be at least 6 characters.";
}
if ($newPassword !== $confirmPassword) {
    $errors[] = "New password and confirmation do not match.";
}

if (!empty($errors)) {
    $response['errors']  = $errors;
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}

// ---------- Re-validate the token server-side (never trust the ----------
// ---------- fact that the page rendered a form as proof it's valid) ----------
$tokenHash = hash('sha256', $token);

$stmt = $pdo->prepare(
    "SELECT reset_id, user_id FROM password_resets
     WHERE token_hash = ? AND used = 0 AND expires_at > NOW()"
);
$stmt->execute([$tokenHash]);
$reset = $stmt->fetch();

if (!$reset) {
    $response['message'] = 'This reset link is invalid or has expired. Please request a new one.';
    echo json_encode($response);
    exit;
}

// ---------- Update the password (hashed) and mark the token used ----------
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$updateUser = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$updateUser->execute([$newHash, $reset['user_id']]);

// mark this token as used so it can never be reused, even if the
// link is reopened or shared
$markUsed = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE reset_id = ?");
$markUsed->execute([$reset['reset_id']]);

$response['success'] = true;
$response['message']  = 'Your password has been reset successfully. You can now log in.';

echo json_encode($response);
```

---

### `auth/ajax/change_password.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/change_password.js using fetch().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => [], 'message' => ''];

// ---------- Must be logged in AND be a CommunityUser ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to change your password.';
    echo json_encode($response);
    exit;
}

if (!isCommunityUser()) {
    http_response_code(403);
    $response['message'] = 'This action is only available to Community User accounts.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_new_password'] ?? '';
$errors = [];

// ---------- Basic validation ----------
if ($currentPassword === '') {
    $errors[] = "Please enter your current password.";
}
if (strlen($newPassword) < 6) {
    $errors[] = "New password must be at least 6 characters.";
}
if ($newPassword !== $confirmPassword) {
    $errors[] = "New password and confirmation do not match.";
}

if (!empty($errors)) {
    $response['errors']  = $errors;
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}

// ---------- Fetch the CURRENT user's own password hash ----------
// (always scoped to currentUserId() - never accepts a user_id from
// the form, so nobody can change someone else's password)
$stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->execute([currentUserId()]);
$user = $stmt->fetch();

if (!$user || !password_verify($currentPassword, $user['password'])) {
    $response['errors']  = ["Current password is incorrect."];
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}

// ---------- Hash and save the new password ----------
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$updateStmt->execute([$newHash, currentUserId()]);

$response['success'] = true;
$response['message']  = 'Your password has been updated successfully.';

echo json_encode($response);
```

---

### `community/create_post.php`

```php
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireRole('community_user'); // Only CommunityUser accounts may access this page

// Load categories for the dropdown (PostCategoryType values)
$categories = $pdo->query("SELECT category_id, name FROM categories ORDER BY name")->fetchAll();

// Load this user's recent posts for the initial page load
$recentStmt = $pdo->prepare(
    "SELECT p.post_id, p.title, p.content, p.image, p.created_at, c.name AS category_name,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id) AS like_count,
            (SELECT COUNT(*) FROM likes l2 WHERE l2.post_id = p.post_id AND l2.user_id = ?) AS user_liked,
            (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.post_id) AS comment_count
     FROM posts p
     JOIN categories c ON p.category_id = c.category_id
     WHERE p.user_id = ?
     ORDER BY p.created_at DESC
     LIMIT 5"
);
$recentStmt->execute([currentUserId(), currentUserId()]);
$recentPosts = $recentStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
  <h1>Create a Post</h1>
  <p class="subtitle">Share something with your neighbourhood.</p>

  <!-- Success / error messages get injected here by JavaScript -->
  <div id="postAlert"></div>

  <form id="createPostForm" enctype="multipart/form-data">
    <label>Title</label>
    <input type="text" name="title" id="title" maxlength="150" required>

    <label>Category</label>
    <select name="category_id" id="category_id" required>
      <option value="">-- Select a category --</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?php echo $cat['category_id']; ?>">
          <?php echo htmlspecialchars($cat['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>What's happening in your neighbourhood?</label>
    <textarea name="content" id="content" rows="5" required></textarea>

    <label>Photo (optional)</label>
    <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.gif,.webp">

    <button type="submit" class="btn btn-primary" id="submitBtn">Publish Post</button>
  </form>
</div>

<div class="recent-posts">
  <h2>Your Recent Posts</h2>

  <div id="recentPostsList">
    <?php if (empty($recentPosts)): ?>
      <p class="muted" id="noPostsMsg">You haven't posted anything yet — try the form above.</p>
    <?php else: ?>
      <?php foreach ($recentPosts as $p): ?>
        <div class="post-card" data-post-id="<?php echo (int) $p['post_id']; ?>">
          <div class="post-meta">
            <span class="badge"><?php echo htmlspecialchars($p['category_name']); ?></span>
            <span class="post-date"><?php echo date('d M Y, g:i A', strtotime($p['created_at'])); ?></span>
          </div>
          <h3><?php echo htmlspecialchars($p['title']); ?></h3>
          <p><?php echo nl2br(htmlspecialchars($p['content'])); ?></p>
          <?php if ($p['image']): ?>
            <img src="/WebTech/uploads/posts/<?php echo htmlspecialchars($p['image']); ?>"
                 class="post-image" alt="Post image">
          <?php endif; ?>
          <div class="post-actions">
            <button type="button" class="like-btn<?php echo $p['user_liked'] > 0 ? ' liked' : ''; ?>"
                    data-post-id="<?php echo (int) $p['post_id']; ?>">
              <span class="like-label"><?php echo $p['user_liked'] > 0 ? 'Liked' : 'Like'; ?></span>
              (<span class="like-count"><?php echo (int) $p['like_count']; ?></span>)
            </button>
            <a href="/WebTech/community/edit_post.php?post_id=<?php echo (int) $p['post_id']; ?>"
               class="btn btn-outline btn-sm">Edit</a>
            <button type="button" class="btn btn-danger btn-sm delete-post-btn"
                    data-post-id="<?php echo (int) $p['post_id']; ?>">Delete</button>
            <button type="button" class="btn btn-outline btn-sm toggle-report-btn"
                    data-target-type="post" data-target-id="<?php echo (int) $p['post_id']; ?>">Report</button>
          </div>

          <div class="report-form" id="report-post-<?php echo (int) $p['post_id']; ?>" style="display:none;">
            <select class="report-reason-select">
              <option value="Spam">Spam</option>
              <option value="Harassment">Harassment or Bullying</option>
              <option value="Inappropriate Content">Inappropriate Content</option>
              <option value="False Information">False Information</option>
              <option value="Other">Other</option>
            </select>
            <button type="button" class="btn btn-outline btn-sm submit-report-btn"
                    data-target-type="post" data-target-id="<?php echo (int) $p['post_id']; ?>">Submit Report</button>
            <div class="report-msg"></div>
          </div>

          <div class="comments-section">
            <button type="button" class="toggle-comments-btn"
                    data-post-id="<?php echo (int) $p['post_id']; ?>">
              Comments (<?php echo (int) $p['comment_count']; ?>)
            </button>
            <div class="comments-list" id="comments-<?php echo (int) $p['post_id']; ?>" style="display:none;"></div>
            <form class="add-comment-form" data-post-id="<?php echo (int) $p['post_id']; ?>">
              <input type="text" name="content" placeholder="Write a comment..." maxlength="1000" required>
              <button type="submit" class="btn btn-primary btn-sm">Post</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script src="/WebTech/assets/js/create_post.js"></script>
<script src="/WebTech/assets/js/like_post.js"></script>
<script src="/WebTech/assets/js/comment_post.js"></script>
<script src="/WebTech/assets/js/report_content.js"></script>
<script src="/WebTech/assets/js/delete_post.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```

---

### `community/edit_post.php`

```php
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireRole('community_user'); // Only CommunityUser accounts may access this page

$postId = $_GET['post_id'] ?? '';
$notFound = true;
$post = null;

// Only load the post if it exists AND belongs to the logged-in user
if (ctype_digit((string) $postId)) {
    $stmt = $pdo->prepare(
        "SELECT post_id, category_id, title, content, image
         FROM posts
         WHERE post_id = ? AND user_id = ?"
    );
    $stmt->execute([$postId, currentUserId()]);
    $post = $stmt->fetch();
    $notFound = !$post;
}

$categories = $pdo->query("SELECT category_id, name FROM categories ORDER BY name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
  <h1>Edit Post</h1>

  <?php if ($notFound): ?>
    <p class="alert alert-error">This post doesn't exist, or you don't have permission to edit it.</p>
    <p><a href="/WebTech/community/create_post.php" class="btn btn-outline">Back to Community</a></p>

  <?php else: ?>
    <p class="subtitle">Update your post below.</p>

    <div id="postAlert"></div>

    <form id="editPostForm" enctype="multipart/form-data">
      <input type="hidden" name="post_id" value="<?php echo (int) $post['post_id']; ?>">

      <label>Title</label>
      <input type="text" name="title" id="title" maxlength="150"
             value="<?php echo htmlspecialchars($post['title']); ?>" required>

      <label>Category</label>
      <select name="category_id" id="category_id" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?php echo $cat['category_id']; ?>"
            <?php echo ($cat['category_id'] == $post['category_id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($cat['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>Content</label>
      <textarea name="content" id="content" rows="5" required><?php echo htmlspecialchars($post['content']); ?></textarea>

      <?php if ($post['image']): ?>
        <label>Current Photo</label>
        <img src="/WebTech/uploads/posts/<?php echo htmlspecialchars($post['image']); ?>"
             class="post-image" style="max-width:200px;display:block;margin-bottom:10px;">
        <label style="font-weight:400;">
          <input type="checkbox" name="remove_image" value="1"
                 style="width:auto;display:inline-block;margin-right:6px;">
          Remove current photo
        </label>
      <?php endif; ?>

      <label>Replace Photo (optional)</label>
      <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.gif,.webp">

      <button type="submit" class="btn btn-primary" id="submitBtn">Save Changes</button>
    </form>

    <p style="margin-top:14px;">
      <a href="/WebTech/community/create_post.php" class="btn btn-outline">Back to Community</a>
    </p>
  <?php endif; ?>
</div>

<script src="/WebTech/assets/js/edit_post.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```

---

### `community/notices.php`

```php
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireRole('community_user'); // Only CommunityUser accounts may access this page

// Initial load: all notices, most urgent priority first, newest first
$stmt = $pdo->query(
    "SELECT notice_id, title, content, priority, location, created_at, expiry_date
     FROM notices
     ORDER BY FIELD(priority, 'Critical', 'Important', 'General'), created_at DESC"
);
$notices = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="max-width:700px;">
  <h1>Notices</h1>
  <p class="subtitle">Stay informed about important community notices.</p>

  <label>Filter by Priority</label>
  <select id="priorityFilter">
    <option value="">All Priorities</option>
    <option value="Critical">Critical</option>
    <option value="Important">Important</option>
    <option value="General">General</option>
  </select>
</div>

<div class="notices-list" id="noticesList" style="max-width:700px;margin:0 auto;">
  <?php if (empty($notices)): ?>
    <p class="muted">No notices at this time.</p>
  <?php else: ?>
    <?php foreach ($notices as $n): ?>
      <div class="post-card">
        <div class="post-meta">
          <span class="badge badge-<?php echo strtolower($n['priority']); ?>">
            <?php echo htmlspecialchars($n['priority']); ?>
          </span>
          <span class="post-date"><?php echo date('d M Y', strtotime($n['created_at'])); ?></span>
        </div>
        <h3><?php echo htmlspecialchars($n['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($n['content'])); ?></p>
        <p class="muted" style="margin-top:6px;">📍 <?php echo htmlspecialchars($n['location']); ?></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script src="/WebTech/assets/js/notices.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```

---

### `community/events.php`

```php
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireRole('community_user'); // Only CommunityUser accounts may access this page

// Initial load: upcoming events only, soonest first
$stmt = $pdo->query(
    "SELECT event_id, title, description, event_date, event_time, location
     FROM events
     WHERE event_date >= CURDATE()
     ORDER BY event_date ASC, event_time ASC"
);
$events = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="max-width:700px;">
  <h1>Events</h1>
  <p class="subtitle">Discover upcoming events in your neighbourhood.</p>

  <label>Show</label>
  <select id="eventFilter">
    <option value="upcoming">Upcoming Events</option>
    <option value="past">Past Events</option>
    <option value="all">All Events</option>
  </select>
</div>

<div class="notices-list" id="eventsList" style="max-width:700px;margin:0 auto;">
  <?php if (empty($events)): ?>
    <p class="muted">No upcoming events at this time.</p>
  <?php else: ?>
    <?php foreach ($events as $ev): ?>
      <div class="post-card">
        <div class="post-meta">
          <span class="badge badge-general">Event</span>
          <span class="post-date">
            <?php echo date('d M Y', strtotime($ev['event_date'])); ?>,
            <?php echo date('g:i A', strtotime($ev['event_time'])); ?>
          </span>
        </div>
        <h3><?php echo htmlspecialchars($ev['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($ev['description'])); ?></p>
        <p class="muted" style="margin-top:6px;">📍 <?php echo htmlspecialchars($ev['location']); ?></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script src="/WebTech/assets/js/events.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```

---

### `community/search.php`

```php
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireRole('community_user'); // Only CommunityUser accounts may access this page

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="max-width:700px;">
  <h1>Search NeighbourNet</h1>
  <p class="subtitle">Search posts, notices &amp; events, businesses, and community members.</p>

  <label>Search in</label>
  <select id="searchType">
    <option value="posts">Posts</option>
    <option value="notices_events">Notices &amp; Events</option>
    <option value="businesses">Businesses</option>
    <option value="members">Community Members</option>
  </select>

  <label>Keyword</label>
  <input type="text" id="searchQuery" placeholder="Type at least 2 characters...">

  <button type="button" id="searchBtn" class="btn btn-primary" style="margin-top:14px;">Search</button>
</div>

<div id="searchResults" style="max-width:700px;margin:0 auto;"></div>

<script src="/WebTech/assets/js/search.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```

---

### `community/profile.php`

```php
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
```

---

### `community/dashboard.php`

```php
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireRole('community_user'); // Only CommunityUser accounts may access this page

$userId = currentUserId();

// ---------- Basic profile info ----------
$userStmt = $pdo->prepare("SELECT name, email, created_at FROM users WHERE user_id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

// ---------- Total posts created ----------
$postCountStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM posts WHERE user_id = ?");
$postCountStmt->execute([$userId]);
$postCount = (int) $postCountStmt->fetch()['total'];

// ---------- Total likes received across all of the user's posts ----------
$likesReceivedStmt = $pdo->prepare(
    "SELECT COUNT(*) AS total
     FROM likes l
     JOIN posts p ON l.post_id = p.post_id
     WHERE p.user_id = ?"
);
$likesReceivedStmt->execute([$userId]);
$likesReceived = (int) $likesReceivedStmt->fetch()['total'];

// ---------- Total comments received across all of the user's posts ----------
$commentsReceivedStmt = $pdo->prepare(
    "SELECT COUNT(*) AS total
     FROM comments c
     JOIN posts p ON c.post_id = p.post_id
     WHERE p.user_id = ?"
);
$commentsReceivedStmt->execute([$userId]);
$commentsReceived = (int) $commentsReceivedStmt->fetch()['total'];

// ---------- Recent activity: last 5 posts with their like/comment counts ----------
$recentStmt = $pdo->prepare(
    "SELECT p.post_id, p.title, p.created_at, c.name AS category_name,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id) AS like_count,
            (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.post_id) AS comment_count
     FROM posts p
     JOIN categories c ON p.category_id = c.category_id
     WHERE p.user_id = ?
     ORDER BY p.created_at DESC
     LIMIT 5"
);
$recentStmt->execute([$userId]);
$recentActivity = $recentStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="max-width:700px;">
  <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?> 👋</h1>
  <p class="subtitle">
    <?php echo htmlspecialchars($user['email']); ?> ·
    Community Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
  </p>
</div>

<div class="stats-grid" style="max-width:700px;margin:0 auto 24px;">
  <div class="stat-box">
    <div class="stat-number"><?php echo $postCount; ?></div>
    <div class="stat-label">Posts Created</div>
  </div>
  <div class="stat-box">
    <div class="stat-number"><?php echo $likesReceived; ?></div>
    <div class="stat-label">Likes Received</div>
  </div>
  <div class="stat-box">
    <div class="stat-number"><?php echo $commentsReceived; ?></div>
    <div class="stat-label">Comments Received</div>
  </div>
</div>

<div class="form-card" style="max-width:700px;">
  <h2 style="font-size:18px;margin-top:0;">Quick Links</h2>
  <div class="quick-links">
    <a href="/WebTech/community/create_post.php" class="btn btn-primary btn-sm">Create Post</a>
    <a href="/WebTech/community/notices.php" class="btn btn-outline btn-sm">Notices</a>
    <a href="/WebTech/community/events.php" class="btn btn-outline btn-sm">Events</a>
    <a href="/WebTech/community/search.php" class="btn btn-outline btn-sm">Search</a>
    <a href="/WebTech/community/profile.php" class="btn btn-outline btn-sm">Profile</a>
    <a href="/WebTech/auth/change_password.php" class="btn btn-outline btn-sm">Change Password</a>
    <a href="/WebTech/auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</div>

<div class="notices-list" style="max-width:700px;margin:0 auto;">
  <h2 style="font-size:18px;">Recent Activity</h2>

  <?php if (empty($recentActivity)): ?>
    <p class="muted">You haven't posted anything yet. <a href="/WebTech/community/create_post.php">Create your first post</a>.</p>
  <?php else: ?>
    <?php foreach ($recentActivity as $a): ?>
      <div class="post-card">
        <div class="post-meta">
          <span class="badge badge-general"><?php echo htmlspecialchars($a['category_name']); ?></span>
          <span class="post-date"><?php echo date('d M Y', strtotime($a['created_at'])); ?></span>
        </div>
        <h3><?php echo htmlspecialchars($a['title']); ?></h3>
        <p class="muted">
          👍 <?php echo (int) $a['like_count']; ?> likes ·
          💬 <?php echo (int) $a['comment_count']; ?> comments
        </p>
        <div class="post-actions">
          <a href="/WebTech/community/edit_post.php?post_id=<?php echo (int) $a['post_id']; ?>"
             class="btn btn-outline btn-sm">Edit</a>
        </div>
      </div>
    <?php endforeach; ?>
    <p class="muted"><a href="/WebTech/community/create_post.php">View all your posts →</a></p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```

---

### `community/ajax/create_post.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/create_post.js using fetch().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => [], 'message' => ''];

// ---------- Must be logged in ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to create a post.';
    echo json_encode($response);
    exit;
}

if (!isCommunityUser()) {
    http_response_code(403);
    $response['message'] = 'This action is only available to Community User accounts.';
    echo json_encode($response);
    exit;
}

// ---------- Must be a POST request ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$errors      = [];
$title       = trim($_POST['title'] ?? '');
$content     = trim($_POST['content'] ?? '');
$category_id = $_POST['category_id'] ?? '';
$categoryRow = null;

// ---------- Validate text fields ----------
if ($title === '' || mb_strlen($title) > 150) {
    $errors[] = "Title is required (max 150 characters).";
}
if ($content === '') {
    $errors[] = "Post content cannot be empty.";
}
if (!ctype_digit((string) $category_id)) {
    $errors[] = "Please select a valid category.";
} else {
    $catCheck = $pdo->prepare("SELECT category_id, name FROM categories WHERE category_id = ?");
    $catCheck->execute([$category_id]);
    $categoryRow = $catCheck->fetch();
    if (!$categoryRow) {
        $errors[] = "Selected category does not exist.";
    }
}

// ---------- Validate + handle optional image ----------
$imageFileName = null;
if (!empty($_FILES['image']['name'])) {
    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "There was a problem uploading the image.";
    } else {
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mime, $allowedTypes)) {
            $errors[] = "Image must be a JPG, PNG, GIF, or WEBP file.";
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            $errors[] = "Image must be smaller than 5MB.";
        } else {
            $ext = $allowedTypes[$mime];
            $imageFileName = 'post_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destDir = __DIR__ . '/../../uploads/posts/';
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            if (!move_uploaded_file($file['tmp_name'], $destDir . $imageFileName)) {
                $errors[] = "Failed to save the uploaded image. Please try again.";
                $imageFileName = null;
            }
        }
    }
}

// ---------- Stop here if anything failed validation ----------
if (!empty($errors)) {
    $response['errors']  = $errors;
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}

// ---------- Insert into DB (prepared statement) ----------
$stmt = $pdo->prepare(
    "INSERT INTO posts (user_id, category_id, title, content, image)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->execute([
    currentUserId(),
    $category_id,
    $title,
    $content,
    $imageFileName,
]);
$postId = $pdo->lastInsertId();

// ---------- Build a ready-to-insert HTML snippet for the new post ----------
$imageHtml = $imageFileName
    ? '<img src="/WebTech/uploads/posts/' . htmlspecialchars($imageFileName) . '" class="post-image" alt="Post image">'
    : '';

$postHtml = '
<div class="post-card" data-post-id="' . $postId . '">
  <div class="post-meta">
    <span class="badge">' . htmlspecialchars($categoryRow['name']) . '</span>
    <span class="post-date">' . date('d M Y, g:i A') . '</span>
  </div>
  <h3>' . htmlspecialchars($title) . '</h3>
  <p>' . nl2br(htmlspecialchars($content)) . '</p>
  ' . $imageHtml . '
  <div class="post-actions">
    <button type="button" class="like-btn" data-post-id="' . $postId . '">
      <span class="like-label">Like</span> (<span class="like-count">0</span>)
    </button>
    <a href="/WebTech/community/edit_post.php?post_id=' . $postId . '" class="btn btn-outline btn-sm">Edit</a>
    <button type="button" class="btn btn-danger btn-sm delete-post-btn" data-post-id="' . $postId . '">Delete</button>
    <button type="button" class="btn btn-outline btn-sm toggle-report-btn"
            data-target-type="post" data-target-id="' . $postId . '">Report</button>
  </div>

  <div class="report-form" id="report-post-' . $postId . '" style="display:none;">
    <select class="report-reason-select">
      <option value="Spam">Spam</option>
      <option value="Harassment">Harassment or Bullying</option>
      <option value="Inappropriate Content">Inappropriate Content</option>
      <option value="False Information">False Information</option>
      <option value="Other">Other</option>
    </select>
    <button type="button" class="btn btn-outline btn-sm submit-report-btn"
            data-target-type="post" data-target-id="' . $postId . '">Submit Report</button>
    <div class="report-msg"></div>
  </div>

  <div class="comments-section">
    <button type="button" class="toggle-comments-btn" data-post-id="' . $postId . '">Comments (0)</button>
    <div class="comments-list" id="comments-' . $postId . '" style="display:none;"></div>
    <form class="add-comment-form" data-post-id="' . $postId . '">
      <input type="text" name="content" placeholder="Write a comment..." maxlength="1000" required>
      <button type="submit" class="btn btn-primary btn-sm">Post</button>
    </form>
  </div>
</div>';

$response['success'] = true;
$response['message'] = 'Your post was published successfully!';
$response['post'] = [
    'post_id' => $postId,
    'html'    => $postHtml,
];

echo json_encode($response);
```

---

### `community/ajax/edit_post.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/edit_post.js using fetch().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => [], 'message' => ''];

// ---------- Must be logged in ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to edit a post.';
    echo json_encode($response);
    exit;
}

if (!isCommunityUser()) {
    http_response_code(403);
    $response['message'] = 'This action is only available to Community User accounts.';
    echo json_encode($response);
    exit;
}

// ---------- Must be a POST request ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$postId      = $_POST['post_id'] ?? '';
$title       = trim($_POST['title'] ?? '');
$content     = trim($_POST['content'] ?? '');
$category_id = $_POST['category_id'] ?? '';
$removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';

$errors = [];
$existingPost = null;

// ---------- Confirm the post exists AND belongs to this logged-in user ----------
// (never trust a hidden form field alone - always re-check ownership server-side)
if (!ctype_digit((string) $postId)) {
    $errors[] = "Invalid post.";
} else {
    $ownCheck = $pdo->prepare("SELECT post_id, image FROM posts WHERE post_id = ? AND user_id = ?");
    $ownCheck->execute([$postId, currentUserId()]);
    $existingPost = $ownCheck->fetch();
    if (!$existingPost) {
        $errors[] = "Post not found, or you don't have permission to edit it.";
    }
}

// ---------- Validate text fields ----------
if ($title === '' || mb_strlen($title) > 150) {
    $errors[] = "Title is required (max 150 characters).";
}
if ($content === '') {
    $errors[] = "Post content cannot be empty.";
}

if (!ctype_digit((string) $category_id)) {
    $errors[] = "Please select a valid category.";
} else {
    $catCheck = $pdo->prepare("SELECT category_id FROM categories WHERE category_id = ?");
    $catCheck->execute([$category_id]);
    if (!$catCheck->fetch()) {
        $errors[] = "Selected category does not exist.";
    }
}

// Stop here if ownership/basic validation already failed - avoid touching any files
if (!empty($errors)) {
    $response['errors']  = $errors;
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}

$imageFileName = $existingPost['image']; // keep the current image unless changed below
$destDir = __DIR__ . '/../../uploads/posts/';

// ---------- Handle a new image upload (replaces the old one) ----------
if (!empty($_FILES['image']['name'])) {
    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "There was a problem uploading the image.";
    } else {
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mime, $allowedTypes)) {
            $errors[] = "Image must be a JPG, PNG, GIF, or WEBP file.";
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            $errors[] = "Image must be smaller than 5MB.";
        } else {
            $ext = $allowedTypes[$mime];
            $newFileName = 'post_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            if (!move_uploaded_file($file['tmp_name'], $destDir . $newFileName)) {
                $errors[] = "Failed to save the uploaded image. Please try again.";
            } else {
                // remove the old image file from disk, if there was one
                if ($imageFileName && is_file($destDir . $imageFileName)) {
                    unlink($destDir . $imageFileName);
                }
                $imageFileName = $newFileName;
            }
        }
    }
} elseif ($removeImage && $imageFileName) {
    // user checked "remove current photo" and did not upload a replacement
    if (is_file($destDir . $imageFileName)) {
        unlink($destDir . $imageFileName);
    }
    $imageFileName = null;
}

if (!empty($errors)) {
    $response['errors']  = $errors;
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}

// ---------- Update the post (prepared statement, scoped to this user) ----------
$stmt = $pdo->prepare(
    "UPDATE posts
     SET title = ?, content = ?, category_id = ?, image = ?
     WHERE post_id = ? AND user_id = ?"
);
$stmt->execute([$title, $content, $category_id, $imageFileName, $postId, currentUserId()]);

$response['success'] = true;
$response['message']  = 'Your post was updated successfully!';
$response['post'] = [
    'post_id' => $postId,
    'image'   => $imageFileName,
];

echo json_encode($response);
```

---

### `community/ajax/delete_post.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/delete_post.js using fetch().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// ---------- Must be logged in ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to delete a post.';
    echo json_encode($response);
    exit;
}

if (!isCommunityUser()) {
    http_response_code(403);
    $response['message'] = 'This action is only available to Community User accounts.';
    echo json_encode($response);
    exit;
}

// ---------- Must be a POST request ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$postId = $_POST['post_id'] ?? '';

if (!ctype_digit((string) $postId)) {
    $response['message'] = 'Invalid post.';
    echo json_encode($response);
    exit;
}

// ---------- Confirm the post exists AND belongs to this user ----------
// (never trust post_id alone - always check ownership server-side)
$stmt = $pdo->prepare("SELECT image FROM posts WHERE post_id = ? AND user_id = ?");
$stmt->execute([$postId, currentUserId()]);
$post = $stmt->fetch();

if (!$post) {
    $response['message'] = 'Post not found, or you do not have permission to delete it.';
    echo json_encode($response);
    exit;
}

// ---------- Delete the post (prepared statement, scoped to this user) ----------
$deleteStmt = $pdo->prepare("DELETE FROM posts WHERE post_id = ? AND user_id = ?");
$deleteStmt->execute([$postId, currentUserId()]);

// ---------- Clean up the uploaded image file, if any ----------
if ($post['image']) {
    $imagePath = __DIR__ . '/../../uploads/posts/' . $post['image'];
    if (is_file($imagePath)) {
        unlink($imagePath);
    }
}

$response['success'] = true;
$response['message'] = 'Post deleted successfully.';
$response['post_id'] = (int) $postId;

echo json_encode($response);
```

---

### `community/ajax/toggle_like.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/like_post.js using fetch().
// Toggles: if the user already liked this post, unlike it; otherwise, like it.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// ---------- Must be logged in ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to like a post.';
    echo json_encode($response);
    exit;
}

if (!isCommunityUser()) {
    http_response_code(403);
    $response['message'] = 'This action is only available to Community User accounts.';
    echo json_encode($response);
    exit;
}

// ---------- Must be a POST request ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$postId = $_POST['post_id'] ?? '';

if (!ctype_digit((string) $postId)) {
    $response['message'] = 'Invalid post.';
    echo json_encode($response);
    exit;
}

// ---------- Confirm the post actually exists ----------
$postCheck = $pdo->prepare("SELECT post_id FROM posts WHERE post_id = ?");
$postCheck->execute([$postId]);
if (!$postCheck->fetch()) {
    $response['message'] = 'Post not found.';
    echo json_encode($response);
    exit;
}

$userId = currentUserId();

// ---------- Check if this user already liked this post ----------
$likeCheck = $pdo->prepare("SELECT like_id FROM likes WHERE post_id = ? AND user_id = ?");
$likeCheck->execute([$postId, $userId]);
$existingLike = $likeCheck->fetch();

if ($existingLike) {
    // Already liked -> remove the like (unlike)
    $del = $pdo->prepare("DELETE FROM likes WHERE like_id = ?");
    $del->execute([$existingLike['like_id']]);
    $liked = false;
} else {
    // Not liked yet -> add the like
    $ins = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $ins->execute([$postId, $userId]);
    $liked = true;
}

// ---------- Return the updated total like count ----------
$countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM likes WHERE post_id = ?");
$countStmt->execute([$postId]);
$count = $countStmt->fetch()['total'];

$response['success']    = true;
$response['liked']      = $liked;
$response['like_count'] = (int) $count;

echo json_encode($response);
```

---

### `community/ajax/add_comment.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/comment_post.js using fetch().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => [], 'message' => ''];

// ---------- Must be logged in ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to comment.';
    echo json_encode($response);
    exit;
}

if (!isCommunityUser()) {
    http_response_code(403);
    $response['message'] = 'This action is only available to Community User accounts.';
    echo json_encode($response);
    exit;
}

// ---------- Must be a POST request ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$postId  = $_POST['post_id'] ?? '';
$content = trim($_POST['content'] ?? '');
$errors  = [];

// ---------- Confirm the post actually exists ----------
if (!ctype_digit((string) $postId)) {
    $errors[] = "Invalid post.";
} else {
    $postCheck = $pdo->prepare("SELECT post_id FROM posts WHERE post_id = ?");
    $postCheck->execute([$postId]);
    if (!$postCheck->fetch()) {
        $errors[] = "Post not found.";
    }
}

// ---------- Validate comment content ----------
if ($content === '') {
    $errors[] = "Comment cannot be empty.";
} elseif (mb_strlen($content) > 1000) {
    $errors[] = "Comment is too long (max 1000 characters).";
}

if (!empty($errors)) {
    $response['errors']  = $errors;
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}

// ---------- Insert the comment (prepared statement) ----------
$stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
$stmt->execute([$postId, currentUserId(), $content]);
$commentId = $pdo->lastInsertId();

// ---------- Get the updated total comment count for this post ----------
$countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM comments WHERE post_id = ?");
$countStmt->execute([$postId]);
$count = $countStmt->fetch()['total'];

$response['success'] = true;
$response['message']  = 'Comment posted.';
$response['comment'] = [
    'comment_id'     => $commentId,
    'content'        => $content,
    'commenter_name' => currentUserName(),
    'created_at'     => date('d M Y, g:i A'),
];
$response['comment_count'] = (int) $count;

echo json_encode($response);
```

---

### `community/ajax/get_comments.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/comment_post.js using fetch().
// This is a read-only lookup, so it does not require login -
// anyone viewing the community feed can read comments.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'comments' => []];

$postId = $_GET['post_id'] ?? '';

if (!ctype_digit((string) $postId)) {
    $response['message'] = 'Invalid post.';
    echo json_encode($response);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT c.comment_id, c.content, c.created_at, u.name AS commenter_name
     FROM comments c
     JOIN users u ON c.user_id = u.user_id
     WHERE c.post_id = ?
     ORDER BY c.created_at ASC"
);
$stmt->execute([$postId]);
$rows = $stmt->fetchAll();

// Format the date server-side so the JS doesn't need its own date logic
$comments = [];
foreach ($rows as $row) {
    $comments[] = [
        'comment_id'     => $row['comment_id'],
        'content'        => $row['content'],
        'commenter_name' => $row['commenter_name'],
        'created_at'     => date('d M Y, g:i A', strtotime($row['created_at'])),
    ];
}

$response['success']  = true;
$response['comments'] = $comments;

echo json_encode($response);
```

---

### `community/ajax/report_content.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/report_content.js using fetch().
// Implements CommunityUser.reportContent() / Report.submitReport() from the UML.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// ---------- Must be logged in ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to report content.';
    echo json_encode($response);
    exit;
}

if (!isCommunityUser()) {
    http_response_code(403);
    $response['message'] = 'This action is only available to Community User accounts.';
    echo json_encode($response);
    exit;
}

// ---------- Must be a POST request ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$targetType = $_POST['target_type'] ?? '';
$targetId   = $_POST['target_id'] ?? '';
$reason     = trim($_POST['reason'] ?? '');

// ---------- Validate target type (post XOR comment - never both) ----------
$allowedTypes = ['post', 'comment'];
if (!in_array($targetType, $allowedTypes, true)) {
    $response['message'] = 'Invalid report target.';
    echo json_encode($response);
    exit;
}

if (!ctype_digit((string) $targetId)) {
    $response['message'] = 'Invalid content to report.';
    echo json_encode($response);
    exit;
}

// ---------- Validate reason against a fixed list (dropdown-driven) ----------
$allowedReasons = ['Spam', 'Harassment', 'Inappropriate Content', 'False Information', 'Other'];
if (!in_array($reason, $allowedReasons, true)) {
    $response['message'] = 'Please select a valid reason.';
    echo json_encode($response);
    exit;
}

// ---------- Confirm the content being reported actually exists ----------
$postId    = null;
$commentId = null;

if ($targetType === 'post') {
    $check = $pdo->prepare("SELECT post_id FROM posts WHERE post_id = ?");
    $check->execute([$targetId]);
    if (!$check->fetch()) {
        $response['message'] = 'This post no longer exists.';
        echo json_encode($response);
        exit;
    }
    $postId = $targetId;
} else {
    $check = $pdo->prepare("SELECT comment_id FROM comments WHERE comment_id = ?");
    $check->execute([$targetId]);
    if (!$check->fetch()) {
        $response['message'] = 'This comment no longer exists.';
        echo json_encode($response);
        exit;
    }
    $commentId = $targetId;
}

// ---------- Insert the report (prepared statement) ----------
try {
    $stmt = $pdo->prepare(
        "INSERT INTO reports (user_id, post_id, comment_id, reason, status)
         VALUES (?, ?, ?, ?, 'pending')"
    );
    $stmt->execute([currentUserId(), $postId, $commentId, $reason]);

    $response['success'] = true;
    $response['message']  = 'Thank you. Your report has been submitted and will be reviewed by our team.';
} catch (PDOException $e) {
    // SQLSTATE 23000 = integrity constraint violation
    // (in this case: the unique_post_report / unique_comment_report key,
    // meaning this user already reported this exact content)
    if ($e->getCode() === '23000') {
        $response['message'] = 'You have already reported this content.';
    } else {
        $response['message'] = 'Something went wrong while submitting your report.';
    }
}

echo json_encode($response);
```

---

### `community/ajax/get_notices.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/notices.js using fetch().
// Read-only browsing endpoint for CommunityUser.browseNotices().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'notices' => []];

$priority = $_GET['priority'] ?? '';
$allowedPriorities = ['General', 'Important', 'Critical'];

if ($priority !== '' && !in_array($priority, $allowedPriorities, true)) {
    $response['message'] = 'Invalid priority filter.';
    echo json_encode($response);
    exit;
}

if ($priority === '') {
    $stmt = $pdo->query(
        "SELECT notice_id, title, content, priority, location, created_at
         FROM notices
         ORDER BY FIELD(priority, 'Critical', 'Important', 'General'), created_at DESC"
    );
    $notices = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare(
        "SELECT notice_id, title, content, priority, location, created_at
         FROM notices
         WHERE priority = ?
         ORDER BY created_at DESC"
    );
    $stmt->execute([$priority]);
    $notices = $stmt->fetchAll();
}

// Format the date server-side so the JS doesn't need its own date logic
$formatted = [];
foreach ($notices as $n) {
    $formatted[] = [
        'notice_id' => $n['notice_id'],
        'title'     => $n['title'],
        'content'   => $n['content'],
        'priority'  => $n['priority'],
        'location'  => $n['location'],
        'created_at' => date('d M Y', strtotime($n['created_at'])),
    ];
}

$response['success'] = true;
$response['notices'] = $formatted;

echo json_encode($response);
```

---

### `community/ajax/get_events.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/events.js using fetch().
// Read-only browsing endpoint for CommunityUser.browseEvents().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'events' => []];

$filter = $_GET['filter'] ?? 'upcoming';
$allowedFilters = ['upcoming', 'past', 'all'];

if (!in_array($filter, $allowedFilters, true)) {
    $response['message'] = 'Invalid filter.';
    echo json_encode($response);
    exit;
}

if ($filter === 'upcoming') {
    $stmt = $pdo->prepare(
        "SELECT event_id, title, description, event_date, event_time, location
         FROM events
         WHERE event_date >= CURDATE()
         ORDER BY event_date ASC, event_time ASC"
    );
    $stmt->execute();
} elseif ($filter === 'past') {
    $stmt = $pdo->prepare(
        "SELECT event_id, title, description, event_date, event_time, location
         FROM events
         WHERE event_date < CURDATE()
         ORDER BY event_date DESC, event_time DESC"
    );
    $stmt->execute();
} else { // all
    $stmt = $pdo->prepare(
        "SELECT event_id, title, description, event_date, event_time, location
         FROM events
         ORDER BY event_date ASC, event_time ASC"
    );
    $stmt->execute();
}

$events = $stmt->fetchAll();

// Format the date/time server-side so the JS doesn't need its own date logic
$formatted = [];
foreach ($events as $ev) {
    $formatted[] = [
        'event_id'    => $ev['event_id'],
        'title'       => $ev['title'],
        'description' => $ev['description'],
        'location'    => $ev['location'],
        'date_display' => date('d M Y', strtotime($ev['event_date'])) . ', ' .
                          date('g:i A', strtotime($ev['event_time'])),
    ];
}

$response['success'] = true;
$response['events'] = $formatted;

echo json_encode($response);
```

---

### `community/ajax/search.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/search.js using fetch().
// Implements CommunityUser.searchContent() from the UML, split across
// the 4 search targets named in the project proposal.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'results' => [], 'message' => ''];

// ---------- Must be logged in ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to search.';
    echo json_encode($response);
    exit;
}

if (!isCommunityUser()) {
    http_response_code(403);
    $response['message'] = 'This action is only available to Community User accounts.';
    echo json_encode($response);
    exit;
}

$type = $_GET['type'] ?? '';
$q    = trim($_GET['q'] ?? '');

$allowedTypes = ['posts', 'notices_events', 'businesses', 'members'];
if (!in_array($type, $allowedTypes, true)) {
    $response['message'] = 'Invalid search type.';
    echo json_encode($response);
    exit;
}

if (mb_strlen($q) < 2) {
    $response['message'] = 'Please enter at least 2 characters to search.';
    echo json_encode($response);
    exit;
}

$like = '%' . $q . '%';
$results = [];

switch ($type) {

    // -------------------------------------------------------
    // Search Posts - across ALL community members' posts,
    // not just the logged-in user's own (unlike create_post.php)
    // -------------------------------------------------------
    case 'posts':
        $stmt = $pdo->prepare(
            "SELECT p.title, p.content, p.created_at, u.name AS author_name, c.name AS category_name
             FROM posts p
             JOIN users u ON p.user_id = u.user_id
             JOIN categories c ON p.category_id = c.category_id
             WHERE p.title LIKE ? OR p.content LIKE ?
             ORDER BY p.created_at DESC
             LIMIT 20"
        );
        $stmt->execute([$like, $like]);

        foreach ($stmt->fetchAll() as $r) {
            $results[] = [
                'type'    => 'Post',
                'title'   => $r['title'],
                'snippet' => mb_substr($r['content'], 0, 150),
                'meta'    => 'By ' . $r['author_name'] . ' · ' . $r['category_name'] .
                             ' · ' . date('d M Y', strtotime($r['created_at'])),
            ];
        }
        break;

    // -------------------------------------------------------
    // Search Notices/Events - combined results from both tables
    // -------------------------------------------------------
    case 'notices_events':
        $noticeStmt = $pdo->prepare(
            "SELECT title, content, priority, location
             FROM notices
             WHERE title LIKE ? OR content LIKE ?
             ORDER BY created_at DESC
             LIMIT 10"
        );
        $noticeStmt->execute([$like, $like]);
        foreach ($noticeStmt->fetchAll() as $r) {
            $results[] = [
                'type'    => 'Notice',
                'title'   => $r['title'],
                'snippet' => mb_substr($r['content'], 0, 150),
                'meta'    => $r['priority'] . ' · ' . $r['location'],
            ];
        }

        $eventStmt = $pdo->prepare(
            "SELECT title, description, event_date, event_time, location
             FROM events
             WHERE title LIKE ? OR description LIKE ?
             ORDER BY event_date ASC
             LIMIT 10"
        );
        $eventStmt->execute([$like, $like]);
        foreach ($eventStmt->fetchAll() as $r) {
            $results[] = [
                'type'    => 'Event',
                'title'   => $r['title'],
                'snippet' => mb_substr($r['description'], 0, 150),
                'meta'    => date('d M Y', strtotime($r['event_date'])) . ' · ' . $r['location'],
            ];
        }
        break;

    // -------------------------------------------------------
    // Search Businesses - only shows approved listings
    // -------------------------------------------------------
    case 'businesses':
        $stmt = $pdo->prepare(
            "SELECT name, category, address, opening_hours
             FROM businesses
             WHERE status = 'approved'
               AND (name LIKE ? OR category LIKE ? OR address LIKE ?)
             ORDER BY name ASC
             LIMIT 20"
        );
        $stmt->execute([$like, $like, $like]);

        foreach ($stmt->fetchAll() as $r) {
            $results[] = [
                'type'    => 'Business',
                'title'   => $r['name'],
                'snippet' => $r['category'] . ' — ' . $r['address'],
                'meta'    => $r['opening_hours'] ?? '',
            ];
        }
        break;

    // -------------------------------------------------------
    // Search Community Members - name only, no email/password
    // exposed, admins excluded from public search results
    // -------------------------------------------------------
    case 'members':
        $stmt = $pdo->prepare(
            "SELECT name, role, created_at
             FROM users
             WHERE role != 'admin' AND status = 'active' AND name LIKE ?
             ORDER BY name ASC
             LIMIT 20"
        );
        $stmt->execute([$like]);

        foreach ($stmt->fetchAll() as $r) {
            $roleLabel = ($r['role'] === 'business_person') ? 'Business Owner' : 'Community Member';
            $results[] = [
                'type'    => 'Member',
                'title'   => $r['name'],
                'snippet' => $roleLabel,
                'meta'    => 'Member since ' . date('M Y', strtotime($r['created_at'])),
            ];
        }
        break;
}

$response['success'] = true;
$response['results'] = $results;

echo json_encode($response);
```

---

### `community/ajax/update_profile.php`

```php
<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/profile.js using fetch().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => [], 'message' => ''];

// ---------- Must be logged in AND be a CommunityUser ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to update your profile.';
    echo json_encode($response);
    exit;
}

if (!isCommunityUser()) {
    http_response_code(403);
    $response['message'] = 'This action is only available to Community User accounts.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$errors = [];

// ---------- Validate ----------
if ($name === '' || mb_strlen($name) > 100) {
    $errors[] = "Name is required (max 100 characters).";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors[] = "Please enter a valid email address.";
}

// Check the new email isn't already used by a DIFFERENT account
if (empty($errors)) {
    $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $check->execute([$email, currentUserId()]);
    if ($check->fetch()) {
        $errors[] = "That email is already in use by another account.";
    }
}

if (!empty($errors)) {
    $response['errors']  = $errors;
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}

// ---------- Update (prepared statement, ALWAYS scoped to the ----------
// ---------- logged-in user's own ID - never trusts a submitted ----------
// ---------- user_id, so nobody can edit someone else's profile) ----------
$stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
$stmt->execute([$name, $email, currentUserId()]);

// Keep the session in sync so the header/nav reflects the new name
// immediately, without requiring the user to log out and back in.
$_SESSION['user_name'] = $name;

$response['success'] = true;
$response['message']  = 'Profile updated successfully.';
$response['name']     = $name;
$response['email']    = $email;

echo json_encode($response);
```

---

### `config/db.php`

```php
<?php
// Database connection using PDO (default XAMPP MySQL credentials)
$host   = 'localhost';
$dbname = 'webtech_community';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass
    );
    // Throw exceptions on SQL errors instead of failing silently
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
```

---

### `config/session.php`

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Is anyone logged in right now?
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUserName() {
    return $_SESSION['user_name'] ?? null;
}

function currentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

// Call this at the top of any page that requires login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /WebTech/auth/login.php");
        exit;
    }
}

// Call this at the top of any page that requires a SPECIFIC role.
// Redirects to login if not logged in at all; shows a 403 page if
// logged in as the wrong role (e.g. an Admin or BusinessPerson
// account trying to open a CommunityUser page).
function requireRole($role) {
    requireLogin();

    if (currentUserRole() !== $role) {
        http_response_code(403);
        echo '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body style="font-family:sans-serif;text-align:center;padding:60px;">';
        echo '<h1>403 - Access Denied</h1>';
        echo '<p>Your account does not have permission to view this page.</p>';
        echo '<p><a href="/WebTech/index.php">Return home</a></p>';
        echo '</body></html>';
        exit;
    }
}

// Same idea as requireRole(), but for AJAX/JSON endpoints, which
// must never redirect or print HTML - they need to keep returning
// clean JSON even when access is denied.
function isCommunityUser() {
    return isLoggedIn() && currentUserRole() === 'community_user';
}
```

---

### `includes/header.php`

```php
<?php require_once __DIR__ . '/../config/session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NeighbourNet - Bashundhara Community</title>
<link rel="stylesheet" href="/WebTech/assets/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <a href="/WebTech/index.php" class="logo">🏠 NeighbourNet</a>

    <nav class="main-nav">
      <a href="/WebTech/index.php">Home</a>
      <a href="/WebTech/community/create_post.php">Community</a>
      <a href="/WebTech/community/notices.php">Notices</a>
      <a href="/WebTech/community/events.php">Events</a>
      <a href="/WebTech/community/search.php">Search</a>
      <?php if (isLoggedIn()): ?>
        <a href="/WebTech/community/dashboard.php">Dashboard</a>
        <a href="/WebTech/community/profile.php">Profile</a>
      <?php endif; ?>
    </nav>

    <div class="auth-area">
      <?php if (isLoggedIn()): ?>
        <span class="welcome">Hi, <?php echo htmlspecialchars(currentUserName()); ?></span>
        <a href="/WebTech/auth/logout.php" class="btn btn-outline">Logout</a>
      <?php else: ?>
        <a href="/WebTech/auth/login.php" class="btn btn-outline">Login</a>
        <a href="/WebTech/auth/register.php" class="btn btn-primary">Create Account</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="container page-content">
```

---

### `includes/footer.php`

```php
</main>

<footer class="site-footer">
  <div class="container">
    <p>&copy; 2025 NeighbourNet — Bashundhara Community (CommunityUser module by Amit)</p>
  </div>
</footer>

</body>
</html>
```

---

## 3. CSS / JavaScript

### `assets/css/style.css`

```css
/* ============================================================
   NeighbourNet - Base Styles
   ============================================================ */
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
    background: #f5f7fb;
    color: #1f2937;
    line-height: 1.5;
}

.container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 20px;
}

a { text-decoration: none; color: inherit; }

/* ---------- Header ---------- */
.site-header {
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    position: sticky;
    top: 0;
    z-index: 10;
}

.header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
}

.logo {
    font-size: 20px;
    font-weight: 700;
    color: #2563eb;
}

.main-nav a {
    margin: 0 12px;
    font-weight: 500;
    color: #374151;
}

.main-nav a:hover { color: #2563eb; }

.auth-area { display: flex; align-items: center; gap: 10px; }

.welcome { font-weight: 500; color: #374151; margin-right: 4px; }

/* ---------- Buttons ---------- */
.btn {
    display: inline-block;
    padding: 9px 18px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    border: 1px solid transparent;
}

.btn-primary { background: #2563eb; color: #fff; }
.btn-primary:hover { background: #1d4ed8; }

.btn-outline { background: #fff; color: #2563eb; border: 1px solid #2563eb; }
.btn-outline:hover { background: #eff6ff; }

.btn-danger { background: #fff; color: #dc2626; border: 1px solid #dc2626; }
.btn-danger:hover { background: #fef2f2; }

.like-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #374151;
    font-weight: 600;
    font-size: 12px;
    cursor: pointer;
}
.like-btn:hover { background: #f9fafb; }
.like-btn.liked {
    background: #eff6ff;
    border-color: #2563eb;
    color: #2563eb;
}

/* ---------- Comments ---------- */
.comments-section {
    margin-top: 14px;
    border-top: 1px solid #e5e7eb;
    padding-top: 10px;
}

.toggle-comments-btn {
    background: none;
    border: none;
    color: #2563eb;
    font-weight: 600;
    font-size: 12px;
    cursor: pointer;
    padding: 0;
    margin-bottom: 8px;
}
.toggle-comments-btn:hover { text-decoration: underline; }

.comments-list { margin-bottom: 10px; }

.comment-item {
    background: #f9fafb;
    border-radius: 8px;
    padding: 8px 10px;
    margin-bottom: 6px;
    font-size: 13px;
}
.comment-item strong { font-size: 13px; }
.comment-date { font-size: 11px; color: #9ca3af; margin-left: 6px; }
.comment-item p { margin: 4px 0 0; color: #374151; }

.add-comment-form {
    display: flex;
    gap: 6px;
}
.add-comment-form input {
    flex: 1;
    padding: 7px 10px;
    font-size: 13px;
}
.add-comment-form .btn {
    width: auto;
    margin-top: 0;
    padding: 7px 14px;
}

/* ---------- Report ---------- */
.report-form {
    margin-top: 8px;
    padding: 8px 10px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.report-form select {
    width: auto;
    padding: 6px 8px;
    font-size: 12px;
}
.report-msg { width: 100%; }
.report-inline-msg {
    margin: 4px 0 0;
    padding: 6px 10px;
    font-size: 12px;
}
.comment-item .toggle-report-btn {
    margin-top: 6px;
}

/* ---------- Dashboard ---------- */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}

.stat-box {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #2563eb;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
    font-weight: 600;
}

.quick-links {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

/* ---------- Page content ---------- */
.page-content { padding: 32px 20px 60px; }

/* ---------- Hero (home page) ---------- */
.hero {
    background: #fff;
    border-radius: 14px;
    padding: 48px 32px;
    text-align: center;
    margin-bottom: 30px;
    border: 1px solid #e5e7eb;
}
.hero h1 { font-size: 32px; margin-bottom: 12px; }
.hero p { color: #4b5563; margin-bottom: 22px; }
.hero .btn { margin: 0 6px; }

/* ---------- Forms / Cards ---------- */
.form-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 28px 30px;
    max-width: 560px;
    margin: 0 auto 30px;
}

.form-card h1 { font-size: 22px; margin: 0 0 6px; }
.subtitle { color: #6b7280; margin-bottom: 18px; font-size: 14px; }

label {
    display: block;
    font-weight: 600;
    font-size: 13px;
    margin: 14px 0 6px;
    color: #374151;
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="file"],
select,
textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
}

textarea { resize: vertical; }

.form-card .btn { width: 100%; margin-top: 20px; padding: 11px; }

/* ---------- Alerts ---------- */
.alert {
    padding: 12px 14px;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 16px;
}
.alert ul { margin: 0; padding-left: 18px; }

.alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

.muted { color: #6b7280; font-size: 14px; }

/* ---------- Recent posts list ---------- */
.recent-posts { max-width: 560px; margin: 0 auto; }
.recent-posts h2 { font-size: 18px; margin-bottom: 14px; }

.post-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px 18px;
    margin-bottom: 14px;
}

.post-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.badge {
    background: #eff6ff;
    color: #2563eb;
    font-size: 12px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 999px;
}

.badge-critical  { background: #fee2e2; color: #991b1b; }
.badge-important { background: #ffedd5; color: #9a3412; }
.badge-general    { background: #eff6ff; color: #2563eb; }

.post-date { font-size: 12px; color: #9ca3af; }

.post-card h3 { margin: 4px 0 6px; font-size: 16px; }
.post-card p { margin: 0 0 10px; color: #374151; font-size: 14px; }

.post-image {
    max-width: 100%;
    border-radius: 8px;
    margin-top: 6px;
}

.post-actions {
    margin-top: 12px;
    display: flex;
    gap: 8px;
}

.btn-sm {
    padding: 5px 12px;
    font-size: 12px;
    width: auto;
    margin-top: 0;
}

/* ---------- Footer ---------- */
.site-footer {
    background: #fff;
    border-top: 1px solid #e5e7eb;
    padding: 18px 0;
    text-align: center;
    color: #6b7280;
    font-size: 13px;
}
```

---

### `assets/js/create_post.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const form        = document.getElementById('createPostForm');
    const alertBox     = document.getElementById('postAlert');
    const submitBtn     = document.getElementById('submitBtn');
    const recentList    = document.getElementById('recentPostsList');
    const noPostsMsg    = document.getElementById('noPostsMsg');

    form.addEventListener('submit', function (e) {
        e.preventDefault(); // stop the normal full-page form submit

        submitBtn.disabled = true;
        submitBtn.textContent = 'Publishing...';
        alertBox.innerHTML = '';

        // FormData automatically includes the uploaded file too
        const formData = new FormData(form);

        fetch('/WebTech/community/ajax/create_post.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json(); // parse the JSON the PHP endpoint sent back
            })
            .then(function (data) {
                if (data.success) {
                    alertBox.innerHTML =
                        '<p class="alert alert-success">' + data.message + '</p>';

                    // remove the "you haven't posted yet" placeholder if present
                    if (noPostsMsg) {
                        noPostsMsg.remove();
                    }

                    // add the new post to the top of the list, no reload needed
                    recentList.insertAdjacentHTML('afterbegin', data.post.html);

                    form.reset();
                } else {
                    let errorHtml = '<div class="alert alert-error"><ul>';
                    if (data.errors && data.errors.length > 0) {
                        data.errors.forEach(function (err) {
                            errorHtml += '<li>' + err + '</li>';
                        });
                    } else {
                        errorHtml += '<li>' + data.message + '</li>';
                    }
                    errorHtml += '</ul></div>';
                    alertBox.innerHTML = errorHtml;
                }
            })
            .catch(function (err) {
                alertBox.innerHTML =
                    '<p class="alert alert-error">Something went wrong. Please try again.</p>';
                console.error(err);
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Publish Post';
            });
    });
});
```

---

### `assets/js/edit_post.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('editPostForm');
    if (!form) return; // page is in "not found" state, no form to attach to

    const alertBox  = document.getElementById('postAlert');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
        e.preventDefault(); // no full-page reload

        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        alertBox.innerHTML = '';

        const formData = new FormData(form);

        fetch('/WebTech/community/ajax/edit_post.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    alertBox.innerHTML =
                        '<p class="alert alert-success">' + data.message +
                        ' <a href="/WebTech/community/create_post.php">Back to Community</a></p>';
                } else {
                    let errorHtml = '<div class="alert alert-error"><ul>';
                    if (data.errors && data.errors.length > 0) {
                        data.errors.forEach(function (err) {
                            errorHtml += '<li>' + err + '</li>';
                        });
                    } else {
                        errorHtml += '<li>' + data.message + '</li>';
                    }
                    errorHtml += '</ul></div>';
                    alertBox.innerHTML = errorHtml;
                }
            })
            .catch(function (err) {
                alertBox.innerHTML =
                    '<p class="alert alert-error">Something went wrong. Please try again.</p>';
                console.error(err);
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Changes';
            });
    });
});
```

---

### `assets/js/delete_post.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const recentList = document.getElementById('recentPostsList');
    if (!recentList) return;

    // Event delegation - this also works for posts added to the page
    // dynamically after Create Post, since we're not attaching to each
    // button individually.
    recentList.addEventListener('click', function (e) {
        const btn = e.target.closest('.delete-post-btn');
        if (!btn) return;

        const postId = btn.dataset.postId;
        const card = btn.closest('.post-card');

        const confirmed = confirm('Are you sure you want to delete this post? This cannot be undone.');
        if (!confirmed) return;

        btn.disabled = true;
        btn.textContent = 'Deleting...';

        const formData = new FormData();
        formData.append('post_id', postId);

        fetch('/WebTech/community/ajax/delete_post.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    card.remove();

                    // if that was the last post, bring back the placeholder message
                    if (recentList.children.length === 0) {
                        recentList.innerHTML =
                            '<p class="muted" id="noPostsMsg">You haven\'t posted anything yet — try the form above.</p>';
                    }
                } else {
                    alert(data.message || 'Failed to delete post.');
                    btn.disabled = false;
                    btn.textContent = 'Delete';
                }
            })
            .catch(function (err) {
                alert('Something went wrong. Please try again.');
                console.error(err);
                btn.disabled = false;
                btn.textContent = 'Delete';
            });
    });
});
```

---

### `assets/js/like_post.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('recentPostsList');
    if (!container) return;

    // Event delegation - works for posts added dynamically too
    container.addEventListener('click', function (e) {
        const btn = e.target.closest('.like-btn');
        if (!btn) return;

        const postId = btn.dataset.postId;
        btn.disabled = true;

        const formData = new FormData();
        formData.append('post_id', postId);

        fetch('/WebTech/community/ajax/toggle_like.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    const countSpan = btn.querySelector('.like-count');
                    const labelSpan = btn.querySelector('.like-label');

                    countSpan.textContent = data.like_count;

                    if (data.liked) {
                        btn.classList.add('liked');
                        labelSpan.textContent = 'Liked';
                    } else {
                        btn.classList.remove('liked');
                        labelSpan.textContent = 'Like';
                    }
                } else {
                    alert(data.message || 'Something went wrong.');
                }
            })
            .catch(function (err) {
                alert('Something went wrong. Please try again.');
                console.error(err);
            })
            .finally(function () {
                btn.disabled = false;
            });
    });
});
```

---

### `assets/js/comment_post.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('recentPostsList');
    if (!container) return;

    // Comment content comes from users, so always escape it before
    // inserting into the page - never trust it as raw HTML.
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderComment(c) {
        return '<div class="comment-item">' +
                    '<strong>' + escapeHtml(c.commenter_name) + '</strong>' +
                    '<span class="comment-date">' + escapeHtml(c.created_at) + '</span>' +
                    '<p>' + escapeHtml(c.content).replace(/\n/g, '<br>') + '</p>' +
                    '<button type="button" class="btn btn-outline btn-sm toggle-report-btn" ' +
                            'data-target-type="comment" data-target-id="' + c.comment_id + '">Report</button>' +
                    '<div class="report-form" id="report-comment-' + c.comment_id + '" style="display:none;">' +
                        '<select class="report-reason-select">' +
                            '<option value="Spam">Spam</option>' +
                            '<option value="Harassment">Harassment or Bullying</option>' +
                            '<option value="Inappropriate Content">Inappropriate Content</option>' +
                            '<option value="False Information">False Information</option>' +
                            '<option value="Other">Other</option>' +
                        '</select>' +
                        '<button type="button" class="btn btn-outline btn-sm submit-report-btn" ' +
                                'data-target-type="comment" data-target-id="' + c.comment_id + '">Submit Report</button>' +
                        '<div class="report-msg"></div>' +
                    '</div>' +
               '</div>';
    }

    // ---------- Toggle comments open/closed, load them the first time ----------
    container.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('.toggle-comments-btn');
        if (!toggleBtn) return;

        const postId  = toggleBtn.dataset.postId;
        const listDiv = document.getElementById('comments-' + postId);

        const isHidden = listDiv.style.display === 'none' || listDiv.style.display === '';

        if (isHidden) {
            listDiv.style.display = 'block';

            if (!listDiv.dataset.loaded) {
                listDiv.innerHTML = '<p class="muted">Loading comments...</p>';

                fetch('/WebTech/community/ajax/get_comments.php?post_id=' + encodeURIComponent(postId))
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.success) {
                            if (data.comments.length === 0) {
                                listDiv.innerHTML = '<p class="muted">No comments yet. Be the first to comment!</p>';
                            } else {
                                listDiv.innerHTML = data.comments.map(renderComment).join('');
                            }
                            listDiv.dataset.loaded = '1';
                        } else {
                            listDiv.innerHTML = '<p class="alert alert-error">Could not load comments.</p>';
                        }
                    })
                    .catch(function () {
                        listDiv.innerHTML = '<p class="alert alert-error">Could not load comments.</p>';
                    });
            }
        } else {
            listDiv.style.display = 'none';
        }
    });

    // ---------- Handle "add comment" form submissions ----------
    container.addEventListener('submit', function (e) {
        const form = e.target.closest('.add-comment-form');
        if (!form) return;
        e.preventDefault();

        const postId    = form.dataset.postId;
        const input      = form.querySelector('input[name="content"]');
        const listDiv    = document.getElementById('comments-' + postId);
        const toggleBtn  = container.querySelector('.toggle-comments-btn[data-post-id="' + postId + '"]');

        const formData = new FormData(form);

        fetch('/WebTech/community/ajax/add_comment.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    input.value = '';
                    listDiv.style.display = 'block';

                    // remove "no comments yet" / loading placeholder if present
                    const placeholder = listDiv.querySelector('.muted');
                    if (placeholder) {
                        listDiv.innerHTML = '';
                    }

                    listDiv.insertAdjacentHTML('beforeend', renderComment(data.comment));
                    listDiv.dataset.loaded = '1';

                    if (toggleBtn) {
                        toggleBtn.textContent = 'Comments (' + data.comment_count + ')';
                    }
                } else {
                    alert((data.errors && data.errors[0]) || data.message || 'Could not post comment.');
                }
            })
            .catch(function () {
                alert('Something went wrong. Please try again.');
            });
    });
});
```

---

### `assets/js/report_content.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('recentPostsList');
    if (!container) return;

    container.addEventListener('click', function (e) {
        // ---------- Toggle a report form open/closed ----------
        const toggleBtn = e.target.closest('.toggle-report-btn');
        if (toggleBtn) {
            const targetType = toggleBtn.dataset.targetType;
            const targetId   = toggleBtn.dataset.targetId;
            const formDiv    = document.getElementById('report-' + targetType + '-' + targetId);
            if (!formDiv) return;

            formDiv.style.display =
                (formDiv.style.display === 'none' || formDiv.style.display === '') ? 'flex' : 'none';
            return;
        }

        // ---------- Submit a report ----------
        const submitBtn = e.target.closest('.submit-report-btn');
        if (submitBtn) {
            const targetType = submitBtn.dataset.targetType;
            const targetId   = submitBtn.dataset.targetId;
            const formDiv    = document.getElementById('report-' + targetType + '-' + targetId);
            const select     = formDiv.querySelector('.report-reason-select');
            const msgDiv     = formDiv.querySelector('.report-msg');

            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            const formData = new FormData();
            formData.append('target_type', targetType);
            formData.append('target_id', targetId);
            formData.append('reason', select.value);

            fetch('/WebTech/community/ajax/report_content.php', {
                method: 'POST',
                body: formData
            })
                .then(function (res) {
                    return res.json();
                })
                .then(function (data) {
                    if (data.success) {
                        msgDiv.innerHTML =
                            '<p class="alert alert-success report-inline-msg">' + data.message + '</p>';
                        select.disabled = true;
                        submitBtn.style.display = 'none';
                    } else {
                        msgDiv.innerHTML =
                            '<p class="alert alert-error report-inline-msg">' +
                            (data.message || 'Could not submit report.') + '</p>';
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit Report';
                    }
                })
                .catch(function () {
                    msgDiv.innerHTML =
                        '<p class="alert alert-error report-inline-msg">Something went wrong. Please try again.</p>';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Report';
                });
        }
    });
});
```

---

### `assets/js/notices.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const filter = document.getElementById('priorityFilter');
    const list = document.getElementById('noticesList');
    if (!filter || !list) return;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderNotice(n) {
        return '<div class="post-card">' +
                    '<div class="post-meta">' +
                        '<span class="badge badge-' + n.priority.toLowerCase() + '">' +
                            escapeHtml(n.priority) +
                        '</span>' +
                        '<span class="post-date">' + escapeHtml(n.created_at) + '</span>' +
                    '</div>' +
                    '<h3>' + escapeHtml(n.title) + '</h3>' +
                    '<p>' + escapeHtml(n.content).replace(/\n/g, '<br>') + '</p>' +
                    '<p class="muted" style="margin-top:6px;">📍 ' + escapeHtml(n.location) + '</p>' +
               '</div>';
    }

    filter.addEventListener('change', function () {
        const priority = filter.value;
        list.innerHTML = '<p class="muted">Loading...</p>';

        fetch('/WebTech/community/ajax/get_notices.php?priority=' + encodeURIComponent(priority))
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    if (data.notices.length === 0) {
                        list.innerHTML = '<p class="muted">No notices match this filter.</p>';
                    } else {
                        list.innerHTML = data.notices.map(renderNotice).join('');
                    }
                } else {
                    list.innerHTML = '<p class="alert alert-error">Could not load notices.</p>';
                }
            })
            .catch(function () {
                list.innerHTML = '<p class="alert alert-error">Could not load notices.</p>';
            });
    });
});
```

---

### `assets/js/events.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const filter = document.getElementById('eventFilter');
    const list = document.getElementById('eventsList');
    if (!filter || !list) return;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderEvent(ev) {
        return '<div class="post-card">' +
                    '<div class="post-meta">' +
                        '<span class="badge badge-general">Event</span>' +
                        '<span class="post-date">' + escapeHtml(ev.date_display) + '</span>' +
                    '</div>' +
                    '<h3>' + escapeHtml(ev.title) + '</h3>' +
                    '<p>' + escapeHtml(ev.description).replace(/\n/g, '<br>') + '</p>' +
                    '<p class="muted" style="margin-top:6px;">📍 ' + escapeHtml(ev.location) + '</p>' +
               '</div>';
    }

    filter.addEventListener('change', function () {
        const filterValue = filter.value;
        list.innerHTML = '<p class="muted">Loading...</p>';

        fetch('/WebTech/community/ajax/get_events.php?filter=' + encodeURIComponent(filterValue))
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    if (data.events.length === 0) {
                        list.innerHTML = '<p class="muted">No events found.</p>';
                    } else {
                        list.innerHTML = data.events.map(renderEvent).join('');
                    }
                } else {
                    list.innerHTML = '<p class="alert alert-error">Could not load events.</p>';
                }
            })
            .catch(function () {
                list.innerHTML = '<p class="alert alert-error">Could not load events.</p>';
            });
    });
});
```

---

### `assets/js/search.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect  = document.getElementById('searchType');
    const queryInput  = document.getElementById('searchQuery');
    const searchBtn   = document.getElementById('searchBtn');
    const resultsDiv  = document.getElementById('searchResults');
    if (!typeSelect || !queryInput || !searchBtn || !resultsDiv) return;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderResult(r) {
        return '<div class="post-card">' +
                    '<div class="post-meta">' +
                        '<span class="badge badge-general">' + escapeHtml(r.type) + '</span>' +
                    '</div>' +
                    '<h3>' + escapeHtml(r.title) + '</h3>' +
                    '<p>' + escapeHtml(r.snippet) + '</p>' +
                    '<p class="muted" style="margin-top:6px;">' + escapeHtml(r.meta) + '</p>' +
               '</div>';
    }

    function runSearch() {
        const type = typeSelect.value;
        const q = queryInput.value.trim();

        if (q.length < 2) {
            resultsDiv.innerHTML = '<p class="muted">Type at least 2 characters to search.</p>';
            return;
        }

        resultsDiv.innerHTML = '<p class="muted">Searching...</p>';

        fetch('/WebTech/community/ajax/search.php?type=' + encodeURIComponent(type) +
              '&q=' + encodeURIComponent(q))
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    if (data.results.length === 0) {
                        resultsDiv.innerHTML = '<p class="muted">No results found.</p>';
                    } else {
                        resultsDiv.innerHTML = data.results.map(renderResult).join('');
                    }
                } else {
                    resultsDiv.innerHTML = '<p class="alert alert-error">' +
                        (data.message || 'Search failed.') + '</p>';
                }
            })
            .catch(function () {
                resultsDiv.innerHTML = '<p class="alert alert-error">Something went wrong. Please try again.</p>';
            });
    }

    searchBtn.addEventListener('click', runSearch);

    // Allow pressing Enter in the search box too
    queryInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            runSearch();
        }
    });
});
```

---

### `assets/js/profile.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('profileForm');
    if (!form) return;

    const alertBox  = document.getElementById('profileAlert');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        alertBox.innerHTML = '';

        const formData = new FormData(form);

        fetch('/WebTech/community/ajax/update_profile.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    alertBox.innerHTML = '<p class="alert alert-success">' + data.message + '</p>';

                    // update the "Hi, name" text in the header immediately,
                    // without needing a full page reload
                    const welcomeEl = document.querySelector('.welcome');
                    if (welcomeEl) {
                        welcomeEl.textContent = 'Hi, ' + data.name;
                    }
                } else {
                    let errorHtml = '<div class="alert alert-error"><ul>';
                    if (data.errors && data.errors.length > 0) {
                        data.errors.forEach(function (err) {
                            errorHtml += '<li>' + err + '</li>';
                        });
                    } else {
                        errorHtml += '<li>' + data.message + '</li>';
                    }
                    errorHtml += '</ul></div>';
                    alertBox.innerHTML = errorHtml;
                }
            })
            .catch(function () {
                alertBox.innerHTML = '<p class="alert alert-error">Something went wrong. Please try again.</p>';
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Changes';
            });
    });
});
```

---

### `assets/js/change_password.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('changePasswordForm');
    if (!form) return;

    const alertBox  = document.getElementById('passwordAlert');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.textContent = 'Updating...';
        alertBox.innerHTML = '';

        const formData = new FormData(form);

        fetch('/WebTech/auth/ajax/change_password.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    alertBox.innerHTML = '<p class="alert alert-success">' + data.message + '</p>';
                    form.reset();
                } else {
                    let errorHtml = '<div class="alert alert-error"><ul>';
                    if (data.errors && data.errors.length > 0) {
                        data.errors.forEach(function (err) {
                            errorHtml += '<li>' + err + '</li>';
                        });
                    } else {
                        errorHtml += '<li>' + data.message + '</li>';
                    }
                    errorHtml += '</ul></div>';
                    alertBox.innerHTML = errorHtml;
                }
            })
            .catch(function () {
                alertBox.innerHTML = '<p class="alert alert-error">Something went wrong. Please try again.</p>';
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update Password';
            });
    });
});
```

---

### `assets/js/forgot_password.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('forgotPasswordForm');
    if (!form) return;

    const alertBox  = document.getElementById('resetAlert');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';
        alertBox.innerHTML = '';

        const formData = new FormData(form);

        fetch('/WebTech/auth/ajax/request_reset.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    let html = '<p class="alert alert-success">' + data.message + '</p>';

                    if (data.reset_link) {
                        // No email server is configured in this XAMPP setup,
                        // so the reset link is shown directly here instead
                        // of being emailed (this stands in for "check your inbox").
                        html += '<div class="alert alert-success" style="word-break:break-all;">' +
                                    '<strong>Demo mode - reset link:</strong><br>' +
                                    '<a href="' + data.reset_link + '">' + data.reset_link + '</a>' +
                                '</div>';
                    }

                    alertBox.innerHTML = html;
                    form.reset();
                } else {
                    alertBox.innerHTML = '<p class="alert alert-error">' +
                        (data.message || 'Something went wrong.') + '</p>';
                }
            })
            .catch(function () {
                alertBox.innerHTML = '<p class="alert alert-error">Something went wrong. Please try again.</p>';
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Reset Link';
            });
    });
});
```

---

### `assets/js/reset_password.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('resetPasswordForm');
    if (!form) return;

    const alertBox  = document.getElementById('resetAlert');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.textContent = 'Resetting...';
        alertBox.innerHTML = '';

        const formData = new FormData(form);

        fetch('/WebTech/auth/ajax/reset_password.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    alertBox.innerHTML =
                        '<p class="alert alert-success">' + data.message +
                        ' <a href="/WebTech/auth/login.php">Go to Login</a></p>';
                    form.style.display = 'none';
                } else {
                    let errorHtml = '<div class="alert alert-error"><ul>';
                    if (data.errors && data.errors.length > 0) {
                        data.errors.forEach(function (err) {
                            errorHtml += '<li>' + err + '</li>';
                        });
                    } else {
                        errorHtml += '<li>' + data.message + '</li>';
                    }
                    errorHtml += '</ul></div>';
                    alertBox.innerHTML = errorHtml;
                }
            })
            .catch(function () {
                alertBox.innerHTML = '<p class="alert alert-error">Something went wrong. Please try again.</p>';
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Reset Password';
            });
    });
});
```

---

## 4. Database Files

### `sql/schema.sql`

```sql
-- ============================================================
-- Bashundhara Community (NeighbourNet) - Database Schema
-- Covers: users, categories, posts  (CommunityUser - Feature 1: Create Post)
-- Import this once via phpMyAdmin -> Import, or run in phpMyAdmin's SQL tab.
-- ============================================================

CREATE DATABASE IF NOT EXISTS webtech_community
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE webtech_community;

-- ------------------------------------------------------------
-- USERS  (matches UML: User class)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin', 'community_user', 'business_person') NOT NULL DEFAULT 'community_user',
    status     ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CATEGORIES  (matches UML: Category class + PostCategoryType enum)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL,
    type        VARCHAR(50) NOT NULL DEFAULT 'PostCategoryType'
) ENGINE=InnoDB;

INSERT INTO categories (name, type) VALUES
    ('Local Discussion', 'PostCategoryType'),
    ('Emergency',         'PostCategoryType'),
    ('Lost & Found',      'PostCategoryType'),
    ('Road & Traffic',    'PostCategoryType'),
    ('Suggestions',       'PostCategoryType'),
    ('Local News',        'PostCategoryType'),
    ('Recommendations',   'PostCategoryType');

-- ------------------------------------------------------------
-- POSTS  (matches UML: Post class)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS posts (
    post_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    category_id INT NOT NULL,
    title       VARCHAR(150) NOT NULL,
    content     TEXT NOT NULL,
    image       VARCHAR(255) DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- LIKES  (matches UML: Like class)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS likes (
    like_id    INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- COMMENTS  (matches UML: Comment class)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    content    TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- REPORTS  (matches UML: Report class, {xor} post/comment target)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reports (
    report_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    post_id    INT NULL,
    comment_id INT NULL,
    reason     VARCHAR(255) NOT NULL,
    status     ENUM('pending', 'reviewed', 'resolved') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_report (user_id, post_id),
    UNIQUE KEY unique_comment_report (user_id, comment_id),
    CONSTRAINT chk_report_target CHECK (
        (post_id IS NOT NULL AND comment_id IS NULL) OR
        (post_id IS NULL AND comment_id IS NOT NULL)
    )
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- NOTICES  (matches UML: Notice class - CommunityUser browses only,
--           Admin.publishNotice() is out of scope here)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notices (
    notice_id   INT AUTO_INCREMENT PRIMARY KEY,
    created_by  INT NULL,
    title       VARCHAR(150) NOT NULL,
    content     TEXT NOT NULL,
    priority    ENUM('General', 'Important', 'Critical') NOT NULL DEFAULT 'General',
    location    VARCHAR(150) NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- EVENTS  (matches UML: Event class - CommunityUser browses only)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
    event_id    INT AUTO_INCREMENT PRIMARY KEY,
    created_by  INT NULL,
    title       VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    event_date  DATE NOT NULL,
    event_time  TIME NOT NULL,
    location    VARCHAR(150) NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- BUSINESSES  (matches UML: Business class - CommunityUser searches
--              only; management/approval are BusinessPerson/Admin)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS businesses (
    business_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NULL,
    name          VARCHAR(150) NOT NULL,
    category      VARCHAR(100) NOT NULL,
    address       VARCHAR(255) NOT NULL,
    opening_hours VARCHAR(150) NULL,
    status        ENUM('pending', 'approved', 'rejected', 'suspended') NOT NULL DEFAULT 'approved',
    verified      TINYINT(1) NOT NULL DEFAULT 1,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PASSWORD RESETS  (matches UML: User.resetPassword())
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    reset_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used       TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

---

### `sql/migration_2_add_likes.sql`

```sql
-- ============================================================
-- Migration: Add "likes" table  (CommunityUser - Feature 4: Like Post)
-- Matches UML: Like class (likeId, postId, userId, createdAt)
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing users/categories/posts data.
-- ============================================================

USE webtech_community;

CREATE TABLE IF NOT EXISTS likes (
    like_id    INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- a user can only like a given post once (prevents duplicate likes,
    -- and lets us tell "already liked" apart from "not liked" with one query)
    UNIQUE KEY unique_like (post_id, user_id),

    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

---

### `sql/migration_3_add_comments.sql`

```sql
-- ============================================================
-- Migration: Add "comments" table  (CommunityUser - Feature 5: Comment on Post)
-- Matches UML: Comment class (commentId, postId, userId, content, createdAt)
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing users/categories/posts/likes data.
-- ============================================================

USE webtech_community;

CREATE TABLE IF NOT EXISTS comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    content    TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

---

### `sql/migration_4_add_reports.sql`

```sql
-- ============================================================
-- Migration: Add "reports" table  (CommunityUser - Feature 6: Report Content)
-- Matches UML: Report class (reportId, userId, postId[0..1], commentId[0..1],
--              reason, status: ReportStatus, createdAt)
--
-- {xor} constraint from the UML: a report targets exactly ONE of
-- a Post OR a Comment - enforced below with a CHECK constraint,
-- and double-checked in PHP (community/ajax/report_content.php)
-- in case your MySQL/MariaDB version ignores CHECK constraints.
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing users/categories/posts/likes/comments data.
-- ============================================================

USE webtech_community;

CREATE TABLE IF NOT EXISTS reports (
    report_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    post_id    INT NULL,
    comment_id INT NULL,
    reason     VARCHAR(255) NOT NULL,
    status     ENUM('pending', 'reviewed', 'resolved') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE,

    -- a user can't submit two reports for the exact same post/comment
    UNIQUE KEY unique_post_report (user_id, post_id),
    UNIQUE KEY unique_comment_report (user_id, comment_id),

    -- {xor} from the UML: exactly one of post_id / comment_id must be set
    CONSTRAINT chk_report_target CHECK (
        (post_id IS NOT NULL AND comment_id IS NULL) OR
        (post_id IS NULL AND comment_id IS NOT NULL)
    )
) ENGINE=InnoDB;
```

---

### `sql/migration_5_add_notices.sql`

```sql
-- ============================================================
-- Migration: Add "notices" table  (CommunityUser - Feature 7: Browse Notices)
-- Matches UML: Notice class (noticeId, createdBy, title, content,
--              priority, location, createdAt, expiryDate)
--
-- NOTE: Creating/publishing notices is an Admin capability
-- (Admin.publishNotice()) and is intentionally NOT built here -
-- that is out of scope for the CommunityUser module. This
-- migration seeds a few sample notices so there is something
-- for CommunityUser.browseNotices() to actually display.
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing users/categories/posts/likes/comments/reports data.
-- ============================================================

USE webtech_community;

CREATE TABLE IF NOT EXISTS notices (
    notice_id   INT AUTO_INCREMENT PRIMARY KEY,
    created_by  INT NULL,
    title       VARCHAR(150) NOT NULL,
    content     TEXT NOT NULL,
    priority    ENUM('General', 'Important', 'Critical') NOT NULL DEFAULT 'General',
    location    VARCHAR(150) NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE NULL,

    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Sample seed data (created_by left NULL - no Admin module exists yet
-- to actually publish these; this is just demo content to browse)
INSERT INTO notices (title, content, priority, location, expiry_date) VALUES
    ('Road Closure Notice',
     'MG Road will be closed from 17 May to 19 May for utility works. Please use alternate routes.',
     'Critical', 'MG Road, City Center', '2026-09-19'),

    ('Emergency Alert',
     'Heavy rain forecast for the next 24 hours. Avoid low-lying areas and stay safe.',
     'Critical', 'Neighbourhood Wide', '2026-09-10'),

    ('Elevator Maintenance',
     'Elevator maintenance at Oakview Residency on 18 May, 10 AM - 2 PM.',
     'Important', 'Oakview Residency', '2026-09-12'),

    ('Community Meeting',
     'Monthly community meeting on 25 May at 6 PM at Community Hall. All residents welcome.',
     'General', 'Community Hall', '2026-09-25'),

    ('Water Supply Notice',
     'Intermittent water supply on 20 May due to pipeline maintenance.',
     'General', 'Sector 14 & 15', '2026-09-20');
```

---

### `sql/migration_6_add_events.sql`

```sql
-- ============================================================
-- Migration: Add "events" table  (CommunityUser - Feature 8: Browse Events)
-- Matches UML: Event class (eventId, createdBy, title, description,
--              date, time, location)
--
-- NOTE: Creating/publishing events is an Admin capability
-- (Admin.manageEvent(), Event.createEvent()/publishEvent()) and is
-- intentionally NOT built here - out of scope for CommunityUser.
-- This migration seeds sample events so CommunityUser.browseEvents()
-- has real data to display.
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing data.
-- ============================================================

USE webtech_community;

CREATE TABLE IF NOT EXISTS events (
    event_id    INT AUTO_INCREMENT PRIMARY KEY,
    created_by  INT NULL,
    title       VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    event_date  DATE NOT NULL,
    event_time  TIME NOT NULL,
    location    VARCHAR(150) NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Sample seed data (created_by left NULL - no Admin module exists yet)
INSERT INTO events (title, description, event_date, event_time, location) VALUES
    ('Community Tree Plantation Drive',
     'Join us this Sunday for a community tree plantation drive. Bring your friends and let''s grow together!',
     '2026-09-20', '07:00:00', 'Sunrise Park'),

    ('NeighbourNet Food Fiesta',
     'A celebration of local food from businesses across the neighbourhood. Come hungry!',
     '2026-09-27', '18:00:00', 'Community Ground'),

    ('Blood Donation Camp',
     'Donate blood and help save lives. Organized in partnership with City Health Center.',
     '2026-09-14', '09:00:00', 'City Health Center'),

    ('Yoga in the Park',
     'A relaxing morning yoga session open to all residents, beginners welcome.',
     '2026-09-13', '07:00:00', 'Green Park'),

    ('Local Cultural Evening',
     'An evening of local music, dance, and food celebrating our diverse community.',
     '2026-10-04', '18:30:00', 'Community Hall');
```

---

### `sql/migration_7_add_businesses.sql`

```sql
-- ============================================================
-- Migration: Add "businesses" table  (CommunityUser - Search Businesses)
-- Matches UML: Business class (businessId, userId, name, logo, category,
--              address, openingHours, status, verified)
--
-- NOTE: Creating/managing a business profile is a BusinessPerson
-- capability (manageBusinessProfile(), submitForApproval()) and
-- approving it is an Admin capability (approveBusiness()). Both are
-- intentionally NOT built here - out of scope for CommunityUser.
-- This migration seeds a few already-approved sample businesses so
-- CommunityUser can search a real business directory.
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing data.
-- ============================================================

USE webtech_community;

CREATE TABLE IF NOT EXISTS businesses (
    business_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NULL,
    name          VARCHAR(150) NOT NULL,
    category      VARCHAR(100) NOT NULL,
    address       VARCHAR(255) NOT NULL,
    opening_hours VARCHAR(150) NULL,
    status        ENUM('pending', 'approved', 'rejected', 'suspended') NOT NULL DEFAULT 'approved',
    verified      TINYINT(1) NOT NULL DEFAULT 1,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Sample seed data - all pre-approved so they're visible in search
-- (user_id left NULL - no BusinessPerson module exists yet to own these)
INSERT INTO businesses (name, category, address, opening_hours, status, verified) VALUES
    ('Greenleaf Cafe', 'Cafe', '12 Oak Avenue, Green Park', 'Mon - Sun: 7:00 AM - 5:00 PM', 'approved', 1),
    ('Caring Hands Pharmacy', 'Pharmacy', '8 Sunrise Parade, Sunrise Park', 'Mon - Sat: 8:00 AM - 8:00 PM', 'approved', 1),
    ('FitWell Gym', 'Fitness Center', '3 Power Street, Sunrise Park', 'Mon - Sun: 5:00 AM - 10:00 PM', 'approved', 1),
    ('Sunrise Fresh Mart', 'Grocery Store', '15 Market Square, Green Park', 'Mon - Sun: 7:00 AM - 9:00 PM', 'approved', 1),
    ('Tech Zone Electronics', 'Electronics', 'Unit 6, City Center Mall, Green Park', 'Mon - Sat: 9:00 AM - 7:00 PM', 'approved', 1),
    ('Green Park Family Clinic', 'Clinic', '22 Health Way, Green Park', 'Mon - Fri: 8:30 AM - 6:00 PM', 'approved', 1);
```

---

### `sql/migration_8_add_password_resets.sql`

```sql
-- ============================================================
-- Migration: Add "password_resets" table  (CommunityUser - Password Reset)
-- Matches UML: User.resetPassword()
--
-- Run this ONCE via phpMyAdmin -> SQL tab (paste and Go),
-- or Import -> choose this file. It will NOT affect your
-- existing data.
-- ============================================================

USE webtech_community;

CREATE TABLE IF NOT EXISTS password_resets (
    reset_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used       TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

---

## 5. Database Setup Order

This is the exact order to run SQL files in phpMyAdmin for a **fresh install**
(a computer/database that has never had this project on it before).

> If you already have `webtech_community` set up from working through this
> project feature-by-feature, you do **not** need to redo this — your database
> already has everything. This section is for setting up on a **new** machine
> or after a full reset.

**Step 1 — Create everything at once with the master schema:**

1. Open `http://localhost/phpmyadmin`
2. Click the **SQL** tab (top nav, not inside any specific database)
3. Open `sql/schema.sql` in a text editor, copy the entire contents, paste into the SQL box
4. Click **Go**

This single file creates the `webtech_community` database and all 8 tables
(`users`, `categories`, `posts`, `likes`, `comments`, `reports`, `notices`,
`events`, `businesses`, `password_resets`), and seeds the 7 post categories.

**Step 2 — Seed sample content (notices, events, businesses):**

`schema.sql` creates the *structure* for notices/events/businesses but does
NOT insert the sample rows (those live only in the individual migration
files, to avoid duplicate inserts if schema.sql is ever re-run). Run these
three migration files, in this order, via the same SQL tab:

1. `sql/migration_5_add_notices.sql` — adds 5 sample notices
2. `sql/migration_6_add_events.sql` — adds 5 sample events
3. `sql/migration_7_add_businesses.sql` — adds 6 sample businesses

**Step 3 — Confirm:**

In the phpMyAdmin sidebar, click `webtech_community` and confirm you see all
10 tables: `users`, `categories`, `posts`, `likes`, `comments`, `reports`,
`notices`, `events`, `businesses`, `password_resets`.

---

### If you are NOT doing a fresh install (upgrading an existing database)

If your database already has `users`, `categories`, and `posts` (i.e. you
built this incrementally, feature by feature, like the original build of
this project), do **NOT** run `schema.sql` again — its `INSERT INTO
categories` statement would create 7 duplicate category rows.

Instead, run only the individual migration files you haven't already run,
**in numeric order**:

```
migration_2_add_likes.sql
migration_3_add_comments.sql
migration_4_add_reports.sql
migration_5_add_notices.sql
migration_6_add_events.sql
migration_7_add_businesses.sql
migration_8_add_password_resets.sql
```

Every migration uses `CREATE TABLE IF NOT EXISTS`, so re-running a migration
you've already applied is safe and won't cause errors or duplicate tables -
though `migration_5`, `6`, and `7` will insert duplicate sample rows if
re-run, since their `INSERT` statements aren't guarded. Only run each
migration file once.

---

## 6. Run Instructions (XAMPP, macOS)

**1. Start XAMPP**
Open the XAMPP Control Panel app → click **Start** next to both **Apache**
and **MySQL** → wait until both show green/"Running".

**2. Confirm the project files are in the right place**
The project must live directly at:
```
/Applications/XAMPP/xamppfiles/htdocs/WebTech/
```
with `index.php` directly inside that folder (not nested in a sub-folder
like `WebTech/WebTech/`).

**3. Set up the database**
Follow the **Database Setup Order** section above.

**4. Open the site**
In your browser, go to:
```
http://localhost/WebTech/
```
There is no build step, no `npm start`, nothing to compile - PHP runs the
moment Apache serves the page. Do not open any `.php` file by double-clicking
it or via `file:///...` - it must go through that `localhost` URL, or the
PHP code will not execute (you'd just see raw code or nothing).

**5. Basic smoke test**
1. Click **Create Account** → register a test account
2. **Login** with it → should land on the **Dashboard**
3. Click **Create Post** → publish a post with a category and optional image
4. Like it, comment on it, check the Dashboard stats update
5. Visit **Notices**, **Events**, and **Search** from the nav to confirm they load
6. Visit **Profile** → edit your name → confirm the header updates live
7. Visit **Change Password** (via Profile) → change it → log out → log back in
   with the new password to confirm it took effect
8. Log out → click **Forgot your password?** on the login page → confirm the
   demo-mode reset link appears and works end-to-end

**6. Everything is self-contained**
No external services, API keys, or mail server are required. Uploaded images
are stored locally under `uploads/posts/`.
