<?php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

// Ensure only admins can delete orders
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the order ID from the request
    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

    if ($orderId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        exit;
    }

    // Delete the order from the database
    try {
        // Start a transaction
        $conn->begin_transaction();

        // Delete from customer_orders table
        $deleteCustomerOrdersQuery = "DELETE FROM customer_orders WHERE order_id = ?";
        $stmt_customer = $conn->prepare($deleteCustomerOrdersQuery);
        $stmt_customer->bind_param("i", $orderId);
        $stmt_customer->execute();

        // Delete from orders table
        $deleteOrdersQuery = "DELETE FROM orders WHERE OrderID = ?";
        $stmt = $conn->prepare($deleteOrdersQuery);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        error_log("Error deleting order: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to delete order']);
    }

    $stmt->close();
    $stmt_customer->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>