<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/delete_post.js using fetch().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// ---------- Must be logged in ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to delete a post.';
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

// ---------- Confirm the post exists AND belongs to this user ----------
// (never trust post_id alone - always check ownership server-side)
$stmt = $pdo->prepare("SELECT image FROM posts WHERE post_id = ? AND user_id = ?");
$stmt->execute([$postId, currentUserId()]);
$post = $stmt->fetch();

if (!$post) {
    $response['message'] = 'Post not found, or you do not have permission to delete it.';
    echo json_encode($response);
    exit;
}

// ---------- Delete the post (prepared statement, scoped to this user) ----------
$deleteStmt = $pdo->prepare("DELETE FROM posts WHERE post_id = ? AND user_id = ?");
$deleteStmt->execute([$postId, currentUserId()]);

// ---------- Clean up the uploaded image file, if any ----------
if ($post['image']) {
    $imagePath = __DIR__ . '/../../uploads/posts/' . $post['image'];
    if (is_file($imagePath)) {
        unlink($imagePath);
    }
}

$response['success'] = true;
$response['message'] = 'Post deleted successfully.';
$response['post_id'] = (int) $postId;

echo json_encode($response);
