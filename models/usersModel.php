<?php
// ============================================================
// MEMBER 1 (galib) - Authentication & User Module
// ============================================================
require_once __DIR__."/dbConnect.php";

function login($userId, $pass)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT * FROM users WHERE userId=? AND pass=? AND status='active'";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $userId, $pass);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)>0)
        {
            $row=mysqli_fetch_assoc($result);
            return $row;
        }
        else
        {
            return null;
        }
    }
    else
    {
        echo "connection failed";
    }
}

function registerUser($userId, $name, $email, $phone, $address, $pass, $role)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="INSERT INTO users (userId, name, email, phone, address, pass, role) VALUES (?,?,?,?,?,?,?)";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $userId, $name, $email, $phone, $address, $pass, $role);

        if(mysqli_stmt_execute($stmt))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    else
    {
        echo "connection failed";
    }
}

function isUserIdTaken($userId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT userId FROM users WHERE userId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $userId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

function isEmailTaken($email)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT email FROM users WHERE email=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

function getUserById($userId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT * FROM users WHERE userId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $userId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)>0)
        {
            $row=mysqli_fetch_assoc($result);
            return $row;
        }
        else
        {
            return null;
        }
    }
}

function updateProfile($userId, $name, $email, $phone, $address)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE users SET name=?, email=?, phone=?, address=? WHERE userId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $phone, $address, $userId);

        if(mysqli_stmt_execute($stmt))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

function isEmailTakenByOther($email, $userId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT email FROM users WHERE email=? AND userId!=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email, $userId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

function changePassword($userId, $oldPass, $newPass)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT userId FROM users WHERE userId=? AND pass=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $userId, $oldPass);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)>0)
        {
            $sql2="UPDATE users SET pass=? WHERE userId=?";
            $stmt2=mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt2, "ss", $newPass, $userId);

            if(mysqli_stmt_execute($stmt2))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
}

?>
