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
