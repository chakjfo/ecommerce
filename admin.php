<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            background-color: #f3f4f6;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            width: 200px;
            height: 100vh;
            background-color: rgb(8, 8, 8);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar .logo {
            padding: 20px;
            font-size: 22px;
            font-weight: bold;
        }
        .sidebar nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: white;
            cursor: pointer;
        }
        .sidebar nav a:hover {
            background-color: rgb(255, 255, 255);
            color: black;
            transition: 0.3s;
        }
        .logout-btn {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            background-color: black;
            text-decoration: none;
            margin-top: 10px;
        }
        .logout-btn:hover {
            background-color: white;
            color: black;
        }
        .logout-btn i {
            margin-right: 8px;
        }
        .user-info {
            padding: 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
        }
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .user-info div {
            margin-left: 12px;
        }
    </style>
</head>
<body>

<div class="flex">
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <div class="logo">Dashboard</div>
            <nav>
                <a href="#"><i class="fas fa-users"></i> <span class="ml-3">Users</span></a>
                <a href="#"><i class="fas fa-box"></i> <span class="ml-3">Orders</span></a>
                <a id="load-products" class="cursor-pointer"><i class="fas fa-shopping-cart"></i> <span class="ml-3">Items</span></a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <div class="user-info">
            <img src="https://via.placeholder.com/40" alt="User Profile">
            <div>
                <p class="font-semibold"><?= htmlspecialchars($username); ?></p>
                <p class="text-sm text-blue-200">Admin</p>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 p-10" id="content">
        <h1 class="text-2xl font-bold">Welcome, <?= htmlspecialchars($username); ?>!</h1>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#load-products").click(function() {
            $("#content").load("products.php");
        });
    });
</script>

</body>
</html>
