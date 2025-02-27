<?php
require_once "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $category_name = $_POST['category_name'];

    $sql = "UPDATE categories SET category_name=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $category_name, $id);

    if ($stmt->execute()) {
        header("Location: categories.php");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
