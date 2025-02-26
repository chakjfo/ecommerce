<?php 
    session_start();
    require 'db_connection.php'; // Ensure this connects to your database

    if (!isset($_SESSION['username'])) {
        $_SESSION['username'] = "Guest";
    }
    $username = htmlspecialchars($_SESSION['username']);
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
            margin: 100px auto 50px;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .product-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: scale(1.05);
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .product-card h3 {
            margin-top: 10px;
            font-size: 18px;
        }
        .product-card p {
            font-size: 16px;
            color: #555;
        }
        .product-card .price {
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        .add-to-cart {
            background: black;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        .add-to-cart:hover {
            background: gray;
        }
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
            }
            .nav-links {
                margin-top: 10px;
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
            <a href="shop_customer.php"><img src="images/the_accents_logo.png" alt="The Accents Logo"></a>
        </div>
        <div class="nav-links">
            <a href="shop_customer.php">Shop</a>
            <a href="#">Women</a>
            <a href="#">Men</a>
            <a href="#">Accessories</a>
        </div>
        <div class="user-links">
            <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
            <a href="notifications.php"><i class="fas fa-bell"></i></a>
            <div class="profile-circle">
                <?php echo strtoupper(substr($username, 0, 1)); ?>
            </div>
            <?php if ($username !== "Guest") : ?>
                <a href="logout.php" style="font-size: 14px; color: black;">Logout</a>
            <?php else : ?>
                <a href="login.php" style="font-size: 14px;">Login</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
</body>
</html>
