<?php
require 'db_connection.php'; // Database connection

// Fetch products from the database
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<div class="p-5">
    <h2 class="text-xl font-bold mb-4">Manage Products</h2>
    
    <button id="add-product-btn" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">Add Product</button>
    
    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2">Image</th>
                <th class="border p-2">Product Name</th>
                <th class="border p-2">Price</th>
                <th class="border p-2">Sizes</th>
                <th class="border p-2">Stock</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $products->fetch_assoc()): ?>
            <tr>
                <td class="border p-2"><img src="uploads/<?= htmlspecialchars($row['images']); ?>" width="50"></td>
                <td class="border p-2"><?= htmlspecialchars($row['ProductName']); ?></td>
                <td class="border p-2">$<?= number_format($row['Price'], 2); ?></td>
                <td class="border p-2"><?= htmlspecialchars($row['sizes']); ?></td>
                <td class="border p-2"><?= htmlspecialchars($row['StockQuantity']); ?></td>
                <td class="border p-2">
                    <button class="bg-green-500 text-white px-2 py-1 rounded">Edit</button>
                    <button class="bg-red-500 text-white px-2 py-1 rounded delete-product" data-id="<?= $row['ProductID']; ?>">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Product Form Modal -->
<div id="product-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-5 rounded shadow-lg">
        <h2 class="text-xl font-bold mb-2">Add Product</h2>
        <form id="product-form" action="add_product.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="ProductName" placeholder="Product Name" class="border p-2 w-full mb-2" required>
            <textarea name="Description" placeholder="Description" class="border p-2 w-full mb-2"></textarea>
            <input type="number" name="Price" placeholder="Price" class="border p-2 w-full mb-2" step="0.01" required>
            <input type="text" name="sizes" placeholder="Sizes (e.g., S,M,L)" class="border p-2 w-full mb-2" required>
            <input type="number" name="StockQuantity" placeholder="Stock Quantity" class="border p-2 w-full mb-2" required>
            <input type="file" name="image" class="border p-2 w-full mb-2" required>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
            <button type="button" id="close-modal" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('add-product-btn').addEventListener('click', function() {
        document.getElementById('product-modal').classList.remove('hidden');
    });

    document.getElementById('close-modal').addEventListener('click', function() {
        document.getElementById('product-modal').classList.add('hidden');
    });

    document.querySelectorAll('.delete-product').forEach(button => {
        button.addEventListener('click', function() {
            let productId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this product?')) {
                fetch('delete_product.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + productId
                })
                .then(response => response.text())
                .then(data => {
                    alert('Product deleted successfully!');
                    location.reload();
                })
                .catch(error => console.error('Error:', error));
            }
        });
    });
</script>
