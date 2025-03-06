<?php
session_start();
require 'db_connection.php';

// Check if request is a POST request with JSON data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data from request
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to add items to cart']);
        exit;
    }
    
    // Validate required fields
    if (!isset($data['ProductID']) || !isset($data['quantity']) || !isset($data['size'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    $product_id = (int)$data['ProductID'];
    $quantity = (int)$data['quantity'];
    $size = mysqli_real_escape_string($conn, $data['size']);
    $user_id = (int)$_SESSION['user_id'];
    
    // Validate product stock before adding to cart
    $stock_check = "SELECT StockQuantity FROM products WHERE ProductID = ?";
    $stmt = $conn->prepare($stock_check);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    $product = $result->fetch_assoc();
    if ($quantity > $product['StockQuantity']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit;
    }
    
    // Check if item already exists in cart
    $check_cart = "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND size = ?";
    $stmt = $conn->prepare($check_cart);
    $stmt->bind_param("iis", $user_id, $product_id, $size);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    
    if ($cart_result->num_rows > 0) {
        // Update existing cart item
        $cart_item = $cart_result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        // Check if new quantity exceeds stock
        if ($new_quantity > $product['StockQuantity']) {
            echo json_encode(['success' => false, 'message' => 'Cannot add more items than available in stock']);
            exit;
        }
        
        $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart: ' . $conn->error]);
        }
    } else {
        // Insert new cart item
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity, size, added_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iiis", $user_id, $product_id, $quantity, $size);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Item added to cart successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add item to cart: ' . $conn->error]);
        }
    }
} else {
    // Handle non-POST requests
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>