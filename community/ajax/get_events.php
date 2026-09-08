<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/events.js using fetch().
// Read-only browsing endpoint for CommunityUser.browseEvents().

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'events' => []];

$filter = $_GET['filter'] ?? 'upcoming';
$allowedFilters = ['upcoming', 'past', 'all'];

if (!in_array($filter, $allowedFilters, true)) {
    $response['message'] = 'Invalid filter.';
    echo json_encode($response);
    exit;
}

if ($filter === 'upcoming') {
    $stmt = $pdo->prepare(
        "SELECT event_id, title, description, event_date, event_time, location
         FROM events
         WHERE event_date >= CURDATE()
         ORDER BY event_date ASC, event_time ASC"
    );
    $stmt->execute();
} elseif ($filter === 'past') {
    $stmt = $pdo->prepare(
        "SELECT event_id, title, description, event_date, event_time, location
         FROM events
         WHERE event_date < CURDATE()
         ORDER BY event_date DESC, event_time DESC"
    );
    $stmt->execute();
} else { // all
    $stmt = $pdo->prepare(
        "SELECT event_id, title, description, event_date, event_time, location
         FROM events
         ORDER BY event_date ASC, event_time ASC"
    );
    $stmt->execute();
}

$events = $stmt->fetchAll();

// Format the date/time server-side so the JS doesn't need its own date logic
$formatted = [];
foreach ($events as $ev) {
    $formatted[] = [
        'event_id'    => $ev['event_id'],
        'title'       => $ev['title'],
        'description' => $ev['description'],
        'location'    => $ev['location'],
        'date_display' => date('d M Y', strtotime($ev['event_date'])) . ', ' .
                          date('g:i A', strtotime($ev['event_time'])),
    ];
}

$response['success'] = true;
$response['events'] = $formatted;

echo json_encode($response);
