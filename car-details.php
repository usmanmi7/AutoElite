<?php
include 'config/db.php';

// Get car ID from URL
$car_id = $_GET['id'] ?? null;

if (!$car_id) {
    header('Location: inventory.php');
    exit;
}

// Get car details from database
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND status = 'active'");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

// If car not found, redirect to inventory
if (!$car) {
    header('Location: inventory.php');
    exit;
}

// Get car images
$stmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY image_order ASC, is_cover DESC");
$stmt->execute([$car_id]);
$car_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no images found, use the main image from cars table
if (empty($car_images) && !empty($car['image'])) {
    $car_images = [['image_path' => $car['image'], 'is_cover' => 1]];
}

// Get similar cars (same make, excluding current car)
$similar_stmt = $pdo->prepare("SELECT * FROM cars WHERE make = ? AND id != ? AND status = 'active' ORDER BY created_at DESC LIMIT 4");
$similar_stmt->execute([$car['make'], $car_id]);
$similar_cars = $similar_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?> <?= $car['year'] ?> | AutoElite</title>
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
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
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
            text-decoration: none;
        }

        .logo span {
            color: var(--primary);
        }

        .nav-menu {
            display: flex;
            list-style: none;
        }

        .nav-item {
            margin: 0 15px;
        }

        .nav-link {
            font-weight: 500;
            color: white;
            text-decoration: none;
            transition: var(--transition);
            padding: 8px 0;
            position: relative;
        }

        .nav-link:hover {
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

        .nav-link:hover::after {
            width: 100%;
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
            text-decoration: none;
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

        .btn-outline {
            background-color: transparent;
            color: var(--secondary);
            border: 2px solid var(--secondary);
        }

        .btn-outline:hover {
            background-color: var(--secondary);
            color: white;
        }

        /* Hero Image Section */
        .hero-image-section {
            background: linear-gradient(rgb(0 0 0 / 38%), rgb(14 53 135 / 86%));
    background-size: cover;
    background-position: center 50%;
    color: white;
    padding: 200px 0 100px;
    text-align: center;
    position: relative;
    overflow: hidden
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            text-align: center;
            padding-top: 0px;
        }

        .hero-title {
            font-size: 3.0rem;
            font-weight: 700;
            margin-bottom: 20px;
            
        }

        .hero-subtitle {
            font-size: 20px;
            margin-bottom: 0;
            opacity: 0.9;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Image Gallery Styles */
        .image-gallery {
            margin-bottom: 40px;
        }

        .main-image-container {
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            margin-bottom: 15px;
            height: 500px;
        }

        .main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
        }

        .thumbnail {
            cursor: pointer;
            border-radius: var(--border-radius);
            overflow: hidden;
            border: 2px solid transparent;
            transition: var(--transition);
            height: 80px;
        }

        .thumbnail.active {
            border-color: var(--primary);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Car Details Section */
        .car-details-section {
            padding: 80px 0;
        }

        .breadcrumb {
            margin-bottom: 30px;
            color: var(--gray);
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        .car-details-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-bottom: 60px;
        }

        .car-info {
            padding: 0;
        }

        .car-badge {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .car-title {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .car-price {
            font-size: 28px;
            font-weight: 700;
            color: #1e1e1e;
            margin-bottom: 25px;
        }

        /* Quick Specs */
        .quick-specs {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gray);
        }

        .spec-item i {
            color: var(--primary);
            width: 16px;
        }

        /* Description */
        .car-description {
            margin-bottom: 30px;
            line-height: 1.7;
            color: var(--gray);
            padding: 20px;
            background: var(--light);
            border-radius: var(--border-radius);
        }

        /* Detailed Specifications */
        .specs-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--secondary);
        }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .spec-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .spec-label {
            color: var(--gray);
        }

        .spec-value {
            font-weight: 500;
            color: var(--dark);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
        }

        /* Similar Cars Section */
        .similar-cars-section {
            padding: 60px 0;
            background-color: var(--light);
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-main-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--secondary);
        }

        .section-subtitle {
            font-size: 16px;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .car-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .car-card-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }

        .car-card-details {
            padding: 20px;
        }

        .car-card-make {
            font-size: 14px;
            color: var(--gray);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .car-card-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .car-card-price {
            font-size: 20px;
            font-weight: 700;
            color: #1e1e1e;
            margin-bottom: 15px;
        }

        .car-card-specs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 12px;
            color: var(--gray);
        }

        .car-card-specs span {
            display: flex;
            align-items: center;
            gap: 5px;
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

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #b0b7c3;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #b0b7c3;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .car-details-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .quick-specs {
                justify-content: center;
            }
            
            .specs-grid {
                grid-template-columns: 1fr;
            }
            
            .cars-grid {
                grid-template-columns: 1fr;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 18px;
            }
            
            .main-image-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="container header-container">
            <a href="index.php" class="logo">Auto<span>Elite</span></a>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="inventory.php" class="nav-link">Inventory</a></li>
                <li class="nav-item"><a href="about.php" class="nav-link">About Us</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                <li class="nav-item"><a href="admin/login.php" class="nav-link">Admin</a></li>
            </ul>
            <div class="header-actions">
                <a href="contact.php" class="btn btn-primary" style="border-radius: 55px;">
                    Contact Us <i class="fas fa-phone-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Image Section -->
    <section class="hero-image-section">
        <img src="assets/uploads/inventory-hero.jpg" 
             alt="AutoElite Car Details" 
             class="hero-background">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Car Details</h1>
                <p class="hero-subtitle">Discover a premium range of vehicles built for drivers who appreciate true quality, refined aesthetics, and exhilarating performance</p>
            </div>
        </div>
    </section>

    <!-- Car Details Section -->
    <section class="car-details-section">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a> &gt; 
                <a href="inventory.php">Inventory</a> &gt; 
                <?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?>
            </div>

            <div class="car-details-container">
                <div class="image-gallery">
                    <?php if (!empty($car_images)): ?>
                        <div class="main-image-container">
                            <img src="<?= htmlspecialchars($car_images[0]['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?>" 
                                 class="main-image" id="mainImage">
                        </div>
                        
                        <div class="thumbnail-container">
                            <?php foreach ($car_images as $index => $image): ?>
                                <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                                     onclick="changeImage('<?= htmlspecialchars($image['image_path']) ?>', this)">
                                    <img src="<?= htmlspecialchars($image['image_path']) ?>" 
                                         alt="Thumbnail <?= $index + 1 ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="main-image-container">
                            <img src="https://images.unsplash.com/photo-1494976388531-d1058494cdd8?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" 
                                 alt="No image available" 
                                 class="main-image">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="car-info">
                    <span class="car-badge"><?= htmlspecialchars($car['car_condition']) ?></span>
                    <h1 class="car-title"><?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?> <?= $car['year'] ?></h1>
                    <div class="car-price">LKR <?= number_format($car['price'], 2) ?></div>
                    
                    <!-- Quick Specs -->
                    <div class="quick-specs">
                        <div class="spec-item">
                            <i class="fas fa-tachometer-alt"></i>
                            <span><?= number_format($car['mileage']) ?> km</span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-gas-pump"></i>
                            <span><?= htmlspecialchars($car['fuel_type']) ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-cog"></i>
                            <span><?= htmlspecialchars($car['transmission']) ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-car"></i>
                            <span><?= htmlspecialchars($car['body_type']) ?></span>
                        </div>
                    </div>

                    <!-- Description -->
                    <?php if (!empty($car['description'])): ?>
                    <div class="car-description">
                        <p><?= nl2br(htmlspecialchars($car['description'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Detailed Specifications -->
                    <div class="specs-section">
                        <h3 class="section-title">Vehicle Specifications</h3>
                        <div class="specs-grid">
                            <div class="spec-row">
                                <span class="spec-label">Year</span>
                                <span class="spec-value"><?= $car['year'] ?></span>
                            </div>
                            <div class="spec-row">
                                <span class="spec-label">Mileage</span>
                                <span class="spec-value"><?= number_format($car['mileage']) ?> km</span>
                            </div>
                            <div class="spec-row">
                                <span class="spec-label">Fuel Type</span>
                                <span class="spec-value"><?= htmlspecialchars($car['fuel_type']) ?></span>
                            </div>
                            <div class="spec-row">
                                <span class="spec-label">Transmission</span>
                                <span class="spec-value"><?= htmlspecialchars($car['transmission']) ?></span>
                            </div>
                            <div class="spec-row">
                                <span class="spec-label">Body Type</span>
                                <span class="spec-value"><?= htmlspecialchars($car['body_type']) ?></span>
                            </div>
                            <div class="spec-row">
                                <span class="spec-label">Condition</span>
                                <span class="spec-value"><?= htmlspecialchars($car['car_condition']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="contact.php" class="btn btn-primary">
                            <i class="fas fa-phone"></i> Contact About This Car
                        </a>
                        <a href="inventory.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Cars Section -->
    <?php if (!empty($similar_cars)): ?>
    <section class="similar-cars-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-main-title">Similar Vehicles</h2>
                <p class="section-subtitle">You might also be interested in these vehicles</p>
            </div>
            <div class="cars-grid">
                <?php foreach ($similar_cars as $similar_car): ?>
                <div class="car-card">
                    <img src="<?= htmlspecialchars($similar_car['image']) ?>" 
                         alt="<?= htmlspecialchars($similar_car['make']) ?> <?= htmlspecialchars($similar_car['model']) ?>" 
                         class="car-card-image">
                    <div class="car-card-details">
                        <p class="car-card-make"><?= htmlspecialchars($similar_car['make']) ?></p>
                        <h3 class="car-card-name"><?= htmlspecialchars($similar_car['model']) ?> <?= $similar_car['year'] ?></h3>
                        <div class="car-card-price">LKR <?= number_format($similar_car['price'], 2) ?></div>
                        <div class="car-card-specs">
                            <span><i class="fas fa-tachometer-alt"></i> <?= number_format($similar_car['mileage']) ?> km</span>
                            <span><i class="fas fa-gas-pump"></i> <?= htmlspecialchars($similar_car['fuel_type']) ?></span>
                            <span><i class="fas fa-cog"></i> <?= htmlspecialchars($similar_car['transmission']) ?></span>
                        </div>
                        <a href="car-details.php?id=<?= $similar_car['id'] ?>" class="btn btn-primary" style="width: 100%; padding: 10px;">
                            View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>AutoElite</h3>
                    <p>Your trusted partner for premium vehicles. We're committed to providing the best car buying experience.</p>
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
                    <h3>Contact Info</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Auto Avenue, Motor City</li>
                        <li><i class="fas fa-phone"></i> (555) 123-4567</li>
                        <li><i class="fas fa-envelope"></i> info@autoelite.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> AutoElite. All rights reserved.</p>
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

        // Image gallery functionality
        function changeImage(imageSrc, element) {
            // Update main image
            document.getElementById('mainImage').src = imageSrc;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            element.classList.add('active');
        }
    </script>
</body>
</html>