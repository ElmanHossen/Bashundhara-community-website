<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/profile.js using fetch().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => [], 'message' => ''];

// ---------- Must be logged in AND be a CommunityUser ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to update your profile.';
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

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$errors = [];

// ---------- Validate ----------
if ($name === '' || mb_strlen($name) > 100) {
    $errors[] = "Name is required (max 100 characters).";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors[] = "Please enter a valid email address.";
}

// Check the new email isn't already used by a DIFFERENT account
if (empty($errors)) {
    $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $check->execute([$email, currentUserId()]);
    if ($check->fetch()) {
        $errors[] = "That email is already in use by another account.";
    }
}

if (!empty($errors)) {
    $response['errors']  = $errors;
    $response['message'] = 'Please fix the errors below.';
    echo json_encode($response);
    exit;
}

// ---------- Update (prepared statement, ALWAYS scoped to the ----------
// ---------- logged-in user's own ID - never trusts a submitted ----------
// ---------- user_id, so nobody can edit someone else's profile) ----------
$stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
$stmt->execute([$name, $email, currentUserId()]);

// Keep the session in sync so the header/nav reflects the new name
// immediately, without requiring the user to log out and back in.
$_SESSION['user_name'] = $name;

$response['success'] = true;
$response['message']  = 'Profile updated successfully.';
$response['name']     = $name;
$response['email']    = $email;

echo json_encode($response);
