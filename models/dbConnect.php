<?php
$serverName="localhost";
$userName="root";
$password="";
$db="community_portal";

function dbConnection()
{
    global $serverName;
    global $userName;
    global $password;
    global $db;
    $conn=mysqli_connect($serverName, $userName, $password, $db);

    if($conn)
    {
        return $conn;
    }
    else
    {
        echo "connection failed".mysqli_connect_error();
    }
}

?>
