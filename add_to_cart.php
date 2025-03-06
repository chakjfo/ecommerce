<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    $_SESSION['error_message'] = "Please log in to add items to cart.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['UserID']; // Get logged-in user ID
$product_id = isset($_GET['iProductID']) ? mysqli_real_escape_string($conn, $_GET['ProductID']) : null;
$size = isset($_GET['size']) ? mysqli_real_escape_string($conn, $_GET['size']) : 'M';

// Validate product ID
if (!$product_id) {
    $_SESSION['error_message'] = "Invalid product selection.";
    header("Location: shop_customer.php");
    exit();
}

// Check if product exists
$query = "SELECT * FROM products WHERE ProductID = '$product_id'";
$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Product not found.";
    header("Location: shop_customer.php");
    exit();
}

// Check if product is already in the cart
$cart_check = "SELECT * FROM cart WHERE UserID = '$user_id' AND ProductID = '$product_id' AND Size = '$size'";
$cart_result = mysqli_query($conn, $cart_check);

if (mysqli_num_rows($cart_result) > 0) {
    // Update quantity if already in cart
    $update_cart = "UPDATE cart SET Quantity = Quantity + 1 WHERE UserID = '$user_id' AND ProductID = '$product_id' AND Size = '$size'";
    mysqli_query($conn, $update_cart);
} else {
    // Insert new cart item
    $insert_cart = "INSERT INTO cart (UserID, ProductID, Size, Quantity) VALUES ('$user_id', '$product_id', '$size', 1)";
    mysqli_query($conn, $insert_cart);
}

// Success message
$_SESSION['success_message'] = "Product added to cart successfully!";
header("Location: shop_customer.php");
exit();
?>