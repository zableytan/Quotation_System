<?php
session_start();

// Redirect to login page if not logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch the product from the database
    $stmt = $conn->prepare("SELECT product_name FROM products WHERE id = :product_id");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Product not found.";
        exit();
    }

    // Fetch the product history from the database
    $stmt = $conn->prepare("SELECT * FROM product_history WHERE product_id = :product_id ORDER BY date DESC");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "Product ID not specified.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product History - <?php echo htmlspecialchars($product['product_name']); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Product History - <?php echo htmlspecialchars($product['product_name']); ?></h1>
        <a href="list_products.php" class="btn btn-secondary mb-3">Back to Product List</a>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Quantity Change</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $entry): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($entry['date']); ?></td>
                            <td><?php echo htmlspecialchars($entry['quantity_change']); ?></td>
                            <td><?php echo htmlspecialchars($entry['description']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
