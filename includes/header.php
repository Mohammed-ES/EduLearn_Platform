<?php
// Determine base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$baseURL = $protocol . $host . '/EduLearn';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'EduLearn Platform | Modern Educational Experience'; ?></title>    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">    <link rel="stylesheet" href="<?php echo $baseURL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo $baseURL; ?>/css/auth.css">
    <link rel="stylesheet" href="<?php echo $baseURL; ?>/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php echo isset($additionalCSS) ? $additionalCSS : ''; ?>
</head>
<body>
    <div class="loader">
        <div class="loader-content">
            <span>E</span>
            <span>d</span>
            <span>u</span>
            <span>L</span>
            <span>e</span>
            <span>a</span>
            <span>r</span>
            <span>n</span>
            <span>.</span>
            <span>.</span>
            <span>.</span>
        </div>
    </div>

    <header class="header" id="header">
        <div class="container">
            <div class="header-content">                <div class="logo">
                    <a href="<?php echo $baseURL; ?>/index.php">
                        <!-- SVG Logo -->
                        <svg width="150" height="40" viewBox="0 0 150 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 8H35V12H15V8Z" fill="#007BFF"/>
                            <path d="M15 18H30V22H15V18Z" fill="#007BFF"/>
                            <path d="M15 28H35V32H15V28Z" fill="#007BFF"/>
                            <path d="M45 10L55 10L55 30L45 30L45 10Z" fill="#D4AF37"/>
                            <text x="60" y="25" fill="#0F4C75" font-family="Poppins" font-size="20" font-weight="600">EduLearn</text>
                        </svg>
                    </a>
                </div><nav class="nav-menu">
                    <ul class="nav-list">
                        <li class="nav-item"><a href="<?php echo $baseURL; ?>/index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Home</a></li>
                        <li class="nav-item"><a href="<?php echo $baseURL; ?>/auth/login.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : ''; ?>">Login</a></li>
                        <li class="nav-item"><a href="<?php echo $baseURL; ?>/auth/register.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : ''; ?>">Register</a></li>
                        <li class="nav-item"><a href="<?php echo $baseURL; ?>/index.php#contact" class="nav-link">Contact</a></li>
                    </ul>
                </nav>
                <div class="hamburger" id="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </div>    </header>

    <?php /* Base URL is now defined at the top of the file */ ?>
