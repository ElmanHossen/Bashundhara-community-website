<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/reset_password.js using fetch().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => [], 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$token           = $_POST['token'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_new_password'] ?? '';
$errors = [];

if ($token === '') {
    $errors[] = "Invalid reset link.";
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

// ---------- Re-validate the token server-side (never trust the ----------
// ---------- fact that the page rendered a form as proof it's valid) ----------
$tokenHash = hash('sha256', $token);

$stmt = $pdo->prepare(
    "SELECT reset_id, user_id FROM password_resets
     WHERE token_hash = ? AND used = 0 AND expires_at > NOW()"
);
$stmt->execute([$tokenHash]);
$reset = $stmt->fetch();

if (!$reset) {
    $response['message'] = 'This reset link is invalid or has expired. Please request a new one.';
    echo json_encode($response);
    exit;
}

// ---------- Update the password (hashed) and mark the token used ----------
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$updateUser = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$updateUser->execute([$newHash, $reset['user_id']]);

// mark this token as used so it can never be reused, even if the
// link is reopened or shared
$markUsed = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE reset_id = ?");
$markUsed->execute([$reset['reset_id']]);

$response['success'] = true;
$response['message']  = 'Your password has been reset successfully. You can now log in.';

echo json_encode($response);
