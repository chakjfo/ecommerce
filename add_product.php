<?php
session_start();
require 'db_connection.php'; // Ensure this connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate POST data to prevent undefined array keys
    $productName = isset($_POST['ProductName']) ? $_POST['ProductName'] : null;
    $description = isset($_POST['Description']) ? $_POST['Description'] : null;
    $price = isset($_POST['Price']) ? $_POST['Price'] : null;
    $sizes = isset($_POST['sizes']) ? $_POST['sizes'] : null;
    $stock = isset($_POST['StockQuantity']) ? $_POST['StockQuantity'] : null;

    // Validate required fields
    if (!$productName || !$price || !$sizes || !$stock) {
        die("<script>alert('Missing required fields!'); window.history.back();</script>");
    }

    // Image Upload Handling
    $targetDir = "uploads/"; // Folder where images will be stored
    $imageNames = [];

    for ($i = 1; $i <= 4; $i++) {
        if (isset($_FILES["image$i"]) && $_FILES["image$i"]["error"] == 0) {
            $fileName = time() . "_" . basename($_FILES["image$i"]["name"]);
            $targetFilePath = $targetDir . $fileName;

            // Move uploaded file
            if (move_uploaded_file($_FILES["image$i"]["tmp_name"], $targetFilePath)) {
                $imageNames[] = $fileName;
            }
        }
    }

    // Convert image names array to JSON for database storage
    $imageJson = json_encode($imageNames);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO products (ProductName, Description, Price, sizes, StockQuantity, images) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssi", $productName, $description, $price, $sizes, $stock, $imageJson);

    if ($stmt->execute()) {
        echo "<script>alert('Product added successfully!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Error adding product'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
