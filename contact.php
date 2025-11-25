<?php
include 'config/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Here you can add code to save the contact form data to database or send email
    // For now, we'll just set a success message
    $success_message = "Thank you for your message! We'll get back to you soon.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | AutoElite</title>
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

        /* Updated Send Message Button Styles */
        .contact-form .btn-primary {
            width: 100%;
            padding: 15px 24px; /* Increased padding for better height */
            font-size: 16px;
            font-weight: 600;
            border-radius: var(--border-radius);
            background-color: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .contact-form .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(20, 110, 245, 0.3);
        }

        /* Rest of the CSS remains the same */
        /* Hero Section */
        .contact-hero {
            background: linear-gradient(rgb(0 0 0 / 38%), rgb(14 53 135 / 86%)), url(assets/uploads/inventory-hero.jpg);
            background-size: cover;
            background-position: center;
            color: white;
            padding: 200px 0 100px;
            text-align: center;
        }

        .contact-hero h1 {
            font-size: 3.0rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .contact-hero p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Contact Section */
        .contact-section {
            padding: 80px 0;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
        }

        .contact-info {
            padding-right: 40px;
        }

        .contact-info h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--secondary);
        }

        .contact-info p {
            color: var(--gray);
            margin-bottom: 40px;
            line-height: 1.7;
        }

        .contact-methods {
            display: flex;
            flex-direction: column;
            gap: 30px;
            margin-bottom: 40px;
        }

        .contact-method {
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }

        .method-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .method-icon i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .method-info h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--secondary);
        }

        .method-info p {
            color: var(--gray);
            margin: 0;
        }

        /* Success Message */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            border: 1px solid #c3e6cb;
        }

        /* Contact Form */
        .contact-form {
            background: var(--light);
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--secondary);
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 16px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(20, 110, 245, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        /* Map Section */
        .map-section {
            padding: 80px 0;
            background-color: var(--light);
        }

        .map-container {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            height: 400px;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* FAQ Section */
        .faq-section {
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

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        .faq-grid {
            display: grid;
            gap: 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .faq-question {
            padding: 25px;
            font-weight: 600;
            color: var(--secondary);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
        }

        .faq-question:hover {
            background: var(--light);
        }

        .faq-answer {
            padding: 0 25px;
            max-height: 0;
            overflow: hidden;
            transition: var(--transition);
            color: var(--gray);
            line-height: 1.6;
        }

        .faq-item.active .faq-answer {
            padding: 0 25px 25px;
            max-height: 500px;
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
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
            
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .contact-info {
                padding-right: 0;
            }
            
            .contact-hero h1 {
                font-size: 2.5rem;
            }
            
            .contact-info h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 576px) {
            .contact-hero {
                padding: 150px 0 80px;
            }
            
            .contact-hero h1 {
                font-size: 2rem;
            }
            
            .contact-form {
                padding: 25px;
            }
            
            .method-icon {
                width: 50px;
                height: 50px;
            }
            
            .method-icon i {
                font-size: 1.2rem;
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
                <li class="nav-item"><a href="about.php" class="nav-link">About Us</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link active">Contact</a></li>
                <li class="nav-item"><a href="admin/login.php" class="nav-link">Admin</a></li>
            </ul>
            
            <div class="header-actions">
                <a href="contact.php" class="btn btn-primary cta-button">
                    Contact Us <i class="fas fa-phone-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Rest of the contact.php content remains the same -->
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <h1>Contact Us</h1>
            <p>Get in touch with our team for any inquiries about our vehicles or services</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <h2>Get In Touch</h2>
                    <p>Have questions about our vehicles or services? Our team is here to help you find the perfect car and provide exceptional customer service every step of the way.</p>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="method-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="method-info">
                                <h3>Visit Our Showroom</h3>
                                <p>123 Auto Avenue, Motor City, MC 12345</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="method-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="method-info">
                                <h3>Call Us</h3>
                                <p>(555) 123-4567</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="method-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="method-info">
                                <h3>Email Us</h3>
                                <p>info@autoelite.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="method-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="method-info">
                                <h3>Business Hours</h3>
                                <p>Mon-Fri: 9am-7pm<br>Sat: 10am-5pm<br>Sun: 12pm-4pm</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <?php if (isset($success_message)): ?>
                        <div class="success-message">
                            <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="contact.php">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select id="subject" name="subject" class="form-control" required>
                                <option value="">Select a subject</option>
                                <option value="general" <?= ($_POST['subject'] ?? '') === 'general' ? 'selected' : '' ?>>General Inquiry</option>
                                <option value="test-drive" <?= ($_POST['subject'] ?? '') === 'test-drive' ? 'selected' : '' ?>>Schedule Test Drive</option>
                                <option value="financing" <?= ($_POST['subject'] ?? '') === 'financing' ? 'selected' : '' ?>>Financing Questions</option>
                                <option value="service" <?= ($_POST['subject'] ?? '') === 'service' ? 'selected' : '' ?>>Service & Maintenance</option>
                                <option value="other" <?= ($_POST['subject'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" class="form-control" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            Send Message <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.177631294649!2d-74.00594908459418!3d40.71274397922685!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a316e12bcd1%3A0x5a6e83d25b9c0b!2sAuto%20Avenue!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Quick answers to common questions about our services</p>
            </div>
            
            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question">
                        What is your vehicle inspection process?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Every vehicle in our inventory undergoes a comprehensive 150-point inspection process. This includes mechanical inspection, safety checks, cosmetic evaluation, and test drives to ensure optimal performance and reliability.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        Do you offer financing options?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Yes, we work with multiple financial institutions to provide competitive financing options. Our finance specialists will help you find the best rates and terms based on your credit profile and budget.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        Can I schedule a test drive?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Absolutely! You can schedule a test drive by calling us, using our online form, or visiting our showroom. We recommend scheduling in advance to ensure the vehicle you're interested in is available.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        What is your return policy?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        We offer a 7-day money-back guarantee on all vehicles. If you're not completely satisfied with your purchase, you can return the vehicle within 7 days for a full refund, no questions asked.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        Do you accept trade-ins?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Yes, we accept trade-ins and offer competitive valuations. Bring your vehicle to our showroom for a free appraisal, and we'll apply the value toward your new purchase.
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

        // FAQ functionality
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const faqItem = question.parentElement;
                faqItem.classList.toggle('active');
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            
            // Basic email validation
            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }
            
            // Basic phone validation (optional)
            if (phone && !phone.match(/^[\d\s\-\+\(\)]{10,}$/)) {
                e.preventDefault();
                alert('Please enter a valid phone number.');
                return;
            }
        });
    </script>
</body>
</html>