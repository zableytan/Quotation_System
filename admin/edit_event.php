<?php
session_start();
require_once '../includes/session_helper.php';
require_login();
include '../database/db.php';

$message = '';

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $message = "Event not found.";
    }
} else {
    $message = "Event ID not specified.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = $_POST['description'];
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);

    // Image Upload Handling
    $image_path = $event['image_path']; // Keep existing path if no new image is uploaded
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
        $stmt = $conn->prepare("UPDATE events SET title = :title, description = :description, image_path = :image_path, event_date = :event_date WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':event_date', $event_date);

        if ($stmt->execute()) {
            $message = "Event updated successfully!";
        } else {
            $message = "Error updating event.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container mt-5">
        <h1>Edit Event</h1>
        <div class="mb-3">
            <a href="list_events.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Event List</a>
        </div>
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger'; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if (isset($event)): ?>
        <form method="POST" action="edit_event.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
             <input type="hidden" name="MAX_FILE_SIZE" value="5000000"> <!-- 5MB -->
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>
             <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" class="form-control" id="image" name="image">
                <?php if (!empty($event['image_path'])): ?>
                    <img src="../<?php echo htmlspecialchars($event['image_path']); ?>" alt="Current Image" style="max-width: 200px; margin-top: 10px;">
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="event_date" class="form-label">Event Date</label>
                <input type="date" class="form-control" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Event</button>
        </form>
        <?php endif; ?>
    </div>
       <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
