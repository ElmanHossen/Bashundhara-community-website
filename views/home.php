<?php

session_start();
require_once "../models/adminModel.php";

$hero=getHomepageSection("hero");
$about=getHomepageSection("about");
$notices=getNotices("all");
?>
<!doctype html>
<html>

<head>
    <title>Community Portal</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <?php $base="."; include "partials/nav.php"; ?>

    <div class="container">
        <div class="card hero">
            <h1><?php echo $hero["title"]; ?></h1>
            <p><?php echo $hero["content"]; ?></p>
            <a class="hero-link" href="community/feed.php">Visit the community feed</a>
            <a class="hero-link" href="marketplace/shop.php">Browse local shops</a>
        </div>

        <div class="card">
            <h2>Notices and events</h2>

            <?php if(count($notices)==0) { ?>
                <p class="msg">No notices right now.</p>
            <?php } else { ?>
                <?php foreach($notices as $n) { ?>
                    <div class="notice">
                        <span class="tag"><?php echo $n["type"]; ?></span>
                        <h2><?php echo $n["title"]; ?></h2>
                        <p class="meta">
                            Posted <?php echo $n["createdAt"]; ?>
                            <?php if($n["eventDate"]!=null) { ?>
                                | Event date: <?php echo $n["eventDate"]; ?>
                            <?php } ?>
                        </p>
                        <p><?php echo $n["content"]; ?></p>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>

        <div class="card">
            <h2><?php echo $about["title"]; ?></h2>
            <p><?php echo $about["content"]; ?></p>
        </div>
    </div>
</body>

</html>
