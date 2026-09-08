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
