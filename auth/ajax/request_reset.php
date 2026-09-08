<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/forgot_password.js using fetch().
//
// IMPORTANT (student-project note): this XAMPP setup has no configured
// mail server, so instead of emailing the reset link, this endpoint
// returns it directly in the JSON response for the demo to work end
// to end. In a real deployment, you would send `reset_link` via
// mail()/PHPMailer instead of returning it to the browser, and this
// response would just say "check your email" with no link included.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please enter a valid email address.';
    echo json_encode($response);
    exit;
}

// This message is always the same regardless of whether the account
// exists, to avoid revealing which emails are registered.
$genericMessage = 'If an account with that email exists, a password reset link has been generated below.';

// Only generate a real token for CommunityUser accounts - this keeps
// password reset contained to the CommunityUser module, since Admin
// and BusinessPerson aren't built yet.
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND role = 'community_user' AND status = 'active'");
$stmt->execute([$email]);
$user = $stmt->fetch();

$resetLink = null;

if ($user) {
    // Generate a random token, store only its hash (never the raw
    // token) in the database - same principle as password hashing.
    $token     = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    $insert = $pdo->prepare(
        "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)"
    );
    $insert->execute([$user['user_id'], $tokenHash, $expiresAt]);

    $resetLink = "http://localhost/WebTech/auth/reset_password.php?token=" . $token;
}

$response['success']    = true;
$response['message']    = $genericMessage;
$response['reset_link'] = $resetLink; // null if no matching CommunityUser account

echo json_encode($response);
