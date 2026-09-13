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
    <title>Business Approval</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="js/manageBusinessesJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Business Approval</h1>

            <div class="filter-bar">
                <select id="statusFilter" onchange="loadBusinesses()">
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="all">All</option>
                </select>
            </div>

            <span class="success" id="businessMsg"></span>
        </div>

        <div id="businessList"></div>
    </div>
</body>

</html>
