<?php
session_start();
require_once "../../models/postsModel.php";

$categories=getPostCategories();
?>
<!doctype html>
<html>

<head>
    <title>Community Feed</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/community.css">
    <script src="js/feedJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>Community Feed</h1>

            <div class="filter-bar">
                <input type="text" id="searchBox" placeholder="Search posts..." onkeyup="loadPosts()">

                <select id="categoryFilter" onchange="loadPosts()">
                    <option value="0">All categories</option>
                    <?php foreach($categories as $c) { ?>
                        <option value="<?php echo $c["categoryId"]; ?>"><?php echo $c["categoryName"]; ?></option>
                    <?php } ?>
                </select>

                <?php if(isset($_SESSION["userId"])) { ?>
                    <a class="new-post-link" href="createPost.php">Write a post</a>
                <?php } ?>
            </div>
        </div>

        <div id="postList"></div>
    </div>
</body>

</html>
