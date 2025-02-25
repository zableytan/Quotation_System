<?php
session_start();
require_once '../includes/session_helper.php';
require_login();
include '../database/db.php';

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $stmt = $conn->prepare("DELETE FROM events WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        header('Location: list_events.php');
        exit();
    } else {
        echo "Error deleting event.";
    }
} else {
    echo "Event ID not specified.";
}
?>
