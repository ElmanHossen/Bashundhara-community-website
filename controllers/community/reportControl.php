<?php
session_start();
require_once "../../models/postsModel.php";

header('Content-Type: application/json');

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    if(!isset($_SESSION["userId"]))
    {
        echo json_encode(["success"=>false, "message"=>"Please login to report a post"]);
        exit();
    }

    $reporterId=$_SESSION["userId"];
    $postId=$_POST["postId"];
    $reason=trim($_POST["reason"]);

    if(empty($reason))
    {
        echo json_encode(["success"=>false, "message"=>"Please write why you are reporting this post"]);
        exit();
    }

    if(addReport($reporterId, $postId, $reason))
    {
        echo json_encode(["success"=>true, "message"=>"Report sent to the admin"]);
    }
    else
    {
        echo json_encode(["success"=>false, "message"=>"Could not send the report"]);
    }
}

?>
