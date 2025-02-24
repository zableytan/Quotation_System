<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}
include '../database/db.php';

// --- Date Range Filtering ---
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-1 month')); // Default: Last month
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// --- Sales By Product Report ---
$stmt = $conn->prepare("
    SELECT p.product_name, SUM(poi.quantity) AS total_quantity, SUM(p.price * poi.quantity) AS total_revenue
    FROM pre_order_items poi
    JOIN pre_orders po ON poi.pre_order_id = po.id
    JOIN products p ON poi.product_id = p.id
    WHERE po.order_date BETWEEN :start_date AND :end_date
    GROUP BY p.product_name
    ORDER BY total_revenue DESC
");
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$sales_by_product = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Reports</h1>
        <div class="mb-3">
            <a href="list_products.php" class="btn btn-secondary">Back to Product List</a>
        </div>
        <form method="GET" action="reports.php">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Start Date:</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Date:</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Generate Report</button>
        </form>

        <h2>Sales by Product</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Total Quantity Sold</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales_by_product as $sale): ?>
                <tr>
                    <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($sale['total_quantity']); ?></td>
                    <td>₱<?php echo number_format($sale['total_revenue'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
