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

.product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.product-image:hover {
    transform: scale(1.1);
}

.action-buttons .btn {
    margin-right: 5px;
    border-radius: 6px;
    transition: all 0.2s;
}

.action-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 30px;
}

.card-header {
    font-weight: 600;
    padding: 15px 20px;
}

.card-header.bg-dark {
    background: linear-gradient(135deg, #212529 0%, #1a1e21 100%) !important;
}

.card-body {
    padding: 25px;
}
/* Table Styling */
.table {
    width: 100%;
    border-collapse: collapse; /* Collapse borders */
    margin-top: 20px;
    background-color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.table th, .table td {
    padding: 12px 15px;
    text-align: left;
    border: 1px solid #ddd; /* Add borders to cells */
}

.table th {
    background-color: #f8f9fa;
    font-weight: bold;
    color: #333;
    border-bottom: 2px solid #ddd;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.05); /* Striped rows */
}

.table tr:hover {
    background-color: #f5f5f5; /* Hover effect */
}


/* Action Buttons Styling */
.action-buttons .btn {
    margin-right: 5px;
    transition: opacity 0.3s ease;
}

.action-buttons .btn:hover {
    opacity: 0.9;
}
.btn {
    font-weight: 500;
    border-radius: 6px;
    padding: 8px 16px;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    border: none;
    box-shadow: 0 4px 6px rgba(13, 110, 253, 0.15);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0a58ca 0%, #084298 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(13, 110, 253, 0.2);
}

.btn-info {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
    color: #fff;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 0.85rem;
}

.alert {
    border-radius: 10px;
    border: none;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
}

.alert-success {
    background-color: #d1e7dd;
    color: #0f5132;
}

.alert-danger {
    background-color: #f8d7da;
    color: #842029;
}

.navbar {
    background-color: #f8f9fa !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    margin-bottom: 25px;
}

.navbar .btn {
    border-radius: 6px;
}

.container-fluid {
    padding: 0 25px;
}

h2, h3, h4, h5 {
    font-weight: 600;
}

h2 {
    color: #343a40;
    margin-bottom: 25px;
    font-size: 1.8rem;
}

/* Modal styling */
.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.modal-header {
    padding: 18px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.modal-header.bg-danger {
    background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%) !important;
}

.modal-body {
    padding: 25px;
    font-size: 1.05rem;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

/* Add these styles to your existing CSS */
.dataTables_wrapper .dataTables_length {
    float: left;
    margin-bottom: 0;
}

.dataTables_wrapper .dataTables_filter {
    float: right;
    text-align: right;
    margin-bottom: 0;
}

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    padding: 5px 0;
}

/* Fix for mobile responsiveness */
@media screen and (max-width: 767px) {
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        float: none;
        text-align: left;
    }
    
    .dataTables_wrapper .dataTables_filter {
        margin-top: 10px;
    }
}
.dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 10px;
}


.dataTables_filter input:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}
/* Custom DataTables Layout */
.dataTables_wrapper {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.dataTables_length {
    order: 1; /* Move the "Show entries" dropdown to the left */
    margin-bottom: -40px;

}



.dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 10px;
}

.dataTables_filter input {
    width: 200px; /* Adjust the width of the search input */
    border-radius: 4px;
    border: 1px solid #ddd;
    padding: 8px;
    transition: border-color 0.3s ease;
}

.dataTables_filter input:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Make the table more responsive */
.table-responsive {
    border-radius: 10px;
    overflow: hidden;
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
            <!-- Table with Striped Rows and Borders -->
            <table class="table table-striped table-bordered table-hover" id="productsTable">
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
    <!-- Include DataTables CSS and JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

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
    // Initialize DataTable
    $('#productsTable').DataTable({
        "paging": true,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "searching": true,
        "ordering": true,
        "info": true,
        "responsive": true,
        // Update the DOM layout to match the reference image
        "dom": '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"ms-auto"f>><"clear">rt<"d-flex justify-content-between"ip><"clear">',
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Search:",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
});
</script>
</body>
</html>