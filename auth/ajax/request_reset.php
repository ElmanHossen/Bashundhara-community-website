<?php

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


$genericMessage = 'If an account with that email exists, a password reset link has been generated below.';


$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND role = 'community_user' AND status = 'active'");
$stmt->execute([$email]);
$user = $stmt->fetch();

$resetLink = null;

if ($user) {

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
$response['reset_link'] = $resetLink; 

echo json_encode($response);
