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
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <style>
        body { background-color: #f3f4f6; font-family: Arial, sans-serif; }
        .sidebar { width: 200px; height: 100vh; background-color: rgb(8, 8, 8); color: white; position: fixed; left: 0; top: 0; }
        .sidebar .logo { padding: 20px; font-size: 22px; font-weight: bold; }
        .sidebar nav a { display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: white; cursor: pointer; }
        .sidebar nav a:hover { background-color: white; color: black; transition: 0.3s; }
        .logout-btn { display: flex; align-items: center; padding: 12px 20px; color: white; background-color: black; text-decoration: none; margin-top: 10px; }
        .logout-btn:hover { background-color: white; color: black; }
        .user-info { padding: 20px; border-top: 1px solid rgba(0, 0, 0, 0.4); display: flex; align-items: center; }
        .user-info img { width: 40px; height: 40px; border-radius: 50%; }
        .content { margin-left: 200px; padding: 20px; }
    </style>
</head>
<body>
<div class="flex">
    <div class="sidebar">
        <div>
            <div class="logo">Dashboard</div>
            <nav>
                <a onclick="loadPage('users.php')"><i class="fas fa-users"></i> <span class="ml-3">Users</span></a>
                <a onclick="loadPage('products.php')"><i class="fas fa-box"></i> <span class="ml-3">Products</span></a>
                <a onclick="loadPage('orders.php')"><i class="fas fa-shopping-cart"></i> <span class="ml-3">Orders</span></a>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
        <div class="user-info">
            <img src="https://via.placeholder.com/40" alt="User Profile">
            <div>
                <p class="font-semibold"><?php echo htmlspecialchars($username); ?></p>
                <p class="text-sm text-blue-200">Admin</p>
            </div>
        </div>
    </div>
    
    <div class="content">
        <h1 class="text-2xl font-bold">Welcome to the Dashboard</h1>
        <div class="grid grid-cols-3 gap-4 mt-6">
            <div class="bg-blue-500 text-white p-6 rounded-lg">
                <p class="text-lg">Products</p>
                <p class="text-3xl font-bold">
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM products";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    echo $row['total'];
                    ?>
                </p>
            </div>
            <div class="bg-yellow-500 text-white p-6 rounded-lg">
                <p class="text-lg">Users</p>
                <p class="text-3xl font-bold">
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM users";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    echo $row['total'];
                    ?>
                </p>
            </div>
            <div class="bg-green-500 text-white p-6 rounded-lg">
                <p class="text-lg">Orders</p>
                <p class="text-3xl font-bold">
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM orders";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    echo $row['total'];
                    ?>
                </p>
            </div>
        </div>
        <div id="page-content" class="mt-6"></div>
    </div>
</div>

<script>
    function loadPage(page) {
        fetch(page)
            .then(response => response.text())
            .then(data => {
                document.getElementById('page-content').innerHTML = data;
                if (page === 'users.php') {
                    $('#usersTable').DataTable();
                }
            })
            .catch(error => console.error('Error loading page:', error));
    }
</script>
</body>
</html>