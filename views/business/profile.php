<?php

require_once "views/layouts/header.php";

<div class="card">
<h2>Business Profile Settings</h2>
<p style= "color: #666; margin-bottom: 20px;">Manage your business information visible to Bashundhara residents.</p>

<?php if (!empty($message)): ?>
        <div class="alert-success"><?php echo $message; ?></div>
<?php endif; ?> 

 <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo $error; ?></div>
    <?php endif; ?>



    form action="index.php?controller=business&action=profile" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Business Name *</label>
  <input type="text" name="business_name" required value="<?php echo htmlspecialchars($business['business_name'] ?? ''); ?>">
        </div>

         <div class="form-group">
            <label>Category *</label>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="Grocery & Super shop" <?php echo ($business['category'] ?? '') === 'Grocery & Super shop' ? 'selected' : ''; ?>>Grocery & Super shop</option>


                <option value="Pharmacy & Healthcare" <?php echo ($business['category'] ?? '') === 'Pharmacy & Healthcare' ? 'selected' : ''; ?>>Pharmacy & Healthcare</option>
                
                  <option value="Restaurant & Bakery" <?php echo ($business['category'] ?? '') === 'Restaurant & Bakery' ? 'selected' : ''; ?>>Restaurant & Bakery</option>

                  <option value="Home Services" <?php echo ($business['category'] ?? '') === 'Home Services' ? 'selected' : ''; ?>>Home Services</option>

            <option value="Stationery & Electronics" <?php echo ($business['category'] ?? '') === 'Stationery & Electronics' ? 'selected' : ''; ?>>Stationery & Electronics</option>

            </select>
        </div>

         <div class="form-group">
            <label>Phone Number *</label>
            <input type="text" name="phone" required value="<?php echo htmlspecialchars($business['phone'] ?? ''); ?>">

        </div>

      <div class="form-group">
            <label>Physical Address (Block / Road in Bashundhara) *</label>
            <input type="text" name="address" required value="<?php echo htmlspecialchars($business['address'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label>Opening Hours</label>
            <input type="text" name="opening_hours" placeholder="e.g. 9:00 AM - 10:00 PM" value="<?php echo htmlspecialchars($business['opening_hours'] ?? ''); ?>">
        </div>


      <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4"><?php echo htmlspecialchars($business['description'] ?? ''); ?></textarea>
        </div>
        
      <div class="form-group">
            <label>Business Logo</label>
            <input type="file" name="logo" accept="image/*">
            <?php if (!empty($business['logo']) && $business['logo'] !== 'default_logo.png'): ?>
                
                 <p style="margin-top: 5px;"><small>Current: <?php echo htmlspecialchars($business['logo']); ?></small></p>
            <?php endif; ?>
        </div>


       <button type="submit" class="btn">Save Profile</button>
    </form>
</div>



<?php

require_once "views/layouts/footer.php"; 

?>

        