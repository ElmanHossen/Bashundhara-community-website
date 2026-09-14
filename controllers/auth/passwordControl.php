<?php

session_start();
require_once "../../models/usersModel.php";

if(!isset($_SESSION["userId"])){   
    header('Content-Type: application/json');
    echo json_encode(["success"=>false, "message"=>"Please login first"]);
    exit();
}

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $userId=$_SESSION["userId"];
    $oldPass=$_POST["oldPass"];
    $newPass=$_POST["newPass"];
    $confirmPass=$_POST["confirmPass"];

    $oldPassErr="";
    $newPassErr="";
    $confirmPassErr="";
    $hasErr=false;

    if(empty($oldPass))
    {
        $hasErr=true;
        $oldPassErr="Old password cannot be empty";
    }

    if(empty($newPass))
    {
        $hasErr=true;
        $newPassErr="New password cannot be empty";
    }
    else if(strlen($newPass)<4)
    {
        $hasErr=true;
        $newPassErr="New password must be at least 4 characters";
    }

    if($newPass!=$confirmPass)
    {
        $hasErr=true;
        $confirmPassErr="Passwords do not match";
    }

    if($hasErr)
    {
        $response=["success"=>false, "oldPassErr"=>$oldPassErr, "newPassErr"=>$newPassErr, "confirmPassErr"=>$confirmPassErr];
    }
    else
    {
        if(changePassword($userId, $oldPass, $newPass))
        {
            $response=["success"=>true, "message"=>"Password changed successfully"];
        }
        else
        {
            $response=["success"=>false, "oldPassErr"=>"Old password is wrong"];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

?>
