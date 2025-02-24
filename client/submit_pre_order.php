<?php
include '../database/db.php';

// Set Content-Type header to return JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $customer_phone = $_POST['customer_phone'];
    $selected_products = explode(',', $_POST['selected_products']);
    $quantities = explode(',', $_POST['quantities']);

    try {
        // Insert pre-order into the database
        $stmt = $conn->prepare("INSERT INTO pre_orders (customer_name, customer_email, customer_phone) VALUES (:customer_name, :customer_email, :customer_phone)");
        $stmt->bindParam(':customer_name', $customer_name);
        $stmt->bindParam(':customer_email', $customer_email);
        $stmt->bindParam(':customer_phone', $customer_phone);

        if ($stmt->execute()) {
            $pre_order_id = $conn->lastInsertId();

            // Insert pre-order items with quantities
            for ($i = 0; $i < count($selected_products); $i++) {
                $product_id = $selected_products[$i];
                $quantity = (int)$quantities[$i]; // Convert to integer for safety

                $stmt = $conn->prepare("INSERT INTO pre_order_items (pre_order_id, product_id, quantity) VALUES (:pre_order_id, :product_id, :quantity)");
                $stmt->bindParam(':pre_order_id', $pre_order_id);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);  //Important:  Tell PDO it's an integer

                $stmt->execute();
            }

            // Return success JSON
            echo json_encode(['success' => true, 'message' => 'Pre-order submitted successfully!']);
        } else {
            // Return error JSON
            echo json_encode(['success' => false, 'message' => 'Error submitting pre-order.']);
        }
    } catch (Exception $e) {
        // Return error JSON with exception message
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    // Return error JSON for invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
