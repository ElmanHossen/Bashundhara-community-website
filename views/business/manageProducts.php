<?php
session_start();
require_once "../../models/businessModel.php";

if(!isset($_SESSION["userId"]))
{
    header("Location: ../auth/login.php");
}

$business=getBusinessByOwner($_SESSION["userId"]);

if(!$business)
{
    header("Location: businessRegister.php");
    exit();
}

$categories=getProductCategories();
?>
<!doctype html>
<html>

<head>
    <title>Manage Products</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/business.css">
    <script src="js/manageProductsJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Add a product</h1>

            <form id="productForm">
                <input type="hidden" id="productId" value="0">

                <label for="productName">Product Name:</label>
                <input type="text" id="productName">
                <span class="error" id="nameErr"></span>

                <label for="pCategoryId">Category:</label>
                <select id="pCategoryId">
                    <option value="0">Select a category</option>
                    <?php foreach($categories as $c) { ?>
                        <option value="<?php echo $c["pCategoryId"]; ?>"><?php echo $c["pCategoryName"]; ?></option>
                    <?php } ?>
                </select>
                <span class="error" id="categoryErr"></span>

                <label for="description">Description:</label>
                <textarea id="description"></textarea>

                <label for="price">Price (Tk):</label>
                <input type="number" id="price" step="0.01">
                <span class="error" id="priceErr"></span>

                <label for="stock">Stock:</label>
                <input type="number" id="stock">
                <span class="error" id="stockErr"></span>

                <br>
                <input type="submit" id="submitBtn" value="Add product">
                <button type="button" id="cancelBtn" onclick="resetForm()">Cancel edit</button>
                <span class="success" id="productMsg"></span>
            </form>
        </div>

        <div class="card">
            <h2>My products</h2>
            <div id="productList"></div>
        </div>
    </div>
</body>

</html>
