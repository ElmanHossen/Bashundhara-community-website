<?php


require_once __DIR__."/dbConnect.php";

function getPostCategories()
{
    $conn=dbConnection();
    $categories=[];

    if($conn)
    {
        $sql="SELECT * FROM post_categories ORDER BY categoryName";
        $result=mysqli_query($conn, $sql);

        while($row=mysqli_fetch_assoc($result))
        {
            $categories[]=$row;
        }
    }

    return $categories;
}

function addPost($userId, $categoryId, $title, $content)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="INSERT INTO posts (userId, categoryId, title, content) VALUES (?,?,?,?)";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "siss", $userId, $categoryId, $title, $content);

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

function getPosts($search, $categoryId)
{
    $conn=dbConnection();
    $posts=[];

    if($conn)
    {
        $search="%".$search."%";

        if($categoryId=="" || $categoryId=="0")
        {
            $sql="SELECT p.*, u.name AS authorName, c.categoryName,
                         (SELECT COUNT(*) FROM likes l WHERE l.postId=p.postId) AS likeCount,
                         (SELECT COUNT(*) FROM comments cm WHERE cm.postId=p.postId AND cm.status='active') AS commentCount
                  FROM posts p
                  JOIN users u ON p.userId=u.userId
                  LEFT JOIN post_categories c ON p.categoryId=c.categoryId
                  WHERE p.status='active' AND (p.title LIKE ? OR p.content LIKE ?)
                  ORDER BY p.createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $search, $search);
        }
        else
        {
            $sql="SELECT p.*, u.name AS authorName, c.categoryName,
                         (SELECT COUNT(*) FROM likes l WHERE l.postId=p.postId) AS likeCount,
                         (SELECT COUNT(*) FROM comments cm WHERE cm.postId=p.postId AND cm.status='active') AS commentCount
                  FROM posts p
                  JOIN users u ON p.userId=u.userId
                  LEFT JOIN post_categories c ON p.categoryId=c.categoryId
                  WHERE p.status='active' AND p.categoryId=? AND (p.title LIKE ? OR p.content LIKE ?)
                  ORDER BY p.createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iss", $categoryId, $search, $search);
        }

        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $posts[]=$row;
        }
    }

    return $posts;
}

function getPostsByUser($userId)
{
    $conn=dbConnection();
    $posts=[];

    if($conn)
    {
        $sql="SELECT p.*, c.categoryName,
                     (SELECT COUNT(*) FROM likes l WHERE l.postId=p.postId) AS likeCount
              FROM posts p
              LEFT JOIN post_categories c ON p.categoryId=c.categoryId
              WHERE p.userId=? AND p.status='active'
              ORDER BY p.createdAt DESC";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $userId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $posts[]=$row;
        }
    }

    return $posts;
}

function getPostById($postId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT p.*, u.name AS authorName, c.categoryName,
                     (SELECT COUNT(*) FROM likes l WHERE l.postId=p.postId) AS likeCount
              FROM posts p
              JOIN users u ON p.userId=u.userId
              LEFT JOIN post_categories c ON p.categoryId=c.categoryId
              WHERE p.postId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $postId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)>0)
        {
            return mysqli_fetch_assoc($result);
        }
        else
        {
            return null;
        }
    }
}

function updatePost($postId, $userId, $categoryId, $title, $content)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE posts SET categoryId=?, title=?, content=? WHERE postId=? AND userId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issis", $categoryId, $title, $content, $postId, $userId);

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

function deletePost($postId, $userId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE posts SET status='deleted' WHERE postId=? AND userId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $postId, $userId);

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


function addComment($postId, $userId, $comment)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="INSERT INTO comments (postId, userId, comment) VALUES (?,?,?)";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $postId, $userId, $comment);

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

function getComments($postId)
{
    $conn=dbConnection();
    $comments=[];

    if($conn)
    {
        $sql="SELECT cm.*, u.name AS authorName
              FROM comments cm
              JOIN users u ON cm.userId=u.userId
              WHERE cm.postId=? AND cm.status='active'
              ORDER BY cm.createdAt ASC";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $postId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $comments[]=$row;
        }
    }

    return $comments;
}

function deleteComment($commentId, $userId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE comments SET status='deleted' WHERE commentId=? AND userId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $commentId, $userId);

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


function hasLiked($postId, $userId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT likeId FROM likes WHERE postId=? AND userId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $postId, $userId);
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

function toggleLike($postId, $userId)
{
    $conn=dbConnection();
    if($conn)
    {
        if(hasLiked($postId, $userId))
        {
            $sql="DELETE FROM likes WHERE postId=? AND userId=?";
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "is", $postId, $userId);
            mysqli_stmt_execute($stmt);
            return "unliked";
        }
        else
        {
            $sql="INSERT INTO likes (postId, userId) VALUES (?,?)";
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "is", $postId, $userId);
            mysqli_stmt_execute($stmt);
            return "liked";
        }
    }
}

function getLikeCount($postId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT COUNT(*) AS total FROM likes WHERE postId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $postId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);
        $row=mysqli_fetch_assoc($result);
        return $row["total"];
    }
}


function addReport($reporterId, $postId, $reason)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="INSERT INTO reports (reporterId, postId, reason) VALUES (?,?,?)";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sis", $reporterId, $postId, $reason);

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

?>
