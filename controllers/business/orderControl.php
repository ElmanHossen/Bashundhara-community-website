<?php
// MEMBER 3 (fahim) - order controller (AJAX, returns JSON)
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

    $userId=$_SESSION["userId"];
    $action=$_POST["action"];

    // ---------- PLACE ORDER ----------
    if($action=="place")
    {
        $shippingAddress=trim($_POST["shippingAddress"]);

        if(empty($shippingAddress))
        {
            echo json_encode(["success"=>false, "addressErr"=>"Shipping address cannot be empty"]);
            exit();
        }

        $orderId=placeOrder($userId, $shippingAddress);

        if($orderId)
        {
            echo json_encode(["success"=>true, "message"=>"Order placed. Your order number is ".$orderId]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Your cart is empty"]);
        }
        exit();
    }

    // ---------- MY ORDERS ----------
    if($action=="myOrders")
    {
        $orders=getOrdersByUser($userId);

        for($i=0; $i<count($orders); $i++)
        {
            $orders[$i]["items"]=getOrderItems($orders[$i]["orderId"]);
        }

        echo json_encode(["success"=>true, "orders"=>$orders]);
        exit();
    }

    // ---------- BUSINESS ORDERS ----------
    if($action=="businessOrders")
    {
        $business=getBusinessByOwner($userId);

        if(!$business)
        {
            echo json_encode(["success"=>false, "message"=>"You do not own a business"]);
            exit();
        }

        $orders=getOrdersForBusiness($business["businessId"]);

        for($i=0; $i<count($orders); $i++)
        {
            $orders[$i]["items"]=getOrderItems($orders[$i]["orderId"]);
        }

        echo json_encode(["success"=>true, "orders"=>$orders]);
        exit();
    }

    // ---------- UPDATE STATUS ----------
    if($action=="updateStatus")
    {
        $business=getBusinessByOwner($userId);

        if(!$business)
        {
            echo json_encode(["success"=>false, "message"=>"You do not own a business"]);
            exit();
        }

        $orderId=$_POST["orderId"];
        $status=$_POST["status"];

        if($status!="pending" && $status!="processing" && $status!="delivered" && $status!="cancelled")
        {
            echo json_encode(["success"=>false, "message"=>"Invalid status"]);
            exit();
        }

        updateOrderStatus($orderId, $status);
        echo json_encode(["success"=>true, "message"=>"Order status updated"]);
        exit();
    }
}

?>
