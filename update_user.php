<?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $updates = $_POST['updates'];

    $setQuery = [];
    foreach ($updates as $column => $value) {
        $setQuery[] = "$column = ?";
    }

    $query = "UPDATE users SET " . implode(", ", $setQuery) . " WHERE UserID = ?";
    $stmt = $conn->prepare($query);

    $params = array_values($updates);
    $params[] = $id;
    $types = str_repeat("s", count($updates)) . "i"; 
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo "User updated successfully!";
    } else {
        echo "Error updating user.";
    }

    $stmt->close();
}
?>
