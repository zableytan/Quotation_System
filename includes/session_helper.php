<?php
require_once __DIR__ . '/../includes/session_helper.php';
init_session();

function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in() {
    init_session();
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ../admin/login.php');
        exit();
    }
}
?>