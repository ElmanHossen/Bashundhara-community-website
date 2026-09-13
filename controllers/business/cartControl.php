<?php
// MEMBER 3 (fahim) - shopping cart controller (AJAX, returns JSON)
session_start();
require_once "../../models/businessModel.php";

header('Content-Type: application/json');

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    if(!isset($_SESSION["userId"]))
    {
        echo json_encode(["success"=>false, "message"=>"Please login to use the cart"]);
        exit();
    }

    $userId=$_SESSION["userId"];
    $action=$_POST["action"];

    if($action=="add")
    {
        $productId=$_POST["productId"];
        $quantity=isset($_POST["quantity"]) ? $_POST["quantity"] : 1;

        if(!is_numeric($quantity) || $quantity<1)
        {
            echo json_encode(["success"=>false, "message"=>"Quantity must be at least 1"]);
            exit();
        }

        $product=getProductById($productId);

        if(!$product)
        {
            echo json_encode(["success"=>false, "message"=>"Product not found"]);
            exit();
        }

        if($product["stock"]<$quantity)
        {
            echo json_encode(["success"=>false, "message"=>"Not enough stock available"]);
            exit();
        }

        if(addToCart($userId, $productId, $quantity))
        {
            echo json_encode(["success"=>true, "message"=>"Added to cart"]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not add to cart"]);
        }
        exit();
    }

    if($action=="list")
    {
        $items=getCart($userId);
        $total=0;

        foreach($items as $item)
        {
            $total=$total+($item["price"]*$item["quantity"]);
        }

        echo json_encode(["success"=>true, "items"=>$items, "total"=>number_format($total, 2, '.', '')]);
        exit();
    }

    if($action=="updateQty")
    {
        $cartId=$_POST["cartId"];
        $quantity=$_POST["quantity"];

        if(!is_numeric($quantity) || $quantity<1)
        {
            echo json_encode(["success"=>false, "message"=>"Quantity must be at least 1"]);
            exit();
        }

        updateCartQuantity($cartId, $userId, $quantity);
        echo json_encode(["success"=>true, "message"=>"Cart updated"]);
        exit();
    }

    if($action=="remove")
    {
        $cartId=$_POST["cartId"];
        removeFromCart($cartId, $userId);
        echo json_encode(["success"=>true, "message"=>"Item removed"]);
        exit();
    }
}

?>
