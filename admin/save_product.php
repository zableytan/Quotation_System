<?php
include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO products (product_name, description, price) VALUES (:product_name, :description, :price)");
    $stmt->bindParam(':product_name', $product_name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);

    if ($stmt->execute()) {
        echo "Product added successfully!";
    } else {
        echo "Error adding product.";
    }
}
?>