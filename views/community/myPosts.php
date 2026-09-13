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
    <title>My Posts</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/community.css">
    <script src="js/myPostsJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card">
            <h1>My Posts</h1>
            <p class="msg">Edit or remove the posts you shared with the community.</p>
        </div>

        <div id="myPostList"></div>

        <div class="card" id="editBox">
            <h2>Edit post</h2>

            <form id="editForm">
                <input type="hidden" id="editPostId">

                <label for="editTitle">Title:</label>
                <input type="text" id="editTitle">

                <label for="editCategory">Category:</label>
                <select id="editCategory">
                    <?php foreach($categories as $c) { ?>
                        <option value="<?php echo $c["categoryId"]; ?>"><?php echo $c["categoryName"]; ?></option>
                    <?php } ?>
                </select>

                <label for="editContent">Content:</label>
                <textarea id="editContent"></textarea>

                <br>
                <input type="submit" value="Save changes">
                <button type="button" onclick="closeEdit()">Cancel</button>
                <span class="success" id="editMsg"></span>
            </form>
        </div>
    </div>
</body>

</html>
