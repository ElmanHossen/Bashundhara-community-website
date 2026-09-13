<?php

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
        $status=isset($_POST["status"]) ? $_POST["status"] : "all";
        $reports=getReports($status);
        echo json_encode(["success"=>true, "reports"=>$reports]);
        exit();
    }

    if($action=="resolve")
    {
        $reportId=$_POST["reportId"];
        $status=$_POST["status"];

        if($status!="resolved" && $status!="dismissed" && $status!="pending")
        {
            echo json_encode(["success"=>false, "message"=>"Invalid status"]);
            exit();
        }

        updateReportStatus($reportId, $status);
        echo json_encode(["success"=>true, "message"=>"Report marked as ".$status]);
        exit();
    }

    if($action=="hidePost")
    {
        $postId=$_POST["postId"];
        $reportId=$_POST["reportId"];

        adminUpdatePostStatus($postId, "hidden");
        updateReportStatus($reportId, "resolved");

        echo json_encode(["success"=>true, "message"=>"Post hidden and report resolved"]);
        exit();
    }
}

?>
