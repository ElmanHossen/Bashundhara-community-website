<?php
session_start();
require_once "../../models/businessModel.php";

$categories=getProductCategories();
?>
<!doctype html>
<html>

<head>
    <title>Shop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/business.css">
    <script src="js/shopJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Shop local products</h1>

            <div class="filter-bar">
                <input type="text" id="searchBox" placeholder="Search products..." onkeyup="loadProducts()">

                <select id="categoryFilter" onchange="loadProducts()">
                    <option value="0">All categories</option>
                    <?php foreach($categories as $c) { ?>
                        <option value="<?php echo $c["pCategoryId"]; ?>"><?php echo $c["pCategoryName"]; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div id="productGrid" class="product-grid"></div>
    </div>
</body>

</html>
