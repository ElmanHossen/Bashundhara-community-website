<?php
require_once "../../models/usersModel.php";
if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $userId=trim($_POST["userId"]);
    $pass=$_POST["pass"];

    $userIdErr="";
    $passErr="";
    $hasErr=false;

    if(empty($userId))
    {
        $hasErr=true;
        $userIdErr="User id cannot be empty";
    }

    if(empty($pass))
    {
        $hasErr=true;
        $passErr="Password cannot be empty";
    }

    if($hasErr)
    {
        $response=["success"=>false, "userIdErr"=>$userIdErr, "passErr"=>$passErr];
    }
    else
    {
        $user=login($userId, $pass);

        if($user)
        {
            session_start();
            $_SESSION["userId"]=$user["userId"];
            $_SESSION["name"]=$user["name"];
            $_SESSION["role"]=$user["role"];

            if($user["role"]=="admin")
            {
                $redirect="../../views/admin/adminDashboard.php";
            }
            else if($user["role"]=="business")
            {
                $redirect="../../views/business/businessDashboard.php";
            }
            else
            {
                $redirect="../../views/user/userDashboard.php";
            }

            $response=["success"=>true, "redirect"=>$redirect];
        }
        else
        {
            $response=["success"=>false, "notFoundErr"=>"User not found or account is blocked"];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

?>
