<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $orderId = (int)$data['orderId'];

    // Log the received data for debugging
    error_log("Received orderId: $orderId, userId: $user_id");

    // Validate order ownership
    $query = "SELECT UserID FROM orders WHERE OrderID = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        if ($row['UserID'] === $user_id) {
            // Update delivery_status to "Cancelled"
            $updateQuery = "UPDATE orders SET delivery_status = 'Cancelled' WHERE OrderID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            if (!$updateStmt) {
                error_log("Prepare failed: " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Database error']);
                exit;
            }
            $updateStmt->bind_param("i", $orderId);
            $updateStmt->execute();

            if ($updateStmt->affected_rows > 0) {
                echo json_encode(['success' => true]);
            } else {
                error_log("No rows affected. OrderId: $orderId");
                echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
            }
        } else {
            error_log("User does not have permission to cancel this order. UserId: $user_id, OrderId: $orderId");
            echo json_encode(['success' => false, 'message' => 'You do not have permission to cancel this order']);
        }
    } else {
        error_log("Order not found. OrderId: $orderId");
        echo json_encode(['success' => false, 'message' => 'Order not found']);
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>