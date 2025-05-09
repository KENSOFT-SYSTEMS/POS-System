<?php
session_start();
// Assuming this is in your login script
if ($userIsAdmin) {
    $_SESSION['role'] = 'admin'; // Set role to admin for admin users
} else {
    $_SESSION['role'] = 'user'; // For regular users
}


require 'database.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        header("Location: dashboard.php");
        exit();
    }
}

$_SESSION['error'] = "Invalid username or password";
header("Location: index.php");
