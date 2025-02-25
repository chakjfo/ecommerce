<?php
require 'db_connection.php';

$id = $_POST['id'];
$name = $_POST['name'];
$description = $_POST['description'];
$price = $_POST['price'];
$sizes = $_POST['sizes'];
$stock = $_POST['stock'];
$image = json_encode([$_POST['image']]);

$query = "UPDATE products SET ProductName=?, Description=?, Price=?, sizes=?, StockQuantity=?, images=? WHERE ProductID=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssdssii", $name, $description, $price, $sizes, $stock, $image, $id);
$stmt->execute();
?>
