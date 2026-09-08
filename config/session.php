<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Is anyone logged in right now?
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUserName() {
    return $_SESSION['user_name'] ?? null;
}

function currentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

// Call this at the top of any page that requires login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /WebTech/auth/login.php");
        exit;
    }
}

// Call this at the top of any page that requires a SPECIFIC role.
// Redirects to login if not logged in at all; shows a 403 page if
// logged in as the wrong role (e.g. an Admin or BusinessPerson
// account trying to open a CommunityUser page).
function requireRole($role) {
    requireLogin();

    if (currentUserRole() !== $role) {
        http_response_code(403);
        echo '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body style="font-family:sans-serif;text-align:center;padding:60px;">';
        echo '<h1>403 - Access Denied</h1>';
        echo '<p>Your account does not have permission to view this page.</p>';
        echo '<p><a href="/WebTech/index.php">Return home</a></p>';
        echo '</body></html>';
        exit;
    }
}

// Same idea as requireRole(), but for AJAX/JSON endpoints, which
// must never redirect or print HTML - they need to keep returning
// clean JSON even when access is denied.
function isCommunityUser() {
    return isLoggedIn() && currentUserRole() === 'community_user';
}
