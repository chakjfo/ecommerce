<?php
// cart_handler.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }
    
    // Connect to database
    require_once 'db_connection.php'; // Your database connection file
    
    $user_id = $_SESSION['user_id'];
    $product_id = $data['product_id'];
    $quantity = $data['quantity'];
    $size = $data['size'];
    
    // Check if item already exists in cart
    $check_sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND size = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("iis", $user_id, $product_id, $size);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing cart item
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        
        $update_sql = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ii", $new_quantity, $row['cart_id']);
        $success = $stmt->execute();
    } else {
        // Insert new cart item
        $insert_sql = "INSERT INTO cart (user_id, product_id, quantity, size, added_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iiis", $user_id, $product_id, $quantity, $size);
        $success = $stmt->execute();
    }
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Item added to cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
    }
    
    $stmt->close();
    $conn->close();
}
?>