<?php
// MEMBER 2 (amit) - comment controller (AJAX, returns JSON)
session_start();
require_once "../../models/postsModel.php";

header('Content-Type: application/json');

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $action=$_POST["action"];

    // ---------- LIST COMMENTS ----------
    if($action=="list")
    {
        $postId=$_POST["postId"];
        $comments=getComments($postId);
        echo json_encode(["success"=>true, "comments"=>$comments]);
        exit();
    }

    if(!isset($_SESSION["userId"]))
    {
        echo json_encode(["success"=>false, "message"=>"Please login to comment"]);
        exit();
    }

    $userId=$_SESSION["userId"];

    // ---------- ADD COMMENT ----------
    if($action=="add")
    {
        $postId=$_POST["postId"];
        $comment=trim($_POST["comment"]);

        if(empty($comment))
        {
            echo json_encode(["success"=>false, "message"=>"Comment cannot be empty"]);
            exit();
        }

        if(addComment($postId, $userId, $comment))
        {
            $comments=getComments($postId);
            echo json_encode(["success"=>true, "comments"=>$comments]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not add the comment"]);
        }
        exit();
    }

    // ---------- DELETE COMMENT ----------
    if($action=="delete")
    {
        $commentId=$_POST["commentId"];
        $postId=$_POST["postId"];

        if(deleteComment($commentId, $userId))
        {
            $comments=getComments($postId);
            echo json_encode(["success"=>true, "comments"=>$comments]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"You can only delete your own comment"]);
        }
        exit();
    }
}

?>
