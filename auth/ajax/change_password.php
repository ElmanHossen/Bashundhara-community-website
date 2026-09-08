<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/change_password.js using fetch().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => [], 'message' => ''];

// ---------- Must be logged in AND be a CommunityUser ----------
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

// ---------- Fetch the CURRENT user's own password hash ----------
// (always scoped to currentUserId() - never accepts a user_id from
// the form, so nobody can change someone else's password)
$stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->execute([currentUserId()]);
$user = $stmt->fetch();

if (!$user || !password_verify($currentPassword, $user['password'])) {
    $response['errors']  = ["Current password is incorrect."];
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}

// ---------- Hash and save the new password ----------
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$updateStmt->execute([$newHash, currentUserId()]);

$response['success'] = true;
$response['message']  = 'Your password has been updated successfully.';

echo json_encode($response);
