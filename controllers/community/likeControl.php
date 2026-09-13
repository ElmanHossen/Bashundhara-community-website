<?php

session_start();
require_once "../../models/postsModel.php";

header('Content-Type: application/json');

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    if(!isset($_SESSION["userId"]))
    {
        echo json_encode(["success"=>false, "message"=>"Please login to react"]);
        exit();
    }

    $userId=$_SESSION["userId"];
    $postId=$_POST["postId"];

    $state=toggleLike($postId, $userId);
    $count=getLikeCount($postId);

    echo json_encode(["success"=>true, "state"=>$state, "likeCount"=>$count, "postId"=>$postId]);
}

?>
