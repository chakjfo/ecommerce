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

// Get categories for the dropdown
$categoryQuery = "SELECT id, category_name FROM categories ORDER BY category_name";
$categories = $conn->query($categoryQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $productName = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stockQuantity = intval($_POST['stock_quantity'] ?? 0);
    $categoryId = intval($_POST['category'] ?? 0);
    
    // Validate form data
    $errors = [];
    
    if (empty($productName)) {
        $errors[] = "Product name is required.";
    }
    
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    
    if ($price <= 0) {
        $errors[] = "Price must be greater than zero.";
    }
    
    if ($stockQuantity < 0) {
        $errors[] = "Stock quantity cannot be negative.";
    }
    
    // Handle image uploads (up to 4 images)
    $uploadedImages = [];
    $uploadDir = 'uploads/products/';
    
    // Create the upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Process up to 4 image uploads
    for ($i = 1; $i <= 4; $i++) {
        if (isset($_FILES['product_image_' . $i]) && $_FILES['product_image_' . $i]['error'] === UPLOAD_ERR_OK) {
            $tempName = $_FILES['product_image_' . $i]['tmp_name'];
            $fileName = $_FILES['product_image_' . $i]['name'];
            $fileSize = $_FILES['product_image_' . $i]['size'];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Check file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "Image $i: Only JPG, JPEG, PNG, and GIF files are allowed.";
                continue;
            }
            
            // Check file size (max 2MB)
            if ($fileSize > 2 * 1024 * 1024) {
                $errors[] = "Image $i: File size should not exceed 2MB.";
                continue;
            }
            
            // Generate unique filename
            $newFileName = uniqid('product_') . '_' . time() . '.' . $fileType;
            $destination = $uploadDir . $newFileName;
            
            // Move uploaded file
            if (move_uploaded_file($tempName, $destination)) {
                $uploadedImages[] = $destination;
            } else {
                $errors[] = "Image $i: Failed to upload image.";
            }
        }
    }
    
    // If no image was uploaded, use a default image
    if (empty($uploadedImages)) {
        $uploadedImages[] = 'uploads/no-image.jpg';
    }
    
    // If there are no errors, insert the product into the database
    if (empty($errors)) {
        // Convert the image paths to JSON
        $imagesJSON = json_encode($uploadedImages);
        
        // Insert the product
        $insertQuery = "INSERT INTO products (ProductName, Description, Price, StockQuantity, images, categories, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ssdisi", $productName, $description, $price, $stockQuantity, $imagesJSON, $categoryId);
        
        if ($insertStmt->execute()) {
            $_SESSION['success_message'] = "Product added successfully!";
            header("Location: products.php");
            exit();
        } else {
            $errors[] = "Error adding product: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
/* Reuse Admin Panel Styles */
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

/* Card Styles */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: all 0.3s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
}

.card-header {
    font-weight: 600;
    padding: 15px 20px;
    background: linear-gradient(135deg, #2b3035 0%, #1a1e21 100%);
    color: #fff;
}

.card-body {
    padding: 25px;
}

/* Form Styles */
.form-control {
    border-radius: 8px;
    border: 1px solid #ddd;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

.form-label {
    font-weight: 500;
    color: #495057;
}

/* Button Styles */
.btn {
    border-radius: 6px;
    font-weight: 500;
    padding: 10px 20px;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0a58ca 0%, #0d6efd 100%);
    transform: translateY(-2px);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    border: none;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #6c757d 100%);
    transform: translateY(-2px);
}

/* Image Preview Styles */
.image-preview {
    margin-top: 10px;
}

.image-preview img {
    max-width: 100px;
    max-height: 100px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 5px;
    margin-right: 10px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.image-preview img:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Table Styles */
.table {
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

/* Alert Styles */
.alert {
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.alert-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: #fff;
}

.alert-success {
    background: linear-gradient(135deg, #198754 0%, #146c43 100%);
    color: #fff;
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
                        <li>
                            <a href="products.php">View All Products</a>
                        </li>
                        <li class="active">
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
                    </button>
                    <div>
                        <h4>Welcome, <?php echo $_SESSION['Username'] ?? 'Admin'; ?></h4>
                    </div>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h2>Add New Product</h2>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
                
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong>
                        <ul>
                            <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header bg-dark text-white" style="background: linear-gradient(135deg, #343a40, #212529);">
                        <h5 class="mb-0"><i class="fas fa-cube"></i> Product Details</h5>
                    </div>
                    <div class="card-body">
                        <form action="add_product.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo $_POST['product_name'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="0">-- Select Category --</option>
                                        <?php while($category = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category']) && $_POST['category'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo $_POST['description'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $_POST['price'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo $_POST['stock_quantity'] ?? ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Product Images (Max 4 Images, 2MB per image)</label>
                                <div class="row">
                                    <?php for($i = 1; $i <= 4; $i++): ?>
                                        <div class="col-md-3 mb-3">
                                            <label for="product_image_<?php echo $i; ?>" class="form-label">Image <?php echo $i; ?> <?php echo $i === 1 ? '<span class="text-danger">*</span>' : ''; ?></label>
                                            <input type="file" class="form-control" id="product_image_<?php echo $i; ?>" name="product_image_<?php echo $i; ?>" accept="image/*" <?php echo $i === 1 ? 'required' : ''; ?> onchange="previewImage(this, 'preview_<?php echo $i; ?>')">
                                            <div id="preview_<?php echo $i; ?>" class="image-preview mt-2"></div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="reset" class="btn btn-secondary">Reset</button>
                                <button type="submit" class="btn btn-primary">Add Product</button>
                            </div>
                        </form>
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
        
        // Image preview
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Preview';
                    preview.appendChild(img);
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>