<?php
session_start();
require_once "../../models/postsModel.php";

$postId=isset($_GET["postId"]) ? $_GET["postId"] : 0;
$post=getPostById($postId);

if(!$post || $post["status"]!="active")
{
    header("Location: feed.php");
    exit();
}
?>
<!doctype html>
<html>

<head>
    <title><?php echo $post["title"]; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/community.css">
    <script src="js/postDetailsJs.js" defer></script>
</head>

<body>
    <?php $base=".."; include "../partials/nav.php"; ?>

    <div class="container">
        <div class="card post">
            <span class="tag"><?php echo $post["categoryName"]; ?></span>
            <h1><?php echo $post["title"]; ?></h1>
            <p class="meta">by <?php echo $post["authorName"]; ?> on <?php echo $post["createdAt"]; ?></p>
            <p><?php echo $post["content"]; ?></p>

            <div class="post-actions">
                <button class="btn-small" onclick="likeThisPost()">
                    Like (<span id="likeCount"><?php echo $post["likeCount"]; ?></span>)
                </button>
                <a class="btn-small link-btn" href="feed.php">Back to feed</a>
            </div>
        </div>

        <div class="card">
            <h2>Comments</h2>
            <div id="commentList"></div>

            <?php if(isset($_SESSION["userId"])) { ?>
                <form id="commentForm">
                    <label for="comment">Write a comment:</label>
                    <textarea id="comment" name="comment"></textarea>
                    <br>
                    <input type="submit" value="Post comment">
                    <span class="error" id="commentMsg"></span>
                </form>
            <?php } else { ?>
                <p class="msg"><a href="../auth/login.php">Login</a> to join the discussion.</p>
            <?php } ?>
        </div>
    </div>

    <input type="hidden" id="postId" value="<?php echo $post["postId"]; ?>">
    <input type="hidden" id="currentUser" value="<?php echo isset($_SESSION["userId"]) ? $_SESSION["userId"] : ""; ?>">
</body>

</html>
