<?php
session_start();
if(isset($_SESSION["userId"]))
{
    if($_SESSION["role"]=="admin")
    {
        header("Location: ../admin/adminDashboard.php");
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
?>
<!doctype html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
    <script src="js/loginJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card auth-box">
            <h1>Login</h1>

            <form id="loginForm">
                <label for="userId">User Id:</label>
                <input type="text" name="userId" id="userId">
                <span class="error" id="userIdErr"></span>

                <label for="pass">Password:</label>
                <input type="password" name="pass" id="pass">
                <span class="error" id="passErr"></span>

                <br>
                <input type="submit" name="submit" value="Login">
                <span class="error" id="notFoundErr"></span>
            </form>

            <p class="msg">No account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>

</html>
