<?php
session_start();
if(!isset($_SESSION["userId"]))
{
    header("Location: ../auth/login.php");
}
?>
<!doctype html>
<html>

<head>
    <title>My Orders</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/business.css">
    <script src="js/myOrdersJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>My Orders</h1>
        </div>

        <div id="orderList"></div>
    </div>
</body>

</html>
