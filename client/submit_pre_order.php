<?php
include '../database/db.php';

// Set Content-Type header to return JSON
header('Content-Type: application/json');

// Function to log errors (replace with your logging mechanism)
function logError($message) {
    error_log($message . PHP_EOL, 3, '../error.log'); // Log to file
    // or log to database, etc.
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and Validate Inputs
    $customer_name = filter_input(INPUT_POST, 'customer_name', FILTER_SANITIZE_STRING);
    $customer_email = filter_input(INPUT_POST, 'customer_email', FILTER_VALIDATE_EMAIL);
    $customer_phone = filter_input(INPUT_POST, 'customer_phone', FILTER_SANITIZE_STRING);
    $selected_products_string = filter_input(INPUT_POST, 'selected_products', FILTER_SANITIZE_STRING);
    $quantities_string = filter_input(INPUT_POST, 'quantities', FILTER_SANITIZE_STRING);
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);

     // Validate CSRF token
    session_start();
     if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $csrf_token) {
        logError("CSRF token validation failed.");
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed.']);
        exit;
    }

    // Validate data
    if (empty($customer_name) || strlen($customer_name) > 255) {
        logError("Invalid customer name: " . $customer_name);
        echo json_encode(['success' => false, 'message' => 'Invalid customer name.']);
        exit;
    }

    if (!$customer_email) {
        logError("Invalid customer email: " . $customer_email);
        echo json_encode(['success' => false, 'message' => 'Invalid customer email.']);
        exit;
    }

    if (empty($customer_phone)) {
        logError("Invalid customer phone: " . $customer_phone);
        echo json_encode(['success' => false, 'message' => 'Invalid customer phone.']);
        exit;
    }
    if (empty($selected_products_string) || empty($quantities_string)) {
           logError("No products selected.");
           echo json_encode(['success' => false, 'message' => 'No products selected.']);
           exit;
       }

    $selected_products = explode(',', $selected_products_string);
    $quantities = explode(',', $quantities_string);

    if (count($selected_products) !== count($quantities)) {
        logError("Product and quantity count mismatch.");
        echo json_encode(['success' => false, 'message' => 'Product and quantity count mismatch.']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // Insert pre-order into the database
        $stmt = $conn->prepare("INSERT INTO pre_orders (customer_name, customer_email, customer_phone) VALUES (:customer_name, :customer_email, :customer_phone)");
        $stmt->bindParam(':customer_name', $customer_name);
        $stmt->bindParam(':customer_email', $customer_email);
        $stmt->bindParam(':customer_phone', $customer_phone);

        if ($stmt->execute()) {
            $pre_order_id = $conn->lastInsertId();

            // Insert pre-order items with quantities
            for ($i = 0; $i < count($selected_products); $i++) {
                $product_id = (int)$selected_products[$i]; // Ensure product ID is an integer
                $quantity = (int)$quantities[$i]; // Convert to integer for safety

                // Validate product ID
                $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = :product_id");
                $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                 if (!$product) {
                    logError("Invalid product ID: " . $product_id);
                    echo json_encode(['success' => false, 'message' => 'Invalid product selected.']);
                    $conn->rollBack();
                    exit;
                }
                 if ($quantity <= 0) {
                       logError("Invalid product quantity: " . $quantity);
                       echo json_encode(['success' => false, 'message' => 'Invalid product quantity.']);
                       $conn->rollBack();
                       exit;
                   }

                if ($quantity > $product['quantity']) {
                    logError("Quantity exceeds available stock for product ID: " . $product_id);
                    echo json_encode(['success' => false, 'message' => 'Quantity exceeds available stock.']);
                    $conn->rollBack();
                    exit;
                }

                $stmt = $conn->prepare("INSERT INTO pre_order_items (pre_order_id, product_id, quantity) VALUES (:pre_order_id, :product_id, :quantity)");
                $stmt->bindParam(':pre_order_id', $pre_order_id);
                $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);  //Important:  Tell PDO it's an integer
                $stmt->execute();
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Pre-order submitted successfully!']);
        } else {
            logError("Error inserting pre-order.");
            echo json_encode(['success' => false, 'message' => 'Error submitting pre-order. Please try again.']);
            $conn->rollBack();
        }
    } catch (Exception $e) {
        $conn->rollBack();
        logError("Exception: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please contact support.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
