<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once "db_connection.php";
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <style>
        body {
            background-color: #f3f4f6;
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: rgb(8, 8, 8);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            transition: 0.3s ease-in-out;
            overflow: hidden;
        }
        .sidebar .logo {
            padding: 20px;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #333;
        }
        .sidebar nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: white;
            cursor: pointer;
            transition: 0.3s;
        }
        .sidebar nav a i {
            margin-right: 10px;
        }
        .sidebar nav a:hover {
            background-color: white;
            color: black;
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
        .user-info {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            position: absolute;
            bottom: 10px;
            left: 10px;
        }
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: 0.3s;
            width: 100%;
        }
        /* Toggle Sidebar */
        .sidebar.collapsed {
            width: 70px;
        }
        .sidebar.collapsed .logo {
            font-size: 16px;
            padding: 15px;
        }
        .sidebar.collapsed nav a {
            justify-content: center;
            padding: 15px;
        }
        .sidebar.collapsed nav a span {
            display: none;
        }
        .sidebar.collapsed .user-info {
            display: none;
        }
        .content.collapsed {
            margin-left: 70px;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
                width: 250px;
            }
            .sidebar.active {
                left: 0;
            }
            .content {
                margin-left: 0;
                width: 100%;
            }
            .toggle-btn {
                display: block;
            }
        }
        /* Sidebar Toggle Button */
        .toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: black;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 18px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">Dashboard</div>
    <nav>
        <a href="#" onclick="loadPage('users.php')"><i class="fas fa-users"></i> <span>Users</span></a>
        <a onclick="loadPage('products.php')"><i class="fas fa-box"></i> <span>Products</span></a>
        <a onclick="loadPage('orders.php')"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </nav>
    <div class="user-info">
        <img src="https://via.placeholder.com/40" alt="User Profile">
        <div>
            <p><?php echo htmlspecialchars($username); ?></p>
            <p class="text-sm text-blue-200">Admin</p>
        </div>
    </div>
</div>

<!-- Toggle Button -->
<button class="toggle-btn" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Main Content -->
<div class="content">
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    <p>Manage your dashboard efficiently.</p>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const content = document.querySelector('.content');

        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('collapsed');
        }
    }

    window.addEventListener("resize", function() {
        if (window.innerWidth > 768) {
            document.querySelector('.sidebar').classList.remove('active');
        }
    });
    // Add this script to your dashboard page
function loadPage(page) {
    $.ajax({
        url: page,
        type: 'GET',
        success: function(data) {
            $('.content').html(data);
        },
        error: function() {
            $('.content').html('<h2>Error loading page</h2>');
        }
    });
}
</script>

</body>
</html>
