<?php
require_once "db_connection.php";

// Check if request is valid
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["userId"])) {
    $userId = intval($_POST["userId"]);
    
    try {
        // Prevent deleting current admin
        if ($userId === $_SESSION['user_id']) {
            throw new Exception("Cannot delete currently logged-in admin");
        }

        $sql = "DELETE FROM users WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
    } finally {
        if (isset($stmt)) $stmt->close();
        $conn->close();
    }
} else {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method or parameters"
    ]);
}
?>