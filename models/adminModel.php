<?php

require_once __DIR__."/dbConnect.php";

function getAllUsers($search, $role)
{
    $conn=dbConnection();
    $users=[];

    if($conn)
    {
        $search="%".$search."%";

        if($role=="" || $role=="all")
        {
            $sql="SELECT userId, name, email, phone, role, status, createdAt FROM users
                  WHERE (userId LIKE ? OR name LIKE ? OR email LIKE ?)
                  ORDER BY createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $search, $search, $search);
        }
        else
        {
            $sql="SELECT userId, name, email, phone, role, status, createdAt FROM users
                  WHERE role=? AND (userId LIKE ? OR name LIKE ? OR email LIKE ?)
                  ORDER BY createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $role, $search, $search, $search);
        }

        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $users[]=$row;
        }
    }

    return $users;
}

function updateUserStatus($userId, $status)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE users SET status=? WHERE userId=? AND role!='admin'";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $status, $userId);
        return mysqli_stmt_execute($stmt);
    }
}

function getAllBusinesses($status)
{
    $conn=dbConnection();
    $businesses=[];

    if($conn)
    {
        if($status=="" || $status=="all")
        {
            $sql="SELECT b.*, u.name AS ownerName, u.email AS ownerEmail
                  FROM businesses b
                  JOIN users u ON b.ownerId=u.userId
                  ORDER BY b.createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
        }
        else
        {
            $sql="SELECT b.*, u.name AS ownerName, u.email AS ownerEmail
                  FROM businesses b
                  JOIN users u ON b.ownerId=u.userId
                  WHERE b.status=?
                  ORDER BY b.createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $status);
        }

        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $businesses[]=$row;
        }
    }

    return $businesses;
}

function updateBusinessStatus($businessId, $status)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE businesses SET status=? WHERE businessId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $businessId);

        if(mysqli_stmt_execute($stmt))
        {
            if($status=="approved")
            {
                $sql2="UPDATE users u JOIN businesses b ON u.userId=b.ownerId
                       SET u.role='business' WHERE b.businessId=?";
                $stmt2=mysqli_prepare($conn, $sql2);
                mysqli_stmt_bind_param($stmt2, "i", $businessId);
                mysqli_stmt_execute($stmt2);
            }
            return true;
        }
        else
        {
            return false;
        }
    }
}

function getAllPostsForAdmin($search)
{
    $conn=dbConnection();
    $posts=[];

    if($conn)
    {
        $search="%".$search."%";
        $sql="SELECT p.postId, p.title, p.content, p.status, p.createdAt, u.name AS authorName, u.userId,
                     (SELECT COUNT(*) FROM comments c WHERE c.postId=p.postId AND c.status='active') AS commentCount
              FROM posts p
              JOIN users u ON p.userId=u.userId
              WHERE p.title LIKE ? OR p.content LIKE ?
              ORDER BY p.createdAt DESC";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $search, $search);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $posts[]=$row;
        }
    }

    return $posts;
}

function adminUpdatePostStatus($postId, $status)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE posts SET status=? WHERE postId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $postId);
        return mysqli_stmt_execute($stmt);
    }
}

function getCommentsForAdmin($postId)
{
    $conn=dbConnection();
    $comments=[];

    if($conn)
    {
        $sql="SELECT c.commentId, c.comment, c.status, c.createdAt, u.name AS authorName
              FROM comments c
              JOIN users u ON c.userId=u.userId
              WHERE c.postId=?
              ORDER BY c.createdAt ASC";
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

function adminUpdateCommentStatus($commentId, $status)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE comments SET status=? WHERE commentId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $commentId);
        return mysqli_stmt_execute($stmt);
    }
}

function getReports($status)
{
    $conn=dbConnection();
    $reports=[];

    if($conn)
    {
        if($status=="" || $status=="all")
        {
            $sql="SELECT r.*, u.name AS reporterName, p.title AS postTitle, p.status AS postStatus
                  FROM reports r
                  JOIN users u ON r.reporterId=u.userId
                  LEFT JOIN posts p ON r.postId=p.postId
                  ORDER BY r.createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
        }
        else
        {
            $sql="SELECT r.*, u.name AS reporterName, p.title AS postTitle, p.status AS postStatus
                  FROM reports r
                  JOIN users u ON r.reporterId=u.userId
                  LEFT JOIN posts p ON r.postId=p.postId
                  WHERE r.status=?
                  ORDER BY r.createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $status);
        }

        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $reports[]=$row;
        }
    }

    return $reports;
}

function updateReportStatus($reportId, $status)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE reports SET status=? WHERE reportId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $reportId);
        return mysqli_stmt_execute($stmt);
    }
}

function addNotice($title, $content, $type, $eventDate, $createdBy)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="INSERT INTO notices (title, content, type, eventDate, createdBy) VALUES (?,?,?,?,?)";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $title, $content, $type, $eventDate, $createdBy);
        return mysqli_stmt_execute($stmt);
    }
}

function getNotices($type)
{
    $conn=dbConnection();
    $notices=[];

    if($conn)
    {
        if($type=="" || $type=="all")
        {
            $sql="SELECT * FROM notices ORDER BY createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
        }
        else
        {
            $sql="SELECT * FROM notices WHERE type=? ORDER BY createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $type);
        }

        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $notices[]=$row;
        }
    }

    return $notices;
}

function updateNotice($noticeId, $title, $content, $type, $eventDate)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE notices SET title=?, content=?, type=?, eventDate=? WHERE noticeId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $title, $content, $type, $eventDate, $noticeId);
        return mysqli_stmt_execute($stmt);
    }
}

function deleteNotice($noticeId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="DELETE FROM notices WHERE noticeId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $noticeId);
        return mysqli_stmt_execute($stmt);
    }
}

function getHomepageContent()
{
    $conn=dbConnection();
    $content=[];

    if($conn)
    {
        $sql="SELECT * FROM homepage_content ORDER BY contentId";
        $result=mysqli_query($conn, $sql);

        while($row=mysqli_fetch_assoc($result))
        {
            $content[]=$row;
        }
    }

    return $content;
}

function getHomepageSection($section)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT * FROM homepage_content WHERE section=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $section);
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

function updateHomepageContent($contentId, $title, $content)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE homepage_content SET title=?, content=?, updatedAt=NOW() WHERE contentId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $contentId);
        return mysqli_stmt_execute($stmt);
    }
}

function getWebsiteStats()
{
    $conn=dbConnection();
    $stats=[];

    if($conn)
    {
        $queries=[
            "totalUsers"=>"SELECT COUNT(*) AS total FROM users",
            "activeUsers"=>"SELECT COUNT(*) AS total FROM users WHERE status='active'",
            "blockedUsers"=>"SELECT COUNT(*) AS total FROM users WHERE status='blocked'",
            "totalPosts"=>"SELECT COUNT(*) AS total FROM posts WHERE status='active'",
            "totalComments"=>"SELECT COUNT(*) AS total FROM comments WHERE status='active'",
            "totalBusinesses"=>"SELECT COUNT(*) AS total FROM businesses WHERE status='approved'",
            "pendingBusinesses"=>"SELECT COUNT(*) AS total FROM businesses WHERE status='pending'",
            "totalProducts"=>"SELECT COUNT(*) AS total FROM products WHERE status='active'",
            "totalOrders"=>"SELECT COUNT(*) AS total FROM orders",
            "pendingReports"=>"SELECT COUNT(*) AS total FROM reports WHERE status='pending'"
        ];

        foreach($queries as $key=>$sql)
        {
            $result=mysqli_query($conn, $sql);
            $row=mysqli_fetch_assoc($result);
            $stats[$key]=$row["total"];
        }

        $sql="SELECT IFNULL(SUM(totalAmount),0) AS total FROM orders WHERE status!='cancelled'";
        $result=mysqli_query($conn, $sql);
        $row=mysqli_fetch_assoc($result);
        $stats["totalSales"]=number_format($row["total"], 2, '.', '');
    }

    return $stats;
}

?>
