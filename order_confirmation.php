<?php
session_start();
require 'db_connection.php';

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Guest";

if (!isset($_SESSION['order_id'])) {
    header('Location: cart.php');
    exit;
}

$order_id = $_SESSION['order_id'];
unset($_SESSION['order_id']);

// Fetch order details
$order_query = "SELECT *, DATE_FORMAT(delivery_date, '%M %e, %Y') AS eta FROM orders WHERE OrderID = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Fetch customer details
$customer_query = "SELECT customer_name, email, phone, address FROM customer_orders WHERE order_id = ?";
$stmt = $conn->prepare($customer_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// Fetch order items with price from the products table
$items_query = "SELECT co.product_id, co.quantity, p.ProductName, p.Price 
               FROM customer_orders co
               JOIN products p ON co.product_id = p.ProductID
               WHERE co.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
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
        
        /* Profile Dropdown Styles */
        .profile-container {
            position: relative;
            display: inline-block;
        }
        
        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 200px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 10px 0;
            z-index: 1100;
            margin-top: 8px;
            display: none;
        }
        
        .profile-dropdown:before {
            content: '';
            position: absolute;
            top: -8px;
            right: 16px;
            width: 16px;
            height: 16px;
            background-color: white;
            transform: rotate(45deg);
            border-left: 1px solid rgba(0, 0, 0, 0.1);
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-item {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            transition: background-color 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: #f5f5f5;
        }
        
        .dropdown-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            color: #555;
        }
        
        .dropdown-item:first-child {
            border-bottom: 1px solid #eee;
            pointer-events: none;
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
        
        .profile-dropdown.show {
            display: block;
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

        .order-confirmation {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .waybill-header {
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }
        
        .order-items-list {
            list-style: none;
            padding: 0;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .details-section {
            margin-bottom: 25px;
        }
        .totals-section {
            border-top: 1px solid #ddd;
            padding-top: 15px;
            text-align: right;
        }
        .order-id {
            font-size: 24px;
            font-weight: bold;
        }
        .payment-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .status-badge {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 5px;
        }
        .estimated-delivery {
            color: #28a745;
            font-weight: bold;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .order-confirmation {
                box-shadow: none;
                padding: 0;
            }
            .print-button {
                display: none;
            }
        }
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
                <?php
                // Fetch categories for navigation
                $category_query = "SELECT category_name FROM categories";
                $category_result = $conn->query($category_query);
                
                if ($category_result && $category_result->num_rows > 0) {
                    while ($row = $category_result->fetch_assoc()) {
                        $category = htmlspecialchars($row['category_name']); 
                        echo "<a href='shop_customer.php?category=$category'>$category</a>";
                    }
                }
                ?>
            </div>
                <div class="user-links">
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
                    <div class="profile-container">
                        <div class="profile-circle" id="profileToggle">
                            <?php echo strtoupper(substr($username, 0, 1)); ?>
                        </div>
                        <div class="profile-dropdown" id="profileDropdown">
                            <div class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($username); ?></span>
                            </div>
                            <a href="<?php echo $username !== 'Guest' ? 'order_users.php' : 'login.php'; ?>" class="dropdown-item">
                                <i class="fas fa-box"></i>
                                <span>My Orders</span>
                            </a>
                            <?php if ($username !== 'Guest') : ?>
                                <a href="logout.php" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
        </nav>
    </header>

    <div class="container">
        <div class="waybill">

            <div class="order-confirmation">
    <div class="waybill-header d-flex justify-content-between align-items-center">
        <h1 class="order-id">Order Confirmation #<?php echo $order_id; ?></h1>
        <button class="btn btn-primary print-button" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
    
    <div class="row details-section">
        <div class="col-md-6">
            <h5>Order Information</h5>
            <p><strong>Order Date:</strong> <?php echo $order['OrderDate']; ?></p>
            <p><strong>Estimated Delivery:</strong> <span class="estimated-delivery"><?php echo $order['eta']; ?></span></p>
            <p><strong>Status:</strong> <span class="status-badge"><?php echo $order['delivery_status']; ?></span></p>
        </div>
        <div class="col-md-6">
            <h5>Shipping Address</h5>
            <p><?php echo $customer['address']; ?></p>
        </div>
    </div>
    
    <div class="details-section">
    <h5>Order Items</h5>
    <?php
    // Store items in an array for reuse
    $order_items = [];
    $subtotal = 0;

    if ($items->num_rows > 0) {
        while ($item = $items->fetch_assoc()) {
            $order_items[] = $item;
            $subtotal += $item['Price'] * $item['quantity'];
        }
    }
    ?>

    <?php if (!empty($order_items)): ?>
        <ul class="order-items-list">
            <?php foreach ($order_items as $item): ?>
                <li class="order-item">
                    <div><?php echo $item['ProductName']; ?> (ID: <?php echo $item['product_id']; ?>)</div>
                    <div>Qty: <?php echo $item['quantity']; ?> × $<?php echo $item['Price']; ?> = $<?php echo $item['quantity'] * $item['Price']; ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-muted">No items found for this order.</p>
    <?php endif; ?>
</div>

<div class="totals-section">
    <div class="row">
        <div class="col-md-6"></div>
        <div class="col-md-6">
            <?php
            // Calculate tax and total
            $tax = $subtotal * 0.08;
            $total_paid = $subtotal + $tax;
            ?>
            <p><strong>Subtotal:</strong> $<?php echo number_format($subtotal, 2); ?></p>
            <p><strong>Tax (8%):</strong> $<?php echo number_format($tax, 2); ?></p>
            <p><strong>Shipping:</strong> $0.00</p>
            <h4><strong>Total Paid:</strong> $<?php echo number_format($total_paid, 2); ?></h4>
        </div>
    </div>
</div>
    
    <div class="payment-info">
        <h5>Payment Information</h5>
        <p><strong>Payment Method:</strong> <?php echo $order['payment_method']; ?></p>
    </div>
    
    <div class="mt-4 text-center">
        <p>Thank you for your order!</p>
        <p class="text-muted">If you have any questions about your order, please contact our customer service.</p>
    </div>
</div>
        </div> 
</body>
</html>