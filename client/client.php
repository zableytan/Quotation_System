<?php include '../database/db.php'; ?>

<?php
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get a Quotation</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .quantity-input {
            width: 60px;
        }
        .stock-level {
            font-size: 0.8em;
            color: green;
            margin-left: 5px;
        }
        .stock-level.low {
            color: red;
        }
        .message {
            display: none;
            margin-top: 10px;
        }
        .message.success {
            color: green;
        }
        .message.error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Get a Quotation</h1>
        <form id="quotationForm">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Product Name</th>
                        <th>Description</th>
                        <th>Price (₱)</th>
                        <th>Available Stock</th>
                        <th>Desired Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="products[<?php echo $product['id']; ?>][selected]" value="<?php echo $product['id']; ?>" data-product-id="<?php echo $product['id']; ?>" id="product-<?php echo $product['id']; ?>">
                                <label class="form-check-label" for="product-<?php echo $product['id']; ?>">
                                    Select
                                </label>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td>&#8369; <?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <span id="stock-<?php echo $product['id']; ?>" class="stock-level <?php echo ($product['quantity'] <= 10) ? 'low' : ''; ?>">
                                <?php echo htmlspecialchars($product['quantity']); ?>
                            </span>
                        </td>
                        <td>
                            <input type="number" class="quantity-input" name="products[<?php echo $product['id']; ?>][quantity]" value="1" min="1" max="<?php echo $product['quantity']; ?>" data-product-id="<?php echo $product['id']; ?>" aria-labelledby="quantity-<?php echo $product['id']; ?>">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" class="btn btn-primary" onclick="calculateTotal()">Calculate Total</button>
        </form>
        <h3 class="mt-4">Total: &#8369; <span id="total">0.00</span></h3>

        <!-- Pre-Order Button -->
        <button type="button" class="btn btn-warning mt-3" data-bs-toggle="modal" data-bs-target="#preOrderModal" id="preOrderButton" disabled>
            Pre-Order Selected Products
        </button>

        <!-- Message Display Area -->
        <div id="preOrderMessage" class="message" role="alert"></div>
    </div>
<!-- Pre-Order Modal -->
    <div class="modal fade" id="preOrderModal" tabindex="-1" aria-labelledby="preOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="preOrderModalLabel">Submit Pre-Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="preOrderForm">
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Your Name:</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_email" class="form-label">Your Email:</label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Your Phone:</label>
                            <input type="tel" class="form-control" id="customer_phone" name="customer_phone" required>
                        </div>
                        <!-- Hidden inputs for selected product IDs and quantities -->
                        <input type="hidden" name="selected_products" id="selectedProducts">
                        <input type="hidden" name="quantities" id="quantities">
                        <input type="hidden" name="csrf_token" id="csrfToken" value="<?php echo bin2hex(random_bytes(32)); ?>">
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary" onclick="submitPreOrder()">Submit Pre-Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function calculateTotal() {
            const form = document.getElementById('quotationForm');
            const products = form.querySelectorAll('tbody tr'); // Select table rows

            let total = 0;
            let selectedProductIds = [];
            let quantities = [];
            let allStocksAvailable = true;

            products.forEach(row => {
                const selectCheckbox = row.querySelector('input[name^="products"][name$="[selected]"]');
                const productId = selectCheckbox?.dataset.productId; // Get product ID from data attribute
                if (!productId) return; // Skip if no product ID found in this row

                const isSelected = selectCheckbox.checked;
                const quantityInput = row.querySelector(`input[name="products[${productId}][quantity]"]`);
                if (!quantityInput) return;

                const quantity = parseInt(quantityInput.value);
                const availableStock = parseInt(row.querySelector(`.stock-level`).textContent); // Select stock-level inside the row
                const price = parseFloat(row.querySelector('td:nth-child(4)').textContent.replace(/[^\d\.]/g, ''));

                if (isSelected) {
                    if (quantity > availableStock) {
                        alert(`The quantity you selected for Product ID ${productId} exceeds available stock. Please adjust the quantity.`);
                        allStocksAvailable = false;
                        return;
                    }
                    total += price * quantity;
                    selectedProductIds.push(productId);
                    quantities.push(quantity);
                }
            });

            document.getElementById('total').textContent = total.toFixed(2);
            const preOrderButton = document.getElementById('preOrderButton');
            preOrderButton.disabled = selectedProductIds.length === 0 || !allStocksAvailable;
            document.getElementById('selectedProducts').value = selectedProductIds.join(',');
            document.getElementById('quantities').value = quantities.join(',');
        }

        function submitPreOrder() {
            const form = document.getElementById('preOrderForm');
            const formData = new FormData(form);

            formData.append('selected_products', document.getElementById('selectedProducts').value);
            formData.append('quantities', document.getElementById('quantities').value);

            fetch('submit_pre_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('preOrderMessage');
                messageDiv.classList.remove('success', 'error');
                messageDiv.style.display = 'block';

                if (data.success) {
                    messageDiv.textContent = data.message;
                    messageDiv.classList.add('success');
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('preOrderModal')).hide();
                } else {
                    messageDiv.textContent = data.message;
                    messageDiv.classList.add('error');
                }
            })
            .catch(error => {
                const messageDiv = document.getElementById('preOrderMessage');
                messageDiv.classList.remove('success', 'error');
                messageDiv.style.display = 'block';
                messageDiv.textContent = 'An error occurred while submitting the pre-order.';
                messageDiv.classList.add('error');
            });
        }
    </script>
</body>
</html>
