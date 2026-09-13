<?php 
require_once "views/layouts/header.php"; 
?>

<div class="card">
<h2>Merchant Dashboard</h2>



<?php 
if ($business): 

?>
        <p style="margin-top: 10px;">Welcome back, <strong><?php echo htmlspecialchars($business['business_name']); ?></strong>!</p>
        <p style="color: #6b7280; font-size: 0.9rem; margin-top: 5px;">
            
    Category: <?php 
    echo htmlspecialchars($business['category']); ?> | 
        Status: <span style="color: green; font-weight: bold;"><?php 
            echo ucfirst($business['status']); ?></span>
        </p>

    <?php else: 
        ?>
        <p style="color: #dc2626; margin-top: 10px;">You haven't set up your store profile yet! 
            <a href="index.php?controller=business&action=profile" style="color: #2563eb; font-weight: bold;">Click here to set up your profile.</a>
        </p>


    <?php endif; 
    ?>

    <div class="stats-grid">
        <div class="stat-box">
            <h3>Listed Products</h3>
            <p><?php echo $stats['total_products']; ?></p>
        </div>



        <div class="stat-box">
            <h3>Pending Orders</h3>
            <p><?php echo $stats['pending_orders'] ?? 0; ?></p>
        </div>


        
        <div class="stat-box">
            <h3>Completed Orders</h3>
            <p><?php echo $stats['completed_orders']; ?></p>
</div>
</div>
</div>


<?php 
require_once "views/layouts/footer.php"; 
?>