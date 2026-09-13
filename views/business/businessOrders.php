<?php
session_start();
require_once "../../models/businessModel.php";

if(!isset($_SESSION["userId"]))
{
    header("Location: ../auth/login.php");
}

$business=getBusinessByOwner($_SESSION["userId"]);

if(!$business)
{
    header("Location: businessRegister.php");
    exit();
}
?>
<!doctype html>
<html>

<head>
    <title>Customer Orders</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/business.css">
    <script src="js/businessOrdersJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Customer Orders</h1>
            <p class="msg">Change the status as you process each order.</p>
        </div>

        <div id="orderList"></div>
    </div>
</body>

</html>
