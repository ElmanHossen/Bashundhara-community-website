<?php

session_start();
require_once "../../models/usersModel.php";

if(!isset($_SESSION["userId"]))
{
    header('Content-Type: application/json');
    echo json_encode(["success"=>false, "message"=>"Please login first"]);
    exit();
}

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $userId=$_SESSION["userId"];
    $name=trim($_POST["name"]);
    $email=trim($_POST["email"]);
    $phone=trim($_POST["phone"]);
    $address=trim($_POST["address"]);

    $nameErr="";
    $emailErr="";
    $phoneErr="";
    $hasErr=false;

    if(empty($name))
    {
        $hasErr=true;
        $nameErr="Name cannot be empty";
    }

    if(empty($email))
    {
        $hasErr=true;
        $emailErr="Email cannot be empty";
    }
    else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        $hasErr=true;
        $emailErr="Please enter a valid email";
    }
    else if(isEmailTakenByOther($email, $userId))
    {
        $hasErr=true;
        $emailErr="This email is used by another account";
    }

    if(empty($phone))
    {
        $hasErr=true;
        $phoneErr="Phone cannot be empty";
    }

    if($hasErr)
    {
        $response=["success"=>false, "nameErr"=>$nameErr, "emailErr"=>$emailErr, "phoneErr"=>$phoneErr];
    }
    else
    {
        if(updateProfile($userId, $name, $email, $phone, $address))
        {
            $_SESSION["name"]=$name;
            $response=["success"=>true, "message"=>"Profile updated successfully"];
        }
        else
        {
            $response=["success"=>false, "message"=>"Update failed. Please try again."];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

?>
