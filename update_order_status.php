<?php
session_start();
require_once "db_connection.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $status = $_POST['status'] ?? null;

    // Debugging: Log received data
    error_log("Received order_id: $order_id, status: $status");

    if (!$order_id || !$status) {
        echo json_encode(['error' => 'Invalid data']);
        exit();
    }

    // Update the order status in the database
    $query = "UPDATE orders SET delivery_status = ? WHERE OrderID = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error); // Debugging
        echo json_encode(['error' => 'Database error: Prepare failed']);
        exit();
    }

    $stmt->bind_param('si', $status, $order_id);
    if ($stmt->execute()) {
        error_log("Status updated successfully for OrderID: $order_id"); // Debugging
        echo json_encode(["success" => true, "message" => "Status updated successfully"]);
    } else {
        error_log("Execute failed: " . $stmt->error); // Debugging
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>