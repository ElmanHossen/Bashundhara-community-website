<?php

session_start();
require_once "../../models/businessModel.php";

header('Content-Type: application/json');

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $action=$_POST["action"];

    if($action=="shop")
    {
        $search=isset($_POST["search"]) ? trim($_POST["search"]) : "";
        $pCategoryId=isset($_POST["pCategoryId"]) ? $_POST["pCategoryId"] : "0";

        $products=getProducts($search, $pCategoryId);
        echo json_encode(["success"=>true, "products"=>$products]);
        exit();
    }

    if(!isset($_SESSION["userId"]))
    {
        echo json_encode(["success"=>false, "message"=>"Please login first"]);
        exit();
    }

    $business=getBusinessByOwner($_SESSION["userId"]);

    if(!$business)
    {
        echo json_encode(["success"=>false, "message"=>"You do not own a business"]);
        exit();
    }

    if($business["status"]!="approved")
    {
        echo json_encode(["success"=>false, "message"=>"Your business is not approved yet"]);
        exit();
    }

    $businessId=$business["businessId"];

    if($action=="list")
    {
        $products=getProductsByBusiness($businessId);
        echo json_encode(["success"=>true, "products"=>$products]);
        exit();
    }

    if($action=="add")
    {
        $productName=trim($_POST["productName"]);
        $description=trim($_POST["description"]);
        $price=$_POST["price"];
        $stock=$_POST["stock"];
        $pCategoryId=$_POST["pCategoryId"];

        $nameErr="";
        $priceErr="";
        $stockErr="";
        $categoryErr="";
        $hasErr=false;

        if(empty($productName))
        {
            $hasErr=true;
            $nameErr="Product name cannot be empty";
        }

        if($price=="" || !is_numeric($price) || $price<=0)
        {
            $hasErr=true;
            $priceErr="Enter a valid price";
        }

        if($stock=="" || !is_numeric($stock) || $stock<0)
        {
            $hasErr=true;
            $stockErr="Enter a valid stock quantity";
        }

        if(empty($pCategoryId) || $pCategoryId=="0")
        {
            $hasErr=true;
            $categoryErr="Please select a category";
        }

        if($hasErr)
        {
            echo json_encode(["success"=>false, "nameErr"=>$nameErr, "priceErr"=>$priceErr, "stockErr"=>$stockErr, "categoryErr"=>$categoryErr]);
            exit();
        }

        if(addProduct($businessId, $pCategoryId, $productName, $description, $price, $stock, ""))
        {
            echo json_encode(["success"=>true, "message"=>"Product added"]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not add the product"]);
        }
        exit();
    }

    if($action=="update")
    {
        $productId=$_POST["productId"];
        $productName=trim($_POST["productName"]);
        $description=trim($_POST["description"]);
        $price=$_POST["price"];
        $stock=$_POST["stock"];
        $pCategoryId=$_POST["pCategoryId"];

        if(empty($productName) || !is_numeric($price) || !is_numeric($stock))
        {
            echo json_encode(["success"=>false, "message"=>"Please fill all fields correctly"]);
            exit();
        }

        if(updateProduct($productId, $businessId, $pCategoryId, $productName, $description, $price, $stock))
        {
            echo json_encode(["success"=>true, "message"=>"Product updated"]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not update the product"]);
        }
        exit();
    }

    if($action=="delete")
    {
        $productId=$_POST["productId"];

        if(deleteProduct($productId, $businessId))
        {
            echo json_encode(["success"=>true, "message"=>"Product removed"]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not remove the product"]);
        }
        exit();
    }
}

?>
