<?php
session_start();
include 'db_connection.php'; // Ensure this file contains your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $stmt = $conn->prepare("SELECT UserID, Username, Password, Role FROM users WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userID, $username_db, $hashed_password, $role);
        $stmt->fetch();
    
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $userID;
            $_SESSION['username'] = $username_db;
            $_SESSION['role'] = $role;
    
            header("Location: " . ($role === 'admin' ? 'admin.php' : 'shop_customer.php'));
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid username or password.";
            header("Location: homepage.php?action=login");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "Invalid username or password.";
        header("Location: homepage.php?action=login");
        exit();
    }
} else {
    $_SESSION['login_error'] = "Invalid request.";
    header("Location: homepage.php?action=login");
    exit();
}
?>