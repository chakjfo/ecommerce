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

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid product ID.";
    header("Location: products.php");
    exit();
}

$productId = $_GET['id'];

// Get product data with category information
$productQuery = "
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.categories = c.id
    WHERE p.ProductID = ?
";
$productStmt = $conn->prepare($productQuery);
$productStmt->bind_param("i", $productId);
$productStmt->execute();
$productResult = $productStmt->get_result();

if ($productResult->num_rows !== 1) {
    $_SESSION['error_message'] = "Product not found.";
    header("Location: products.php");
    exit();
}

$product = $productResult->fetch_assoc();
$productImages = json_decode($product['images'], true);
if (!is_array($productImages)) {
    $productImages = ['uploads/no-image.jpg'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Product - <?php echo htmlspecialchars($product['ProductName']); ?></title>
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
        .product-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .thumbnail-container {
            display: flex;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 10px;
            margin-bottom: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        .thumbnail:hover, .thumbnail.active {
            border-color: #007bff;
        }
        .product-info-table th {
            width: 30%;
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
                        <h2>Product Details: <?php echo htmlspecialchars($product['ProductName']); ?></h2>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                        <a href="edit_product.php?id=<?php echo $productId; ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Product
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Product Images -->
                    <div class="col-md-5">
                        <div class="card mb-4">
                            <div class="card-body">
                                <?php if (!empty($productImages)): ?>
                                    <img id="mainImage" src="<?php echo htmlspecialchars($productImages[0]); ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>" class="product-image img-fluid">
                                    
                                    <!-- Thumbnails -->
                                    <?php if (count($productImages) > 1): ?>
                                        <div class="thumbnail-container">
                                            <?php foreach($productImages as $index => $imagePath): ?>
                                                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Thumbnail <?php echo $index + 1; ?>" class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" onclick="changeMainImage('<?php echo htmlspecialchars($imagePath); ?>', this)">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center p-4">
                                        <i class="fas fa-image fa-5x text-muted"></i>
                                        <p class="mt-3 text-muted">No images available</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Information -->
                    <div class="col-md-7">
                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">Product Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered product-info-table">
                                    <tbody>
                                        <tr>
                                            <th>Product ID</th>
                                            <td><?php echo $product['ProductID']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Product Name</th>
                                            <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Category</th>
                                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Price</th>
                                            <td>$<?php echo number_format($product['Price'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Stock Quantity</th>
                                            <td>
                                                <?php echo $product['StockQuantity']; ?>
                                                <?php if ($product['StockQuantity'] <= 5): ?>
                                                    <span class="badge bg-danger ms-2">Low Stock</span>
                                                <?php elseif ($product['StockQuantity'] <= 20): ?>
                                                    <span class="badge bg-warning ms-2">Medium Stock</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success ms-2">In Stock</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Created Date</th>
                                            <td><?php echo date('F j, Y, g:i a', strtotime($product['created_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated</th>
                                            <td><?php echo date('F j, Y, g:i a', strtotime($product['updated_at'])); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Product Description -->
                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">Product Description</h5>
                            </div>
                            <div class="card-body">
                                <div class="p-2">
                                    <?php echo nl2br(htmlspecialchars($product['Description'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end">
                            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $product['ProductID']; ?>)" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete Product
                            </a>
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
                    <p>Are you sure you want to delete this product?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
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
        
        // Change main image
        function changeMainImage(imagePath, thumbnail) {
            document.getElementById('mainImage').src = imagePath;
            
            // Update active thumbnail
            const thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach(item => {
                item.classList.remove('active');
            });
            
            thumbnail.classList.add('active');
        }
        
        // Delete confirmation
        function confirmDelete(productId) {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('confirmDeleteBtn').href = 'products.php?delete=' + productId;
            deleteModal.show();
        }
    </script>
</body>
</html>