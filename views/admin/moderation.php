<?php
session_start();
if(!isset($_SESSION["userId"]) || $_SESSION["role"]!="admin")
{
    header("Location: ../auth/login.php");
}
?>
<!doctype html>
<html>

<head>
    <title>Moderation</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="js/moderationJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Post &amp; Comment Moderation</h1>

            <div class="filter-bar">
                <input type="text" id="searchBox" placeholder="Search posts..." onkeyup="loadAllPosts()">
            </div>

            <span class="success" id="modMsg"></span>
        </div>

        <div id="postList"></div>
    </div>
</body>

</html>
