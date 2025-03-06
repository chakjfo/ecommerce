<?php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

// Default response for guests or errors
$response = ['count' => 0];

// Only proceed if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    
    // Query to get total items in cart
    $query = "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $response['count'] = (int)$row['total_items'];
    }
}

echo json_encode($response);
?>