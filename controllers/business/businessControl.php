<?php

session_start();
require_once "../../models/businessModel.php";

header('Content-Type: application/json');

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    if(!isset($_SESSION["userId"]))
    {
        echo json_encode(["success"=>false, "message"=>"Please login first"]);
        exit();
    }

    $ownerId=$_SESSION["userId"];
    $action=$_POST["action"];

    $businessName=trim($_POST["businessName"]);
    $description=trim($_POST["description"]);
    $address=trim($_POST["address"]);
    $phone=trim($_POST["phone"]);

    $nameErr="";
    $addressErr="";
    $phoneErr="";
    $hasErr=false;

    if(empty($businessName))
    {
        $hasErr=true;
        $nameErr="Business name cannot be empty";
    }

    if(empty($address))
    {
        $hasErr=true;
        $addressErr="Address cannot be empty";
    }

    if(empty($phone))
    {
        $hasErr=true;
        $phoneErr="Phone cannot be empty";
    }

    if($hasErr)
    {
        echo json_encode(["success"=>false, "nameErr"=>$nameErr, "addressErr"=>$addressErr, "phoneErr"=>$phoneErr]);
        exit();
    }

    if($action=="register")
    {
        if(getBusinessByOwner($ownerId))
        {
            echo json_encode(["success"=>false, "message"=>"You already registered a business"]);
            exit();
        }

        if(registerBusiness($ownerId, $businessName, $description, $address, $phone))
        {
            echo json_encode(["success"=>true, "message"=>"Business submitted. Wait for admin approval."]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not register the business"]);
        }
        exit();
    }

    if($action=="update")
    {
        $businessId=$_POST["businessId"];

        if(updateBusiness($businessId, $ownerId, $businessName, $description, $address, $phone))
        {
            echo json_encode(["success"=>true, "message"=>"Business profile updated"]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not update the profile"]);
        }
        exit();
    }
}

?>
