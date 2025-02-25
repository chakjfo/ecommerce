<?php
require 'db_connection.php';
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM products WHERE ProductID = $id");
echo json_encode($result->fetch_assoc());
?>
