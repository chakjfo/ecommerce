<?php
// remove_from_cart.php
session_start();
require 'db_connection.php';

// Get JSON data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$response = ['success' => false, 'message' => 'Unknown error'];

if (isset($_SESSION['user_id']) && isset($data['cartId'])) {
    $user_id = $_SESSION['user_id'];
    $cart_id = (int)$data['cartId'];
    
    // Prepare and execute the delete statement
    $delete_query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_query);
    
    if ($stmt) {
        $stmt->bind_param("ii", $cart_id, $user_id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Item removed successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to remove item: ' . $stmt->error];
        }
        $stmt->close();
    } else {
        $response = ['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error];
    }
} else {
    $response = ['success' => false, 'message' => 'Invalid request or user not logged in'];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>