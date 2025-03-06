<?php
session_start();
require 'db_connection.php';

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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <!-- Include your existing styles -->
    <style>
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
    <?php include 'header.php'; // Reuse your header ?>

    <div class="container">
        <div class="waybill">
            <h2>Order Confirmation #<?= $order_id ?></h2>
            <p>Estimated Delivery: <span class="eta"><?= $order['eta'] ?></span></p>
            
            <h3>Shipping Address:</h3>
            <p><?= htmlspecialchars($order['shipping_address']) ?></p>
            
            <h3>Order Items:</h3>
            <ul>
                <?php while($item = $items->fetch_assoc()): ?>
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