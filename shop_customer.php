<?php 
session_start();
require 'db_connection.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "Guest";
}
$username = htmlspecialchars($_SESSION['username']);

// Check if a category is selected
$selected_category = isset($_GET['category']) ? $_GET['category'] : "";

// Prepare the query based on whether a category is selected
if (!empty($selected_category)) {
    // Sanitize the input to prevent SQL injection
    $selected_category = mysqli_real_escape_string($conn, $selected_category);
    
    // Use JOIN to filter products by category name
    $query = "SELECT p.*, c.category_name FROM products p 
              JOIN categories c ON p.categories = c.id 
              WHERE c.category_name = '$selected_category' 
              ORDER BY p.ProductID DESC";
} else {
    // No category selected, show all products
    $query = "SELECT * FROM products ORDER BY ProductID DESC";
}

$result = mysqli_query($conn, $query);

// Fetch categories from the database
$category_query = "SELECT category_name FROM categories";
$category_result = $conn->query($category_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Accents Clothing</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Atkinson+Hyperlegible+Mono:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: "Anton", sans-serif;
            box-sizing: border-box;
        }
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
        .container {
            max-width: 1200px;
            margin: 150px auto 50px;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            justify-items: center;
        }
        .product-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
            width: 300px;
            height: 450px;
            display: flex;
            flex-direction: column;
        }
        .product-card:hover {
            transform: scale(1.03);
        }
        .product-card .image-container {
            position: relative;
            width: 100%;
            height: 250px;
            overflow: hidden;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .product-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s;
        }
        .product-card:hover img {
            transform: scale(1.1);
        }
        .product-card h3 {
            margin-top: 10px;
            font-size: 18px;
            height: 25px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .product-card .desc {
            font-size: 14px;
            color: #555;
            margin: 8px 0;
            height: 40px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .product-card .price {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            color: #000;
        }
        .product-card .sizes {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin: 10px 0;
        }
        .product-card .size-badge {
            background-color: #f0f0f0;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .add-to-cart {
            background: black;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
            display: inline-block;
            cursor: pointer;
            width: 100%;
            margin-top: auto;
        }
        .add-to-cart:hover {
            background: #333;
        }
        .error-message {
            background-color: #ffecec;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            font-size: 18px;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 10px;
            width: 100%;
        }
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
            }
            .nav-links {
                margin-top: 10px;
            }
            .container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            .product-card {
                width: 250px;
                height: 400px;
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
            <?php if ($username !== "Guest") : ?>
                <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
                <a href="notifications.php"><i class="fas fa-bell"></i></a>
            <?php else : ?>
                <a href="login.php"><i class="fas fa-shopping-cart"></i></a>
                <a href="login.php"><i class="fas fa-bell"></i></a>
            <?php endif; ?>

            <?php if ($username !== "Guest") : ?>
                <div class="profile-circle">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>
                <a href="logout.php" style="font-size: 14px; color: black;">Logout</a>
            <?php else : ?>
                <a href="signup.php" style="font-size: 14px;">Sign Up</a>
                <a href="login.php" style="font-size: 14px;">Login</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<div class="container">
    <?php
    // Check if we have products
    if ($result && mysqli_num_rows($result) > 0) {
        // Display each product
        while ($product = mysqli_fetch_assoc($result)) {
            // Get the product details
            $productId = $product['ProductID'];
            $productName = htmlspecialchars($product['ProductName']);
            $productDesc = htmlspecialchars($product['Description']);
            $productPrice = number_format($product['Price'], 2);
            
            // Get the first image from the images JSON field
            $productImages = json_decode($product['images'], true);
            $productImage = is_array($productImages) && !empty($productImages) ? $productImages[0] : 'images/default-product.jpg';
            
            // Get sizes if available
            $sizes = ['S', 'M', 'L']; // Default sizes
            if (isset($product['Sizes']) && !empty($product['Sizes'])) {
                $sizes = explode(',', $product['Sizes']);
            }
            
            // Output the product card
            echo "<div class='product-card'>";
            echo "<div class='image-container'>";
            echo "<img src='" . htmlspecialchars($productImage) . "' alt='" . htmlspecialchars($productName) . "' onerror=\"this.src='images/default-product.jpg'\">";
            echo "</div>";
            echo "<h3>$productName</h3>";
            echo "<p class='desc'>$productDesc</p>";
            echo "<div class='price'>$" . $productPrice . "</div>";
            
            // Display sizes
            echo "<div class='sizes'>";
            foreach ($sizes as $size) {
                echo "<span class='size-badge'>" . trim($size) . "</span>";
            }
            echo "</div>";
            
            // Add to cart button
            echo "<a href='add_to_cart.php?id=$productId' class='add-to-cart'>Add to Cart</a>";
            echo "</div>";
        }
    } else {
        echo "<div class='no-products'>No products found. Please check your database connection or add products.</div>";
    }
    ?>
</div>

</body>
</html>