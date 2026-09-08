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
