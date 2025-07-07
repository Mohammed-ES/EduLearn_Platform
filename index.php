<?php 
// Debug information
if (isset($_GET['debug'])) {
    echo "<pre style='position:fixed;top:0;left:0;padding:20px;background:white;z-index:9999;'>";
    echo "Current path: " . $_SERVER['PHP_SELF'] . "\n";
    echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo "</pre>";
}

include 'includes/header.php'; ?>

<main class="main">
    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="container">
            <div class="hero-content">                <div class="hero-text" data-aos="fade-right" data-aos-duration="1000">                    <h1 class="hero-title">Modernize the way you learn.</h1>
                    <p class="hero-subtitle">An innovative educational platform designed for the future of learning</p>
                    <div class="hero-typing-text" id="typingText">transforming education through technology</div>
                    <a href="auth/login.php" class="btn btn-primary btn-animated">Start Now</a>
                </div>
                <div class="hero-image" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="300">
                    <div class="hero-svg-container">
                        <svg width="400" height="300" viewBox="0 0 600 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Education SVG illustration -->
                            <circle cx="300" cy="250" r="150" stroke="#007BFF" stroke-width="2" stroke-dasharray="10 5"/>
                            <path d="M150 250H450" stroke="#0F4C75" stroke-width="3"/>
                            <path d="M300 100V400" stroke="#0F4C75" stroke-width="3"/>
                            <rect x="250" y="200" width="100" height="100" rx="10" fill="#D4AF37" opacity="0.7"/>
                            <circle cx="200" cy="150" r="30" fill="#007BFF" opacity="0.5"/>
                            <circle cx="400" cy="350" r="30" fill="#007BFF" opacity="0.5"/>
                            <path d="M230 300C230 300 270 320 300 320C330 320 370 300 370 300" stroke="#0F4C75" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="wave-separator">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 100">
                <path fill="#F4F6F8" d="M0,64L48,80C96,96,192,128,288,128C384,128,480,96,576,85.3C672,75,768,85,864,96C960,107,1056,117,1152,112C1248,107,1344,85,1392,74.7L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title">Our Services</h2>
                <p class="section-subtitle">Discover our comprehensive learning solutions</p>
            </div>
            <div class="services-grid">
                <div class="service-card" data-aos="zoom-in" data-aos-delay="100">
                    <div class="service-icon">
                        <i class='bx bx-bulb'></i>
                    </div>
                    <h3 class="service-title">Interactive Courses</h3>
                    <p class="service-description">Engaging learning materials with real-time feedback and interactive elements</p>
                </div>
                <div class="service-card" data-aos="zoom-in" data-aos-delay="200">
                    <div class="service-icon">
                        <i class='bx bx-line-chart'></i>
                    </div>
                    <h3 class="service-title">Grade & Exam Tracking</h3>
                    <p class="service-description">Comprehensive monitoring of academic progress with detailed analytics</p>
                </div>
                <div class="service-card" data-aos="zoom-in" data-aos-delay="300">
                    <div class="service-icon">
                        <i class='bx bx-bell'></i>
                    </div>
                    <h3 class="service-title">Smart Announcements</h3>
                    <p class="service-description">Intelligent notification system keeping everyone updated on important events</p>
                </div>
                <div class="service-card" data-aos="zoom-in" data-aos-delay="400">
                    <div class="service-icon">
                        <i class='bx bx-brain'></i>
                    </div>
                    <h3 class="service-title">Quizzes & Planning</h3>
                    <p class="service-description">Personalized study plans with adaptive quizzes to maximize learning efficiency</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <div class="about-content">
                <div class="about-image" data-aos="fade-right" data-aos-duration="1000">
                    <div class="about-svg-container">
                        <svg width="400" height="300" viewBox="0 0 600 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- About SVG illustration -->
                            <rect x="150" y="150" width="300" height="200" rx="20" fill="#0F4C75" opacity="0.1"/>
                            <circle cx="300" cy="250" r="100" fill="#007BFF" opacity="0.2"/>
                            <path d="M250 200L350 300" stroke="#D4AF37" stroke-width="3" stroke-linecap="round"/>
                            <path d="M350 200L250 300" stroke="#D4AF37" stroke-width="3" stroke-linecap="round"/>
                            <circle cx="200" cy="350" r="30" fill="#0F4C75" opacity="0.3"/>
                            <circle cx="400" cy="350" r="30" fill="#0F4C75" opacity="0.3"/>
                            <circle cx="300" cy="150" r="30" fill="#0F4C75" opacity="0.3"/>
                        </svg>
                    </div>
                </div>
                <div class="about-text" data-aos="fade-left" data-aos-duration="1000">
                    <h2 class="section-title">About Our Project</h2>
                    <p>EduLearn Platform is revolutionizing education through advanced technology and innovative teaching methodologies. We bridge the gap between traditional learning and digital transformation, creating an environment where students thrive and educators excel.</p>
                    <div class="stats-container">
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                            <span class="stat-number" data-count="1200">0</span>
                            <span class="stat-label">Students</span>
                        </div>
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                            <span class="stat-number" data-count="65">0</span>
                            <span class="stat-label">Modules</span>
                        </div>
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="400">
                            <span class="stat-number" data-count="95">0</span>
                            <span class="stat-label">% Satisfaction</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title">Contact Us</h2>
                <p class="section-subtitle">Get in touch with our team</p>
            </div>
            <div class="contact-content">
                <div class="contact-info" data-aos="fade-right" data-aos-duration="1000">
                    <div class="contact-item">
                        <i class='bx bx-envelope'></i>
                        <div>
                            <h3>Email</h3>
                            <a href="mailto:contact@edulearn.com">contact@edulearn.com</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class='bx bx-phone'></i>
                        <div>
                            <h3>Phone</h3>
                            <a href="tel:+123456789">+1 234 567 890</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class='bx bx-map'></i>
                        <div>
                            <h3>Address</h3>
                            <address>123 Education St., Knowledge City</address>
                        </div>
                    </div>
                    <div class="contact-social">
                        <h3>Follow Us</h3>
                        <div class="social-icons">
                            <a href="#" aria-label="Facebook"><i class='bx bxl-facebook-circle'></i></a>
                            <a href="#" aria-label="Twitter"><i class='bx bxl-twitter'></i></a>
                            <a href="#" aria-label="Instagram"><i class='bx bxl-instagram'></i></a>
                            <a href="#" aria-label="LinkedIn"><i class='bx bxl-linkedin-square'></i></a>
                        </div>
                    </div>
                </div>
                <div class="contact-form-container" data-aos="fade-left" data-aos-duration="1000">
                    <form class="contact-form" action="#" method="POST">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
