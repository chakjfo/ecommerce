<?php
// Start session
session_start();

// Include database connection
require_once 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user has admin role
$userID = $_SESSION['user_id'];
$sql = "SELECT Role FROM users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if ($row['Role'] !== 'admin') {
        // Redirect non-admin users
        header("Location: login.php");
        exit();
    }
} else {
    // User not found
    session_destroy();
    header("Location: homepage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body {
    font-family: 'Poppins', sans-serif;
    background-color: #f8f9fa;
}
:root {
    --primary-color: #4e73df;
    --secondary-color: #1cc88a;
    --dark-color: #2c3e50;
    --light-color: #f8f9fc;
    --danger-color: #e74a3b;
    --warning-color: #f6c23e;
}
/* Sidebar Styles */
#sidebar {
    min-width: 250px;
    max-width: 250px;
    min-height: 100vh;
    background: linear-gradient(180deg, var(--dark-color) 0%, #1a252f 100%);
    color: #fff;
    transition: all 0.3s;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    z-index: 1000;
}

#sidebar.active {
    margin-left: -250px;
}

#sidebar .sidebar-header {
    padding: 1.5rem 1rem;
    background: rgba(0,0,0,0.1);
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

#sidebar .sidebar-header h3 {
    margin: 0;
    font-weight: 700;
    font-size: 1.5rem;
}

#sidebar ul.components {
    padding: 1rem 0;
}

#sidebar ul li a {
    padding: 0.8rem 1.5rem;
    font-size: 0.9rem;
    display: block;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all 0.2s ease-in-out;
    letter-spacing: 0.5px;
}

#sidebar ul li a:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border-left: 3px solid var(--secondary-color);
}

#sidebar ul li.active > a {
    background: rgba(255,255,255,0.05);
    color: #fff;
    border-left: 3px solid var(--primary-color);
}

#sidebar ul li a i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

#sidebar ul ul a {
    padding-left: 3rem !important;
    font-size: 0.85rem !important;
    background: rgba(0,0,0,0.15);
}



ul ul a {
    font-size: 0.9em !important;
    padding-left: 40px !important;
    background: rgba(0, 0, 0, 0.2);
}

.wrapper {
    display: flex;
    width: 100%;
}

#content {
    width: 100%;
    padding: 20px;
    min-height: 100vh;
    transition: all 0.3s;
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    #sidebar {
        margin-left: -250px;
    }
    
    #sidebar.active {
        margin-left: 0;
    }
    
    .sidebarToggle {
        visibility: visible;
    }
}
.dashboard-card {
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
    border: none;
    overflow: hidden;
}

.dashboard-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
}

.dashboard-card .card-body {
    padding: 25px;
}

.dashboard-card h5 {
    font-weight: 600;
    font-size: 1.1rem;
}

.dashboard-card h3 {
    font-weight: 700;
    font-size: 2.2rem;
}

.dashboard-card .btn {
    border-radius: 6px;
    font-weight: 500;
    padding: 8px 16px;
    transition: all 0.3s;
}

.navbar {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    margin-bottom: 25px;
}

.navbar .btn {
    border-radius: 6px;
    padding: 10px 16px;
}

table {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.table-striped > tbody > tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.02);
}

.table thead th {
    background-color: #f5f5f5;
    border-bottom: none;
    font-weight: 600;
    color: #495057;
}

.table td, .table th {
    padding: 15px;
    vertical-align: middle;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.card-header {
    font-weight: 600;
    padding: 15px 20px;
}

.btn {
    border-radius: 6px;
    font-weight: 500;
}

.btn-sm {
    padding: 5px 12px;
    font-size: 0.85rem;
}

.btn-info {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
}

.container-fluid {
    padding: 0 25px;
}

h2, h3, h4, h5 {
    font-weight: 600;
}

h2.mb-4 {
    color: #343a40;
    margin-bottom: 25px !important;
    font-size: 1.8rem;
}

/* Enhanced card colors */
.bg-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
}

.bg-success {
    background: linear-gradient(135deg, #198754 0%, #146c43 100%) !important;
}

.bg-info {
    background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%) !important;
}

.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e5ac06 100%) !important;
}

.bg-dark {
    background: linear-gradient(135deg, #212529 0%, #1a1e21 100%) !important;
}
</style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="active">
                    <a href="admin.php">Home</a>
                </li>
                <li>
                    <a href="#productSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Products</a>
                    <ul class="collapse list-unstyled" id="productSubmenu">
                        <li>
                            <a href="products.php">View All Products</a>
                        </li>
                        <li>
                            <a href="add_product.php">Add New Product</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#categorySubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Categories</a>
                    <ul class="collapse list-unstyled" id="categorySubmenu">
                        <li>
                            <a href="categories.php">View All Categories</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#userSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Users</a>
                    <ul class="collapse list-unstyled" id="userSubmenu">
                        <li>
                            <a href="users.php">View All Users</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#orderSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Orders</a>
                    <ul class="collapse list-unstyled" id="orderSubmenu">
                        <li>
                            <a href="orders.php">View All Orders</a>
                        </li>
                        <li>
                            <a href="order_details.php">Order Details</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="logout.php">Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-dark">
                        <i class="fas fa-align-left"></i>
                        <span>Toggle Sidebar</span>
                    </button>
                    <div>
                        <h4>Welcome, <?php echo $_SESSION['Username'] ?? 'Admin'; ?></h4>
                    </div>
                </div>
            </nav>

            <div class="container-fluid">
                <h2 class="mb-4">Dashboard</h2>
                
                <div class="row">
                    <?php
                    // Count products
                    $productCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
                    
                    // Count users
                    $userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
                    
                    // Count categories
                    $categoryCount = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];
                    
                    // Count orders
                    $orderCount = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
                    ?>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Products</h5>
                                        <h3 class="mb-0"><?php echo $productCount; ?></h3>
                                    </div>
                                    <i class="fas fa-box fa-3x"></i>
                                </div>
                                <a href="products.php" class="btn btn-light mt-3">View All</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Categories</h5>
                                        <h3 class="mb-0"><?php echo $categoryCount; ?></h3>
                                    </div>
                                    <i class="fas fa-tags fa-3x"></i>
                                </div>
                                <a href="categories.php" class="btn btn-light mt-3">View All</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Users</h5>
                                        <h3 class="mb-0"><?php echo $userCount; ?></h3>
                                    </div>
                                    <i class="fas fa-users fa-3x"></i>
                                </div>
                                <a href="users.php" class="btn btn-light mt-3">View All</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Orders</h5>
                                        <h3 class="mb-0"><?php echo $orderCount; ?></h3>
                                    </div>
                                    <i class="fas fa-shopping-cart fa-3x"></i>
                                </div>
                                <a href="orders.php" class="btn btn-light mt-3">View All</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">Recent Orders</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>User</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Get recent orders
                                        $recentOrders = $conn->query("
                                            SELECT o.OrderID, u.Username, o.OrderDate, o.TotalAmount 
                                            FROM orders o
                                            LEFT JOIN users u ON o.UserID = u.UserID
                                            ORDER BY o.OrderDate DESC
                                            LIMIT 5
                                        ");
                                        
                                        if ($recentOrders->num_rows > 0) {
                                            while($order = $recentOrders->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $order['OrderID'] . "</td>";
                                                echo "<td>" . ($order['Username'] ?? 'Guest') . "</td>";
                                                echo "<td>" . date('M d, Y', strtotime($order['OrderDate'])) . "</td>";
                                                echo "<td>$" . number_format($order['TotalAmount'], 2) . "</td>";
                                                echo "<td>
                                                    <a href='orders.php?id=" . $order['OrderID'] . "' class='btn btn-sm btn-info'>View</a>
                                                </td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>No recent orders</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            document.getElementById('sidebarCollapse').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
            });
        });
    </script>
</body>
</html>