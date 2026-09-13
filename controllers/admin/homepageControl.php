<?php
// MEMBER 4 (elman) - homepage content controller (AJAX, returns JSON)
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

    if($action=="list")
    {
        $content=getHomepageContent();
        echo json_encode(["success"=>true, "content"=>$content]);
        exit();
    }

    if($action=="update")
    {
        $contentId=$_POST["contentId"];
        $title=trim($_POST["title"]);
        $content=trim($_POST["content"]);

        if(empty($title) || empty($content))
        {
            echo json_encode(["success"=>false, "message"=>"Title and content cannot be empty"]);
            exit();
        }

        if(updateHomepageContent($contentId, $title, $content))
        {
            echo json_encode(["success"=>true, "message"=>"Homepage section updated"]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not update the section"]);
        }
        exit();
    }
}

?>
