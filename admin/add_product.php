<?php include '../database/db.php'; ?>

<?php
$success_message = ''; // Initialize success message variable

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Insert product into the database
    $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, quantity) VALUES (:product_name, :description, :price, :quantity)");
    $stmt->bindParam(':product_name', $product_name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':quantity', $quantity);

    if ($stmt->execute()) {
        $success_message = "Product added successfully!";
    } else {
        $success_message = "Error adding product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            border-radius: 10px 10px 0 0;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Add Product</h4>
                    </div>
                    <div class="card-body">
                        <!-- Success Message -->
                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                                <?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Add Product Form -->
                        <form action="add_product.php" method="POST">
                            <!-- Product Name -->
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name:</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" required>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description:</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>

                            <!-- Price -->
                            <div class="mb-3">
                                <label for="price" class="form-label">Price (₱):</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                            </div>

                            <!-- Quantity -->
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity:</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Add Product</button>
                                <a href="list_products.php" class="btn btn-secondary btn-lg">Back to Product List</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS (optional, for interactive components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Auto-dismiss success message after 5 seconds -->
    <script>
        setTimeout(function() {
            var alert = document.getElementById('successAlert');
            if (alert) {
                alert.classList.remove('show');
                alert.classList.add('fade');
            }
        }, 5000); // 5000 milliseconds = 5 seconds
    </script>
</body>
</html>