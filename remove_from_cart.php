<?php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not authenticated"]);
    exit();
}

if (isset($data['id'])) {
    $cart_id = (int)$data['id']; // Use 'id' instead of 'cartId'
    $user_id = $_SESSION['user_id'];

    // Ensure the item belongs to the user
    $query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $cart_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Item removed from cart"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to remove item"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>