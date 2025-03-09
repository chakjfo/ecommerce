<?php 
session_start();
require 'db_connection.php';

// User authentication handling
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Guest";

// Category selection handling
$selected_category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : "";

// Prepare query based on category selection
if (!empty($selected_category)) {
    $query = "SELECT p.*, c.category_name FROM products p 
              JOIN categories c ON p.categories = c.id 
              WHERE c.category_name = '$selected_category' 
              ORDER BY p.ProductID DESC";
} else {
    $query = "SELECT * FROM products ORDER BY ProductID DESC";
}
$result = mysqli_query($conn, $query);

// Fetch categories for navigation
$category_query = "SELECT category_name FROM categories";
$category_result = $conn->query($category_query);
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
    <link rel="stylesheet" href="css/shop_customer.css">
</head>
<style>
    /* Base Styles */
* {
    margin: 0;
    padding: 0;
    font-family: "Anton", sans-serif;
    box-sizing: border-box;
}

/* Header Styles */
header {
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    background: white;
}

/* Running Text Banner */
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

/* Navigation Styles */
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

/* User Links & Profile */
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

/* Product Grid Container */
.container {
    max-width: 1200px;
    margin: 150px auto 50px;
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    justify-items: center;
}

/* Product Card Styles */
.product-card {
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s;
    width: 300px;
    height: 450px;
    display: flex;
    flex-direction: column;
    cursor: pointer;
}
.product-card:hover {
    transform: scale(1.03);
}
.product-card .image-container {
    position: relative;
    width: 100%;
    height: 250px;
    overflow: hidden;
    border-radius: 10px;
    margin-bottom: 10px;
}
.product-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.5s;
}
.product-card:hover img {
    transform: scale(1.1);
}
.product-card h3 {
    margin-top: 10px;
    font-size: 18px;
    height: 25px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.product-card .price {
    font-size: 24px;
    font-weight: bold;
    margin: 10px 0;
    color: #000;
}

/* Modal Styles */
.modal {
    display: none;
    justify-content: center;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 2000;
}
.modal.show {
    display: flex;
}
.modal-content {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    background: white;
    padding: 20px;
    border-radius: 10px;
    width: 60%;
    max-width: 800px;
    position: relative;
}
.modal-left {
    flex: 1;
    text-align: center;
}
.modal-left img {
    width: 200px;
    height: auto;
    border-radius: 10px;
}
.modal-right {
    flex: 2;
    padding-left: 20px;
    text-align: left;
}
.modal-description {
    color: rgb(80, 80, 80);
    font-size: 18px;
    font-weight: lighter;
}
.modal-price {
    color: rgb(3, 3, 3);
    font-size: 20px;
    font-weight: normal;
}

/* Size and Quantity Controls */
.size-quantity-container {
    margin: 20px 0;
    width: 100%;
}
.control-row {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 10px;
    width: 100%;
}
.control-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
}
.control-group label {
    white-space: nowrap;
    font-weight: bold;
}
.control-group select,
.control-group input {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    flex: 1;
}

/* Button Styles */
.button-container {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 15px;
    width: 100%;
}
.buy-now {
    background-color: red;
    color: white;
    padding: 10px 20px;
    border: none;
    font-weight: bold;
    cursor: pointer;
    flex: 1;
    border-radius: 4px;
}
.add-to-cart {
    background-color: green;
    color: white;
    padding: 10px 20px;
    border: none;
    font-weight: bold;
    cursor: pointer;
    flex: 1;
    border-radius: 4px;
}
.buy-now:hover {
    background-color: darkred;
}
.add-to-cart:hover {
    background-color: darkgreen;
}

/* Error Messages */
#stockMessage {
    color: red;
    display: none;
    font-size: 14px;
    font-weight: bold;
    margin-top: 5px;
}
.no-products {
    grid-column: 1 / -1;
    text-align: center;
    font-size: 18px;
    padding: 30px;
    background-color: #f8f9fa;
    border-radius: 10px;
    width: 100%;
}

/* Profile Dropdown Styles */
.profile-container {
    position: relative;
    display: inline-block;
}
.profile-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 200px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 10px 0;
    z-index: 1100;
    margin-top: 8px;
    display: none;
}
.profile-dropdown:before {
    content: '';
    position: absolute;
    top: -8px;
    right: 16px;
    width: 16px;
    height: 16px;
    background-color: white;
    transform: rotate(45deg);
    border-left: 1px solid rgba(0, 0, 0, 0.1);
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}
.dropdown-item {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #333;
    transition: background-color 0.2s;
}
.dropdown-item:hover {
    background-color: #f5f5f5;
}
.dropdown-item i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
    color: #555;
}
.dropdown-item:first-child {
    border-bottom: 1px solid #eee;
    pointer-events: none;
}
.profile-dropdown.show {
    display: block;
}

/* Responsive Design */
@media (max-width: 768px) {
    nav {
        flex-direction: column;
    }
    .nav-links {
        margin-top: 10px;
    }
    .container {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
    .product-card {
        width: 250px;
        height: 400px;
    }
    .modal-content {
        flex-direction: column;
        width: 90%;
    }
    .modal-right {
        padding-left: 0;
        margin-top: 20px;
    }
    .control-row {
        flex-direction: column;
        align-items: flex-start;
    }
    .control-group {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>
<body>
    <header>
        <div class="running-text">
            <span>Welcome to The Accents Clothing! Enjoy our latest collection with free shipping.</span>
        </div>
        <nav>
            <div class="logo">
                <a href="homepage.php"><img src="images/the_accents_logo.png" alt="The Accents Logo"></a>
            </div>
            <div class="nav-links">
                <a href="shop_customer.php">Shop</a>
                <?php
                if ($category_result && $category_result->num_rows > 0) {
                    while ($row = $category_result->fetch_assoc()) {
                        $category = htmlspecialchars($row['category_name']); 
                        echo "<a href='shop_customer.php?category=$category'>$category</a>";
                    }
                }
                ?>
            </div>
            <div class="user-links">
                <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
                <a href="notifications.php"><i class="fas fa-bell"></i></a>
                <div class="profile-container">
                    <div class="profile-circle" id="profileToggle">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>
                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($username); ?></span>
                        </div>
                        <a href="<?php echo $username !== 'Guest' ? 'order_users.php' : 'login.php'; ?>" class="dropdown-item">
                            <i class="fas fa-box"></i>
                            <span>My Orders</span>
                        </a>
                        <?php if ($username !== 'Guest') : ?>
                            <a href="logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">
        <?php if (mysqli_num_rows($result) > 0) : ?>
            <?php while ($product = mysqli_fetch_assoc($result)) : ?>
                <div class='product-card' onclick="openModal(
                    '<?= (int)$product['ProductID'] ?>',
                    '<?= htmlspecialchars($product['ProductName']) ?>', 
                    '<?= htmlspecialchars($product['Description']) ?>', 
                    '<?= number_format($product['Price'], 2) ?>', 
                    '<?= htmlspecialchars($product['images']) ?>', 
                    <?= (int)$product['StockQuantity'] ?>)">
                    
                    <div class='image-container'>
                        <?php 
                        $images = json_decode($product['images'], true); 
                        $first_image = isset($images[0]) ? $images[0] : 'images/default-product.jpg'; 
                        ?>
                        <img src='<?= htmlspecialchars($first_image) ?>' alt='<?= htmlspecialchars($product['ProductName']) ?>'>
                    </div>
                    
                    <h3><?= htmlspecialchars($product['ProductName']) ?></h3>
                    <p class='price'>$<?= number_format($product['Price'], 2) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-products">No products found in this category.</div>
        <?php endif; ?>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-left">
                <img id="modalImage" src="" alt="Product Image">
            </div>
            
            <div class="modal-right">
                <h2 id="modalTitle"></h2>
                <p id="modalDescription" class="modal-description"></p>
                <p id="modalPrice" class="modal-price"></p>

                <div class="size-quantity-container">
                    <div class="control-row">
                        <div class="control-group">
                            <label for="sizeSelect">Size:</label>
                            <select id="sizeSelect">
                                <option value="S">Small (S)</option>
                                <option value="M">Medium (M)</option>
                                <option value="L">Large (L)</option>
                                <option value="XL">Extra Large (XL)</option>
                            </select>
                        </div>
                        
                        <div class="control-group">
                            <label for="quantityInput">Quantity:</label>
                            <input type="number" id="quantityInput" min="1" value="1" oninput="validateQuantity()" />
                        </div>
                    </div>
                    <span id="stockMessage"></span>
                </div>

                <!-- Buttons -->
                <div class="button-container">
                    <button class="buy-now">Buy Now</button>
                    <button class="add-to-cart">Add to Cart</button>
                </div>
            </div>
        </div>
    </div>
    <script>// Global variables for product information
let currentProductId;
let currentProductName;
let currentProductPrice;
let currentProductImage;

// Open product modal with details
function openModal(productId, name, description, price, imagesJson, stock) {
    // Store current product information
    currentProductId = productId;
    currentProductName = name;
    currentProductPrice = parseFloat(price);
    
    // Set modal content
    document.getElementById('modalTitle').innerText = name;
    document.getElementById('modalDescription').innerText = description;
    document.getElementById('modalPrice').innerText = "$" + price;

    // Parse and set product image
    let images;
    try {
        images = JSON.parse(imagesJson);
    } catch (error) {
        console.error("Invalid JSON format for images:", error);
        images = [];
    }

    let imageSrc = (Array.isArray(images) && images.length > 0 && images[0]) 
        ? images[0] 
        : 'images/default-product.jpg';
        
    currentProductImage = imageSrc;
    document.getElementById('modalImage').src = imageSrc;

    // Set stock information
    document.getElementById('quantityInput').setAttribute("max", stock);
    document.getElementById('quantityInput').value = 1;
    document.getElementById('stockMessage').style.display = "none";

    // Show modal
    document.getElementById('productModal').classList.add('show');
}

// Validate quantity against available stock
function validateQuantity() {
    let quantityInput = document.getElementById('quantityInput');
    let maxStock = parseInt(quantityInput.getAttribute("max"));
    let stockMessage = document.getElementById('stockMessage');

    if (quantityInput.value > maxStock) {
        stockMessage.innerText = `Only ${maxStock} items left in stock!`;
        stockMessage.style.display = "inline";
        quantityInput.value = maxStock;
    } else {
        stockMessage.style.display = "none";
    }
}

// Close modal
function closeModal() {
    document.getElementById('productModal').classList.remove('show');
}

// Add to cart functionality
function addToCart() {
    const size = document.getElementById('sizeSelect').value;
    const quantity = parseInt(document.getElementById('quantityInput').value);
    
    // Validate quantity
    if (quantity <= 0) {
        alert("Please select a valid quantity");
        return;
    }
    
    // Check if user is logged in
    const username = document.querySelector('.dropdown-item span').textContent.trim();
    if (username === "Guest") {
        window.location.href = "login.php";
        return;
    }
    
    // Create cart item data
    const cartData = {
        ProductID: currentProductId,
        quantity: quantity,
        size: size
    };
    
    // Send AJAX request
    fetch('cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(cartData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Added ${quantity} ${currentProductName} (${size}) to your cart`);
            closeModal();
            updateCartCount();
        } else {
            alert(data.message || 'Failed to add item to cart');
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        alert('An error occurred. Please try again.');
    });
}

// Buy now functionality
function buyNow() {
    const username = document.querySelector('.dropdown-item span').textContent.trim();
    if (username === "Guest") {
        window.location.href = "login.php";
        return;
    }
    
    // First add to cart
    addToCart();
    
    // Then redirect to checkout
    window.location.href = "checkout.php";
}

// Update cart count in UI
function updateCartCount() {
    fetch('get_cart_count.php')
    .then(response => response.json())
    .then(data => {
        const cartCounter = document.querySelector('.cart-counter');
        if (cartCounter) {
            cartCounter.textContent = data.count;
            cartCounter.style.display = data.count > 0 ? 'block' : 'none';
        }
    })
    .catch(error => {
        console.error('Error updating cart count:', error);
    });
}

// Initialize event listeners
document.addEventListener("DOMContentLoaded", function() {
    // Add to cart button
    const addToCartButton = document.querySelector(".add-to-cart");
    if (addToCartButton) {
        addToCartButton.addEventListener("click", addToCart);
    }
    
    // Buy now button
    const buyNowButton = document.querySelector(".buy-now");
    if (buyNowButton) {
        buyNowButton.addEventListener("click", buyNow);
    }
    
    // Close modal when clicking outside
    document.getElementById('productModal').addEventListener("click", function(event) {
        if (event.target === this) {
            closeModal();
        }
    });
    
    // Initialize cart count
    updateCartCount();
    
    // Profile dropdown functionality
    const profileToggle = document.getElementById('profileToggle');
    const profileDropdown = document.getElementById('profileDropdown');
    
    if (profileToggle && profileDropdown) {
        // Toggle dropdown when profile circle is clicked
        profileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (profileDropdown.classList.contains('show') && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });
    }
});</script>
</body>
</html>