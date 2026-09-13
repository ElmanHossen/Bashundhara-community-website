<?php

require_once "../../models/usersModel.php";

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $userId=trim($_POST["userId"]);
    $name=trim($_POST["name"]);
    $email=trim($_POST["email"]);
    $phone=trim($_POST["phone"]);
    $address=trim($_POST["address"]);
    $pass=$_POST["pass"];
    $confirmPass=$_POST["confirmPass"];
    $role=$_POST["role"];

    $userIdErr="";
    $nameErr="";
    $emailErr="";
    $phoneErr="";
    $passErr="";
    $confirmPassErr="";
    $hasErr=false;

    if(empty($userId))
    {
        $hasErr=true;
        $userIdErr="User id cannot be empty";
    }
    else if(strlen($userId)<4)
    {
        $hasErr=true;
        $userIdErr="User id must be at least 4 characters";
    }
    else if(isUserIdTaken($userId))
    {
        $hasErr=true;
        $userIdErr="This user id is already taken";
    }

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
    else if(isEmailTaken($email))
    {
        $hasErr=true;
        $emailErr="This email is already registered";
    }

    if(empty($phone))
    {
        $hasErr=true;
        $phoneErr="Phone cannot be empty";
    }

    if(empty($pass))
    {
        $hasErr=true;
        $passErr="Password cannot be empty";
    }
    else if(strlen($pass)<4)
    {
        $hasErr=true;
        $passErr="Password must be at least 4 characters";
    }

    if($pass!=$confirmPass)
    {
        $hasErr=true;
        $confirmPassErr="Passwords do not match";
    }

    if($role!="user" && $role!="business")
    {
        $role="user";
    }

    if($hasErr)
    {
        $response=[
            "success"=>false,
            "userIdErr"=>$userIdErr,
            "nameErr"=>$nameErr,
            "emailErr"=>$emailErr,
            "phoneErr"=>$phoneErr,
            "passErr"=>$passErr,
            "confirmPassErr"=>$confirmPassErr
        ];
    }
    else
    {
        if(registerUser($userId, $name, $email, $phone, $address, $pass, $role))
        {
            $response=["success"=>true, "message"=>"Registration successful. You can login now."];
        }
        else
        {
            $response=["success"=>false, "message"=>"Registration failed. Please try again."];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

?>
