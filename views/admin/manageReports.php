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
    <title>Reports</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="js/manageReportsJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Reported Posts</h1>

            <div class="filter-bar">
                <select id="statusFilter" onchange="loadReports()">
                    <option value="pending">Pending</option>
                    <option value="resolved">Resolved</option>
                    <option value="dismissed">Dismissed</option>
                    <option value="all">All</option>
                </select>
            </div>

            <span class="success" id="reportMsg"></span>
        </div>

        <div id="reportList"></div>
    </div>
</body>

</html>
