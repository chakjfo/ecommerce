<?php
// Ensure this is an admin-only endpoint
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once "db_connection.php"; // Updated to match your database structure

if (isset($_GET['categoryId'])) {
    $categoryId = intval($_GET['categoryId']);
    
    // Prepare and execute SQL query
    $stmt = $conn->prepare("SELECT id, category_name, date_added, date_edited FROM category WHERE id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $categoryData = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($categoryData);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Category not found']);
    }
    
    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing category ID parameter']);
}
$conn->close();
?>
