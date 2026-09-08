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
