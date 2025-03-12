<?php 
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$username = htmlspecialchars($_SESSION['username']);
$user_id = $_SESSION['user_id'];

// Updated query to match your database structure
$query = "SELECT o.OrderID, o.OrderDate, o.TotalAmount, o.delivery_status, 
          o.shipping_address AS address, o.payment_method, 
          co.product_id, co.quantity, p.ProductName, p.Price, p.sizes 
          FROM orders o 
          LEFT JOIN customer_orders co ON o.OrderID = co.order_id 
          LEFT JOIN products p ON co.product_id = p.ProductName 
          WHERE o.UserID = ? 
          ORDER BY o.OrderDate DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Organize orders by OrderID
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orderId = $row['OrderID'];
    
    if (!isset($orders[$orderId])) {
        // Initialize order
        $orders[$orderId] = [
            'order_id' => $orderId,
            'order_date' => $row['OrderDate'],
            'TotalAmount' => $row['TotalAmount'],
            'delivery_status' => $row['delivery_status'],
            'address' => $row['address'],
            'payment_method' => $row['payment_method'],
            'items' => []
        ];
    }
    
    // Add items to order
    if ($row['product_id']) {
        $orders[$orderId]['items'][] = [
            'ProductName' => $row['ProductName'],
            'Size' => $row['sizes'],
            'Quantity' => $row['quantity'],
            'Price' => $row['Price']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - The Accents Clothing</title>
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
        
        /* Header Styles - Same as shop_customer.php */
        header {
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            background: white;
        }
        
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
        
        /* Profile Dropdown Styles */
        .profile-container {
            position: relative;
            display: inline-block;
        }
        
        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 200px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 10px 0;
            z-index: 1100;
            margin-top: 8px;
            display: none;
        }
        
        .profile-dropdown:before {
            content: '';
            position: absolute;
            top: -8px;
            right: 16px;
            width: 16px;
            height: 16px;
            background-color: white;
            transform: rotate(45deg);
            border-left: 1px solid rgba(0, 0, 0, 0.1);
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-item {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            transition: background-color 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: #f5f5f5;
        }
        
        .dropdown-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            color: #555;
        }
        
        .dropdown-item:first-child {
            border-bottom: 1px solid #eee;
            pointer-events: none;
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
        
        .profile-dropdown.show {
            display: block;
        }
        
        /* User Links & Profile - Similar to shop_customer.php */
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
        
        /* Orders Page Specific Styles */
        .container {
            max-width: 1200px;
            margin: 150px auto 50px;
            padding: 20px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            margin-top: -100px;
            font-size: 32px;
            text-transform: uppercase;
        }
        
        .orders-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .order-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        
        .order-number {
            font-size: 18px;
            font-weight: bold;
        }
        
        .order-date {
            color: #666;
            font-size: 16px;
        }
        
        .order-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-shipped {
            background-color:rgb(235, 141, 86);
            color:rgb(0, 0, 0);
        }
        
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .order-items {
            padding: 20px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-size: 16px;
        }
        
        .item-meta {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .item-price {
            font-size: 16px;
        }
        
        .order-footer {
            background-color: #f8f9fa;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
        }
        
        .order-total {
            font-size: 18px;
        }
        
        .shipping-info {
            color: #666;
            font-size: 14px;
        }
        
        .payment-method {
            color: #666;
            font-size: 14px;
        }
        
        .no-orders {
            text-align: center;
            padding: 50px 0;
            background-color: #f8f9fa;
            border-radius: 10px;
            font-size: 18px;
            color: #666;
        }
        
        /* Cancel Order Button Styles */
.cancel-order-btn {
    background-color:rgb(220, 66, 82); /* Red color for cancel action */
    color: black;
    border: none;
    padding: 8px 16px;
    font-size: 14px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.cancel-order-btn:hover {
    background-color:rgb(255, 0, 25); /* Darker red on hover */
}

.cancel-order-btn:active {
    background-color: #bd2130; /* Even darker red when clicked */
}

.cancel-order-btn:focus {
    outline: none; /* Remove default focus outline */
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.5); /* Add a focus ring */
}

        /* Responsive Design */
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
            }
            
            .nav-links {
                margin-top: 10px;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .order-footer {
                flex-direction: column;
                align-items: flex-start;
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
                <a href="shop)customer.php"><img src="images/the_accents_logo.png" alt="The Accents Logo"></a>
            </div>
            <div class="nav-links">
                <a href="shop_customer.php">Shop</a>
                <?php
                // Fetch categories for navigation
                $category_query = "SELECT category_name FROM categories";
                $category_result = $conn->query($category_query);
                
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
                    <div class="profile-container">
                        <div class="profile-circle" id="profileToggle">
                            <?php echo strtoupper(substr($username, 0, 1)); ?>
                        </div>
                        <div class="profile-dropdown" id="profileDropdown">
                            <div class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($username); ?></span>
                            </div>
                            <a href="<?php echo $username !== 'Guest' ? 'order_users.php' : 'login.php'; ?>" class="dropdown-item">
                                <i class="fas fa-box"></i>
                                <span>My Orders</span>
                            </a>
                            <?php if ($username !== 'Guest') : ?>
                                <a href="logout.php" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
        </nav>
    </header>

    <?php 
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$username = htmlspecialchars($_SESSION['username']);
$user_id = $_SESSION['user_id'];

// Updated query to match your database structure
$query = "SELECT o.OrderID, o.OrderDate, o.TotalAmount, o.delivery_status, 
          o.shipping_address AS address, o.payment_method, 
          co.product_id, co.quantity, p.ProductName, p.Price, p.sizes 
          FROM orders o 
          LEFT JOIN customer_orders co ON o.OrderID = co.order_id 
          LEFT JOIN products p ON co.product_id = p.ProductID
          WHERE o.UserID = ? 
          ORDER BY o.OrderDate DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Organize orders by OrderID
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orderId = $row['OrderID'];
    
    if (!isset($orders[$orderId])) {
        // Initialize order
        $orders[$orderId] = [
            'order_id' => $orderId,
            'order_date' => $row['OrderDate'],
            'TotalAmount' => $row['TotalAmount'],
            'delivery_status' => $row['delivery_status'],
            'address' => $row['address'],
            'payment_method' => $row['payment_method'],
            'items' => []
        ];
    }
    
    // Add items to order
    if ($row['product_id']) {
        $orders[$orderId]['items'][] = [
            'ProductName' => $row['ProductName'],
            'Size' => $row['sizes'],
            'Quantity' => $row['quantity'],
            'Price' => $row['Price']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - The Accents Clothing</title>
    <!-- Your CSS links and other head elements here -->
</head>
<body>
    <header>
        <!-- Header content here -->
    </header>

    <div class="container">
    <h1 class="page-title">My Orders</h1>
    
    <?php if (!empty($orders)) : ?>
        <div class="orders-container">
            <?php foreach ($orders as $order) : ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">Order #<?= $order['order_id'] ?></div>
                        <div class="order-date">
                            <?= date('F j, Y', strtotime($order['order_date'])) ?>
                        </div>
                        <div class="order-status status-<?= strtolower($order['delivery_status']) ?>">
                            <?= htmlspecialchars($order['delivery_status']) ?>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <?php if (!empty($order['items'])) : ?>
                            <?php foreach ($order['items'] as $item) : ?>
                                <div class="order-item">
                                    <div class="item-details">
                                        <div class="item-name"><?= htmlspecialchars($item['ProductName']) ?></div>
                                        <div class="item-meta">
                                            Size: <?= htmlspecialchars($item['Size']) ?> | 
                                            Qty: <?= (int)$item['Quantity'] ?>
                                        </div>
                                    </div>
                                    <div class="item-price">$<?= number_format($item['Price'], 2) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No item details available</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-footer">
                        <div class="order-total">Total: $<?= number_format($order['TotalAmount'], 2) ?></div>
                        <div class="shipping-info">
                            Shipping: <?= htmlspecialchars($order['address']) ?>
                        </div>
                        <div class="payment-method">
                            Payment: <?= htmlspecialchars($order['payment_method']) ?>
                        </div>
                        <?php if ($order['delivery_status'] !== 'Cancelled' && $order['delivery_status'] !== 'Delivered') : ?>
                            <button class="cancel-order-btn" data-order-id="<?= $order['order_id'] ?>">Cancel Order</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-orders">
            <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px;"></i>
            <p>You don't have any orders yet.</p>
            <a href="shop_customer.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: black; color: white; text-decoration: none; border-radius: 4px;">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const profileToggle = document.getElementById('profileToggle');
            const profileDropdown = document.getElementById('profileDropdown');
            
            if (profileToggle && profileDropdown) {
                // Toggle dropdown when profile circle is clicked
                profileToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('show');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (profileDropdown.classList.contains('show') && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.remove('show');
                    }
                });
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
        const cancelOrderButtons = document.querySelectorAll('.cancel-order-btn');
        
        cancelOrderButtons.forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                
                // Show confirmation prompt
                if (confirm('Are you sure you want to cancel this order?')) {
                    // Send AJAX request to cancel the order
                    fetch('cancel_order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            orderId: orderId
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Update the UI to reflect the cancelled status
                            const orderCard = this.closest('.order-card');
                            const statusElement = orderCard.querySelector('.order-status');
                            
                            // Update status text and styling
                            statusElement.textContent = 'Cancelled';
                            statusElement.classList.remove('status-pending', 'status-shipped');
                            statusElement.classList.add('status-cancelled');
                            
                            // Remove the Cancel Order button
                            this.remove();
                        } else {
                            alert(data.message || 'Failed to cancel the order');
                        }
                    })
                    .catch(error => {
                        console.error('Error cancelling order:', error);
                        alert('An error occurred. Please try again.');
                    });
                }
            });
        });
    });
    </script>
</body>
</html>