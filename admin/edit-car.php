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

// Get current car data
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header('Location: dashboard.php');
    exit;
}

// Get car images
$stmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY image_order ASC");
$stmt->execute([$car_id]);
$car_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $make = $_POST['make'] ?? '';
    $model = $_POST['model'] ?? '';
    $year = $_POST['year'] ?? '';
    $price = $_POST['price'] ?? '';
    $mileage = $_POST['mileage'] ?? 0;
    $fuel_type = $_POST['fuel_type'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $body_type = $_POST['body_type'] ?? '';
    $car_condition = $_POST['car_condition'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    try {
        // Update car data
        $stmt = $pdo->prepare("UPDATE cars SET make = ?, model = ?, year = ?, price = ?, mileage = ?, fuel_type = ?, transmission = ?, body_type = ?, car_condition = ?, description = ?, status = ? WHERE id = ?");
        $stmt->execute([$make, $model, $year, $price, $mileage, $fuel_type, $transmission, $body_type, $car_condition, $description, $status, $car_id]);
        
        // Handle new image uploads
        if (!empty($_FILES['car_images']['name'][0])) {
            $upload_dir = '../assets/uploads/cars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $images = $_FILES['car_images'];
            $cover_set = empty($car_images); // Set cover if no existing images
            
            for ($i = 0; $i < count($images['name']); $i++) {
                if ($images['error'][$i] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($images['name'][$i], PATHINFO_EXTENSION);
                    $filename = 'car_' . $car_id . '_' . time() . '_' . $i . '.' . $file_extension;
                    $filepath = $upload_dir . $filename;
                    
                    if (move_uploaded_file($images['tmp_name'][$i], $filepath)) {
                        $is_cover = $cover_set ? 1 : 0;
                        $image_order = count($car_images) + $i;
                        
                        $stmt = $pdo->prepare("INSERT INTO car_images (car_id, image_path, image_order, is_cover) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$car_id, 'assets/uploads/cars/' . $filename, $image_order, $is_cover]);
                        
                        if ($is_cover) {
                            $cover_set = false;
                        }
                    }
                }
            }
        }
        
        // Handle image deletions
        if (!empty($_POST['delete_images'])) {
            $delete_ids = $_POST['delete_images'];
            foreach ($delete_ids as $image_id) {
                // Get image path before deletion
                $stmt = $pdo->prepare("SELECT image_path FROM car_images WHERE id = ?");
                $stmt->execute([$image_id]);
                $image = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Delete from database
                $stmt = $pdo->prepare("DELETE FROM car_images WHERE id = ?");
                $stmt->execute([$image_id]);
                
                // Delete file from server
                if ($image && file_exists('../' . $image['image_path'])) {
                    unlink('../' . $image['image_path']);
                }
            }
            
            // Update cover image if needed
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM car_images WHERE car_id = ? AND is_cover = 1");
            $stmt->execute([$car_id]);
            $has_cover = $stmt->fetchColumn();
            
            if (!$has_cover) {
                $stmt = $pdo->prepare("SELECT id FROM car_images WHERE car_id = ? ORDER BY image_order ASC LIMIT 1");
                $stmt->execute([$car_id]);
                $first_image = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($first_image) {
                    $stmt = $pdo->prepare("UPDATE car_images SET is_cover = 1 WHERE id = ?");
                    $stmt->execute([$first_image['id']]);
                }
            }
        }
        
        // Update main image in cars table
        $stmt = $pdo->prepare("SELECT image_path FROM car_images WHERE car_id = ? AND is_cover = 1");
        $stmt->execute([$car_id]);
        $cover_image = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cover_image) {
            $stmt = $pdo->prepare("UPDATE cars SET image = ? WHERE id = ?");
            $stmt->execute([$cover_image['image_path'], $car_id]);
        }
        
        $message = 'Car updated successfully!';
        
        // Refresh car data
        $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
        $stmt->execute([$car_id]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Refresh car images
        $stmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY image_order ASC");
        $stmt->execute([$car_id]);
        $car_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $message = 'Error updating car: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car - AutoElite Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #146ef5;
            --primary-dark: #0d5ed6;
            --primary-light: rgba(20, 110, 245, 0.1);
            --secondary: #1d3557;
            --dark: #121212;
            --light: #eff7ff;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 14px;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(20, 110, 245, 0.3);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #14213d;
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--secondary);
            border: 2px solid var(--secondary);
        }

        .btn-outline:hover {
            background-color: var(--secondary);
            color: white;
        }

        /* Header Styles */
        .admin-header {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: white;
        }

        .logo span {
            color: #ffd700;
        }

        .admin-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-nav a {
            color: white;
            font-weight: 500;
            transition: var(--transition);
        }

        .admin-nav a:hover {
            color: #ffd700;
        }

        /* Form Styles */
        .form-container {
            padding: 30px 0;
        }

        .form-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 30px;
        }

        .form-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--secondary);
        }

        .form-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--secondary);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 15px;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(20, 110, 245, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        textarea.form-control {
            height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-light);
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .current-image {
            margin-top: 10px;
        }

        .current-image img {
            max-width: 200px;
            max-height: 150px;
            border-radius: var(--border-radius);
            border: 2px solid var(--gray-light);
        }

        /* Image Preview Styles */
        .image-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .image-preview-item {
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            border: 2px solid var(--gray-light);
        }

        .image-preview-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }

        .image-preview-remove {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .existing-images {
            margin-bottom: 20px;
        }

        .cover-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: var(--primary);
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }

        .text-muted {
            color: var(--gray);
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                gap: 10px;
            }
            
            .form-card {
                padding: 25px 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .image-preview {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container header-container">
            <a href="dashboard.php" class="logo">Auto<span>Elite</span> Admin</a>
            
            <div class="admin-nav">
                <a href="../index.php" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="add-car.php">
                    <i class="fas fa-plus"></i> Add Car
                </a>
                <a href="?logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Form Content -->
    <div class="form-container">
        <div class="container">
            <div class="form-header">
                <h1 class="form-title">Edit Car</h1>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert <?= strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="make">Make *</label>
                            <input type="text" id="make" name="make" class="form-control" value="<?= htmlspecialchars($car['make']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="model">Model *</label>
                            <input type="text" id="model" name="model" class="form-control" value="<?= htmlspecialchars($car['model']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="year">Year *</label>
                            <input type="number" id="year" name="year" class="form-control" min="1900" max="<?= date('Y') + 1 ?>" value="<?= $car['year'] ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price (LKR) *</label>
                            <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" value="<?= $car['price'] ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="mileage">Mileage (km)</label>
                            <input type="number" id="mileage" name="mileage" class="form-control" min="0" value="<?= $car['mileage'] ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="fuel_type">Fuel Type</label>
                            <select id="fuel_type" name="fuel_type" class="form-control">
                                <option value="">Select Fuel Type</option>
                                <option value="Petrol" <?= $car['fuel_type'] === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                                <option value="Diesel" <?= $car['fuel_type'] === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                                <option value="Hybrid" <?= $car['fuel_type'] === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                                <option value="Electric" <?= $car['fuel_type'] === 'Electric' ? 'selected' : '' ?>>Electric</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="transmission">Transmission</label>
                            <select id="transmission" name="transmission" class="form-control">
                                <option value="">Select Transmission</option>
                                <option value="Automatic" <?= $car['transmission'] === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                                <option value="Manual" <?= $car['transmission'] === 'Manual' ? 'selected' : '' ?>>Manual</option>
                                <option value="Semi-Automatic" <?= $car['transmission'] === 'Semi-Automatic' ? 'selected' : '' ?>>Semi-Automatic</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="body_type">Body Type</label>
                            <select id="body_type" name="body_type" class="form-control">
                                <option value="">Select Body Type</option>
                                <option value="Sedan" <?= $car['body_type'] === 'Sedan' ? 'selected' : '' ?>>Sedan</option>
                                <option value="SUV" <?= $car['body_type'] === 'SUV' ? 'selected' : '' ?>>SUV</option>
                                <option value="Hatchback" <?= $car['body_type'] === 'Hatchback' ? 'selected' : '' ?>>Hatchback</option>
                                <option value="Coupe" <?= $car['body_type'] === 'Coupe' ? 'selected' : '' ?>>Coupe</option>
                                <option value="Convertible" <?= $car['body_type'] === 'Convertible' ? 'selected' : '' ?>>Convertible</option>
                                <option value="Wagon" <?= $car['body_type'] === 'Wagon' ? 'selected' : '' ?>>Wagon</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="car_condition">Condition</label>
                            <select id="car_condition" name="car_condition" class="form-control">
                                <option value="">Select Condition</option>
                                <option value="New" <?= $car['car_condition'] === 'New' ? 'selected' : '' ?>>New</option>
                                <option value="Used" <?= $car['car_condition'] === 'Used' ? 'selected' : '' ?>>Used</option>
                                <option value="Certified" <?= $car['car_condition'] === 'Certified' ? 'selected' : '' ?>>Certified</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="active" <?= $car['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $car['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Existing Images -->
                    <?php if (!empty($car_images)): ?>
                    <div class="form-group existing-images">
                        <label>Existing Images</label>
                        <div class="image-preview">
                            <?php foreach ($car_images as $image): ?>
                                <div class="image-preview-item">
                                    <img src="../<?= htmlspecialchars($image['image_path']) ?>" alt="Car image">
                                    <?php if ($image['is_cover']): ?>
                                        <span class="cover-badge">Cover</span>
                                    <?php endif; ?>
                                    <button type="button" class="image-preview-remove" onclick="markForDeletion(<?= $image['id'] ?>, this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted">Click the X button to remove images. The first remaining image will become the cover.</small>
                    </div>
                    <?php endif; ?>

                    <!-- New Images -->
                    <div class="form-group">
                        <label for="car_images">Add More Images</label>
                        <div class="file-input-wrapper">
                            <button type="button" class="btn btn-outline" style="width: 100%;">
                                <i class="fas fa-images"></i> Select Additional Images
                            </button>
                            <input type="file" id="car_images" name="car_images[]" multiple accept="image/*">
                        </div>
                        <small class="text-muted">Select additional images to add to this car. You can select multiple images at once.</small>
                        <div class="image-preview" id="imagePreview"></div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Enter car description..."><?= htmlspecialchars($car['description']) ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Car
                        </button>
                        <a href="dashboard.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Image preview functionality for new images
        document.getElementById('car_images').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            const files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'image-preview-item';
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="image-preview-remove" onclick="removeNewImage(${i})">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        preview.appendChild(previewItem);
                    }
                    reader.readAsDataURL(file);
                }
            }
        });

        function removeNewImage(index) {
            const input = document.getElementById('car_images');
            const dt = new DataTransfer();
            const files = input.files;
            
            for (let i = 0; i < files.length; i++) {
                if (i !== index) {
                    dt.items.add(files[i]);
                }
            }
            
            input.files = dt.files;
            
            // Update preview
            const event = new Event('change');
            input.dispatchEvent(event);
        }

        function markForDeletion(imageId, element) {
            // Create hidden input for deletion if it doesn't exist
            let deleteContainer = document.getElementById('deleteImagesContainer');
            if (!deleteContainer) {
                deleteContainer = document.createElement('div');
                deleteContainer.id = 'deleteImagesContainer';
                deleteContainer.style.display = 'none';
                document.querySelector('form').appendChild(deleteContainer);
            }
            
            // Create new hidden input for this image
            const newDeleteInput = document.createElement('input');
            newDeleteInput.type = 'hidden';
            newDeleteInput.name = 'delete_images[]';
            newDeleteInput.value = imageId;
            deleteContainer.appendChild(newDeleteInput);
            
            // Remove the image preview
            element.parentElement.remove();
            
            // Show message if this was the cover image
            const coverBadge = element.parentElement.querySelector('.cover-badge');
            if (coverBadge) {
                alert('Cover image removed. The first remaining image will become the new cover image.');
            }
        }
    </script>
</body>
</html>