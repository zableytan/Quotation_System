<?php
session_start();

// Check if the user is logged in as an admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Generate and store CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'database/db.php';

// Fetch events from the database
$stmt = $conn->query("SELECT * FROM events ORDER BY event_date ASC LIMIT 3"); // Fetch only the upcoming 3 events
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
     <!-- Font Awesome CSS for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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

         .event-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s ease; /* Add transition for smooth effect */
        }

        .event-card:hover {
            transform: scale(1.05); /* Slightly scale up the card on hover */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for depth */
        }

        .event-image {
            width: 100%;
            height: 200px; /* Fixed height for consistency */
            object-fit: cover; /* Ensure images fill the space without distortion */
        }

        .event-body {
            padding: 15px;
        }

        .event-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .event-date {
            font-style: italic;
            color: #777;
            margin-bottom: 10px;
        }

        .event-description {
            color: #333;
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
                         <li class="nav-item">
                            <a href="admin/list_events.php" class="nav-link">Manage Events</a>
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

          <!-- Event Section -->
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center mb-4"><i class = "fas fa-calendar-alt"></i> Upcoming Events</h2>
            </div>
            <?php if (empty($events)): ?>
                <div class="col-md-12 text-center">
                    <p>No upcoming events.</p>
                </div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="col-md-4">
                        <div class="card event-card">
                            <?php if (!empty($event['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="card-img-top event-image">
                            <?php endif; ?>
                            <div class="card-body event-body">
                                <h5 class="card-title event-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                <p class="card-text event-date"><i class = "far fa-clock"></i> <?php echo htmlspecialchars(date('F j, Y', strtotime($event['event_date']))); ?></p>
                                <p class="card-text event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
