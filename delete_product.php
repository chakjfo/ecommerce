<?php
require_once 'config/database.php';

if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location: index.php");
    exit();
}

$id = intval($_GET['id']);

try {
    $img_stmt = $conn->prepare("SELECT image_path FROM products WHERE id = :id");
    $img_stmt->bindParam(":id", $id);
    $img_stmt->execute();
    
    if ($img_stmt->rowCount() > 0) {
        $product = $img_stmt->fetch();
        $image_path = $product['image_path'];
        
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindParam(":id", $id);
        
        if ($stmt->execute()) {
            if (!empty($image_path) && file_exists($image_path)) {
                unlink($image_path);
            }
            
            session_start();
            $_SESSION['success_message'] = "Product deleted successfully.";
        } else {
            $_SESSION['success_message'] = "Error: Unable to delete product.";
        }
    } else {
        $_SESSION['success_message'] = "Error: Product not found.";
    }
} catch(PDOException $e) {
    session_start();
    $_SESSION['success_message'] = "Error: " . $e->getMessage();
}

header("location: index.php");
exit();
?>