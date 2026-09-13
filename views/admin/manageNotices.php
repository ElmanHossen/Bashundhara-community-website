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
    <title>Notices &amp; Events</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="js/manageNoticesJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Notices &amp; Events</h1>

            <form id="noticeForm">
                <input type="hidden" id="noticeId" value="0">

                <label for="title">Title:</label>
                <input type="text" id="title">
                <span class="error" id="titleErr"></span>

                <label for="type">Type:</label>
                <select id="type">
                    <option value="notice">Notice</option>
                    <option value="event">Event</option>
                </select>

                <label for="eventDate">Event date (only for events):</label>
                <input type="date" id="eventDate">
                <span class="error" id="dateErr"></span>

                <label for="content">Content:</label>
                <textarea id="content"></textarea>
                <span class="error" id="contentErr"></span>

                <br>
                <input type="submit" id="submitBtn" value="Publish">
                <button type="button" onclick="resetNoticeForm()">Cancel edit</button>
                <span class="success" id="noticeMsg"></span>
            </form>
        </div>

        <div class="card">
            <h2>Published notices</h2>
            <div id="noticeList"></div>
        </div>
    </div>
</body>

</html>
