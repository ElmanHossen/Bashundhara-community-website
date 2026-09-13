<?php
session_start();
require_once "../../models/businessModel.php";

if(!isset($_SESSION["userId"]))
{
    header("Location: ../auth/login.php");
}

$business=getBusinessByOwner($_SESSION["userId"]);

if($business)
{
    header("Location: businessDashboard.php");
    exit();
}
?>
<!doctype html>
<html>

<head>
    <title>Register Business</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/business.css">
    <script src="js/businessRegisterJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Register your business</h1>
            <p class="msg">Once the admin approves it, you can start listing products.</p>

            <form id="businessForm">
                <label for="businessName">Business Name:</label>
                <input type="text" id="businessName">
                <span class="error" id="nameErr"></span>

                <label for="description">Description:</label>
                <textarea id="description"></textarea>

                <label for="address">Address:</label>
                <input type="text" id="address">
                <span class="error" id="addressErr"></span>

                <label for="phone">Phone:</label>
                <input type="text" id="phone">
                <span class="error" id="phoneErr"></span>

                <br>
                <input type="submit" value="Submit for approval">
                <span class="success" id="businessMsg"></span>
            </form>
        </div>
    </div>
</body>

</html>
