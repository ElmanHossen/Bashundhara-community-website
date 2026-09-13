<?php

session_start();
require_once "../../models/postsModel.php";

header('Content-Type: application/json');

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $action=$_POST["action"];

    if($action=="list")
    {
        $search=isset($_POST["search"]) ? trim($_POST["search"]) : "";
        $categoryId=isset($_POST["categoryId"]) ? $_POST["categoryId"] : "0";

        $posts=getPosts($search, $categoryId);
        echo json_encode(["success"=>true, "posts"=>$posts]);
        exit();
    }

    if($action=="myPosts")
    {
        if(!isset($_SESSION["userId"]))
        {
            echo json_encode(["success"=>false, "message"=>"Please login first"]);
            exit();
        }

        $posts=getPostsByUser($_SESSION["userId"]);
        echo json_encode(["success"=>true, "posts"=>$posts]);
        exit();
    }

    if(!isset($_SESSION["userId"]))
    {
        echo json_encode(["success"=>false, "message"=>"Please login first"]);
        exit();
    }

    $userId=$_SESSION["userId"];

    if($action=="add")
    {
        $title=trim($_POST["title"]);
        $content=trim($_POST["content"]);
        $categoryId=$_POST["categoryId"];

        $titleErr="";
        $contentErr="";
        $categoryErr="";
        $hasErr=false;

        if(empty($title))
        {
            $hasErr=true;
            $titleErr="Title cannot be empty";
        }
        else if(strlen($title)>150)
        {
            $hasErr=true;
            $titleErr="Title is too long";
        }

        if(empty($content))
        {
            $hasErr=true;
            $contentErr="Post content cannot be empty";
        }

        if(empty($categoryId) || $categoryId=="0")
        {
            $hasErr=true;
            $categoryErr="Please select a category";
        }

        if($hasErr)
        {
            echo json_encode(["success"=>false, "titleErr"=>$titleErr, "contentErr"=>$contentErr, "categoryErr"=>$categoryErr]);
        }
        else
        {
            if(addPost($userId, $categoryId, $title, $content))
            {
                echo json_encode(["success"=>true, "message"=>"Post published successfully"]);
            }
            else
            {
                echo json_encode(["success"=>false, "message"=>"Could not publish the post"]);
            }
        }
        exit();
    }

    if($action=="update")
    {
        $postId=$_POST["postId"];
        $title=trim($_POST["title"]);
        $content=trim($_POST["content"]);
        $categoryId=$_POST["categoryId"];

        if(empty($title) || empty($content))
        {
            echo json_encode(["success"=>false, "message"=>"Title and content cannot be empty"]);
            exit();
        }

        if(updatePost($postId, $userId, $categoryId, $title, $content))
        {
            echo json_encode(["success"=>true, "message"=>"Post updated"]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not update the post"]);
        }
        exit();
    }

    if($action=="delete")
    {
        $postId=$_POST["postId"];

        if(deletePost($postId, $userId))
        {
            echo json_encode(["success"=>true, "message"=>"Post deleted"]);
        }
        else
        {
            echo json_encode(["success"=>false, "message"=>"Could not delete the post"]);
        }
        exit();
    }
}

?>
