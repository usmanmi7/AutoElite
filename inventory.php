<?php
include 'config/db.php';

// Get filter parameters
$make = $_GET['make'] ?? '';
$model = $_GET['model'] ?? '';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 50000000;
$year = $_GET['year'] ?? '';
$body_type = $_GET['body_type'] ?? '';
$fuel_type = $_GET['fuel_type'] ?? '';
$transmission = $_GET['transmission'] ?? '';
$car_condition = $_GET['car_condition'] ?? '';

// Pagination settings
$cars_per_page = 12;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $cars_per_page;

// Build query with cover images - COUNT for pagination
$count_sql = "SELECT COUNT(*) 
              FROM cars c 
              WHERE c.status = 'active'";
$count_params = [];

// Build main query
$sql = "SELECT c.*, 
               COALESCE(ci.image_path, c.image) as display_image 
        FROM cars c 
        LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_cover = 1 
        WHERE c.status = 'active'";
$params = [];

// Apply filters to both queries
if (!empty($make)) {
    $sql .= " AND c.make = ?";
    $count_sql .= " AND c.make = ?";
    $params[] = $make;
    $count_params[] = $make;
}

if (!empty($model)) {
    $sql .= " AND c.model = ?";
    $count_sql .= " AND c.model = ?";
    $params[] = $model;
    $count_params[] = $model;
}

if (!empty($year)) {
    $sql .= " AND c.year = ?";
    $count_sql .= " AND c.year = ?";
    $params[] = $year;
    $count_params[] = $year;
}

if (!empty($body_type) && $body_type !== '') {
    $sql .= " AND c.body_type = ?";
    $count_sql .= " AND c.body_type = ?";
    $params[] = $body_type;
    $count_params[] = $body_type;
}

if (!empty($fuel_type) && $fuel_type !== '') {
    $sql .= " AND c.fuel_type = ?";
    $count_sql .= " AND c.fuel_type = ?";
    $params[] = $fuel_type;
    $count_params[] = $fuel_type;
}

if (!empty($transmission) && $transmission !== '') {
    $sql .= " AND c.transmission = ?";
    $count_sql .= " AND c.transmission = ?";
    $params[] = $transmission;
    $count_params[] = $transmission;
}

if (!empty($car_condition) && $car_condition !== '') {
    $sql .= " AND c.car_condition = ?";
    $count_sql .= " AND c.car_condition = ?";
    $params[] = $car_condition;
    $count_params[] = $car_condition;
}

$sql .= " AND c.price BETWEEN ? AND ?";
$count_sql .= " AND c.price BETWEEN ? AND ?";
$params[] = $min_price;
$params[] = $max_price;
$count_params[] = $min_price;
$count_params[] = $max_price;

// Get total count for pagination
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_cars = $count_stmt->fetchColumn();
$total_pages = ceil($total_cars / $cars_per_page);

// Get total count of all active cars in inventory
$total_inventory_stmt = $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'active'");
$total_inventory_count = $total_inventory_stmt->fetchColumn();

// Add pagination to main query - FIXED: Directly append integers instead of parameters
$sql .= " ORDER BY c.created_at DESC LIMIT $cars_per_page OFFSET $offset";

// Execute query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique values for filters
$makes = $pdo->query("SELECT DISTINCT make FROM cars WHERE status = 'active' ORDER BY make")->fetchAll(PDO::FETCH_COLUMN);
$models = $pdo->query("SELECT DISTINCT model FROM cars WHERE status = 'active' ORDER BY model")->fetchAll(PDO::FETCH_COLUMN);
$years = $pdo->query("SELECT DISTINCT year FROM cars WHERE status = 'active' ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
$body_types = $pdo->query("SELECT DISTINCT body_type FROM cars WHERE status = 'active' AND body_type IS NOT NULL AND body_type != '' ORDER BY body_type")->fetchAll(PDO::FETCH_COLUMN);
$fuel_types = $pdo->query("SELECT DISTINCT fuel_type FROM cars WHERE status = 'active' AND fuel_type IS NOT NULL AND fuel_type != '' ORDER BY fuel_type")->fetchAll(PDO::FETCH_COLUMN);
$transmissions = $pdo->query("SELECT DISTINCT transmission FROM cars WHERE status = 'active' AND transmission IS NOT NULL AND transmission != '' ORDER BY transmission")->fetchAll(PDO::FETCH_COLUMN);
$conditions = $pdo->query("SELECT DISTINCT car_condition FROM cars WHERE status = 'active' AND car_condition IS NOT NULL AND car_condition != '' ORDER BY car_condition")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoElite | Vehicle Inventory</title>
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
            background-color: #fff;
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
            font-size: 16px;
            gap: 10px;
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

        .section {
            padding: 40px 0px 100px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--secondary);
        }

        .section-subtitle {
            font-size: 18px;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Header Styles - Updated for transparent design */
        header {
            background-color: transparent;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: var(--transition);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        header.scrolled {
            background-color: rgba(18, 18, 18, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            color: white;
        }

        .logo span {
            color: var(--primary);
        }

        .nav-menu {
            display: flex;
        }

        .nav-item {
            margin: 0 15px;
            position: relative;
        }

        .nav-link {
            font-weight: 500;
            color: white;
            transition: var(--transition);
            padding: 8px 0;
            position: relative;
        }

        .nav-link:hover {
            color: white;
        }

        .nav-link.active {
            color: white;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            transition: var(--transition);
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .cta-button {
            padding: 12px 28px;
            border-radius: 55px;
        }

        .mobile-menu {
            display: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        /* Modern Photo Hero Section - UPDATED */
        .inventory-hero {
            background: 
                linear-gradient(rgb(0 0 0 / 38%), rgb(14 53 135 / 86%)),
                url('assets/uploads/inventory-hero.jpg');
            background-size: cover;
            background-position: center 50%;
            color: white;
            padding: 200px 0 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .inventory-hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .inventory-hero p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto 0px;
            font-weight: 400;
            line-height: 1.6;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Filter Section */
        .filter-section {
            background-color: var(--light);
            padding: 40px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .filter-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .filter-group {
            margin-bottom: 15px;
        }

        .filter-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--secondary);
            font-size: 14px;
        }

        .filter-select, .filter-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 15px;
            background-color: white;
            transition: var(--transition);
        }

        .filter-select:focus, .filter-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(20, 110, 245, 0.1);
        }

        .price-range {
            display: flex;
            gap: 10px;
        }

        .price-range input {
            flex: 1;
        }

        /* Filter Actions - UPDATED */
        .filter-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            grid-column: 1 / -1;
            justify-content: flex-start;
        }

        .filter-actions .btn {
            flex: 0 1 auto;
            min-width: 150px;
        }

        /* Results Count */
        .results-info {
            background-color: var(--light);
            padding: 20px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .results-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .results-count {
            font-weight: 600;
            color: var(--secondary);
        }

        .sort-options {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sort-select {
            padding: 8px 12px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 14px;
        }

        /* Car Grid */
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .car-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border: 1px solid #f0f0f0;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .car-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
            background-color: #f5f5f5;
        }

        .card-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: var(--primary);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .car-details {
            padding: 20px;
        }

        .car-make {
            font-size: 14px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .car-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .car-price {
            font-size: 20px;
            font-weight: 700;
            color: #1d3557;
            margin-bottom: 15px;
        }

        .car-specs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border-top: 1px solid var(--gray-light);
            padding-top: 15px;
        }

        .spec {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .spec i {
            font-size: 18px;
            color: var(--gray);
            margin-bottom: 5px;
        }

        .spec span {
            font-size: 12px;
            color: var(--gray);
        }

        .car-actions {
            display: flex;
            gap: 10px;
        }

        .car-actions .btn {
            flex: 1;
            padding: 10px;
            font-size: 14px;
        }

        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .no-results i {
            font-size: 48px;
            margin-bottom: 20px;
            color: var(--gray-light);
        }

        /* Image Count Badge */
        .image-count-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            backdrop-filter: blur(5px);
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 50px;
            gap: 8px;
        }

        .pagination-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius);
            background-color: white;
            border: 1px solid var(--gray-light);
            color: var(--secondary);
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
        }

        .pagination-item:hover {
            background-color: var(--primary-light);
            border-color: var(--primary);
        }

        .pagination-item.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-item.disabled:hover {
            background-color: white;
            border-color: var(--gray-light);
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 60px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: white;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #b0b7c3;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: white;
        }

        .contact-info li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            color: #b0b7c3;
        }

        .contact-info i {
            margin-right: 10px;
            color: var(--primary);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #b0b7c3;
            font-size: 14px;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background-color: rgba(18, 18, 18, 0.95);
                backdrop-filter: blur(10px);
                flex-direction: column;
                padding: 20px;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .nav-menu.active {
                display: flex;
            }
            
            .nav-item {
                margin: 10px 0;
            }
            
            .mobile-menu {
                display: block;
            }
            
            .filter-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .inventory-hero h1 {
                font-size: 2.5rem;
            }
            
            .results-container {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }

        @media (max-width: 768px) {
            .filter-container {
                grid-template-columns: 1fr;
            }
            
            .cars-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .inventory-hero h1 {
                font-size: 2.2rem;
            }
            
            .inventory-hero p {
                font-size: 1.1rem;
            }
            
            .filter-actions {
                flex-direction: column;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 576px) {
            .section {
                padding: 60px 0;
            }
            
            .section-title {
                font-size: 30px;
            }
            
            .inventory-hero {
                padding: 150px 0 80px;
                background-attachment: scroll;
            }
            
            .inventory-hero h1 {
                font-size: 2rem;
            }
            
            .car-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header with transparent design -->
    <header id="header">
        <div class="container header-container">
            <a href="index.php" class="logo">Auto<span>Elite</span></a>
            
            <div class="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
            
            <!-- Complete navigation links -->
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="inventory.php" class="nav-link active">Inventory</a></li>
                <li class="nav-item"><a href="about.php" class="nav-link">About Us</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                <li class="nav-item"><a href="admin/login.php" class="nav-link">Admin</a></li>
            </ul>
            
            <div class="header-actions">
                <a href="contact.php" class="btn btn-primary cta-button">
                    Contact Us <i class="fas fa-phone-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Modern Photo Hero Section -->
    <section class="inventory-hero">
        <div class="container hero-content">
            <h1>Vehicle Inventory</h1>
            <p>Browse our extensive collection of <?= $total_inventory_count ?> premium vehicles with our advanced filtering system</p>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="filter-section">
        <div class="container">
            <form method="GET" action="inventory.php">
                <div class="filter-container">
                    <!-- Make Filter -->
                    <div class="filter-group">
                        <label for="make">Make</label>
                        <select id="make" name="make" class="filter-select">
                            <option value="">All Makes</option>
                            <?php foreach ($makes as $make_option): ?>
                                <option value="<?= htmlspecialchars($make_option) ?>" <?= $make === $make_option ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($make_option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Model Filter -->
                    <div class="filter-group">
                        <label for="model">Model</label>
                        <select id="model" name="model" class="filter-select">
                            <option value="">All Models</option>
                            <?php foreach ($models as $model_option): ?>
                                <option value="<?= htmlspecialchars($model_option) ?>" <?= $model === $model_option ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($model_option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Year Filter -->
                    <div class="filter-group">
                        <label for="year">Year</label>
                        <select id="year" name="year" class="filter-select">
                            <option value="">Any Year</option>
                            <?php foreach ($years as $year_option): ?>
                                <option value="<?= htmlspecialchars($year_option) ?>" <?= $year == $year_option ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($year_option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Body Type Filter -->
                    <div class="filter-group">
                        <label for="body_type">Body Type</label>
                        <select id="body_type" name="body_type" class="filter-select">
                            <option value="">Any Body Type</option>
                            <?php foreach ($body_types as $body_type_option): ?>
                                <option value="<?= htmlspecialchars($body_type_option) ?>" <?= $body_type === $body_type_option ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($body_type_option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Fuel Type Filter -->
                    <div class="filter-group">
                        <label for="fuel_type">Fuel Type</label>
                        <select id="fuel_type" name="fuel_type" class="filter-select">
                            <option value="">Any Fuel Type</option>
                            <?php foreach ($fuel_types as $fuel_type_option): ?>
                                <option value="<?= htmlspecialchars($fuel_type_option) ?>" <?= $fuel_type === $fuel_type_option ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($fuel_type_option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Transmission Filter -->
                    <div class="filter-group">
                        <label for="transmission">Transmission</label>
                        <select id="transmission" name="transmission" class="filter-select">
                            <option value="">Any Transmission</option>
                            <?php foreach ($transmissions as $transmission_option): ?>
                                <option value="<?= htmlspecialchars($transmission_option) ?>" <?= $transmission === $transmission_option ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($transmission_option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Condition Filter -->
                    <div class="filter-group">
                        <label for="car_condition">Condition</label>
                        <select id="car_condition" name="car_condition" class="filter-select">
                            <option value="">Any Condition</option>
                            <?php foreach ($conditions as $condition_option): ?>
                                <option value="<?= htmlspecialchars($condition_option) ?>" <?= $car_condition === $condition_option ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($condition_option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-group">
                        <label for="min_price">Price Range (LKR)</label>
                        <div class="price-range">
                            <input type="number" id="min_price" name="min_price" class="filter-input" placeholder="Min" value="<?= $min_price ?>">
                            <input type="number" id="max_price" name="max_price" class="filter-input" placeholder="Max" value="<?= $max_price ?>">
                        </div>
                    </div>

                    <!-- Filter Actions - UPDATED -->
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="inventory.php" class="btn btn-outline">Reset Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Results Info - UPDATED -->
    <section class="results-info">
        <div class="container results-container">
            <div class="results-count">
                Showing <?= count($cars) ?> vehicle<?= count($cars) !== 1 ? 's' : '' ?> 
                <?php if ($total_pages > 1): ?>
                    (Page <?= $current_page ?> of <?= $total_pages ?>)
                <?php endif; ?>
                out of <?= $total_inventory_count ?> total vehicles
            </div>
            <div class="sort-options">
                <label for="sort">Sort by:</label>
                <select id="sort" class="sort-select" onchange="sortInventory(this.value)">
                    <option value="newest">Newest First</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="year_new">Year: Newest First</option>
                    <option value="year_old">Year: Oldest First</option>
                </select>
            </div>
        </div>
    </section>

    <!-- Car Grid -->
    <section class="section">
        <div class="container">
            <?php if (empty($cars)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No vehicles found</h3>
                    <p>Try adjusting your filters to see more results.</p>
                    <a href="inventory.php" class="btn btn-primary" style="margin-top: 20px;">
                        Reset All Filters
                    </a>
                </div>
            <?php else: ?>
                <div class="cars-grid" id="cars-grid">
                    <?php foreach ($cars as $car): 
                        // Get image count for this car
                        $image_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM car_images WHERE car_id = ?");
                        $image_count_stmt->execute([$car['id']]);
                        $image_count = $image_count_stmt->fetchColumn();
                        
                        // Check if image is from uploads folder (local) or external URL
                        $image_src = $car['display_image'];
                        if (strpos($image_src, 'assets/uploads/') === 0) {
                            // Local uploaded image - use relative path
                            $image_src = $car['display_image'];
                        }
                    ?>
                    <div class="car-card" data-year="<?= $car['year'] ?>" data-price="<?= $car['price'] ?>" data-date="<?= $car['created_at'] ?>">
                        <div style="position: relative;">
                            <img src="<?= htmlspecialchars($image_src) ?>" 
                                 alt="<?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?>" 
                                 class="car-image" 
                                 onerror="this.src='https://images.unsplash.com/photo-1494976388531-d1058494cdd8?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'">
                            <span class="card-badge"><?= htmlspecialchars($car['car_condition']) ?></span>
                            <?php if ($image_count > 1): ?>
                                <span class="image-count-badge" title="<?= $image_count ?> photos">
                                    <i class="fas fa-camera"></i> <?= $image_count ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="car-details">
                            <p class="car-make"><?= htmlspecialchars($car['make']) ?></p>
                            <h3 class="car-name"><?= htmlspecialchars($car['model']) ?> <?= htmlspecialchars($car['year']) ?></h3>
                            <p class="car-price">LKR <?= number_format($car['price'], 2) ?></p>
                            <div class="car-specs">
                                <div class="spec">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span><?= number_format($car['mileage']) ?> km</span>
                                </div>
                                <div class="spec">
                                    <i class="fas fa-gas-pump"></i>
                                    <span><?= !empty($car['fuel_type']) ? htmlspecialchars($car['fuel_type']) : 'N/A' ?></span>
                                </div>
                                <div class="spec">
                                    <i class="fas fa-cog"></i>
                                    <span><?= !empty($car['transmission']) ? htmlspecialchars($car['transmission']) : 'N/A' ?></span>
                                </div>
                            </div>
                            <div class="car-actions">
                                <a href="car-details.php?id=<?= $car['id'] ?>" class="btn btn-primary">View Details</a>
                                <a href="contact.php" class="btn btn-outline">Inquire</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php 
                    // Build query string without page parameter
                    $query_params = $_GET;
                    unset($query_params['page']);
                    $query_string = http_build_query($query_params);
                    $query_string = $query_string ? $query_string . '&' : '';
                    ?>
                    
                    <?php if ($current_page > 1): ?>
                        <a href="?<?= $query_string ?>page=1" class="pagination-item">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?<?= $query_string ?>page=<?= $current_page - 1 ?>" class="pagination-item">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-item disabled">
                            <i class="fas fa-angle-double-left"></i>
                        </span>
                        <span class="pagination-item disabled">
                            <i class="fas fa-angle-left"></i>
                        </span>
                    <?php endif; ?>
                    
                    <?php
                    // Show page numbers
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <a href="?<?= $query_string ?>page=<?= $i ?>" 
                           class="pagination-item <?= $i == $current_page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?<?= $query_string ?>page=<?= $current_page + 1 ?>" class="pagination-item">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?<?= $query_string ?>page=<?= $total_pages ?>" class="pagination-item">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-item disabled">
                            <i class="fas fa-angle-right"></i>
                        </span>
                        <span class="pagination-item disabled">
                            <i class="fas fa-angle-double-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>AutoElite</h3>
                    <p>Your trusted partner for premium vehicles. We're committed to providing the best car buying experience with quality assurance and competitive pricing.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="inventory.php">Inventory</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Services</h3>
                    <ul class="footer-links">
                        <li><a href="buy-car.html">Buy a Car</a></li>
                        <li><a href="sell-your-car.html">Sell Your Car</a></li>
                        <li><a href="trade-ins.html">Trade-Ins</a></li>
                        <li><a href="financing.html">Vehicle Financing</a></li>
                        <li><a href="inspection.html">Inspection Services</a></li>
                        <li><a href="warranty.html">Warranty Packages</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Contact Us</h3>
                    <ul class="contact-info">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 Auto Avenue, Motor City, MC 12345</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>(555) 123-4567</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>info@autoelite.com</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Mon-Fri: 9am-7pm, Sat: 10am-5pm, Sun: 12pm-4pm</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> AutoElite. All rights reserved. | <a href="privacy.html">Privacy Policy</a> | <a href="terms.html">Terms & Conditions</a></p>
            </div>
        </div>
    </footer>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        document.querySelector('.mobile-menu').addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('active');
        });

        function sortInventory(sortBy) {
            const grid = document.getElementById('cars-grid');
            const cars = Array.from(grid.getElementsByClassName('car-card'));
            
            cars.sort((a, b) => {
                switch(sortBy) {
                    case 'price_low':
                        return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                    case 'price_high':
                        return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                    case 'year_new':
                        return parseInt(b.dataset.year) - parseInt(a.dataset.year);
                    case 'year_old':
                        return parseInt(a.dataset.year) - parseInt(b.dataset.year);
                    case 'newest':
                    default:
                        return new Date(b.dataset.date) - new Date(a.dataset.date);
                }
            });
            
            // Clear and re-append sorted cars
            cars.forEach(car => grid.appendChild(car));
        }
    </script>
</body>
</html>