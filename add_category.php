<?php
include 'db_connection.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST['category_name']);

    if (!empty($category_name)) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Category added successfully!";
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Category name cannot be empty.";
    }
    
    $conn->close();
    
    // Redirect back to categories page
    header("Location: categories.php");
    exit();
}
?>