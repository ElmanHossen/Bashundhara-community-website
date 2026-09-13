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
    <title>My Profile</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
    <script src="js/profileJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card auth-box">
            <h1>My Profile</h1>

            <form id="profileForm">
                <label>User Id:</label>
                <input type="text" value="<?php echo $user["userId"]; ?>" disabled>

                <label for="name">Full Name:</label>
                <input type="text" name="name" id="name" value="<?php echo $user["name"]; ?>">
                <span class="error" id="nameErr"></span>

                <label for="email">Email:</label>
                <input type="text" name="email" id="email" value="<?php echo $user["email"]; ?>">
                <span class="error" id="emailErr"></span>

                <label for="phone">Phone:</label>
                <input type="text" name="phone" id="phone" value="<?php echo $user["phone"]; ?>">
                <span class="error" id="phoneErr"></span>

                <label for="address">Address:</label>
                <input type="text" name="address" id="address" value="<?php echo $user["address"]; ?>">

                <br>
                <input type="submit" value="Save changes">
                <span class="success" id="profileMsg"></span>
            </form>
        </div>
    </div>
</body>

</html>
