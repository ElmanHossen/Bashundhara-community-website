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
