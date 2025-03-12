<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);

    $errors = [];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match!";
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT Username FROM users WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Username already exists!";
    }
    $stmt->close();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT Email FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Email already in use!";
    }
    $stmt->close();

    // Check if phone number already exists
    $stmt = $conn->prepare("SELECT PhoneNumber FROM users WHERE PhoneNumber = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Phone number already in use!";
    }
    $stmt->close();

    // Validate phone number (must contain only digits)
    if (!ctype_digit($phone)) {
        $errors[] = "Phone number must contain only numbers!";
    }

    // Validate email (must contain @gmail.com)
    if (strpos($email, '@gmail.com') === false) {
        $errors[] = "Email must be a valid Gmail address (@gmail.com)!";
    }

    // If there are any errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['signup_errors'] = $errors;
        $_SESSION['signup_data'] = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'role' => $role
        ];
        header("Location: homepage.php?action=signup");
        exit();
    }

    // If no errors, proceed with registration
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (Username, Password, PhoneNumber, Email, Role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $hashed_password, $phone, $email, $role);

    if ($stmt->execute()) {
        $_SESSION['signup_success'] = "Signup successful! You can now log in.";
        header("Location: homepage.php?action=login");
    } else {
        $_SESSION['signup_errors'] = ["Registration failed: " . $stmt->error];
        $_SESSION['signup_data'] = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'role' => $role
        ];
        header("Location: homepage.php?action=signup");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: homepage.php");
    exit();
}
?>