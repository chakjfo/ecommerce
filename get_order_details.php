<?php
require_once "db_connection.php";

header('Content-Type: application/json');

if (!isset($_POST['order_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$orderId = (int)$_POST['order_id'];

try {
    // Get order details
    $orderQuery = "SELECT o.OrderID, o.OrderDate, o.TotalAmount, o.payment_method, o.delivery_status, o.delivery_date, co.customer_name, co.address 
                  FROM orders o
                  JOIN customer_orders co ON o.OrderID = co.order_id
                  WHERE o.OrderID = ?";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    // Get order items
    $itemsQuery = "SELECT co.product_id, co.quantity, p.ProductName, p.Price 
                  FROM customer_orders co
                  JOIN products p ON co.product_id = p.ProductID
                  WHERE co.order_id = ?";
    $stmt = $conn->prepare($itemsQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'order' => $order,
        'items' => $items
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}