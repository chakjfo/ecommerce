<?php
session_start();
require 'db_connection.php';

// User authentication handling
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Guest";

if (!isset($_SESSION['order_id'])) {
    header('Location: cart.php');
    exit;
}

$order_id = $_SESSION['order_id'];
unset($_SESSION['order_id']);

// Fetch order details
$order_query = "SELECT *, DATE_FORMAT(delivery_date, '%M %e, %Y') AS eta 
               FROM orders WHERE OrderID = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Fetch order items
$items_query = "SELECT oi.*, p.ProductName 
               FROM order_items oi
               JOIN products p ON oi.product_id = p.ProductID
               WHERE order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();

// Fetch categories for navigation (added from shop_customer)
$category_query = "SELECT category_name FROM categories";
$category_result = $conn->query($category_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - The Accents Clothing</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Atkinson+Hyperlegible+Mono:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* Unified Header Styles from shop_customer */
        * {
            margin: 0;
            padding: 0;
            font-family: "Anton", sans-serif;
            box-sizing: border-box;
        }
        
        header {
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            background: white;
        }
        
        .running-text {
            background-color: black;
            color: red;
            padding: 10px 0;
            text-align: center;
            font-size: 14px;
            overflow: hidden;
            white-space: nowrap;
            position: relative;
            height: 30px;
            display: flex;
            align-items: center;
            font-family: "Atkinson Hyperlegible Mono", monospace;
        }
        
        .running-text span {
            display: inline-block;
            position: absolute;
            width: 100%;
            background-color: black;
            animation: marquee 30s linear infinite;
        }
        
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            background-color: white;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo img {
            width: 150px;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
        }
        
        .nav-links a {
            position: relative;
            text-decoration: none;
            font-size: 18px;
            color: black;
            transition: 0.3s;
            display: inline-block;
        }
        
        .nav-links a::after {
            content: '';
            width: 0%;
            height: 2px;
            background: black;
            display: block;
            margin: auto;
            transition: 0.5s;
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        .user-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-links a {
            text-decoration: none;
            font-size: 18px;
            color: black;
            transition: 0.3s;
        }
        
        .profile-circle {
            width: 40px;
            height: 40px;
            background-color: #333;
            color: white;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-transform: uppercase;
            cursor: pointer;
        }

        /* Order Confirmation Specific Styles */
        .container {
            max-width: 1200px;
            margin: 150px auto 50px;
            padding: 20px;
        }
        
        .waybill {
            border: 2px solid #000;
            padding: 20px;
            margin: 20px;
            max-width: 800px;
        }
        
        .eta { color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <div class="running-text">
            <span>Welcome to The Accents Clothing! Enjoy our latest collection with free shipping.</span>
        </div>
        <nav>
            <div class="logo">
                <a href="homepage.php"><img src="images/the_accents_logo.png" alt="The Accents Logo"></a>
            </div>
            <div class="nav-links">
                <a href="shop_customer.php">Shop</a>
                <?php if ($category_result && $category_result->num_rows > 0) : 
                    while ($row = $category_result->fetch_assoc()) : 
                        $category = htmlspecialchars($row['category_name']); ?>
                        <a href="shop_customer.php?category=<?= $category ?>"><?= $category ?></a>
                    <?php endwhile; 
                endif; ?>
            </div>
            <div class="user-links">
                <?php if ($username !== "Guest") : ?>
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
                    <a href="notifications.php"><i class="fas fa-bell"></i></a>
                    <div class="profile-circle">
                        <?= strtoupper(substr($username, 0, 1)) ?>
                    </div>
                    <a href="logout.php" style="font-size: 14px; color: black;">Logout</a>
                <?php else : ?>
                    <a href="login.php"><i class="fas fa-shopping-cart"></i></a>
                    <a href="login.php"><i class="fas fa-bell"></i></a>
                    <a href="signup.php" style="font-size: 14px;">Sign Up</a>
                    <a href="login.php" style="font-size: 14px;">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="waybill">
            <h2>Order Confirmation #<?= $order_id ?></h2>
            <p>Estimated Delivery: <span class="eta"><?= $order['eta'] ?></span></p>
            
            <h3>Shipping Address:</h3>
            <p><?= htmlspecialchars($order['shipping_address']) ?></p>
            
            <h3>Order Items:</h3>
            <ul>
                <?php while($item = $items->fetch_assoc()) : ?>
                <li>
                    <?= htmlspecialchars($item['ProductName']) ?> - 
                    Qty: <?= $item['quantity'] ?> - 
                    $<?= number_format($item['price'], 2) ?>
                </li>
                <?php endwhile; ?>
            </ul>
            
            <h3>Total Paid: $<?= number_format($order['TotalAmount'], 2) ?></h3>
            <p>Payment Method: <?= $order['payment_method'] ?></p>
        </div>
        <a href="shop_customer.php">Continue Shopping</a>
    </div>
</body>
</html>