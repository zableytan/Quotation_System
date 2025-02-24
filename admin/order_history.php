<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}
include '../database/db.php';

// --- Pagination ---
$results_per_page = 10; // Reduced results per page for better readability
$stmt = $conn->query("SELECT COUNT(*) FROM pre_orders");
$number_of_results = $stmt->fetchColumn();
$number_of_pages = ceil($number_of_results / $results_per_page);

if (!isset($_GET['page'])) {
    $page = 1;
} else {
    $page = $_GET['page'];
}

$start_limit = ($page - 1) * $results_per_page;

// --- Sorting ---
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'id'; // Default sort column
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC'; // Default sort order

$allowed_columns = ['id', 'customer_name', 'customer_email', 'order_date', 'status']; // Whitelist allowed columns
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'id'; // Default to 'id' if invalid
}

$allowed_orders = ['ASC', 'DESC']; // Whitelist allowed orders
if (!in_array($sort_order, $allowed_orders)) {
    $sort_order = 'DESC'; // Default to 'DESC' if invalid
}

// --- Fetch Orders with Pagination and Sorting ---
$sql = "SELECT * FROM pre_orders ORDER BY $sort_column $sort_order LIMIT $start_limit, $results_per_page";
$stmt = $conn->query($sql);
$pre_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .pagination a {
            margin: 0 5px;
            padding: 5px 10px;
            border: 1px solid #ccc;
            text-decoration: none;
            color: #333;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Pre-Order History</h1>
        <div class="mb-3">
            <a href="list_products.php" class="btn btn-secondary">Back to Product List</a>
        </div>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th><a href="?sort=id&order=<?php echo ($sort_column == 'id' && $sort_order == 'DESC') ? 'ASC' : 'DESC'; ?>">ID</a></th>
                    <th><a href="?sort=customer_name&order=<?php echo ($sort_column == 'customer_name' && $sort_order == 'DESC') ? 'ASC' : 'DESC'; ?>">Customer Name</a></th>
                    <th><a href="?sort=customer_email&order=<?php echo ($sort_column == 'customer_email' && $sort_order == 'DESC') ? 'ASC' : 'DESC'; ?>">Customer Email</a></th>
                    <th>Customer Phone</th>
                    <th>Products Ordered</th>
                    <th><a href="?sort=order_date&order=<?php echo ($sort_column == 'order_date' && $sort_order == 'DESC') ? 'ASC' : 'DESC'; ?>">Order Date</a></th>
                    <th>Total Amount</th>
                    <th><a href="?sort=status&order=<?php echo ($sort_column == 'status' && $sort_order == 'DESC') ? 'ASC' : 'DESC'; ?>">Status</a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pre_orders as $order): ?>
                <?php
                // Fetch the products ordered for each pre-order
                $stmt = $conn->prepare("
                    SELECT p.product_name, poi.quantity, p.price
                    FROM pre_order_items poi
                    JOIN products p ON poi.product_id = p.id
                    WHERE poi.pre_order_id = :pre_order_id
                ");
                $stmt->bindParam(':pre_order_id', $order['id']);
                $stmt->execute();
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $total_amount = 0;
                foreach ($products as $product) {
                    $total_amount += $product['price'] * $product['quantity'];
                }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                        <td>
                            <ul>
                                <?php
                                $uniqueProducts = [];  //Use array for each pre-order so the list is generated correctly
                                foreach ($products as $product):
                                    $productKey = $product['product_name'] . '-' . $product['quantity']; //Creating a unique key
                                    if(!in_array($productKey, $uniqueProducts)){ //Check if this product combination has already been displayed
                                        echo '<li>' . htmlspecialchars($product['product_name']) . ' (Qty: ' . htmlspecialchars($product['quantity']) . ')</li>';
                                        $uniqueProducts[] = $productKey; //If not, display it and add it to the array
                                    }
                                endforeach;
                                ?>
                            </ul>
                        </td>
                        <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        <td><?php echo '₱' . number_format($total_amount, 2); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php
                $range = 2; // Number of pages to display before and after the current page
                $initial_page = max(1, $page - $range);
                $last_page = min($number_of_pages, $page + $range);

                // Show "First" button if not on the first page
                if ($page > 1) {
                    echo "<li class='page-item'><a class='page-link' href='order_history.php?page=1&sort=$sort_column&order=$sort_order'>First</a></li>";
                }

                // Show "Previous" button if not on the first page
                if ($page > 1) {
                    $prev_page = $page - 1;
                    echo "<li class='page-item'><a class='page-link' href='order_history.php?page=$prev_page&sort=$sort_column&order=$sort_order'>Previous</a></li>";
                }

                // Display page numbers
                for ($i = $initial_page; $i <= $last_page; $i++) {
                    $active_class = ($i == $page) ? 'active' : '';
                    echo "<li class='page-item $active_class'><a class='page-link' href='order_history.php?page=$i&sort=$sort_column&order=$sort_order'>$i</a></li>";
                }

                // Show "Next" button if not on the last page
                if ($page < $number_of_pages) {
                    $next_page = $page + 1;
                    echo "<li class='page-item'><a class='page-link' href='order_history.php?page=$next_page&sort=$sort_column&order=$sort_order'>Next</a></li>";
                }

                // Show "Last" button if not on the last page
                if ($page < $number_of_pages) {
                    echo "<li class='page-item'><a class='page-link' href='order_history.php?page=$number_of_pages&sort=$sort_column&order=$sort_order'>Last</a></li>";
                }
                ?>
            </ul>
        </nav>
    </div>
</body>
</html>
