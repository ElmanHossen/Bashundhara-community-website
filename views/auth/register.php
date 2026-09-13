<?php
session_start();
if(isset($_SESSION["userId"]))
{
    header("Location: ../user/userDashboard.php");
}
?>
<!doctype html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
    <script src="js/registerJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card auth-box">
            <h1>Create an account</h1>

            <form id="registerForm">
                <label for="userId">User Id:</label>
                <input type="text" name="userId" id="userId">
                <span class="error" id="userIdErr"></span>

                <label for="name">Full Name:</label>
                <input type="text" name="name" id="name">
                <span class="error" id="nameErr"></span>

                <label for="email">Email:</label>
                <input type="text" name="email" id="email">
                <span class="error" id="emailErr"></span>

                <label for="phone">Phone:</label>
                <input type="text" name="phone" id="phone">
                <span class="error" id="phoneErr"></span>

                <label for="address">Address:</label>
                <input type="text" name="address" id="address">

                <label for="role">Account Type:</label>
                <select name="role" id="role">
                    <option value="user">Community Member</option>
                    <option value="business">Business Owner</option>
                </select>

                <label for="pass">Password:</label>
                <input type="password" name="pass" id="pass">
                <span class="error" id="passErr"></span>

                <label for="confirmPass">Confirm Password:</label>
                <input type="password" name="confirmPass" id="confirmPass">
                <span class="error" id="confirmPassErr"></span>

                <br>
                <input type="submit" name="submit" value="Register">
                <span class="success" id="successMsg"></span>
            </form>

            <p class="msg">Already registered? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>

</html>
