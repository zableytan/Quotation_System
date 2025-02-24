<?php
session_start();

// Redirect to login page if not logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Fetch products from the database
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .table-responsive {
            overflow-x: auto; /* Enable horizontal scroll if table is too wide */
        }

        /* Match the color scheme from index.php */
        .navbar {
            background-color: #343a40; /* Dark gray navbar */
        }

        .navbar-brand {
            color: white;
            font-weight: bold;
        }

        .navbar-nav .nav-link {
            color: white;
        }

        .btn-primary, .btn-success, .btn-info, .btn-warning, .btn-danger {
            font-weight: bold;
        }

        /* Style the action buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem; /* Add some space between buttons */
            justify-content: center; /* Center the buttons */
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="order_history.php">Order History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="pre_orders.php">Manage Pre-Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="overall_product_history.php">Overall History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h1 class="text-center mb-4">Product List</h1>
        <div class="d-flex justify-content-end mb-3">
            <a href="add_product.php" class="btn btn-success">Add New Product</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td>&#8369; <?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                        <td class="action-buttons">
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i> Delete</a>
                            <a href="product_history.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info">History</a>
                        </td>
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
