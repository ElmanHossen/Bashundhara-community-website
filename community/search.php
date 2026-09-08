<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
requireRole('community_user'); // Only CommunityUser accounts may access this page

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="max-width:700px;">
  <h1>Search NeighbourNet</h1>
  <p class="subtitle">Search posts, notices &amp; events, businesses, and community members.</p>

  <label>Search in</label>
  <select id="searchType">
    <option value="posts">Posts</option>
    <option value="notices_events">Notices &amp; Events</option>
    <option value="businesses">Businesses</option>
    <option value="members">Community Members</option>
  </select>

  <label>Keyword</label>
  <input type="text" id="searchQuery" placeholder="Type at least 2 characters...">

  <button type="button" id="searchBtn" class="btn btn-primary" style="margin-top:14px;">Search</button>
</div>

<div id="searchResults" style="max-width:700px;margin:0 auto;"></div>

<script src="/WebTech/assets/js/search.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
