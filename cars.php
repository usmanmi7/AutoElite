<?php
include 'config/db.php';

if (!isset($_GET['id'])) {
    header('Location: cars.php');
    exit;
}

$car_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND status = 'active'");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header('Location: cars.php');
    exit;
}

// Get related cars
$stmt = $pdo->prepare("SELECT * FROM cars WHERE make = ? AND id != ? AND status = 'active' LIMIT 3");
$stmt->execute([$car['make'], $car_id]);
$related_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?> | AutoElite</title>
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
            padding: 80px 0;
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

        /* Header Styles */
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
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
            color: var(--secondary);
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
            color: var(--dark);
            transition: var(--transition);
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .nav-link.active {
            color: var(--primary);
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

        /* Car Details */
        .car-details-section {
            padding: 120px 0 80px;
        }

        .car-details-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: start;
        }

        .car-images {
            position: sticky;
            top: 100px;
        }

        .main-image {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            margin-bottom: 20px;
        }

        .main-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .car-info h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .car-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            color: var(--gray);
        }

        .car-price {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 30px;
        }

        .car-specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--light);
            border-radius: var(--border-radius);
        }

        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .spec-item:last-child {
            border-bottom: none;
        }

        .spec-label {
            font-weight: 600;
            color: var(--secondary);
        }

        .spec-value {
            color: var(--gray);
        }

        .car-description {
            margin-bottom: 30px;
            line-height: 1.7;
            color: var(--gray);
        }

        .car-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
        }

        .car-actions .btn {
            flex: 1;
        }

        /* Features */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray);
        }

        .feature-item i {
            color: var(--primary);
        }

        /* Related Cars */
        .related-cars {
            background-color: var(--light);
        }

        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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

        .car-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
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

        .car-card .car-details {
            padding: 20px;
        }

        .car-card .car-make {
            font-size: 14px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .car-card .car-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .car-card .car-price {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .car-card .car-specs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border-top: 1px solid var(--gray-light);
            padding-top: 15px;
        }

        .car-card .spec {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .car-card .spec i {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .car-card .spec span {
            font-size: 12px;
            color: var(--gray);
        }

        .car-card .car-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 0;
        }

        .car-card .car-actions .btn {
            flex: 1;
            padding: 10px;
            font-size: 14px;
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
            }
            
            .car-details-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .car-images {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .car-specs-grid {
                grid-template-columns: 1fr;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .car-actions {
                flex-direction: column;
            }
            
            .cars-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .section {
                padding: 60px 0;
            }
            
            .section-title {
                font-size: 30px;
            }
            
            .car-details-section {
                padding: 100px 0 60px;
            }
            
            .car-info h1 {
                font-size: 28px;
            }
            
            .car-price {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">Auto<span>Elite</span></a>
            
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="cars.php" class="nav-link">Cars</a></li>
                <li class="nav-item"><a href="admin/login.php" class="nav-link">Admin</a></li>
            </ul>
            
            <div class="header-actions">
                <a href="contact.html" class="btn btn-primary cta-button">
                    Contact Us <i class="fas fa-phone-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Car Details Section -->
    <section class="car-details-section">
        <div class="container">
            <div class="car-details-container">
                <div class="car-images">
                    <div class="main-image">
                        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?>">
                    </div>
                </div>
                
                <div class="car-info">
                    <h1><?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?> <?= htmlspecialchars($car['year']) ?></h1>
                    <div class="car-meta">
                        <span class="car-condition"><?= htmlspecialchars($car['car_condition']) ?></span>
                        <span>•</span>
                        <span class="car-mileage"><?= number_format($car['mileage']) ?> km</span>
                    </div>
                    
                    <div class="car-price">LKR <?= number_format($car['price'], 2) ?></div>
                    
                    <div class="car-specs-grid">
                        <div class="spec-item">
                            <span class="spec-label">Make</span>
                            <span class="spec-value"><?= htmlspecialchars($car['make']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Model</span>
                            <span class="spec-value"><?= htmlspecialchars($car['model']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Year</span>
                            <span class="spec-value"><?= htmlspecialchars($car['year']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Body Type</span>
                            <span class="spec-value"><?= htmlspecialchars($car['body_type']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Fuel Type</span>
                            <span class="spec-value"><?= htmlspecialchars($car['fuel_type']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Transmission</span>
                            <span class="spec-value"><?= htmlspecialchars($car['transmission']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Mileage</span>
                            <span class="spec-value"><?= number_format($car['mileage']) ?> km</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Condition</span>
                            <span class="spec-value"><?= htmlspecialchars($car['car_condition']) ?></span>
                        </div>
                    </div>
                    
                    <?php if (!empty($car['description'])): ?>
                    <div class="car-description">
                        <h3>Description</h3>
                        <p><?= nl2br(htmlspecialchars($car['description'])) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="car-actions">
                        <a href="contact.html" class="btn btn-primary">
                            <i class="fas fa-phone"></i> Contact Seller
                        </a>
                        <a href="cars.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Cars Section -->
    <?php if (!empty($related_cars)): ?>
    <section class="section related-cars">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Related Vehicles</h2>
                <p class="section-subtitle">You might also be interested in these similar vehicles</p>
            </div>
            
            <div class="cars-grid">
                <?php foreach ($related_cars as $related_car): ?>
                <div class="car-card">
                    <div style="position: relative;">
                        <img src="<?= htmlspecialchars($related_car['image']) ?>" alt="<?= htmlspecialchars($related_car['make']) ?> <?= htmlspecialchars($related_car['model']) ?>" class="car-image">
                        <span class="card-badge"><?= htmlspecialchars($related_car['car_condition']) ?></span>
                    </div>
                    <div class="car-details">
                        <p class="car-make"><?= htmlspecialchars($related_car['make']) ?></p>
                        <h3 class="car-name"><?= htmlspecialchars($related_car['model']) ?> <?= htmlspecialchars($related_car['year']) ?></h3>
                        <p class="car-price">LKR <?= number_format($related_car['price'], 2) ?></p>
                        <div class="car-specs">
                            <div class="spec">
                                <i class="fas fa-tachometer-alt"></i>
                                <span><?= number_format($related_car['mileage']) ?> km</span>
                            </div>
                            <div class="spec">
                                <i class="fas fa-gas-pump"></i>
                                <span><?= htmlspecialchars($related_car['fuel_type']) ?></span>
                            </div>
                            <div class="spec">
                                <i class="fas fa-cog"></i>
                                <span><?= htmlspecialchars($related_car['transmission']) ?></span>
                            </div>
                        </div>
                        <div class="car-actions">
                            <a href="car-details.php?id=<?= $related_car['id'] ?>" class="btn btn-primary">View Details</a>
                        </div>
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
                        <li><a href="cars.php">Cars</a></li>
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="services.html">Services</a></li>
                        <li><a href="financing.html">Financing</a></li>
                        <li><a href="testimonials.html">Testimonials</a></li>
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
</body>
</html>