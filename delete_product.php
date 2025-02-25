<?php
require 'db_connection.php';
$id = $_POST['id'];
$conn->query("DELETE FROM products WHERE ProductID = $id");
?>
