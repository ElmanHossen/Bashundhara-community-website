<?php
// SHARED FILE - do not edit on a personal branch.
// $base = relative path back to the "views" folder from the current page.
if(!isset($base)) { $base=".."; }
?>
<div class="navbar">
    <a class="brand" href="<?php echo $base; ?>/home.php">Community Portal</a>

    <a href="<?php echo $base; ?>/community/feed.php">Community</a>
    <a href="<?php echo $base; ?>/marketplace/shop.php">Shop</a>

    <?php if(isset($_SESSION["userId"])) { ?>
        <a href="<?php echo $base; ?>/marketplace/cart.php">Cart</a>
        <a href="<?php echo $base; ?>/user/userDashboard.php">Dashboard</a>
        <a href="<?php echo $base; ?>/auth/logout.php">Logout (<?php echo $_SESSION["userId"]; ?>)</a>
    <?php } else { ?>
        <a href="<?php echo $base; ?>/auth/login.php">Login</a>
        <a href="<?php echo $base; ?>/auth/register.php">Register</a>
    <?php } ?>
</div>
