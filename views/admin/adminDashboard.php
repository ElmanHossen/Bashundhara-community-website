<?php
session_start();
if(isset($_SESSION["userId"]) && isset($_SESSION["role"]))
{
    if($_SESSION["role"]=="admin")
    {

    }
    else if($_SESSION["role"]=="business")
    {
        header("Location: ../business/businessDashboard.php");
    }
    else
    {
        header("Location: ../user/userDashboard.php");
    }
}
else
{
    header("Location: ../auth/login.php");
}
?>
<!doctype html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="js/adminDashboardJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Admin Dashboard</h1>
            <p class="msg">Logged in as <?php echo $_SESSION["userId"]; ?></p>
        </div>

        <div class="card">
            <h2>Website statistics</h2>
            <div id="statsBox" class="stats-grid"></div>
        </div>

        <div class="card">
            <h2>Management</h2>
            <ul class="link-list">
                <li><a href="manageUsers.php">User management</a></li>
                <li><a href="manageBusinesses.php">Business approval</a></li>
                <li><a href="moderation.php">Post &amp; comment moderation</a></li>
                <li><a href="manageReports.php">Reports</a></li>
                <li><a href="manageNotices.php">Notice &amp; event management</a></li>
                <li><a href="manageHomepage.php">Homepage content</a></li>
            </ul>
        </div>
    </div>
</body>

</html>
