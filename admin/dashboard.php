<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get dashboard statistics
$total_cars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$active_cars = $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'active'")->fetchColumn();
$sold_cars = $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'inactive'")->fetchColumn();
$total_value = $pdo->query("SELECT SUM(price) FROM cars WHERE status = 'active'")->fetchColumn() ?? 0;

// Get recent cars with cover images
$recent_cars = $pdo->query("
    SELECT c.*, 
           COALESCE(ci.image_path, c.image) as display_image 
    FROM cars c 
    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_cover = 1 
    ORDER BY c.created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Get popular makes - FIXED: Show all makes without limit and include all statuses
$popular_makes = $pdo->query("SELECT make, COUNT(*) as count FROM cars GROUP BY make ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get filter parameter
$make_filter = $_GET['make'] ?? '';

// Build query for cars with optional make filter
$cars_query = "
    SELECT c.*, 
           COALESCE(ci.image_path, c.image) as display_image 
    FROM cars c 
    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_cover = 1 
";

if (!empty($make_filter)) {
    $cars_query .= " WHERE c.make = :make";
}

$cars_query .= " ORDER BY c.created_at DESC";

$cars_stmt = $pdo->prepare($cars_query);
if (!empty($make_filter)) {
    $cars_stmt->bindValue(':make', $make_filter);
}
$cars_stmt->execute();
$cars = $cars_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | AutoElite</title>
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

        img {
            max-width: 100%;
            height: auto;
        }

        .container {
            width: 100%;
            max-width: 1400px;
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

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
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

        .btn-sm {
            padding: 8px 16px;
            font-size: 12px;
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Dashboard Styles */
        .dashboard {
            padding: 30px 0;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--secondary);
        }

        .welcome-message {
            color: var(--gray);
            font-size: 16px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            border-left: 4px solid var(--primary);
        }

        .stat-card h3 {
            font-size: 14px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .stat-description {
            font-size: 12px;
            color: var(--gray);
        }

        .admin-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        /* Content Section Styles */
        .content-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .section-header {
            padding: 20px;
            border-bottom: 1px solid var(--gray-light);
        }

        .section-header h3 {
            color: var(--secondary);
            margin: 0;
        }

        .section-content {
            padding: 20px;
        }

        .recent-cars {
            display: grid;
            gap: 15px;
        }

        .car-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
        }

        .car-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        .car-info {
            flex: 1;
        }

        .car-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--secondary);
        }

        .car-details {
            font-size: 12px;
            color: var(--gray);
            display: flex;
            gap: 15px;
        }

        .car-price {
            font-weight: 700;
            color: #1d3557; /* Changed to #1d3557 as requested */
            font-size: 16px;
        }

        .car-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .status-inactive {
            background: rgba(108, 117, 125, 0.1);
            color: var(--gray);
        }

        .makes-list {
            display: grid;
            gap: 10px;
        }

        .make-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: var(--light);
            border-radius: var(--border-radius);
        }

        .make-name {
            font-weight: 600;
            color: var(--secondary);
        }

        .make-count {
            background: var(--primary);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .table-header {
            background: var(--light);
            padding: 20px;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--secondary);
            margin: 0;
        }

        .filter-section {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 14px;
            background: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        th {
            background: var(--light);
            font-weight: 600;
            color: var(--secondary);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: var(--primary-light);
        }

        .car-image-small {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .no-cars {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .no-cars i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--gray-light);
        }

        .grid-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                gap: 10px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-actions {
                flex-direction: column;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .grid-layout {
                grid-template-columns: 1fr;
            }
            
            .table-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
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
                <a href="add-car.php">
                    <i class="fas fa-plus"></i> Add Car
                </a>
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <span>Admin</span>
                </div>
                <a href="?logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <div>
                    <h1 class="dashboard-title">Admin Dashboard</h1>
                    <p class="welcome-message">Welcome back! Here's your overview.</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Cars</h3>
                    <div class="stat-number"><?= $total_cars ?></div>
                    <p class="stat-description">All vehicles in inventory</p>
                </div>
                
                <div class="stat-card">
                    <h3>Active Cars</h3>
                    <div class="stat-number"><?= $active_cars ?></div>
                    <p class="stat-description">Available for sale</p>
                </div>
                
                <div class="stat-card">
                    <h3>Sold Cars</h3>
                    <div class="stat-number"><?= $sold_cars ?></div>
                    <p class="stat-description">Previously sold vehicles</p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Value</h3>
                    <div class="stat-number">LKR <?= number_format($total_value, 2) ?></div>
                    <p class="stat-description">Current inventory value</p>
                </div>
            </div>

            <!-- Admin Actions -->
            <div class="admin-actions">
                <a href="add-car.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Car
                </a>
                <a href="../cars.php" class="btn btn-secondary" target="_blank">
                    <i class="fas fa-car"></i> View Inventory
                </a>
                <a href="../index.php" class="btn btn-outline" target="_blank">
                    <i class="fas fa-home"></i> Visit Homepage
                </a>
            </div>

            <!-- Recent Cars and Popular Makes -->
            <div class="grid-layout">
                <!-- Recent Cars -->
                <div class="content-section">
                    <div class="section-header">
                        <h3>Recent Cars</h3>
                    </div>
                    <div class="section-content">
                        <div class="recent-cars">
                            <?php foreach ($recent_cars as $car): ?>
                            <div class="car-item">
                                <img src="../<?= htmlspecialchars($car['display_image']) ?>" 
                                     alt="<?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?>" 
                                     class="car-image"
                                     onerror="this.src='https://images.unsplash.com/photo-1563720223485-884b46ce7c86?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80'">
                                <div class="car-info">
                                    <div class="car-name"><?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?> (<?= $car['year'] ?>)</div>
                                    <div class="car-details">
                                        <span><?= number_format($car['mileage']) ?> km</span>
                                        <span><?= htmlspecialchars($car['fuel_type']) ?></span>
                                        <span><?= htmlspecialchars($car['transmission']) ?></span>
                                    </div>
                                </div>
                                <div class="car-price">LKR <?= number_format($car['price'], 2) ?></div>
                                <div class="car-status <?= $car['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                    <?= ucfirst($car['status']) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Popular Makes -->
                <div class="content-section">
                    <div class="section-header">
                        <h3>All Makes (<?= count($popular_makes) ?>)</h3>
                    </div>
                    <div class="section-content">
                        <div class="makes-list">
                            <?php foreach ($popular_makes as $make): ?>
                            <div class="make-item">
                                <span class="make-name"><?= htmlspecialchars($make['make']) ?></span>
                                <span class="make-count"><?= $make['count'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cars Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Manage Cars (<?= count($cars) ?> total)</h3>
                    <div class="filter-section">
                        <form method="GET" action="dashboard.php" style="display: flex; gap: 10px; align-items: center;">
                            <select name="make" class="filter-select" onchange="this.form.submit()">
                                <option value="">All Makes</option>
                                <?php foreach ($popular_makes as $make): ?>
                                    <option value="<?= htmlspecialchars($make['make']) ?>" <?= $make_filter === $make['make'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($make['make']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($make_filter)): ?>
                                <a href="dashboard.php" class="btn btn-outline btn-sm">
                                    <i class="fas fa-times"></i> Clear Filter
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <?php if (empty($cars)): ?>
                    <div class="no-cars">
                        <i class="fas fa-car"></i>
                        <h3>No Cars Found</h3>
                        <p><?= !empty($make_filter) ? "No cars found for make: " . htmlspecialchars($make_filter) : "Get started by adding your first car to the inventory." ?></p>
                        <a href="add-car.php" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-plus"></i> Add Your First Car
                        </a>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Make & Model</th>
                                <th>Year</th>
                                <th>Price</th>
                                <th>Condition</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?= $car['id'] ?></td>
                                <td>
                                    <?php if (!empty($car['display_image'])): ?>
                                        <img src="../<?= htmlspecialchars($car['display_image']) ?>" 
                                             alt="<?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?>" 
                                             class="car-image-small"
                                             onerror="this.src='https://images.unsplash.com/photo-1563720223485-884b46ce7c86?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80'">
                                    <?php else: ?>
                                        <span style="color: var(--gray);">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?></strong>
                                </td>
                                <td><?= $car['year'] ?></td>
                                <td><strong>LKR <?= number_format($car['price'], 2) ?></strong></td>
                                <td><?= htmlspecialchars($car['car_condition']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $car['status'] ?>">
                                        <?= ucfirst($car['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit-car.php?id=<?= $car['id'] ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete-car.php?id=<?= $car['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this car?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>