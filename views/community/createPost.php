<?php
session_start();
require_once "../../models/postsModel.php";

if(!isset($_SESSION["userId"]))
{
    header("Location: ../auth/login.php");
}

$categories=getPostCategories();
?>
<!doctype html>
<html>

<head>
    <title>Write a Post</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/community.css">
    <script src="js/createPostJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Write a Post</h1>

            <form id="postForm">
                <label for="title">Title:</label>
                <input type="text" name="title" id="title">
                <span class="error" id="titleErr"></span>

                <label for="categoryId">Category:</label>
                <select name="categoryId" id="categoryId">
                    <option value="0">Select a category</option>
                    <?php foreach($categories as $c) { ?>
                        <option value="<?php echo $c["categoryId"]; ?>"><?php echo $c["categoryName"]; ?></option>
                    <?php } ?>
                </select>
                <span class="error" id="categoryErr"></span>

                <label for="content">What do you want to share?</label>
                <textarea name="content" id="content"></textarea>
                <span class="error" id="contentErr"></span>

                <br>
                <input type="submit" value="Publish post">
                <span class="success" id="postMsg"></span>
            </form>
        </div>
    </div>
</body>

</html>
