<?php include '../database/db.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("UPDATE products SET product_name = :product_name, description = :description, price = :price WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':product_name', $product_name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt = $conn->prepare("UPDATE products SET product_name = :product_name, description = :description, price = :price, quantity = :quantity WHERE id = :id");
    $stmt->bindParam(':quantity', $quantity);

    if ($stmt->execute()) {
        echo "Product updated successfully!";
    } else {
        echo "Error updating product.";
    }
}
?>