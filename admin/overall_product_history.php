<?php
session_start();

// Redirect to login page if not logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Fetch overall product history from the database, including product name
$stmt = $conn->prepare("
    SELECT ph.date, ph.quantity_change, ph.description, p.product_name
    FROM product_history ph
    JOIN products p ON ph.product_id = p.id
    ORDER BY ph.date DESC
");
$stmt->execute();
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overall Product History</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Overall Product History</h1>
            <a href="list_products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Product List</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product Name</th>
                        <th>Quantity Change</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No history available.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['date']); ?></td>
                                <td><?php echo htmlspecialchars($entry['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($entry['quantity_change']); ?></td>
                                <td><?php echo htmlspecialchars($entry['description']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
