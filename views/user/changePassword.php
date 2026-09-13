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
    <title>Change Password</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
    <script src="js/changePasswordJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card auth-box">
            <h1>Change Password</h1>

            <form id="passwordForm">
                <label for="oldPass">Old Password:</label>
                <input type="password" name="oldPass" id="oldPass">
                <span class="error" id="oldPassErr"></span>

                <label for="newPass">New Password:</label>
                <input type="password" name="newPass" id="newPass">
                <span class="error" id="newPassErr"></span>

                <label for="confirmPass">Confirm New Password:</label>
                <input type="password" name="confirmPass" id="confirmPass">
                <span class="error" id="confirmPassErr"></span>

                <br>
                <input type="submit" value="Update password">
                <span class="success" id="passMsg"></span>
            </form>
        </div>
    </div>
</body>

</html>
