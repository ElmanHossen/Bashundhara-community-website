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
