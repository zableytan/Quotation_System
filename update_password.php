<?php

include '../database/db.php';

$username = 'admin';
$password = 'password123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = :password WHERE username = :username");
$stmt->bindParam(':password', $hashed_password);
$stmt->bindParam(':username', $username);
$stmt->execute();

echo "Password updated successfully!";

