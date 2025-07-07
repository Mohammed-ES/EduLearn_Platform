/**
 * EduLearn Platform - Main JavaScript
 * Modern Educational Experience
 * 
 * This file contains all the JavaScript functionality for the EduLearn Platform homepage,
 * including animations, interactivity, and responsive features.
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS (Animate On Scroll)
    AOS.init({
        duration: 800,
        easing: 'ease',
        once: true,
        offset: 100
    });

    // Variables
    const loader = document.querySelector('.loader');
    const header = document.getElementById('header');
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const statNumbers = document.querySelectorAll('.stat-number');
    const heroText = document.getElementById('typingText');

    // Hide loader after 3 seconds
    setTimeout(() => {
        loader.classList.add('fade-out');
        
        // Remove loader from DOM after animation
        setTimeout(() => {
            loader.remove();
        }, 500);
    }, 3000);

    // Header scroll effect
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Mobile menu toggle
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking a navigation link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });

    // Animated counter for statistics
    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-count'));
        const duration = 2000;
        const step = target / duration * 30; // Update every 30ms
        let current = 0;
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                clearInterval(timer);
                element.textContent = target;
                if (element.closest('.stat-item').querySelector('.stat-label').textContent.includes('%')) {
                    element.textContent += '%';
                }
            } else {
                element.textContent = Math.floor(current);
            }
        }, 30);
    }

    // Intersection Observer for counting animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    // Observe all stat numbers
    statNumbers.forEach(stat => {
        observer.observe(stat);
    });

    // Initialize smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Add active class to navigation links based on scroll position
    function updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const scrollY = window.pageYOffset;

        sections.forEach(section => {
            const sectionHeight = section.offsetHeight;
            const sectionTop = section.offsetTop - 100;
            const sectionId = section.getAttribute('id');
            
            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                document.querySelector(`.nav-link[href*="#${sectionId}"]`)?.classList.add('active');
            } else {
                document.querySelector(`.nav-link[href*="#${sectionId}"]`)?.classList.remove('active');
            }
        });
    }

    window.addEventListener('scroll', updateActiveNavLink);

    // Contact form validation and submit handling
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = this.querySelector('#name').value.trim();
            const email = this.querySelector('#email').value.trim();
            const message = this.querySelector('#message').value.trim();
            
            if (name && email && message) {
                // In a real application, you would send this data to the server
                alert('Thank you for your message! We will get back to you soon.');
                this.reset();
            }
        });
    }

    // Initialize parallax effect for images (if needed)
    function parallaxEffect() {
        const parallaxElements = document.querySelectorAll('.hero-svg-container, .about-svg-container');
        
        window.addEventListener('mousemove', (e) => {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            parallaxElements.forEach(el => {
                el.style.transform = `translateX(${x * 15}px) translateY(${y * 15}px)`;
            });
        });
    }

    parallaxEffect();
});
