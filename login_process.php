<?php
session_start();
include 'db_connection.php'; // Ensure this file correctly connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare the statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT UserID, Password FROM users WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userID, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $userID;
            $_SESSION['username'] = $username;
            
            echo "<script>alert('Login successful!'); window.location.href='shop_customer.php';</script>";
            exit();
        } else {
            echo "<script>alert('Invalid password!'); window.location.href='login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('User not found!'); window.location.href='login.php';</script>";
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request.'); window.location.href='login.php';</script>";
    exit();
}
?>
