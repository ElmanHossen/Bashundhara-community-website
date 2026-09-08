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
