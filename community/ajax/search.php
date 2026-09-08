<?php
// This file NEVER outputs HTML - it only ever returns JSON.
// It is called by assets/js/search.js using fetch().
// Implements CommunityUser.searchContent() from the UML, split across
// the 4 search targets named in the project proposal.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$response = ['success' => false, 'results' => [], 'message' => ''];

// ---------- Must be logged in ----------
if (!isLoggedIn()) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to search.';
    echo json_encode($response);
    exit;
}

if (!isCommunityUser()) {
    http_response_code(403);
    $response['message'] = 'This action is only available to Community User accounts.';
    echo json_encode($response);
    exit;
}

$type = $_GET['type'] ?? '';
$q    = trim($_GET['q'] ?? '');

$allowedTypes = ['posts', 'notices_events', 'businesses', 'members'];
if (!in_array($type, $allowedTypes, true)) {
    $response['message'] = 'Invalid search type.';
    echo json_encode($response);
    exit;
}

if (mb_strlen($q) < 2) {
    $response['message'] = 'Please enter at least 2 characters to search.';
    echo json_encode($response);
    exit;
}

$like = '%' . $q . '%';
$results = [];

switch ($type) {

    // -------------------------------------------------------
    // Search Posts - across ALL community members' posts,
    // not just the logged-in user's own (unlike create_post.php)
    // -------------------------------------------------------
    case 'posts':
        $stmt = $pdo->prepare(
            "SELECT p.title, p.content, p.created_at, u.name AS author_name, c.name AS category_name
             FROM posts p
             JOIN users u ON p.user_id = u.user_id
             JOIN categories c ON p.category_id = c.category_id
             WHERE p.title LIKE ? OR p.content LIKE ?
             ORDER BY p.created_at DESC
             LIMIT 20"
        );
        $stmt->execute([$like, $like]);

        foreach ($stmt->fetchAll() as $r) {
            $results[] = [
                'type'    => 'Post',
                'title'   => $r['title'],
                'snippet' => mb_substr($r['content'], 0, 150),
                'meta'    => 'By ' . $r['author_name'] . ' · ' . $r['category_name'] .
                             ' · ' . date('d M Y', strtotime($r['created_at'])),
            ];
        }
        break;

    // -------------------------------------------------------
    // Search Notices/Events - combined results from both tables
    // -------------------------------------------------------
    case 'notices_events':
        $noticeStmt = $pdo->prepare(
            "SELECT title, content, priority, location
             FROM notices
             WHERE title LIKE ? OR content LIKE ?
             ORDER BY created_at DESC
             LIMIT 10"
        );
        $noticeStmt->execute([$like, $like]);
        foreach ($noticeStmt->fetchAll() as $r) {
            $results[] = [
                'type'    => 'Notice',
                'title'   => $r['title'],
                'snippet' => mb_substr($r['content'], 0, 150),
                'meta'    => $r['priority'] . ' · ' . $r['location'],
            ];
        }

        $eventStmt = $pdo->prepare(
            "SELECT title, description, event_date, event_time, location
             FROM events
             WHERE title LIKE ? OR description LIKE ?
             ORDER BY event_date ASC
             LIMIT 10"
        );
        $eventStmt->execute([$like, $like]);
        foreach ($eventStmt->fetchAll() as $r) {
            $results[] = [
                'type'    => 'Event',
                'title'   => $r['title'],
                'snippet' => mb_substr($r['description'], 0, 150),
                'meta'    => date('d M Y', strtotime($r['event_date'])) . ' · ' . $r['location'],
            ];
        }
        break;

    // -------------------------------------------------------
    // Search Businesses - only shows approved listings
    // -------------------------------------------------------
    case 'businesses':
        $stmt = $pdo->prepare(
            "SELECT name, category, address, opening_hours
             FROM businesses
             WHERE status = 'approved'
               AND (name LIKE ? OR category LIKE ? OR address LIKE ?)
             ORDER BY name ASC
             LIMIT 20"
        );
        $stmt->execute([$like, $like, $like]);

        foreach ($stmt->fetchAll() as $r) {
            $results[] = [
                'type'    => 'Business',
                'title'   => $r['name'],
                'snippet' => $r['category'] . ' — ' . $r['address'],
                'meta'    => $r['opening_hours'] ?? '',
            ];
        }
        break;

    // -------------------------------------------------------
    // Search Community Members - name only, no email/password
    // exposed, admins excluded from public search results
    // -------------------------------------------------------
    case 'members':
        $stmt = $pdo->prepare(
            "SELECT name, role, created_at
             FROM users
             WHERE role != 'admin' AND status = 'active' AND name LIKE ?
             ORDER BY name ASC
             LIMIT 20"
        );
        $stmt->execute([$like]);

        foreach ($stmt->fetchAll() as $r) {
            $roleLabel = ($r['role'] === 'business_person') ? 'Business Owner' : 'Community Member';
            $results[] = [
                'type'    => 'Member',
                'title'   => $r['name'],
                'snippet' => $roleLabel,
                'meta'    => 'Member since ' . date('M Y', strtotime($r['created_at'])),
            ];
        }
        break;
}

$response['success'] = true;
$response['results'] = $results;

echo json_encode($response);
