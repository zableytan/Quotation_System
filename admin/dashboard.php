<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}
include '../database/db.php';

// --- Fetch Summary Data ---
$stmt = $conn->query("SELECT COUNT(*) FROM products");
$total_products = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM pre_orders");
$total_preorders = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM pre_orders WHERE status = 'pending'");
$pending_preorders = $stmt->fetchColumn();

$stmt = $conn->query("
    SELECT SUM(p.price * poi.quantity)
    FROM pre_orders po
    JOIN pre_order_items poi ON po.id = poi.pre_order_id
    JOIN products p ON poi.product_id = p.id
    WHERE po.status = 'sold' -- Consider only 'sold' orders for revenue
");
$total_revenue = $stmt->fetchColumn();

$stmt = $conn->query("SELECT id, customer_name, order_date, status FROM pre_orders ORDER BY order_date DESC LIMIT 5");
$recent_preorders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Admin Dashboard</h1>
        <div class="mb-3">
            <a href="list_products.php" class="btn btn-secondary">Back to Product List</a>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-primary text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <p class="card-text"><?php echo htmlspecialchars($total_products); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Pre-Orders</h5>
                        <p class="card-text"><?php echo htmlspecialchars($total_preorders); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-dark mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Pending Pre-Orders</h5>
                        <p class="card-text"><?php echo htmlspecialchars($pending_preorders); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Revenue (Sold Orders)</h5>
                        <p class="card-text">₱<?php echo number_format($total_revenue, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h2>Recent Pre-Orders</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Order Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_preorders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
