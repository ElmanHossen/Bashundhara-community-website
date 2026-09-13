<?php
// ============================================================
// MEMBER 3 (fahim) - Business & Marketplace Module
// ============================================================
require_once __DIR__."/dbConnect.php";

// ---------------- BUSINESS ----------------

function registerBusiness($ownerId, $businessName, $description, $address, $phone)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="INSERT INTO businesses (ownerId, businessName, description, address, phone) VALUES (?,?,?,?,?)";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $ownerId, $businessName, $description, $address, $phone);

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

function getBusinessByOwner($ownerId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT * FROM businesses WHERE ownerId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $ownerId);
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

function updateBusiness($businessId, $ownerId, $businessName, $description, $address, $phone)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE businesses SET businessName=?, description=?, address=?, phone=? WHERE businessId=? AND ownerId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssis", $businessName, $description, $address, $phone, $businessId, $ownerId);

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

// ---------------- PRODUCTS ----------------

function getProductCategories()
{
    $conn=dbConnection();
    $categories=[];

    if($conn)
    {
        $sql="SELECT * FROM product_categories ORDER BY pCategoryName";
        $result=mysqli_query($conn, $sql);

        while($row=mysqli_fetch_assoc($result))
        {
            $categories[]=$row;
        }
    }

    return $categories;
}

function addProduct($businessId, $pCategoryId, $productName, $description, $price, $stock, $image)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="INSERT INTO products (businessId, pCategoryId, productName, description, price, stock, image) VALUES (?,?,?,?,?,?,?)";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iissdis", $businessId, $pCategoryId, $productName, $description, $price, $stock, $image);

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

function getProductsByBusiness($businessId)
{
    $conn=dbConnection();
    $products=[];

    if($conn)
    {
        $sql="SELECT p.*, c.pCategoryName
              FROM products p
              LEFT JOIN product_categories c ON p.pCategoryId=c.pCategoryId
              WHERE p.businessId=? AND p.status='active'
              ORDER BY p.createdAt DESC";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $businessId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $products[]=$row;
        }
    }

    return $products;
}

function getProducts($search, $pCategoryId)
{
    $conn=dbConnection();
    $products=[];

    if($conn)
    {
        $search="%".$search."%";

        if($pCategoryId=="" || $pCategoryId=="0")
        {
            $sql="SELECT p.*, c.pCategoryName, b.businessName
                  FROM products p
                  LEFT JOIN product_categories c ON p.pCategoryId=c.pCategoryId
                  JOIN businesses b ON p.businessId=b.businessId
                  WHERE p.status='active' AND b.status='approved' AND p.productName LIKE ?
                  ORDER BY p.createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $search);
        }
        else
        {
            $sql="SELECT p.*, c.pCategoryName, b.businessName
                  FROM products p
                  LEFT JOIN product_categories c ON p.pCategoryId=c.pCategoryId
                  JOIN businesses b ON p.businessId=b.businessId
                  WHERE p.status='active' AND b.status='approved' AND p.pCategoryId=? AND p.productName LIKE ?
                  ORDER BY p.createdAt DESC";
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "is", $pCategoryId, $search);
        }

        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $products[]=$row;
        }
    }

    return $products;
}

function getProductById($productId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT * FROM products WHERE productId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $productId);
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

function updateProduct($productId, $businessId, $pCategoryId, $productName, $description, $price, $stock)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE products SET pCategoryId=?, productName=?, description=?, price=?, stock=? WHERE productId=? AND businessId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issdiii", $pCategoryId, $productName, $description, $price, $stock, $productId, $businessId);

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

function deleteProduct($productId, $businessId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE products SET status='deleted' WHERE productId=? AND businessId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $productId, $businessId);

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

// ---------------- CART ----------------

function addToCart($userId, $productId, $quantity)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="SELECT cartId, quantity FROM cart WHERE userId=? AND productId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $userId, $productId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)>0)
        {
            $row=mysqli_fetch_assoc($result);
            $newQty=$row["quantity"]+$quantity;

            $sql2="UPDATE cart SET quantity=? WHERE cartId=?";
            $stmt2=mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt2, "ii", $newQty, $row["cartId"]);
            return mysqli_stmt_execute($stmt2);
        }
        else
        {
            $sql2="INSERT INTO cart (userId, productId, quantity) VALUES (?,?,?)";
            $stmt2=mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt2, "sii", $userId, $productId, $quantity);
            return mysqli_stmt_execute($stmt2);
        }
    }
}

function getCart($userId)
{
    $conn=dbConnection();
    $items=[];

    if($conn)
    {
        $sql="SELECT ct.cartId, ct.quantity, p.productId, p.productName, p.price, p.stock, p.image, b.businessName
              FROM cart ct
              JOIN products p ON ct.productId=p.productId
              JOIN businesses b ON p.businessId=b.businessId
              WHERE ct.userId=? AND p.status='active'";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $userId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $items[]=$row;
        }
    }

    return $items;
}

function updateCartQuantity($cartId, $userId, $quantity)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE cart SET quantity=? WHERE cartId=? AND userId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iis", $quantity, $cartId, $userId);
        return mysqli_stmt_execute($stmt);
    }
}

function removeFromCart($cartId, $userId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="DELETE FROM cart WHERE cartId=? AND userId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $cartId, $userId);
        return mysqli_stmt_execute($stmt);
    }
}

function clearCart($userId)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="DELETE FROM cart WHERE userId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $userId);
        return mysqli_stmt_execute($stmt);
    }
}

// ---------------- ORDERS ----------------

function placeOrder($userId, $shippingAddress)
{
    $conn=dbConnection();
    if($conn)
    {
        $items=getCart($userId);

        if(count($items)==0)
        {
            return false;
        }

        $total=0;
        foreach($items as $item)
        {
            $total=$total+($item["price"]*$item["quantity"]);
        }

        $sql="INSERT INTO orders (userId, totalAmount, shippingAddress) VALUES (?,?,?)";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sds", $userId, $total, $shippingAddress);

        if(mysqli_stmt_execute($stmt))
        {
            $orderId=mysqli_insert_id($conn);

            foreach($items as $item)
            {
                $sql2="INSERT INTO order_items (orderId, productId, quantity, price) VALUES (?,?,?,?)";
                $stmt2=mysqli_prepare($conn, $sql2);
                mysqli_stmt_bind_param($stmt2, "iiid", $orderId, $item["productId"], $item["quantity"], $item["price"]);
                mysqli_stmt_execute($stmt2);

                $sql3="UPDATE products SET stock=stock-? WHERE productId=?";
                $stmt3=mysqli_prepare($conn, $sql3);
                mysqli_stmt_bind_param($stmt3, "ii", $item["quantity"], $item["productId"]);
                mysqli_stmt_execute($stmt3);
            }

            clearCart($userId);
            return $orderId;
        }
        else
        {
            return false;
        }
    }
}

function getOrdersByUser($userId)
{
    $conn=dbConnection();
    $orders=[];

    if($conn)
    {
        $sql="SELECT * FROM orders WHERE userId=? ORDER BY orderDate DESC";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $userId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $orders[]=$row;
        }
    }

    return $orders;
}

function getOrderItems($orderId)
{
    $conn=dbConnection();
    $items=[];

    if($conn)
    {
        $sql="SELECT oi.*, p.productName
              FROM order_items oi
              JOIN products p ON oi.productId=p.productId
              WHERE oi.orderId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $orderId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $items[]=$row;
        }
    }

    return $items;
}

function getOrdersForBusiness($businessId)
{
    $conn=dbConnection();
    $orders=[];

    if($conn)
    {
        $sql="SELECT DISTINCT o.orderId, o.userId, o.status, o.orderDate, o.shippingAddress, u.name AS customerName
              FROM orders o
              JOIN order_items oi ON o.orderId=oi.orderId
              JOIN products p ON oi.productId=p.productId
              JOIN users u ON o.userId=u.userId
              WHERE p.businessId=?
              ORDER BY o.orderDate DESC";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $businessId);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);

        while($row=mysqli_fetch_assoc($result))
        {
            $orders[]=$row;
        }
    }

    return $orders;
}

function updateOrderStatus($orderId, $status)
{
    $conn=dbConnection();
    if($conn)
    {
        $sql="UPDATE orders SET status=? WHERE orderId=?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $orderId);
        return mysqli_stmt_execute($stmt);
    }
}

?>
