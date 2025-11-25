<?php
include 'config/db.php';

// Get featured cars with their cover images
$stmt = $pdo->query("
    SELECT c.*, 
           COALESCE(ci.image_path, c.image) as display_image 
    FROM cars c 
    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_cover = 1 
    WHERE c.status = 'active' 
    ORDER BY c.created_at DESC 
    LIMIT 6
");
$featured_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoElite | Premium Car Dealership</title>
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
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.123);
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
            object-fit: cover;
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

        .btn-white {
            background-color: white;
            color: var(--primary);
        }

        .btn-white:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
        }

        .section {
            padding: 80px 0;
        }

        .vehicle-options .section-header {
            text-align: left;
            margin-bottom: 50px;
        }
        .section-header {
            text-align: center;
            margin-bottom: 50px;
                }
        .section-category {
            display: inline-block;
            padding: 8px 16px;
            background-color: var(--primary-light);
            color: var(--primary);
            font-size: 14px;
            font-weight: 600;
            border-radius: 20px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .vehicle-options .section-subtitle {
            font-size: 18px;
            color: var(--gray);
            
            margin: 0 auto;
        }

        .section-subtitle {
            font-size: 18px;
            color: var(--gray);
            
            margin: 0 auto;
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

        /* Video Hero Section */
        .hero {
            position: relative;
            height: 750px;
            min-height: 650px;
            color: white;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .video-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(29, 53, 87, 0.58) 0%, rgba(20, 110, 245, 0.45) 100%);
            z-index: 0;
        }

        .hero-content {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            padding-top: 80px;
        }

        .hero-title {
            font-size: 4.0rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.1;
          
        }

        .hero-subtitle {
            font-size: 22px;
            margin-bottom: 40px;
            opacity: 0.9;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Vehicle Options Section */
        .vehicle-options {
            padding: 80px 0;
            background-color: var(--light);
            position: relative;
        }

        .options-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        .option-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            height: 320px;
        }

        .option-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .option-image-container {
            position: relative;
            height: 100%;
            width: 100%;
            overflow: hidden;
        }

        .option-image {
            height: 100%;
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .option-card:hover .option-image {
            transform: scale(1.08);
        }

        .option-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.4) 100%);
            z-index: 1;
        }

        .option-content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 25px;
            z-index: 2;
            color: white;
        }

        .option-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .option-description {
            font-size: 14px;
            opacity: 0.9;
            line-height: 1.5;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .option-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--primary);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 3;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        /* Featured Cars */
        .featured-cars {
            background-color: white;
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
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .car-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
            background-color: #f5f5f5;
        }

        .car-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--primary);
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
            color: #1e1e1e;
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

        /* About Us Section */
        .about-us {
            background-color: var(--light);
        }

        .about-container {
            display: flex;
            align-items: center;
            gap: 70px;
        }

        .about-image {
            flex: 1;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            height: 620px;
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .about-content {
            flex: 1;
        }

        .about-content .section-category {
            text-align: left;
            margin-left: 0;
        }

        .about-content .section-title {
            text-align: left;
            margin-bottom: 20px;
        }

        .about-text {
            margin-bottom: 30px;
            color: var(--gray);
            line-height: 1.7;
        }

        .about-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .about-feature {
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .about-feature i {
            color: var(--primary);
            font-size: 20px;
            margin-top: 3px;
        }

        .about-feature h4 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .about-feature p {
            color: var(--gray);
            font-size: 14px;
        }

        /* Testimonials Section */
        .testimonials {
            background-color: var(--light);
        }

        .testimonial-slider {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
        }

        .testimonial-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: var(--box-shadow);
            text-align: center;
            display: none;
        }

        .testimonial-card.active {
            display: block;
        }

        .testimonial-text {
            font-size: 18px;
            font-style: italic;
            margin-bottom: 25px;
            color: var(--dark);
            line-height: 1.7;
        }

        .testimonial-rating {
            color: #ffc107;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .author-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
        }

        .author-info h4 {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 18px;
        }

        .author-info p {
            color: var(--gray);
            font-size: 14px;
        }

        .testimonial-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .testimonial-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--gray-light);
            cursor: pointer;
            transition: var(--transition);
        }

        .testimonial-dot.active {
            background-color: var(--primary);
            transform: scale(1.2);
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)),
                url('https://images.pexels.com/photos/30591398/pexels-photo-30591398.jpeg');
            background-position: center; 
            color: white;
            text-align: center;
            padding: 80px 0;
        }

        .cta-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
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
        @media (max-width: 1200px) {
            .options-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

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
            
            .hero-title {
                font-size: 36px;
            }
            
            .section-title {
                font-size: 36px;
            }
            
            .options-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .about-container {
                flex-direction: column;
            }
            
            .about-image {
                height: auto;
            }
            
            .hero {
                height: 600px;
                min-height: 500px;
            }
        }

        @media (max-width: 768px) {
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .hero {
                min-height: 500px;
                height: 550px;
            }
            
            .options-container {
                grid-template-columns: 1fr;
            }
            
            .hero-title {
                font-size: 32px;
            }
            
            .hero-subtitle {
                font-size: 18px;
            }
            
            .about-features {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .section {
                padding: 60px 0;
            }
            
            .hero-title {
                font-size: 28px;
            }
            
            .car-actions {
                flex-direction: column;
            }
            
            .cta-button {
                padding: 10px 20px;
                font-size: 14px;
            }
            
            .hero {
                min-height: 450px;
                height: 500px;
            }
            
            .option-card {
                height: 280px;
            }
            
            .testimonial-card {
                padding: 25px;
            }
            
            .testimonial-text {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="container header-container">
            <a href="index.php" class="logo">Auto<span>Elite</span></a>
            
            <div class="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link active">Home</a></li>
                <li class="nav-item"><a href="inventory.php" class="nav-link">Inventory</a></li>
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

    <!-- Video Hero Section -->
    <section class="hero">
        <video class="video-background" autoplay muted loop playsinline>
            <source src="assets/uploads/hero-video2.mp4" type="video/mp4">
            <source src="https://assets.mixkit.co/videos/preview/mixkit-sports-car-driving-in-a-city-34537-large.webm" type="video/webm">
            Your browser does not support the video tag.
        </video>
        <div class="video-overlay"></div>
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Find Your Perfect Car Today</h1>
                <p class="hero-subtitle">Trust | Quality | Best Price - Discover our premium selection of vehicles</p>
                <div class="cta-buttons">
                    <a href="inventory.php" class="btn btn-primary">
                        Browse Inventory <i class="fas fa-car"></i>
                    </a>
                    <a href="contact.php" class="btn btn-white">
                        Contact Us <i class="fas fa-headset"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Vehicle Options Section -->
    <section class="vehicle-options">
        <div class="container">
            <div class="section-header">
                <span class="section-category">Vehicle Categories</span>
                <h2 class="section-title">Vehicle Options</h2>
                <p class="section-subtitle">Find the perfect vehicle that matches your lifestyle and needs</p>
            </div>
            
            <div class="options-container">
                <!-- Sedan Option -->
                <div class="option-card">
                    <div class="option-image-container">
                        <img src="assets/uploads/sedan4.jpg" alt="Sedans" class="option-image">
                        <div class="option-overlay"></div>
                    </div>
                    <div class="option-content">
                        <h3 class="option-title">Sedans</h3>
                        <p class="option-description">Comfortable and efficient cars for daily commuting</p>
                    </div>
                    <div class="option-badge">Popular</div>
                </div>
                
                <!-- SUV Option -->
                <div class="option-card">
                    <div class="option-image-container">
                        <img src="assets/uploads/suv2.jpg" alt="SUVs" class="option-image">
                        <div class="option-overlay"></div>
                    </div>
                    <div class="option-content">
                        <h3 class="option-title">SUVs</h3>
                        <p class="option-description">Spacious and versatile for family and adventure</p>
                    </div>
                    <div class="option-badge">Family</div>
                </div>
                
                <!-- Luxury Option -->
                <div class="option-card">
                    <div class="option-image-container">
                        <img src="assets/uploads/lux.jpg" alt="Luxury" class="option-image">
                        <div class="option-overlay"></div>
                    </div>
                    <div class="option-content">
                        <h3 class="option-title">Luxury</h3>
                        <p class="option-description">Premium vehicles with advanced features</p>
                    </div>
                    <div class="option-badge">Premium</div>
                </div>
                
                <!-- Electric Option -->
                <div class="option-card">
                    <div class="option-image-container">
                        <img src="assets/uploads/suv3.jpg" alt="Electric" class="option-image">
                        <div class="option-overlay"></div>
                    </div>
                    <div class="option-content">
                        <h3 class="option-title">Electric</h3>
                        <p class="option-description">Eco-friendly and cost-efficient electric vehicles</p>
                    </div>
                    <div class="option-badge">Eco</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Cars Section -->
    <section class="section featured-cars">
        <div class="container">
            <div class="section-header">
                <span class="section-category">Premium Selection</span>
                <h2 class="section-title">Featured Vehicles</h2>
                <p class="section-subtitle">Explore our handpicked selection of premium cars</p>
            </div>
            
            <div class="cars-grid">
                <?php foreach ($featured_cars as $car): ?>
                <div class="car-card">
                    <div style="position: relative;">
                        <img src="<?= htmlspecialchars($car['display_image']) ?>" alt="<?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?>" class="car-image" onerror="this.src='https://images.unsplash.com/photo-1563720223485-884b46ce7c86?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80'">
                        <span class="car-badge"><?= htmlspecialchars($car['car_condition']) ?></span>
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
                                <span><?= htmlspecialchars($car['fuel_type']) ?></span>
                            </div>
                            <div class="spec">
                                <i class="fas fa-cog"></i>
                                <span><?= htmlspecialchars($car['transmission']) ?></span>
                            </div>
                        </div>
                        <div class="car-actions">
                            <a href="car-details.php?id=<?= $car['id'] ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="inventory.php" class="btn btn-secondary">
                    View All Vehicles <i class="fas fa-list"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="section about-us">
        <div class="container">
            <div class="about-container">
                <div class="about-image">
                    <img src="assets/uploads/about.jpg">
                </div>
                <div class="about-content">
                    <span class="section-category">Our Story</span>
                    <h2 class="section-title">About AutoElite</h2>
                    <p class="about-text">
                        AutoElite has been a trusted name in premium car dealership since 2005. With over 15 years of experience, we've built a reputation for excellence, integrity, and customer satisfaction. Our mission is to provide the finest selection of premium vehicles with transparent pricing and exceptional service.
                    </p>
                    <div class="about-features">
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4>Premium Selection</h4>
                                <p>Carefully curated inventory of luxury vehicles</p>
                            </div>
                        </div>
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4>Expert Team</h4>
                                <p>Knowledgeable staff with years of experience</p>
                            </div>
                        </div>
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4>Quality Assurance</h4>
                                <p>Rigorous inspection process for every vehicle</p>
                            </div>
                        </div>
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4>Customer First</h4>
                                <p>Dedicated to providing exceptional service</p>
                            </div>
                        </div>
                    </div>
                    <a href="about.php" class="btn btn-primary">Learn More About Us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section testimonials">
        <div class="container">
            <div class="section-header">
                <span class="section-category">Customer Stories</span>
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="section-subtitle">Don't just take our word for it - hear from our satisfied customers</p>
            </div>
            
            <div class="testimonial-slider">
                <div class="testimonial-track">
                    <!-- Testimonial 1 -->
                    <div class="testimonial-card active">
                        <p class="testimonial-text">"I had an amazing experience buying my BMW from AutoElite. The staff was knowledgeable, the process was smooth, and I got a great deal on my dream car! The financing options made it so easy to get exactly what I wanted."</p>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Michael Johnson" class="author-avatar">
                            <div class="author-info">
                                <h4>Michael Johnson</h4>
                                <p>BMW 3 Series Owner</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 2 -->
                    <div class="testimonial-card">
                        <p class="testimonial-text">"As a first-time car buyer, I was nervous about the process, but AutoElite made it so simple! They helped me find the perfect SUV for my growing family and explained everything clearly. The after-sales service has been exceptional too."</p>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah Williams" class="author-avatar">
                            <div class="author-info">
                                <h4>Sarah Williams</h4>
                                <p>Honda CR-V Owner</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 3 -->
                    <div class="testimonial-card">
                        <p class="testimonial-text">"I've purchased three vehicles from AutoElite over the years, and each experience has been outstanding. Their trade-in program is fair, and they always have the latest models. The team remembers me and my preferences - it feels like family!"</p>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Robert Chen" class="author-avatar">
                            <div class="author-info">
                                <h4>Robert Chen</h4>
                                <p>Multiple Vehicle Owner</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-controls">
                    <div class="testimonial-dot active" data-slide="0"></div>
                    <div class="testimonial-dot" data-slide="1"></div>
                    <div class="testimonial-dot" data-slide="2"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Ready to Find Your Dream Car?</h2>
            <p style="font-size: 18px; margin-bottom: 30px; opacity: 0.9;">Browse our extensive inventory or contact us for personalized assistance</p>
            <div class="cta-buttons">
                <a href="inventory.php" class="btn btn-white">Browse Inventory</a>
                <a href="contact.php" class="btn btn-outline" style="border-color: white; color: white;">Contact Us</a>
            </div>
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

        // Testimonial slider
        const testimonialDots = document.querySelectorAll('.testimonial-dot');
        const testimonialCards = document.querySelectorAll('.testimonial-card');

        testimonialDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                // Remove active class from all dots and cards
                testimonialDots.forEach(d => d.classList.remove('active'));
                testimonialCards.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked dot and corresponding card
                dot.classList.add('active');
                testimonialCards[index].classList.add('active');
            });
        });

        // Auto-rotate testimonials
        let currentTestimonial = 0;
        function rotateTestimonials() {
            testimonialDots.forEach(d => d.classList.remove('active'));
            testimonialCards.forEach(c => c.classList.remove('active'));
            
            currentTestimonial = (currentTestimonial + 1) % testimonialDots.length;
            
            testimonialDots[currentTestimonial].classList.add('active');
            testimonialCards[currentTestimonial].classList.add('active');
        }

        // Start auto-rotation every 5 seconds
        setInterval(rotateTestimonials, 5000);

        // Ensure video plays correctly on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.querySelector('.video-background');
            video.play().catch(function(error) {
                console.log('Video autoplay prevented:', error);
            });
        });
    </script>
</body>
</html>