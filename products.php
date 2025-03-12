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

function safeDeleteProduct($conn, $productId) {
    // Check if there are any active orders for this product
    $orderCheckQuery = "
        SELECT COUNT(*) as active_orders 
        FROM customer_orders co
        JOIN orders o ON co.order_id = o.OrderID
        WHERE co.product_id = ? 
        AND o.delivery_status NOT IN ('delivered', 'cancelled')
    ";

    $checkStmt = $conn->prepare($orderCheckQuery);
    $checkStmt->bind_param("i", $productId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $orderCount = $result->fetch_assoc()['active_orders'];

    // If there are active orders, don't allow deletion
    if ($orderCount > 0) {
        return [
            'success' => false,
            'message' => "Cannot delete this product. It has $orderCount active orders that are not yet delivered or cancelled."
        ];
    }

    // Delete from customer_orders first
$deleteOrdersStmt = $conn->prepare("DELETE FROM customer_orders WHERE product_id = ?");
$deleteOrdersStmt->bind_param("i", $productId);
$deleteOrdersStmt->execute();

    // Safe to proceed with deletion
    // 1. Get product images first
    $imageQuery = "SELECT images FROM products WHERE ProductID = ?";
    $imgStmt = $conn->prepare($imageQuery);
    $imgStmt->bind_param("i", $productId);
    $imgStmt->execute();
    $imageResult = $imgStmt->get_result();

    // Delete images from filesystem if they exist
    if ($imageResult->num_rows > 0) {
        $imageRow = $imageResult->fetch_assoc();
        $imagesList = json_decode($imageRow['images'], true);

        if (is_array($imagesList)) {
            foreach ($imagesList as $imagePath) {
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
    }

    // 2. Delete the product
    $deleteStmt = $conn->prepare("DELETE FROM products WHERE ProductID = ?");
    $deleteStmt->bind_param("i", $productId);

    if ($deleteStmt->execute()) {
        return [
            'success' => true,
            'message' => "Product deleted successfully!"
        ];
    } else {
        return [
            'success' => false,
            'message' => "Error deleting product: " . $conn->error
        ];
    }
}

// Handle Delete Request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $productId = $_GET['delete'];
    
    $result = safeDeleteProduct($conn, $productId);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
    
    // Redirect to refresh the page
    header("Location: products.php");
    exit();
}

// Get all products with their categories
$query = "
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.categories = c.id
    ORDER BY p.ProductID DESC
";
$products = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- DataTables Bootstrap 5 Integration -->
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
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
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .action-buttons .btn {
            margin-right: 5px;
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
                <li>
                    <a href="admin.php">Home</a>
                </li>
                <li class="active">
                    <a href="#productSubmenu" data-bs-toggle="collapse" aria-expanded="true" class="dropdown-toggle">Products</a>
                    <ul class="collapse show list-unstyled" id="productSubmenu">
                        <li class="active">
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
                        <span></span>
                    </button>
                    <div>
                        <h4>Welcome, <?php echo $_SESSION['Username'] ?? 'Admin'; ?></h4>
                    </div>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h2>Products Management</h2>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="add_product.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Product
                        </a>
                    </div>
                </div>
                
                <!-- Display success or error message if any -->
                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                            echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                            echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Products Table -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">All Products</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($products->num_rows > 0): ?>
                                        <?php while($product = $products->fetch_assoc()): 
                                            // Get the first image from the images JSON field
                                            $productImages = json_decode($product['images'], true);
                                            $firstImage = is_array($productImages) && !empty($productImages) ? $productImages[0] : 'uploads/no-image.jpg';
                                        ?>
                                            <tr>
                                                <td><?php echo $product['ProductID']; ?></td>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($firstImage); ?>" class="product-image" alt="<?php echo htmlspecialchars($product['ProductName']); ?>">
                                                </td>
                                                <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                                <td>$<?php echo number_format($product['Price'], 2); ?></td>
                                                <td><?php echo $product['StockQuantity']; ?></td>
                                                <td class="action-buttons">
                                                    <a href="view_product.php?id=<?php echo $product['ProductID']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="edit_product.php?id=<?php echo $product['ProductID']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $product['ProductID']; ?>)" class="btn btn-sm btn-danger">
    <i class="fas fa-trash"></i> Delete
</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No products found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this product? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
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
        
// This function should be placed in your JavaScript section
function confirmDelete(productId) {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    document.getElementById('confirmDeleteBtn').href = 'products.php?delete=' + productId;
    deleteModal.show();
}

    $(document).ready(function() {
        $('#productsTable').DataTable({
            "paging": true, // Enable pagination
            "searching": true, // Enable search bar
            "ordering": true, // Enable column sorting
            "info": true, // Show table information
            "responsive": true, // Make table responsive
            "lengthMenu": [10, 25, 50, 100], // Set entries per page options
            "pageLength": 10, // Default number of entries per page
            "language": {
                "search": "Search:", // Customize search placeholder
                "lengthMenu": "Show _MENU_ entries", // Customize entries per page text
                "info": "Showing _START_ to _END_ of _TOTAL_ entries", // Customize info text
                "paginate": {
                    "previous": "Previous", // Customize previous button text
                    "next": "Next" // Customize next button text
                }
            }
        });
    });
    </script>
</body>
</html>