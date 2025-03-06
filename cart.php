<?php
session_start();
require 'db_connection.php';
include 'add_to_cart.php';

// Ensure user is logged in
if (!isset($_SESSION['UserID'])) {
    $_SESSION['error_message'] = "Please log in to view your cart.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['UserID'];

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $cart_id = intval($_POST['CartID']);
        $new_quantity = max(1, intval($_POST['Quantity'])); // Prevent negative values

        $update_query = "UPDATE cart SET Quantity = '$new_quantity' WHERE CartID = '$cart_id' AND UserID = '$user_id'";
        mysqli_query($conn, $update_query);
    }

    if (isset($_POST['remove_item'])) {
        $cart_id = intval($_POST['CartID']);

        $delete_query = "DELETE FROM cart WHERE CartID = '$cart_id' AND UserID = '$user_id'";
        mysqli_query($conn, $delete_query);
    }
}

// Fetch cart items from the database
$query = "SELECT c.CartID, p.ProductName, p.Price, c.Quantity, c.sizes, p.images 
          FROM cart c 
          JOIN products p ON c.ProductID = p.ProductID 
          WHERE c.UserID = '$user_id'";

$result = mysqli_query($conn, $query);
$cart_items = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['Price'] * $item['Quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - The Accents Clothing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; font-family: Arial, sans-serif; box-sizing: border-box; }
        body { background-color: #f4f4f4; padding: 20px; }
        .cart-container { max-width: 800px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .cart-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .cart-item { display: flex; align-items: center; border-bottom: 1px solid #eee; padding: 15px 0; }
        .cart-item img { width: 100px; height: 100px; object-fit: cover; margin-right: 20px; border-radius: 5px; }
        .item-details { flex-grow: 1; }
        .quantity-control { display: flex; align-items: center; }
        .quantity-control input { width: 50px; text-align: center; margin: 0 10px; }
        .cart-summary { margin-top: 20px; text-align: right; }
        .btn { background: black; color: white; padding: 10px; border-radius: 5px; text-decoration: none; text-align: center; font-weight: bold; transition: background 0.3s; display: inline-block; cursor: pointer; width: 25%; margin-top: auto; margin-left: auto; }
        .btn-remove { background-color: #ff4444; }
        .empty-cart { text-align: center; color: #777; padding: 50px; }
    </style>
</head>
<body>
    <div class="cart-container">
        <div class="cart-header">
            <h1>Your Cart</h1>
            <a href="shop_customer.php" class="btn">Continue Shopping</a>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['images'] ?? 'images/default-product.jpg'); ?>" alt="<?php echo htmlspecialchars($item['ProductName']); ?>">
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['ProductName']); ?></h3>
                        <p>Size: <?php echo htmlspecialchars($item['Size']); ?></p>
                        <p>Price: $<?php echo number_format($item['Price'], 2); ?></p>
                    </div>
                    <form method="POST" class="quantity-control">
                        <input type="hidden" name="cart_id" value="<?php echo $item['CartID']; ?>">
                        <button type="submit" name="update_quantity" value="-1" onclick="this.form.quantity.value = Math.max(1, parseInt(this.form.quantity.value) - 1)">-</button>
                        <input type="number" name="quantity" value="<?php echo $item['Quantity']; ?>" min="1">
                        <button type="submit" name="update_quantity" value="+1" onclick="this.form.quantity.value = parseInt(this.form.quantity.value) + 1">+</button>
                        <button type="submit" name="remove_item" class="btn btn-remove">Remove</button>
                    </form>
                    <div>
                        Subtotal: $<?php echo number_format($item['Price'] * $item['Quantity'], 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="cart-summary">
                <h2>Total: $<?php echo number_format($total, 2); ?></h2>
                <a href="checkout.php" class="btn">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
