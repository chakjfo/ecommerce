<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Guest";

// Get selected cart item IDs from URL
$selected_items = [];
if (isset($_GET['selected_items']) && !empty($_GET['selected_items'])) {
    $selected_items = array_map('intval', explode(',', $_GET['selected_items']));
    $selected_items = array_filter($selected_items, function($v) { return $v > 0; });
}

// Redirect if no items selected
if (empty($selected_items)) {
    $_SESSION['error'] = 'Please select items to checkout';
    header('Location: cart.php');
    exit;
}

// Fetch selected cart items with product details
$placeholders = implode(',', array_fill(0, count($selected_items), '?'));
$query = "SELECT c.*, p.ProductName, p.Price, p.images 
          FROM cart c 
          JOIN products p ON c.product_id = p.ProductID 
          WHERE c.user_id = ? 
          AND c.id IN ($placeholders)";

$stmt = $conn->prepare($query);
$types = str_repeat('i', count($selected_items) + 1);
$params = array_merge([$user_id], $selected_items);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Calculate totals
$subtotal = 0;
$cart_items = [];

while ($item = $result->fetch_assoc()) {
    $item_total = $item['quantity'] * $item['Price'];
    $subtotal += $item_total;
    $item['item_total'] = $item_total;
    $cart_items[] = $item;
}

$tax = $subtotal * 0.08;
$total = $subtotal + $tax;

// Handle form submission for checkout
$order_success = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Validate form fields
    $required_fields = ['full_name', 'email', 'address', 'city', 'state', 'zip', 'payment_method'];
    $is_valid = true;
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $is_valid = false;
            $error_message = 'Please fill in all required fields';
            break;
        }
    }
    
    if ($is_valid) {
        $conn->begin_transaction();
        
        try {
            $required_address_fields = ['address', 'city', 'state', 'zip'];
            foreach ($required_address_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Missing required address field: $field");
                }
            }

            // Build full address
            $address = sprintf(
                "%s, %s, %s %s",
                $_POST['address'],
                $_POST['city'],
                $_POST['state'],
                $_POST['zip']
            );

            // Calculate delivery date (1.5 weeks)
            $delivery_date = date('Y-m-d', strtotime('+10 days'));

            // Insert order into orders table
            $order_query = "INSERT INTO orders (UserID, TotalAmount, shipping_address, payment_method, delivery_date, delivery_status) 
                            VALUES (?, ?, ?, ?, ?, 'Pending')";
            $stmt = $conn->prepare($order_query);
            $payment_method = $_POST['payment_method'];
            $stmt->bind_param("idsss", $user_id, $total, $address, $payment_method, $delivery_date);
            $stmt->execute();
            $order_id = $stmt->insert_id;

            // Insert into customer_orders
            $customer_order_query = "INSERT INTO customer_orders 
                (order_id, customer_name, product_id, email, phone, address, payment_method, quantity, order_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt_customer = $conn->prepare($customer_order_query);

            foreach ($cart_items as $item) {
                // Insert customer order details
                $stmt_customer->bind_param("isissssi", 
                    $order_id,
                    $_POST['full_name'],
                    $item['product_id'],
                    $_POST['email'],
                    $_POST['phone'],
                    $address,
                    $_POST['payment_method'],
                    $item['quantity']
                );
                $stmt_customer->execute();

                // Update product stock
                $update_stock = "UPDATE products SET StockQuantity = StockQuantity - ? WHERE ProductID = ?";
                $stock_stmt = $conn->prepare($update_stock);
                $stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $stock_stmt->execute();
            }

            // Clear only selected items from cart
            $clear_cart = "DELETE FROM cart WHERE user_id = ? AND id IN ($placeholders)";
            $stmt = $conn->prepare($clear_cart);
            $types = str_repeat('i', count($selected_items) + 1);
            $params = array_merge([$user_id], $selected_items);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            
            $conn->commit();
            $order_success = true;
            $_SESSION['order_id'] = $order_id;
            header("Location: order_confirmation.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = 'Error: ' . $e->getMessage();
            error_log($e->getMessage()); // Log errors
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - The Accents Clothing</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Atkinson+Hyperlegible+Mono:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            font-family: "Anton", sans-serif;
            box-sizing: border-box;
        }
        
        /* Header Styles */
        header {
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            background: white;
        }
        
        /* Running Text Banner */
        .running-text {
            background-color: black;
            color: red;
            padding: 10px 0;
            text-align: center;
            font-size: 14px;
            overflow: hidden;
            white-space: nowrap;
            position: relative;
            height: 30px;
            display: flex;
            align-items: center;
            font-family: "Atkinson Hyperlegible Mono", monospace;
        }
        .running-text span {
            display: inline-block;
            position: absolute;
            width: 100%;
            background-color: black;
            animation: marquee 30s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        /* Navigation Styles */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            background-color: white;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .logo img {
            width: 150px;
        }
        
        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 150px auto 50px;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        /* Success Message */
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            width: 100%;
        }
        
        /* Error Message */
        .error-message {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            width: 100%;
        }
        
        /* Cart Section */
        .cart-section {
            flex: 2;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .cart-section h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }
        .cart-items {
            margin-bottom: 20px;
        }
        .cart-item {
            display: flex;
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .cart-item-image {
            width: 80px;
            height: 80px;
            margin-right: 15px;
            border-radius: 5px;
            overflow: hidden;
        }
        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .cart-item-details {
            flex: 1;
        }
        .cart-item-title {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .cart-item-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .cart-item-price {
            font-size: 16px;
            font-weight: bold;
        }
        
        /* Checkout Section */
        .checkout-section {
            flex: 3;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .checkout-section h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }
        .checkout-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group.full-width {
            grid-column: span 2;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        /* Order Summary */
        .order-summary {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .order-summary h3 {
            margin-bottom: 15px;
            font-size: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-row.total {
            font-weight: bold;
            font-size: 20px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        
        /* Checkout Button */
        .checkout-button {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        .checkout-button:hover {
            background-color: #27ae60;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .checkout-form {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="running-text">
            <span>Welcome to The Accents Clothing! Enjoy our latest collection with free shipping.</span>
        </div>
        <nav>
            <div class="logo">
                <a href="homepage.php"><img src="images/the_accents_logo.png" alt="The Accents Logo"></a>
            </div>
        </nav>
    </header>

    <div class="container">
        <?php if ($order_success): ?>
        <?php else: ?>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (empty($cart_items)): ?>
                <div class="cart-section" style="width: 100%;">
                    <h2>Your Cart is Empty</h2>
                    <p>You have no items in your cart. <a href="shop_customer.php">Continue shopping</a>.</p>
                </div>
            <?php else: ?>
                <div class="cart-section">
                    <h2>Your Cart (<?php echo count($cart_items); ?> items)</h2>
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <?php 
                                    $images = json_decode($item['images'], true); 
                                    $first_image = isset($images[0]) ? $images[0] : 'images/default-product.jpg'; 
                                    ?>
                                    <img src="<?php echo htmlspecialchars($first_image); ?>" alt="<?php echo htmlspecialchars($item['ProductName']); ?>">
                                </div>
                                <div class="cart-item-details">
                                    <h3 class="cart-item-title"><?php echo htmlspecialchars($item['ProductName']); ?></h3>
                                    <p class="cart-item-meta">Size: <?php echo htmlspecialchars($item['size']); ?> | Quantity: <?php echo $item['quantity']; ?></p>
                                    <p class="cart-item-price">$<?php echo number_format($item['Price'], 2); ?> each</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Tax (8%)</span>
                            <span>$<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="checkout-section">
                    <h2>Shipping & Payment</h2>
                    <form class="checkout-form" method="POST">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group full-width">
                            <label for="address">Street Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" required>
                        </div>
                        <div class="form-group">
                            <label for="zip">ZIP Code</label>
                            <input type="text" id="zip" name="zip" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="paypal">PayPal</option>
                                <option value="cash_on_delivery">Cash on Delivery</option>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <button type="submit" name="checkout" class="checkout-button">Place Order</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script>
        // Add this function to handle checkout redirection
        function proceedToCheckout() {
            const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'))
                                      .map(checkbox => checkbox.value);
            
            console.log('Selected Items:', selectedItems); // Debugging line
            
            if (selectedItems.length === 0) {
                alert('Please select at least one item to proceed to checkout.');
                return;
            }
            
            // Encode the parameter properly
            const params = new URLSearchParams();
            params.append('selected_items', selectedItems.join(','));
            window.location.href = `checkout.php?${params.toString()}`;
        }

        // Update the checkout button HTML
        document.querySelector('.proceed-checkout').addEventListener('click', proceedToCheckout);
    </script>
</body>
</html>