<?php
// MEMBER 4 (elman) - notice & event controller (AJAX, returns JSON)
session_start();
require_once "../../models/adminModel.php";

header('Content-Type: application/json');

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $action=$_POST["action"];

    // public listing for the homepage
    if($action=="publicList")
    {
        $notices=getNotices("all");
        echo json_encode(["success"=>true, "notices"=>$notices]);
        exit();
    }

    if(!isset($_SESSION["userId"]) || $_SESSION["role"]!="admin")
    {
        echo json_encode(["success"=>false, "message"=>"Admin access only"]);
        exit();
    }

    if($action=="list")
    {
        $type=isset($_POST["type"]) ? $_POST["type"] : "all";
        $notices=getNotices($type);
        echo json_encode(["success"=>true, "notices"=>$notices]);
        exit();
    }

    if($action=="add" || $action=="update")
    {
        $title=trim($_POST["title"]);
        $content=trim($_POST["content"]);
        $type=$_POST["type"];
        $eventDate=$_POST["eventDate"];

        $titleErr="";
        $contentErr="";
        $dateErr="";
        $hasErr=false;

        if(empty($title))
        {
            $hasErr=true;
            $titleErr="Title cannot be empty";
        }

        if(empty($content))
        {
            $hasErr=true;
            $contentErr="Content cannot be empty";
        }

        if($type=="event" && empty($eventDate))
        {
            $hasErr=true;
            $dateErr="An event needs a date";
        }

        if($hasErr)
        {
            echo json_encode(["success"=>false, "titleErr"=>$titleErr, "contentErr"=>$contentErr, "dateErr"=>$dateErr]);
            exit();
        }

        if(empty($eventDate))
        {
            $eventDate=null;
        }

        if($action=="add")
        {
            if(addNotice($title, $content, $type, $eventDate, $_SESSION["userId"]))
            {
                echo json_encode(["success"=>true, "message"=>"Notice published"]);
            }
            else
            {
                echo json_encode(["success"=>false, "message"=>"Could not publish the notice"]);
            }
        }
        else
        {
            $noticeId=$_POST["noticeId"];

            if(updateNotice($noticeId, $title, $content, $type, $eventDate))
            {
                echo json_encode(["success"=>true, "message"=>"Notice updated"]);
            }
            else
            {
                echo json_encode(["success"=>false, "message"=>"Could not update the notice"]);
            }
        }
        exit();
    }

    if($action=="delete")
    {
        $noticeId=$_POST["noticeId"];

        if(deleteNotice($noticeId))
        {
            echo json_encode(["success"=>true, "message"=>"Notice deleted"]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not delete the notice"]);
        }
        exit();
    }
}

?>
