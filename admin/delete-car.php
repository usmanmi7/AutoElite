<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$car_id = $_GET['id'];

// Delete the car
$stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
$stmt->execute([$car_id]);

header('Location: dashboard.php?message=Car deleted successfully');
exit;
?>