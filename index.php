<?php
session_start();

// Check if the user is logged in as an admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa; /* Light gray background */
        }

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

        .btn-success {
            font-weight: bold;
        }

        footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">DMSF Research Lab</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <?php if ($is_admin): ?>
                        <li class="nav-item">
                            <a href="admin/list_products.php" class="nav-link">Admin Dashboard</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="admin/login.php" class="nav-link">Admin Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg p-3 mb-5 bg-white rounded">
                    <div class="card-body">
                        <h1 class="text-center mb-4">Quotation System</h1>
                        <p class="text-center">Welcome to the DMSF Research Lab Quotation System.</p>
                        <div class="d-grid gap-2">
                            <a href="client/client.php" class="btn btn-success btn-lg">Get a Quotation</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> DMSF Research Lab</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
