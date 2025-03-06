<?php
require_once "db_connection.php";

header('Content-Type: application/json');

if (!isset($_POST['order_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$orderId = (int)$_POST['order_id'];

// Get order header details
$orderQuery = "SELECT 
                o.OrderID,
                u.username AS customer_name,
                o.OrderDate,
                o.TotalAmount,
                o.shipping_address,
                o.payment_method,
                o.delivery_date,
                o.delivery_status
               FROM orders o
               JOIN users u ON o.UserID = u.UserID
               WHERE o.OrderID = ?";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Get order items with product names
$itemsQuery = "SELECT 
                oi.product_id,
                p.ProductName,
                oi.quantity,
                oi.price
               FROM order_items oi
               JOIN products p ON oi.product_id = p.ProductID
               WHERE oi.order_id = ?";
$stmt = $conn->prepare($itemsQuery);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'order' => $order,
    'items' => $items
]);