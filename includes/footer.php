    <footer class="footer" id="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <a href="<?php echo $baseURL; ?>/index.php">
                        <svg width="120" height="30" viewBox="0 0 150 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 8H35V12H15V8Z" fill="#FFFFFF"/>
                            <path d="M15 18H30V22H15V18Z" fill="#FFFFFF"/>
                            <path d="M15 28H35V32H15V28Z" fill="#FFFFFF"/>
                            <path d="M45 10L55 10L55 30L45 30L45 10Z" fill="#D4AF37"/>
                            <text x="60" y="25" fill="#FFFFFF" font-family="Poppins" font-size="20" font-weight="600">EduLearn</text>
                        </svg>
                    </a>
                </div>
                <div class="footer-links">
                    <div class="footer-nav">
                        <h4>Navigation</h4>
                        <ul>
                            <li><a href="<?php echo $baseURL; ?>/index.php">Home</a></li>
                            <li><a href="<?php echo $baseURL; ?>/html/terms.html" class="legal-link">Terms & Conditions</a></li>
                            <li><a href="<?php echo $baseURL; ?>/html/privacy.html" class="legal-link">Privacy Policy</a></li>
                            <li><a href="<?php echo $baseURL; ?>/auth/login.php">Login</a></li>
                        </ul>
                    </div>
                    <div class="footer-contact">
                        <h4>Contact Us</h4>
                        <ul>
                            <li><i class='bx bx-envelope'></i> <a href="mailto:contact@edulearn.com">contact@edulearn.com</a></li>
                            <li><i class='bx bx-phone'></i> <a href="tel:+123456789">+1 234 567 890</a></li>
                            <li><i class='bx bx-map'></i> <address>123 Education St., Knowledge City</address></li>
                        </ul>
                    </div>
                    <div class="footer-social">
                        <h4>Follow Us</h4>
                        <div class="social-icons">
                            <a href="#" aria-label="Facebook"><i class='bx bxl-facebook-circle'></i></a>
                            <a href="#" aria-label="Twitter"><i class='bx bxl-twitter'></i></a>
                            <a href="#" aria-label="Instagram"><i class='bx bxl-instagram'></i></a>
                            <a href="#" aria-label="LinkedIn"><i class='bx bxl-linkedin-square'></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> EduLearn Platform. All rights reserved.</p>
            </div>
        </div>
    </footer>    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="<?php echo $baseURL; ?>/js/script.js"></script>
</body>
</html>
