<?php
include 'db_connection.php'; // Ensure this file correctly connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location.href='sighomepage.php';</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (Username, Password, PhoneNumber, Email, Role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $hashed_password, $phone, $email, $role);

    if ($stmt->execute()) {
        echo "<script>alert('Signup successful! You can now log in.'); window.location.href='homepage.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to register.'); window.location.href='homepage.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request.'); window.location.href='homepage.php';</script>";
}
?>
