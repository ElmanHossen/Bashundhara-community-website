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
    <title>Homepage Content</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="js/manageHomepageJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Homepage Content</h1>
            <p class="msg">Edit the text that visitors see on the front page.</p>
            <span class="success" id="homeMsg"></span>
        </div>

        <div id="contentList"></div>
    </div>
</body>

</html>
