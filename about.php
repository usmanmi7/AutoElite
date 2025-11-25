<?php
include 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | AutoElite</title>
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

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
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
            text-decoration: none; /* Remove underline from logo */
        }

        .logo span {
            color: var(--primary);
        }

        .nav-menu {
            display: flex;
            list-style: none; /* Remove dots from navigation */
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
            text-decoration: none; /* Remove underline from nav links */
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

        /* Rest of the CSS remains the same */
        /* Hero Section */
        .about-hero {
            background: linear-gradient(rgb(0 0 0 / 38%), rgb(14 53 135 / 86%)), url(assets/uploads/inventory-hero.jpg);
            background-size: cover;
            background-position: center;
            color: white;
            padding: 200px 0 100px;
            text-align: center;
        }

        .about-hero h1 {
            font-size: 3.0rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .about-hero p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* About Content */
        .about-content {
            padding: 80px 0;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            margin-bottom: 0px;
        }

        .about-image {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            height: 500px;
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .about-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--secondary);
        }

        .about-text p {
            color: var(--gray);
            margin-bottom: 25px;
            line-height: 1.7;
        }

        /* Stats Section */
        .stats-section {
            background-color: var(--light);
            padding: 80px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            text-align: center;
        }

        .stat-item {
            padding: 40px 20px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            color: var(--secondary);
            font-weight: 600;
        }

        /* Team Section */
        .team-section {
            padding: 80px 0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--secondary);
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

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .team-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            text-align: center;
        }

        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .team-image {
            height: 250px;
            width: 100%;
            object-fit: cover;
        }

        .team-info {
            padding: 25px;
        }

        .team-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--secondary);
        }

        .team-role {
            color: var(--primary);
            font-weight: 500;
            margin-bottom: 15px;
        }

        .team-bio {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* Values Section */
        .values-section {
            background-color: var(--light);
            padding: 80px 0;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .value-card {
            background: white;
            padding: 40px 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            transition: var(--transition);
        }

        .value-card:hover {
            transform: translateY(-5px);
        }

        .value-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .value-icon i {
            font-size: 2rem;
            color: var(--primary);
        }

        .value-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--secondary);
        }

        .value-description {
            color: var(--gray);
            line-height: 1.6;
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
            
            .about-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .about-hero h1 {
                font-size: 2.5rem;
            }
            
            .about-text h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .about-hero {
                padding: 150px 0 80px;
            }
            
            .about-hero h1 {
                font-size: 2rem;
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
            
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="inventory.php" class="nav-link">Inventory</a></li>
                <li class="nav-item"><a href="about.php" class="nav-link active">About Us</a></li>
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

    <!-- Rest of the about.php content remains the same -->
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1>About AutoElite</h1>
            <p>Your trusted partner in premium automotive experiences since 2005 from srilanka</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="about-content">
        <div class="container">
            <div class="about-grid">
                <div class="about-image">
                    <img src="assets/uploads/about-page-hero.jpg" alt="AutoElite Dealership">
                </div>
                <div class="about-text">
                    <span class="section-category">About Us</span>
                    <h2>Our Story</h2>
                    <p>Founded in 2005, AutoElite has grown from a small local dealership to one of the most trusted names in premium automotive sales. Our journey began with a simple mission: to provide exceptional vehicles with transparent pricing and unparalleled customer service.</p>
                    <p>Over the years, we've built lasting relationships with thousands of satisfied customers who trust us for their automotive needs. Our commitment to quality, integrity, and customer satisfaction has been the driving force behind our success.</p>
                    <p>Today, we continue to innovate and adapt to the evolving automotive landscape, offering a carefully curated selection of premium vehicles that meet the highest standards of quality and performance.</p>
                    
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">15+</div>
                    <div class="stat-label">Years Experience</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">5,000+</div>
                    <div class="stat-label">Vehicles Sold</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Customer Satisfaction</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Brands Available</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Our Values</h2>
                <p class="section-subtitle">The principles that guide everything we do</p>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="value-title">Integrity</h3>
                    <p class="value-description">We believe in transparent, honest dealings with every customer. No hidden fees, no surprises - just straightforward, trustworthy service.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="value-title">Quality</h3>
                    <p class="value-description">Every vehicle in our inventory undergoes rigorous inspection to ensure it meets our high standards for quality and reliability.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="value-title">Customer First</h3>
                    <p class="value-description">Your satisfaction is our top priority. We're committed to providing exceptional service before, during, and after your purchase.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Meet Our Team</h2>
                <p class="section-subtitle">The passionate professionals behind AutoElite's success</p>
            </div>
            <div class="team-grid">
                <div class="team-card">
                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80" alt="John Anderson" class="team-image">
                    <div class="team-info">
                        <h3 class="team-name">John Anderson</h3>
                        <p class="team-role">Founder & CEO</p>
                        <p class="team-bio">With over 20 years in the automotive industry, John founded AutoElite with a vision to revolutionize car buying experience.</p>
                    </div>
                </div>
                <div class="team-card">
                    <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=761&q=80" alt="Sarah Mitchell" class="team-image">
                    <div class="team-info">
                        <h3 class="team-name">Sarah Mitchell</h3>
                        <p class="team-role">Sales Director</p>
                        <p class="team-bio">Sarah leads our sales team with expertise and dedication, ensuring every customer finds their perfect vehicle.</p>
                    </div>
                </div>
                <div class="team-card">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=870&q=80" alt="Michael Roberts" class="team-image">
                    <div class="team-info">
                        <h3 class="team-name">Michael Roberts</h3>
                        <p class="team-role">Head of Vehicle Inspection</p>
                        <p class="team-bio">Michael ensures every vehicle meets our strict quality standards through comprehensive inspection processes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

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

        // Mobile menu toggle
        document.querySelector('.mobile-menu').addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('active');
        });
    </script>
</body>
</html>