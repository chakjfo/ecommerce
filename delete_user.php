<?php
require_once "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["userId"])) {
    $userId = intval($_POST["userId"]);
    
    // Delete user query
    $sql = "DELETE FROM users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error deleting user"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
