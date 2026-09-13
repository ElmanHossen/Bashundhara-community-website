<?php
// MEMBER 4 (elman) - post & comment moderation controller (AJAX, returns JSON)
session_start();
require_once "../../models/adminModel.php";

header('Content-Type: application/json');

if(!isset($_SESSION["userId"]) || $_SESSION["role"]!="admin")
{
    echo json_encode(["success"=>false, "message"=>"Admin access only"]);
    exit();
}

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $action=$_POST["action"];

    if($action=="listPosts")
    {
        $search=isset($_POST["search"]) ? trim($_POST["search"]) : "";
        $posts=getAllPostsForAdmin($search);
        echo json_encode(["success"=>true, "posts"=>$posts]);
        exit();
    }

    if($action=="setPostStatus")
    {
        $postId=$_POST["postId"];
        $status=$_POST["status"];

        if($status!="active" && $status!="hidden" && $status!="deleted")
        {
            echo json_encode(["success"=>false, "message"=>"Invalid status"]);
            exit();
        }

        if(adminUpdatePostStatus($postId, $status))
        {
            echo json_encode(["success"=>true, "message"=>"Post set to ".$status]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not update the post"]);
        }
        exit();
    }

    if($action=="listComments")
    {
        $postId=$_POST["postId"];
        $comments=getCommentsForAdmin($postId);
        echo json_encode(["success"=>true, "comments"=>$comments]);
        exit();
    }

    if($action=="setCommentStatus")
    {
        $commentId=$_POST["commentId"];
        $status=$_POST["status"];

        if($status!="active" && $status!="deleted")
        {
            echo json_encode(["success"=>false, "message"=>"Invalid status"]);
            exit();
        }

        if(adminUpdateCommentStatus($commentId, $status))
        {
            echo json_encode(["success"=>true, "message"=>"Comment set to ".$status]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not update the comment"]);
        }
        exit();
    }
}

?>
