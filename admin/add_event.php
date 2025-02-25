<?php
session_start();
require_once '../includes/session_helper.php';
require_login();

include '../database/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = $_POST['description']; // Allow HTML, sanitize carefully if needed
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);

    // File Upload Handling
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['image']['name']);
        $target_dir = "../uploads/"; // Create an 'uploads' directory in the main project folder
        $target_file = $target_dir . $file_name;

        // Basic security checks (expand as needed)
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");
        if (!in_array($imageFileType, $allowed_extensions)) {
            $message = "Error: Only JPG, JPEG, PNG & GIF files are allowed.";
        } else if ($_FILES["image"]["size"] > 5000000) {
             $message = "Sorry, your file is too large.";
        }
        else {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = "uploads/" . $file_name;
            } else {
                $message = "Error uploading file.";
            }
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO events (title, description, image_path, event_date) VALUES (:title, :description, :image_path, :event_date)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':event_date', $event_date);

        if ($stmt->execute()) {
            $message = "Event added successfully!";
        } else {
            $message = "Error adding event.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container mt-5">
        <h1>Add Event</h1>
        <div class="mb-3">
            <a href="list_events.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Event List</a>
        </div>
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger'; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="add_event.php" enctype="multipart/form-data">
          <input type="hidden" name="MAX_FILE_SIZE" value="5000000"> <!-- 5MB -->
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" class="form-control" id="image" name="image">
            </div>
            <div class="mb-3">
                <label for="event_date" class="form-label">Event Date</label>
                <input type="date" class="form-control" id="event_date" name="event_date" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Event</button>
        </form>
    </div>
       <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
