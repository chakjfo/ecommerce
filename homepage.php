<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Accents Clothing</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            font-family: "Anton", serif;
            font-weight: 400;
            font-style: normal;
        }
        
        .hero-section {
            min-height: 100vh;
            /* Enhanced gradient background */
            background-image: linear-gradient(135deg, rgba(233, 233, 233, 0.8), rgba(4, 9, 30, 0.8)), url(images/bg1.jpg);
            background-position: center;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .navbar {
            /* Navbar gradient */
            background: transparent !important;
           
        }
        
        .navbar-brand img {
            width: 150px;
        }
        
        .nav-link {
            color: #000;
            font-size: 18px;
            position: relative;
            padding: 8px 12px;
        }
        
        .nav-link::after {
            content: '';
            width: 0%;
            height: 2px;
            background: #000;
            display: block;
            margin: auto;
            transition: 0.5s;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .hero-content {
            color: rgba(0, 1, 1, 0.76);
            text-align: center;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            margin: 20px 0;
        }
        
        .btn-outline-dark {
            border: 1px solid white;
            padding: 12px 34px;
            font-size: 1.2rem;
            transition: 1s;
        }
        
        .btn-outline-dark:hover {
            background-color: #000;
            color: white;
        }
        
        .feature-card {
            transition: transform 0.3s;
            margin-bottom: 1.5rem;
            /* Card gradient */
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            border: none;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .about-img {
            max-width: 100%;
            height: auto;
        }
        
        .section-title {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            /* Gradient for section title underline */
            background: linear-gradient(90deg, #000, #555);
        }
        
        /* About section gradient */
        #about {
            background: linear-gradient(to bottom, rgba(147, 150, 161, 10), rgba(147, 150, 161, 0.8));
        }
        
        /* Features section gradient */
        #features {
            background: linear-gradient(to top, rgba(147, 150, 161, 10), rgba(147, 150, 161, 0.8));
        
        /* Contact section gradient */
        #contact {
            background: linear-gradient(to right, #f5f5f5, #ffffff);
        }
        
        /* Form controls with subtle gradient */
        .form-control {
            background: linear-gradient(145deg, #f5f5f5, #ffffff);
            border: 1px solid #eee;
        }
        
        /* Button gradient */
        .btn-dark {
            background: linear-gradient(to right, #000000, #333333);
            border: none;
        }
        
        .btn-dark:hover {
            background: linear-gradient(to right, #333333, #000000);
        }
        
        footer {
            /* Footer gradient */
            background: linear-gradient(to right, #000000, #222222);
            color: white;
            padding: 2rem 0;
        }
        
        .social-links a {
            color: white;
            margin: 0 10px;
            font-size: 1.5rem;
            transition: 0.3s;
        }
        
        .social-links a:hover {
            color: #aaa;
        }
        
        /* Gradient overlay for image */
        .image-container {
            position: relative;
            overflow: hidden;
            border-radius: 5px;
        }
        
        .image-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(0,0,0,0.2), transparent);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="homepage.php">
                <img src="images/the_accents_logo.png" alt="The Accents Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="shop_customer.php">SHOP</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">ABOUT US</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">CONTACT US</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">LOG IN</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="signup.php">SIGN UP</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>We make stories, not shirts.</h1>
                <p class="lead">The Accents is a lifestyle brand that embraces diversity in different subcultures in our society.</p>
                <a href="shop_customer.php" class="btn btn-outline-dark">SHOP NOW</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-5" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="section-title">About The Accents</h2>
                    <p>Founded with a vision to celebrate individuality, The Accents Clothing stands as a testament to the rich tapestry of human expression. We believe fashion is more than just clothing—it's a language through which we communicate our identity to the world.</p>
                    <p>Our collections are inspired by various subcultures, movements, and artistic expressions that have shaped modern society. Each piece tells a story, carries a history, and adds an accent to your personal style narrative.</p>
                    <a href="#" class="btn btn-dark mt-3">Learn More</a>
                </div>
                <div class="col-lg-6">
                    <div class="image-container">
                        <img src="images/about us.jpg" class="about-img rounded shadow w-100" alt="About The Accents">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features/Services Section -->
    <section class="py-5" id="features">
        <div class="container">
            <h2 class="text-center mb-5">Our Offerings</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-tshirt fa-3x mb-3" style="background: linear-gradient(45deg, #333, #777); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                            <h3 class="card-title">Premium Apparel</h3>
                            <p class="card-text">Our clothing is crafted from high-quality materials, ensuring comfort and durability while making a statement.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-palette fa-3x mb-3" style="background: linear-gradient(45deg, #333, #777); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                            <h3 class="card-title">Unique Designs</h3>
                            <p class="card-text">Each piece features artwork that tells a story, drawing inspiration from diverse cultural influences.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-leaf fa-3x mb-3" style="background: linear-gradient(45deg, #333, #777); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                            <h3 class="card-title">Sustainable Practices</h3>
                            <p class="card-text">We're committed to ethical manufacturing and environmentally conscious production methods.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-5" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="section-title">Get In Touch</h2>
                    <p>We'd love to hear from you. Fill out the form and we'll get back to you as soon as possible.</p>
                    <form>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Your Name">
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your Email">
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="4" placeholder="Your Message"></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark">Send Message</button>
                    </form>
                </div>
                <div class="col-lg-6">
                    <h2 class="section-title">Visit Our Store</h2>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Waze: THE ACCENTS GALLERY STUDIO <br>71 Kamias Rd., 2F RGG Bldg.</p>
                    <p><i class="fas fa-phone me-2"></i> (123) 456-7890</p>
                    <p><i class="fas fa-envelope me-2"></i> theaccentsclothing.com</p>
                    <div class="social-links mt-4">
                        <a href=""><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center py-4">
        <div class="container">
            <p class="mb-0">&copy; 2025 The Accents Clothing. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>