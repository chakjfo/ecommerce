<?php
// Ensure this is an admin-only endpoint
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = intval($_POST['userId']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $role = $_POST['role'];
    $newPassword = trim($_POST['newPassword']);

    // Validate input
    if (empty($username) || empty($email)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Username and email are required']);
        exit();
    }

    // Check if username exists for another user
    $stmt = $conn->prepare("SELECT UserID FROM users WHERE Username = ? AND UserID != ?");
    $stmt->bind_param("si", $username, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit();
    }

    // Check if email exists for another user
    $stmt = $conn->prepare("SELECT UserID FROM users WHERE Email = ? AND UserID != ?");
    $stmt->bind_param("si", $email, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit();
    }

    // Update user information
    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET Username = ?, Password = ?, Email = ?, PhoneNumber = ?, Role = ? WHERE UserID = ?");
        $stmt->bind_param("sssssi", $username, $hashedPassword, $email, $phoneNumber, $role, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET Username = ?, Email = ?, PhoneNumber = ?, Role = ? WHERE UserID = ?");
        $stmt->bind_param("ssssi", $username, $email, $phoneNumber, $role, $userId);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating user: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
