<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['images']) && isset($_POST['car_id'])) {
    $car_id = (int)$_POST['car_id'];
    $uploaded_files = [];
    $errors = [];
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../assets/uploads/cars/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Process each uploaded file
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['images']['name'][$key];
            $file_size = $_FILES['images']['size'][$key];
            $file_tmp = $_FILES['images']['tmp_name'][$key];
            $file_type = $_FILES['images']['type'][$key];
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "File $file_name is not a valid image type.";
                continue;
            }
            
            // Validate file size (max 5MB)
            if ($file_size > 5 * 1024 * 1024) {
                $errors[] = "File $file_name is too large. Maximum size is 5MB.";
                continue;
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_filename = 'car_' . $car_id . '_' . uniqid() . '.' . $file_extension;
            $destination = $upload_dir . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($file_tmp, $destination)) {
                // Save to database
                $stmt = $pdo->prepare("INSERT INTO car_images (car_id, image_path, upload_order) VALUES (?, ?, ?)");
                $stmt->execute([$car_id, 'assets/uploads/cars/' . $new_filename, $key]);
                
                $uploaded_files[] = [
                    'name' => $file_name,
                    'path' => 'assets/uploads/cars/' . $new_filename,
                    'id' => $pdo->lastInsertId()
                ];
            } else {
                $errors[] = "Failed to upload $file_name.";
            }
        }
    }
    
    echo json_encode([
        'success' => count($uploaded_files) > 0,
        'uploaded_files' => $uploaded_files,
        'errors' => $errors
    ]);
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid request']);
}
?>