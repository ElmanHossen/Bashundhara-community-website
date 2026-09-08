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
