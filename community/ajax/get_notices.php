<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/notices.js using fetch().
// Read-only browsing endpoint for CommunityUser.browseNotices().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'notices' => []];

$priority = $_GET['priority'] ?? '';
$allowedPriorities = ['General', 'Important', 'Critical'];

if ($priority !== '' && !in_array($priority, $allowedPriorities, true)) {
    $response['message'] = 'Invalid priority filter.';
    echo json_encode($response);
    exit;
}

if ($priority === '') {
    $stmt = $pdo->query(
        "SELECT notice_id, title, content, priority, location, created_at
         FROM notices
         ORDER BY FIELD(priority, 'Critical', 'Important', 'General'), created_at DESC"
    );
    $notices = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare(
        "SELECT notice_id, title, content, priority, location, created_at
         FROM notices
         WHERE priority = ?
         ORDER BY created_at DESC"
    );
    $stmt->execute([$priority]);
    $notices = $stmt->fetchAll();
}

// Format the date server-side so the JS doesn't need its own date logic
$formatted = [];
foreach ($notices as $n) {
    $formatted[] = [
        'notice_id' => $n['notice_id'],
        'title'     => $n['title'],
        'content'   => $n['content'],
        'priority'  => $n['priority'],
        'location'  => $n['location'],
        'created_at' => date('d M Y', strtotime($n['created_at'])),
    ];
}

$response['success'] = true;
$response['notices'] = $formatted;

echo json_encode($response);
