<?php
// get_cart_count.php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

require_once 'db_connection.php'; // Your database connection file

$user_id = $_SESSION['user_id'];

$sql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['count' => $row['count'] ? intval($row['count']) : 0]);

$stmt->close();
$conn->close();
?>