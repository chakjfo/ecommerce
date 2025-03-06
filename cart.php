<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// User authentication handling
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Guest";
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Fetch cart items for the current user
$cart_query = "SELECT c.*, p.ProductName, p.Price, p.images 
               FROM cart c 
               JOIN products p ON c.product_id = p.ProductID 
               WHERE c.user_id = $userId";
$cart_result = mysqli_query($conn, $cart_query);

// Calculate cart totals
$subtotal = 0;
$shipping = 0; // You can set a shipping cost or logic here
$tax_rate = 0.08; // 8% tax rate
$tax = 0;
$total = 0;

// Fetch categories for navigation
$category_query = "SELECT category_name FROM categories";
$category_result = $conn->query($category_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - The Accents Clothing</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Atkinson+Hyperlegible+Mono:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"></script>
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
        .nav-links {
            display: flex;
            gap: 30px;
        }
        .nav-links a {
            position: relative;
            text-decoration: none;
            font-size: 18px;
            color: black;
            transition: 0.3s;
            display: inline-block;
        }
        .nav-links a::after {
            content: '';
            width: 0%;
            height: 2px;
            background: black;
            display: block;
            margin: auto;
            transition: 0.5s;
        }
        .nav-links a:hover::after {
            width: 100%;
        }
        
        /* User Links & Profile */
        .user-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .user-links a {
            text-decoration: none;
            font-size: 18px;
            color: black;
            transition: 0.3s;
        }
        .user-links a:hover {
            color: gray;
        }
        .user-links i {
            font-size: 20px;
            cursor: pointer;
        }
        .profile-circle {
            width: 40px;
            height: 40px;
            background-color: #333;
            color: white;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-transform: uppercase;
            cursor: pointer;
        }
        
        /* Cart Container */
        .container {
            max-width: 1200px;
            margin: 150px auto 50px;
            padding: 20px;
        }
        
        .cart-title {
            font-size: 32px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        /* Cart Items */
        .cart-items {
            margin-bottom: 30px;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            position: relative;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .cart-item-meta {
            color: #666;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .cart-item-price {
            font-size: 18px;
            font-weight: bold;
        }
        
        .cart-item-subtotal {
            font-size: 20px;
            font-weight: bold;
            margin: 0 30px;
        }
        
        .cart-item-controls {
            display: flex;
            align-items: center;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .quantity-btn {
            background: #f0f0f0;
            border: none;
            width: 30px;
            height: 30px;
            font-size: 16px;
            cursor: pointer;
        }
        
        .quantity-input {
            width: 50px;
            height: 30px;
            text-align: center;
            border: none;
            border-left: 1px solid #ccc;
            border-right: 1px solid #ccc;
        }
        
        .remove-item {
            color: #ff0000;
            cursor: pointer;
            font-size: 20px;
            position: absolute;
            right: 20px;
        }
        
        /* Cart Summary */
        .cart-summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .summary-row.total {
            font-size: 24px;
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        /* Add to existing styles */
.cart-item {
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
}

.item-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #000;
    margin-right: 10px;
}

.remove-item {
    right: 20px;
    position: absolute;
}

        /* Buttons */
        .cart-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .continue-shopping, .proceed-checkout {
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .continue-shopping {
            background: #f0f0f0;
            color: #333;
        }
        
        .proceed-checkout {
            background: green;
            color: white;
        }
        
        .proceed-checkout:hover {
            background: darkgreen;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            font-size: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .empty-cart i {
            font-size: 48px;
            color: #aaa;
            margin-bottom: 20px;
        }
        
        .empty-cart a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
            }
            .nav-links {
                margin-top: 10px;
            }
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }
            .cart-item-image {
                margin-bottom: 15px;
                margin-right: 0;
            }
            .cart-item-controls {
                margin-top: 15px;
            }
            .cart-item-subtotal {
                margin: 15px 0;
            }
            .remove-item {
                position: static;
                margin-top: 10px;
            }
            .cart-buttons {
                flex-direction: column;
                gap: 10px;
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
            <div class="nav-links">
                <a href="shop_customer.php">Shop</a>
                <?php
                if ($category_result && $category_result->num_rows > 0) {
                    while ($row = $category_result->fetch_assoc()) {
                        $category = htmlspecialchars($row['category_name']); 
                        echo "<a href='shop_customer.php?category=$category'>$category</a>";
                    }
                }
                ?>
            </div>
            <div class="user-links">
                <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
                <a href="notifications.php"><i class="fas fa-bell"></i></a>
                <div class="profile-circle">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>
                <a href="logout.php" style="font-size: 14px; color: black;">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1 class="cart-title">Your Shopping Cart</h1>
        
        <?php if (mysqli_num_rows($cart_result) > 0) : ?>
            <div class="cart-items">
                <?php 
                while ($item = mysqli_fetch_assoc($cart_result)) : 
                    // Parse images JSON
                    $images = json_decode($item['images'], true);
                    $first_image = isset($images[0]) ? $images[0] : 'images/default-product.jpg';
                    
                    // Calculate item subtotal
                    $item_subtotal = $item['Price'] * $item['quantity'];
                    $subtotal += $item_subtotal;
                ?>
                <!-- Add data-price and data-quantity attributes to the cart item div -->
<div class="cart-item" data-id="<?= (int)$item['id'] ?>" 
     data-price="<?= $item['Price'] ?>" 
     data-quantity="<?= $item['quantity'] ?>">
    <!-- Change this line in the cart item -->
<input type="checkbox" class="item-checkbox" value="<?= (int)$item['id'] ?>" checked>
    <img src="<?= htmlspecialchars($first_image) ?>" alt="<?= htmlspecialchars($item['ProductName']) ?>" class="cart-item-image">
                    <div class="cart-item-details">
                        <h3 class="cart-item-name"><?= htmlspecialchars($item['ProductName']) ?></h3>
                        <p class="cart-item-meta">Size: <?= htmlspecialchars($item['size']) ?></p>
                        <p class="cart-item-price">$<?= number_format($item['Price'], 2) ?></p>
                    </div>
                    
                    <div class="cart-item-controls">
                        <div class="quantity-control">
                            <button class="quantity-btn decrease-btn" onclick="updateQuantity(<?= (int)$item['id'] ?>, -1)">-</button>
                            <input type="number" class="quantity-input" value="<?= (int)$item['quantity'] ?>" min="1" onchange="updateQuantityInput(<?= (int)$item['id'] ?>, this.value)">
                            <button class="quantity-btn increase-btn" onclick="updateQuantity(<?= (int)$item['id'] ?>, 1)">+</button>
                        </div>
                    </div>
                    
                    <div class="cart-item-subtotal">
                        $<?= number_format($item_subtotal, 2) ?>
                    </div>
                    
                    <i class="fas fa-trash remove-item" onclick="removeItem(<?= (int)$item['id'] ?>)"></i>
                </div>
                <?php endwhile; ?>
            </div>
            
            <?php
            // Calculate final totals
            $tax = $subtotal * $tax_rate;
            $total = $subtotal + $tax + $shipping;
            ?>
            
            <!-- Update the summary section to use dynamic IDs -->
<div class="cart-summary">
    <div class="summary-row">
        <span>Subtotal:</span>
        <span id="summary-subtotal">$0.00</span>
    </div>
    <div class="summary-row">
        <span>Tax (8%):</span>
        <span id="summary-tax">$0.00</span>
    </div>
    <div class="summary-row">
        <span>Shipping:</span>
        <span id="summary-shipping">Free</span>
    </div>
    <div class="summary-row total">
        <span>Total:</span>
        <span id="summary-total">$0.00</span>
    </div>
</div>
            
            <div class="cart-buttons">
                <button class="continue-shopping" onclick="window.location.href='shop_customer.php'">Continue Shopping</button>
                <!-- Change the button to remove the inline onclick -->
<button class="proceed-checkout">Proceed to Checkout</button>
            </div>
            
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <p>Add some products to your cart and come back here to check out.</p>
                <a href="shop_customer.php">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Update quantity
        function updateQuantity(cartId, change) {
            const quantityInput = document.querySelector(`.cart-item[data-id="${cartId}"] .quantity-input`);
            let newQuantity = parseInt(quantityInput.value) + change;
            
            // Ensure quantity is at least 1
            if (newQuantity < 1) newQuantity = 1;
            
            updateCartItem(cartId, newQuantity);
        }
        
        // Update quantity from input
        function updateQuantityInput(cartId, value) {
            let newQuantity = parseInt(value);
            
            // Ensure quantity is at least 1
            if (newQuantity < 1 || isNaN(newQuantity)) newQuantity = 1;
            
            updateCartItem(cartId, newQuantity);
        }
        
        // Send AJAX request to update cart
        function updateCartItem(cartId, quantity) {
            document.querySelector(`.cart-item[data-id="${cartId}"]`).dataset.quantity = quantity;
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    cartId: cartId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update data attribute and recalculate
        document.querySelector(`.cart-item[data-id="${cartId}"]`).dataset.quantity = quantity;
        calculateTotals();
    } else {
        alert(data.message || 'Failed to update cart');
    }
})
        }
        
        function calculateTotals() {
    let subtotal = 0;
    const taxRate = 0.08;
    const shipping = 0;

    document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
        const item = checkbox.closest('.cart-item');
        const price = parseFloat(item.dataset.price);
        const quantity = parseInt(item.dataset.quantity);
        subtotal += price * quantity;
    });

    const tax = subtotal * taxRate;
    const total = subtotal + tax + shipping;

    // Update displayed totals
    document.getElementById('summary-subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('summary-tax').textContent = `$${tax.toFixed(2)}`;
    document.getElementById('summary-total').textContent = `$${total.toFixed(2)}`;
}

// Add event listeners
document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', calculateTotals);
});

// Initial calculation
calculateTotals();

        function removeItem(cartId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cartId: cartId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to reflect changes
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to remove item from cart');
                    }
                })
                .catch(error => {
                    console.error('Error removing item:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }

        // Add this new function to your <script> section
function proceedToCheckout() {
    const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'))
                              .map(checkbox => checkbox.value);
    
    if (selectedItems.length === 0) {
        alert('Please select at least one item to proceed to checkout.');
        return;
    }
    
    window.location.href = `checkout.php?selected_items=${selectedItems.join(',')}`;
}

// Add this at the end of your script section
// Change the DOMContentLoaded handler to
document.addEventListener('DOMContentLoaded', function() {
    // Initialize checkboxes
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', calculateTotals);
    });
    
    // Initial calculation
    calculateTotals();
    
    // Add click handler for checkout button
    document.querySelector('.proceed-checkout').addEventListener('click', proceedToCheckout);
});
    </script>
</body>
</html>