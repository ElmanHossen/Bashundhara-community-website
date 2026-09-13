<?php
// MEMBER 4 (elman) - business approval controller (AJAX, returns JSON)
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
        $businesses=getAllBusinesses($status);
        echo json_encode(["success"=>true, "businesses"=>$businesses]);
        exit();
    }

    if($action=="setStatus")
    {
        $businessId=$_POST["businessId"];
        $status=$_POST["status"];

        if($status!="approved" && $status!="rejected" && $status!="pending")
        {
            echo json_encode(["success"=>false, "message"=>"Invalid status"]);
            exit();
        }

        if(updateBusinessStatus($businessId, $status))
        {
            echo json_encode(["success"=>true, "message"=>"Business ".$status]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not update the business"]);
        }
        exit();
    }
}

?>
