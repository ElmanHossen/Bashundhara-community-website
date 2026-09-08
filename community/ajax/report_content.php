<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/report_content.js using fetch().
// Implements CommunityUser.reportContent() / Report.submitReport() from the UML.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// ---------- Must be logged in ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to report content.';
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

$targetType = $_POST['target_type'] ?? '';
$targetId   = $_POST['target_id'] ?? '';
$reason     = trim($_POST['reason'] ?? '');

// ---------- Validate target type (post XOR comment - never both) ----------
$allowedTypes = ['post', 'comment'];
if (!in_array($targetType, $allowedTypes, true)) {
    $response['message'] = 'Invalid report target.';
    echo json_encode($response);
    exit;
}

if (!ctype_digit((string) $targetId)) {
    $response['message'] = 'Invalid content to report.';
    echo json_encode($response);
    exit;
}

// ---------- Validate reason against a fixed list (dropdown-driven) ----------
$allowedReasons = ['Spam', 'Harassment', 'Inappropriate Content', 'False Information', 'Other'];
if (!in_array($reason, $allowedReasons, true)) {
    $response['message'] = 'Please select a valid reason.';
    echo json_encode($response);
    exit;
}

// ---------- Confirm the content being reported actually exists ----------
$postId    = null;
$commentId = null;

if ($targetType === 'post') {
    $check = $pdo->prepare("SELECT post_id FROM posts WHERE post_id = ?");
    $check->execute([$targetId]);
    if (!$check->fetch()) {
        $response['message'] = 'This post no longer exists.';
        echo json_encode($response);
        exit;
    }
    $postId = $targetId;
} else {
    $check = $pdo->prepare("SELECT comment_id FROM comments WHERE comment_id = ?");
    $check->execute([$targetId]);
    if (!$check->fetch()) {
        $response['message'] = 'This comment no longer exists.';
        echo json_encode($response);
        exit;
    }
    $commentId = $targetId;
}

// ---------- Insert the report (prepared statement) ----------
try {
    $stmt = $pdo->prepare(
        "INSERT INTO reports (user_id, post_id, comment_id, reason, status)
         VALUES (?, ?, ?, ?, 'pending')"
    );
    $stmt->execute([currentUserId(), $postId, $commentId, $reason]);

    $response['success'] = true;
    $response['message']  = 'Thank you. Your report has been submitted and will be reviewed by our team.';
} catch (PDOException $e) {
    // SQLSTATE 23000 = integrity constraint violation
    // (in this case: the unique_post_report / unique_comment_report key,
    // meaning this user already reported this exact content)
    if ($e->getCode() === '23000') {
        $response['message'] = 'You have already reported this content.';
    } else {
        $response['message'] = 'Something went wrong while submitting your report.';
    }
}

echo json_encode($response);
