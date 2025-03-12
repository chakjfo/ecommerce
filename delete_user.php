<?php
// delete_user.php

session_start();
if (!isset($_SESSION['username']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once "db_connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userId'] ?? null;

    if ($userId) {
        // Prepare the SQL statement to delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE UserID = ?");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>