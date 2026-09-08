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
