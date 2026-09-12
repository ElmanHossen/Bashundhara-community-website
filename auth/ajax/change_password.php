<?php


require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => [], 'message' => ''];

if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to change your password.';
    echo json_encode($response);
    exit;
}

if (!isCommunityUser()) {
    http_response_code(403);
    $response['message'] = 'This action is only available to Community User accounts.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_new_password'] ?? '';
$errors = [];

// ---------- Basic validation ----------
if ($currentPassword === '') {
    $errors[] = "Please enter your current password.";
}
if (strlen($newPassword) < 6) {
    $errors[] = "New password must be at least 6 characters.";
}
if ($newPassword !== $confirmPassword) {
    $errors[] = "New password and confirmation do not match.";
}

if (!empty($errors)) {
    $response['errors']  = $errors;
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}


$stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->execute([currentUserId()]);
$user = $stmt->fetch();

if (!$user || !password_verify($currentPassword, $user['password'])) {
    $response['errors']  = ["Current password is incorrect."];
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}

$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$updateStmt->execute([$newHash, currentUserId()]);

$response['success'] = true;
$response['message']  = 'Your password has been updated successfully.';

echo json_encode($response);
