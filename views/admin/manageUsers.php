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
    <title>User Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="js/manageUsersJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>User Management</h1>

            <div class="filter-bar">
                <input type="text" id="searchBox" placeholder="Search by id, name or email..." onkeyup="loadUsers()">

                <select id="roleFilter" onchange="loadUsers()">
                    <option value="all">All roles</option>
                    <option value="user">Community member</option>
                    <option value="business">Business owner</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <span class="success" id="userMsg"></span>
        </div>

        <div class="card">
            <div id="userList"></div>
        </div>
    </div>
</body>

</html>
