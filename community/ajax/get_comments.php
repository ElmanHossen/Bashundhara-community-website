<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/comment_post.js using fetch().
// This is a read-only lookup, so it does not require login -
// anyone viewing the community feed can read comments.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'comments' => []];

$postId = $_GET['post_id'] ?? '';

if (!ctype_digit((string) $postId)) {
    $response['message'] = 'Invalid post.';
    echo json_encode($response);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT c.comment_id, c.content, c.created_at, u.name AS commenter_name
     FROM comments c
     JOIN users u ON c.user_id = u.user_id
     WHERE c.post_id = ?
     ORDER BY c.created_at ASC"
);
$stmt->execute([$postId]);
$rows = $stmt->fetchAll();

// Format the date server-side so the JS doesn't need its own date logic
$comments = [];
foreach ($rows as $row) {
    $comments[] = [
        'comment_id'     => $row['comment_id'],
        'content'        => $row['content'],
        'commenter_name' => $row['commenter_name'],
        'created_at'     => date('d M Y, g:i A', strtotime($row['created_at'])),
    ];
}

$response['success']  = true;
$response['comments'] = $comments;

echo json_encode($response);
