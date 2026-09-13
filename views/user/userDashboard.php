<?php
session_start();
require_once "../../models/usersModel.php";

if(isset($_SESSION["userId"]) && isset($_SESSION["role"]))
{
    if($_SESSION["role"]=="user")
    {

    }
    else if($_SESSION["role"]=="admin")
    {
        header("Location: ../admin/adminDashboard.php");
    }
    else if($_SESSION["role"]=="business")
    {
        header("Location: ../business/businessDashboard.php");
    }
    else
    {
        header("Location: ../auth/login.php");
    }
}
else
{
    header("Location: ../auth/login.php");
    exit();
}

$user=getUserById($_SESSION["userId"]);
?>
<!doctype html>
<html>

<head>
    <title>My Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Welcome, <?php echo $user["name"]; ?></h1>
            <p class="msg">User Id: <?php echo $user["userId"]; ?> | Member since <?php echo $user["createdAt"]; ?></p>
        </div>

        <div class="card">
            <h2>Quick links</h2>
            <ul class="link-list">
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="changePassword.php">Change Password</a></li>
                <li><a href="../community/feed.php">Community Feed</a></li>
                <li><a href="../community/myPosts.php">My Posts</a></li>
                <li><a href="../marketplace/shop.php">Shop Products</a></li>
                <li><a href="../marketplace/myOrders.php">My Orders</a></li>
                <li><a href="../business/businessRegister.php">Register a Business</a></li>
            </ul>
        </div>
    </div>
</body>

</html>
