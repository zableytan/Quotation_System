<?php
session_start();

// Redirect to login page if not logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

include '../database/db.php';

// Fetch pre-orders from the database
$stmt = $conn->query("SELECT * FROM pre_orders");
$pre_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pre_order_id = $_POST['pre_order_id'];
    $status = $_POST['status'];

    // Update pre-order status
    $stmt = $conn->prepare("UPDATE pre_orders SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $pre_order_id);

    try {
        $conn->beginTransaction(); // Start a transaction

        if ($stmt->execute()) {
            // If status is 'sold', deduct quantity from the products
            if ($status === 'sold') {
                // Get pre-order items
                $stmt = $conn->prepare("SELECT product_id, quantity FROM pre_order_items WHERE pre_order_id = :pre_order_id");
                $stmt->bindParam(':pre_order_id', $pre_order_id);
                $stmt->execute();
                $pre_order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Update product quantities and log history
                foreach ($pre_order_items as $item) {
                    $product_id = $item['product_id'];
                    $quantity_change = -$item['quantity']; // Negative because stock is decreasing

                    // Update product quantity
                    $stmt = $conn->prepare("UPDATE products SET quantity = quantity + :quantity_change WHERE id = :product_id");
                    $stmt->bindParam(':quantity_change', $quantity_change);
                    $stmt->bindParam(':product_id', $product_id);
                    $stmt->execute();

                    // Log the quantity change
                    $stmt = $conn->prepare("INSERT INTO product_history (product_id, quantity_change, description) VALUES (:product_id, :quantity_change, :description)");
                    $stmt->bindParam(':product_id', $product_id);
                    $stmt->bindParam(':quantity_change', $quantity_change);
                    $stmt->bindValue(':description', "Pre-order sold (Order ID: " . $pre_order_id . ")"); // Changed to bindValue()
                    $stmt->execute();
                }

                echo "Pre-order status updated successfully!";
            } else {
                throw new Exception("Error updating pre-order status.");
            }

            $conn->commit();  // Commit the transaction
        } else {
            throw new Exception("Error updating pre-order status.");
        }

    } catch (Exception $e) {
        $conn->rollBack(); // Rollback the transaction on error
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pre-Orders</title>
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="text-center mb-4">Manage Pre-Orders</h1>
            <div>
                <a href="list_products.php" class="btn btn-secondary">Back to Product List</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Customer Email</th>
                        <th>Customer Phone</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pre_orders as $pre_order): ?>
                        <?php
                        // Fetch products and quantities for this pre-order
                        $stmt = $conn->prepare("
                            SELECT products.product_name, pre_order_items.quantity
                            FROM pre_order_items
                            JOIN products ON pre_order_items.product_id = products.id
                            WHERE pre_order_items.pre_order_id = :pre_order_id
                        ");
                        $stmt->bindParam(':pre_order_id', $pre_order['id']);
                        $stmt->execute();
                        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pre_order['id']); ?></td>
                            <td><?php echo htmlspecialchars($pre_order['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($pre_order['customer_email']); ?></td>
                            <td><?php echo htmlspecialchars($pre_order['customer_phone']); ?></td>
                            <td>
                                <ul>
                                    <?php foreach ($products as $product): ?>
                                        <li><?php echo htmlspecialchars($product['product_name']) . ' (Quantity: ' . htmlspecialchars($product['quantity']) . ')'; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td><?php echo htmlspecialchars(ucfirst($pre_order['status'])); ?></td>
                            <td>
                                <form action="pre_orders.php" method="POST" class="d-inline">
                                    <input type="hidden" name="pre_order_id" value="<?php echo htmlspecialchars($pre_order['id']); ?>">
                                    <select name="status" class="form-select" onchange="confirmSold(this)">
                                        <option value="pending" <?php echo ($pre_order['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="sold" <?php echo ($pre_order['status'] === 'sold') ? 'selected' : ''; ?>>Sold</option>
                                        <option value="cancelled" <?php echo ($pre_order['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 JS (optional, for interactive components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function confirmSold(selectElement) {
            if (selectElement.value === 'sold') {
                if (confirm('Are you sure you want to mark this pre-order as sold? This will deduct the quantity from the inventory.')) {
                    selectElement.form.submit();
                } else {
                    // Reset the select element to the previous value if the user cancels
                    selectElement.value = '<?php echo htmlspecialchars($pre_order['status']); ?>';
                }
            } else {
                selectElement.form.submit();
            }
        }
    </script>
</body>
</html>
