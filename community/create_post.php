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
