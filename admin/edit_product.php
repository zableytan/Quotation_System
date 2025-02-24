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

    // Fetch the product from the database
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Product not found.";
        exit();
    }
} else {
    echo "Product ID not specified.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $new_quantity = $_POST['quantity']; // New quantity
    $add_quantity = $_POST['add_quantity']; // Quantity to add

    // Get old quantity
    $old_quantity = $product['quantity'];

    // Calculate the final quantity
    $final_quantity = $new_quantity + $add_quantity;

    try {
        $conn->beginTransaction();

        // Update the product in the products table
        $stmt = $conn->prepare("UPDATE products SET product_name = :product_name, description = :description, price = :price, quantity = :quantity WHERE id = :id");
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':quantity', $final_quantity);  // Use the final quantity
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Log the quantity change in the product_history table
        $quantity_change = $final_quantity - $old_quantity;
        $stmt = $conn->prepare("INSERT INTO product_history (product_id, quantity_change, description) VALUES (:product_id, :quantity_change, :description)");
        $stmt->bindParam(':product_id', $id);
        $stmt->bindParam(':quantity_change', $quantity_change);
        $stmt->bindValue(':description', "Stock level manually updated by admin"); // Changed to bindValue()
        $stmt->execute();

        $conn->commit();

        header('Location: list_products.php');
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error updating product: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Edit Product</h1>
        <form method="POST" action="edit_product.php?id=<?php echo htmlspecialchars($product['id']); ?>">
            <div class="mb-3">
                <label for="product_name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="add_quantity" class="form-label">Add Quantity</label>
                <input type="number" class="form-control" id="add_quantity" name="add_quantity" value="0">
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="list_products.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
