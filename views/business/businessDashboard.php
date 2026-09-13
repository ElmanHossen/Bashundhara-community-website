<?php
session_start();
require_once "../../models/businessModel.php";

if(!isset($_SESSION["userId"]))
{
    header("Location: ../auth/login.php");
}

if($_SESSION["role"]=="admin")
{
    header("Location: ../admin/adminDashboard.php");
}

$business=getBusinessByOwner($_SESSION["userId"]);

if(!$business)
{
    header("Location: businessRegister.php");
    exit();
}

$products=getProductsByBusiness($business["businessId"]);
$orders=getOrdersForBusiness($business["businessId"]);
?>
<!doctype html>
<html>

<head>
    <title>Business Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/business.css">
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1><?php echo $business["businessName"]; ?></h1>
            <p class="msg">
                Status:
                <span class="status status-<?php echo $business["status"]; ?>"><?php echo $business["status"]; ?></span>
            </p>
            <p><?php echo $business["description"]; ?></p>
            <p class="msg"><?php echo $business["address"]; ?> | <?php echo $business["phone"]; ?></p>
        </div>

        <?php if($business["status"]!="approved") { ?>
            <div class="card">
                <p class="error">Your business is waiting for admin approval. You can add products once it is approved.</p>
            </div>
        <?php } ?>

        <div class="stat-row">
            <div class="card stat">
                <h2><?php echo count($products); ?></h2>
                <p class="msg">Products listed</p>
            </div>
            <div class="card stat">
                <h2><?php echo count($orders); ?></h2>
                <p class="msg">Orders received</p>
            </div>
        </div>

        <div class="card">
            <h2>Manage</h2>
            <ul class="link-list">
                <li><a href="manageProducts.php">Add / edit products</a></li>
                <li><a href="businessOrders.php">Customer orders</a></li>
                <li><a href="../marketplace/shop.php">View my shop page</a></li>
                <li><a href="../user/changePassword.php">Change password</a></li>
            </ul>
        </div>
    </div>
</body>

</html>
