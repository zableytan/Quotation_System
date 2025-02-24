<?php
session_start();

// Redirect to login page if not logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $conn->beginTransaction();  // Start a transaction

        // 1. Delete related records from pre_order_items
        $stmt = $conn->prepare("DELETE FROM pre_order_items WHERE product_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // 2. Delete the product from the products table
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $conn->commit();  // Commit the transaction

        header('Location: list_products.php');  // Redirect to product list
        exit();

    } catch (PDOException $e) {
        $conn->rollBack();  // Rollback the transaction on error
        echo "Error deleting product: " . $e->getMessage();
    }
} else {
    echo "Product ID not specified.";
}
?>
