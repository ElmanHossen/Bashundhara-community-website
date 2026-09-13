<?php
// MEMBER 4 (elman) - user management controller (AJAX, returns JSON)
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
        $search=isset($_POST["search"]) ? trim($_POST["search"]) : "";
        $role=isset($_POST["role"]) ? $_POST["role"] : "all";

        $users=getAllUsers($search, $role);
        echo json_encode(["success"=>true, "users"=>$users]);
        exit();
    }

    if($action=="setStatus")
    {
        $userId=$_POST["userId"];
        $status=$_POST["status"];

        if($status!="active" && $status!="blocked")
        {
            echo json_encode(["success"=>false, "message"=>"Invalid status"]);
            exit();
        }

        if($userId==$_SESSION["userId"])
        {
            echo json_encode(["success"=>false, "message"=>"You cannot block your own account"]);
            exit();
        }

        if(updateUserStatus($userId, $status))
        {
            echo json_encode(["success"=>true, "message"=>"User set to ".$status]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not update the user"]);
        }
        exit();
    }
}

?>
