<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/like_post.js using fetch().
// Toggles: if the user already liked this post, unlike it; otherwise, like it.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// ---------- Must be logged in ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to like a post.';
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

$postId = $_POST['post_id'] ?? '';

if (!ctype_digit((string) $postId)) {
    $response['message'] = 'Invalid post.';
    echo json_encode($response);
    exit;
}

// ---------- Confirm the post actually exists ----------
$postCheck = $pdo->prepare("SELECT post_id FROM posts WHERE post_id = ?");
$postCheck->execute([$postId]);
if (!$postCheck->fetch()) {
    $response['message'] = 'Post not found.';
    echo json_encode($response);
    exit;
}

$userId = currentUserId();

// ---------- Check if this user already liked this post ----------
$likeCheck = $pdo->prepare("SELECT like_id FROM likes WHERE post_id = ? AND user_id = ?");
$likeCheck->execute([$postId, $userId]);
$existingLike = $likeCheck->fetch();

if ($existingLike) {
    // Already liked -> remove the like (unlike)
    $del = $pdo->prepare("DELETE FROM likes WHERE like_id = ?");
    $del->execute([$existingLike['like_id']]);
    $liked = false;
} else {
    // Not liked yet -> add the like
    $ins = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $ins->execute([$postId, $userId]);
    $liked = true;
}

// ---------- Return the updated total like count ----------
$countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM likes WHERE post_id = ?");
$countStmt->execute([$postId]);
$count = $countStmt->fetch()['total'];

$response['success']    = true;
$response['liked']      = $liked;
$response['like_count'] = (int) $count;

echo json_encode($response);
