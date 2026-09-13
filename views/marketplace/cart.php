<?php
session_start();
require_once "../../models/usersModel.php";

if(!isset($_SESSION["userId"]))
{
    header("Location: ../auth/login.php");
    exit();
}

$user=getUserById($_SESSION["userId"]);
?>
<!doctype html>
<html>

<head>
    <title>My Cart</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/business.css">
    <script src="js/cartJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>My Cart</h1>
            <div id="cartList"></div>
            <h2 id="cartTotal"></h2>
        </div>

        <div class="card" id="checkoutBox">
            <h2>Checkout</h2>

            <form id="checkoutForm">
                <label for="shippingAddress">Shipping address:</label>
                <input type="text" id="shippingAddress" value="<?php echo $user["address"]; ?>">
                <span class="error" id="addressErr"></span>

                <br>
                <input type="submit" value="Place order">
                <span class="success" id="orderMsg"></span>
            </form>
        </div>
    </div>
</body>

</html>
