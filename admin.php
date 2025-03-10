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
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background: #343a40;
            color: #fff;
            transition: all 0.3s;
        }
        #sidebar.active {
            margin-left: -250px;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: #212529;
        }
        #sidebar ul.components {
            padding: 20px 0;
            border-bottom: 1px solid #4b545c;
        }
        #sidebar ul p {
            color: #fff;
            padding: 10px;
        }
        #sidebar ul li a {
            padding: 10px;
            font-size: 1.1em;
            display: block;
            color: #fff;
            text-decoration: none;
        }
        #sidebar ul li a:hover {
            color: #000;
            background: #fff;
        }
        #sidebar ul li.active > a,
        a[aria-expanded="true"] {
            color: #fff;
            background: #6d7fcc;
        }
        ul ul a {
            font-size: 0.9em !important;
            padding-left: 30px !important;
            background: #4b545c;
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
        }
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }
            #sidebar.active {
                margin-left: 0;
            }
            #sidebarCollapse span {
                display: none;
            }
        }
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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
                                                    <a href='view_order.php?id=" . $order['OrderID'] . "' class='btn btn-sm btn-info'>View</a>
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