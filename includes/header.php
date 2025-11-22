<?php
// Set default timezone
date_default_timezone_set('Asia/Manila');

// Initialize variables
$pdo = null;
$mysqli = null;
$user = null;
$pageTitle = $pageTitle ?? 'Canteen Portal';
$currentPage = $currentPage ?? '';

// Include database configuration
$dbConfigPath = __DIR__ . '/../config/db.php';
if (file_exists($dbConfigPath)) {
    require_once $dbConfigPath;
}

// Include functions
$functionsPath = __DIR__ . '/functions.php';
if (file_exists($functionsPath)) {
    require_once $functionsPath;
    
    // Get current user if database is connected
    if (function_exists('currentUser') && isset($mysqli)) {
        try {
            $user = @currentUser($mysqli);
        } catch (Exception $e) {
            error_log("Error getting current user: " . $e->getMessage());
        }
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Lax'
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Anne's Canteen</title>
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/img/logo.png" type="image/png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="/assets/css/styles.css">
    
    <style>
        :root {
            --primary-color: #4a6bff;
            --secondary-color: #f8f9fa;
            --text-color: #333;
            --light-text: #6c757d;
            --white: #ffffff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7ff;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header Styles */
        header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        nav a {
            color: var(--text-color);
            text-decoration: none;
            margin-left: 25px;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav a:hover, nav a.active {
            color: var(--primary-color);
        }
        
        .auth-buttons a {
            padding: 8px 20px;
            border-radius: 5px;
            margin-left: 15px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-login {
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-login:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-signup {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-signup:hover {
            background-color: #3a5bd9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-color);
            cursor: pointer;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                padding: 15px;
            }
            
            nav {
                margin: 15px 0;
                flex-direction: column;
                width: 100%;
                text-align: center;
                display: none;
            }
            
            nav.active {
                display: flex;
            }
            
            nav a {
                margin: 10px 0;
                padding: 10px;
                display: block;
                width: 100%;
                border-bottom: 1px solid #eee;
            }
            
            .auth-buttons {
                margin: 15px 0;
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 10px;
                display: none;
            }
            
            .auth-buttons.active {
                display: flex;
            }
            
            .auth-buttons a {
                margin: 5px 0;
                width: 100%;
                text-align: center;
            }
            
            .mobile-menu-btn {
                display: block;
                position: absolute;
                right: 20px;
                top: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">
                    <img src="/assets/img/logo.png" alt="Anne's Canteen">
                    Anne's Canteen
                </a>
                
                <nav id="main-nav">
                    <a href="/" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Home</a>
                    <a href="/menu.php" class="<?= $currentPage === 'menu' ? 'active' : '' ?>">Menu</a>
                    <?php if (isset($user)): ?>
                        <a href="/orders.php" class="<?= $currentPage === 'orders' ? 'active' : '' ?>">My Orders</a>
                        <a href="/profile.php" class="<?= $currentPage === 'profile' ? 'active' : '' ?>">My Profile</a>
                        <?php if ($user && $user['role'] === 'admin'): ?>
                            <a href="/admin/" class="<?= $currentPage === 'admin' ? 'active' : '' ?>">Admin Panel</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </nav>
                
                <div class="auth-buttons" id="auth-buttons">
                    <?php if (isset($user)): ?>
                        <a href="/cart.php" class="btn btn-login">
                            <i class="fas fa-shopping-cart"></i> Cart 
                            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                <span class="cart-count"><?= count($_SESSION['cart']) ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/profile.php" class="btn btn-login">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($user['full_name'] ?? 'My Account') ?>
                        </a>
                        <a href="/logout.php" class="btn btn-signup">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="/login.php" class="btn btn-login">Login</a>
                        <a href="/register.php" class="btn btn-signup">Sign Up</a>
                    <?php endif; ?>
                </div>
                
                <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>
    
    <main class="container">
        <!-- Mobile menu toggle script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mobileMenuBtn = document.getElementById('mobile-menu-btn');
                const mainNav = document.getElementById('main-nav');
                const authButtons = document.getElementById('auth-buttons');
                
                if (mobileMenuBtn) {
                    mobileMenuBtn.addEventListener('click', function() {
                        mainNav.classList.toggle('active');
                        authButtons.classList.toggle('active');
                        this.querySelector('i').classList.toggle('fa-bars');
                        this.querySelector('i').classList.toggle('fa-times');
                    });
                }
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.header-content')) {
                        mainNav.classList.remove('active');
                        authButtons.classList.remove('active');
                        const icon = mobileMenuBtn.querySelector('i');
                        if (icon.classList.contains('fa-times')) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                });
            });
        </script>
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Anne's Canteen</title>
    <!-- Favicon -->
    <link rel="icon" href="/assets/img/logo.png" type="image/png" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="/assets/css/styles.css">
    
    <!-- Custom CSS for header/nav -->
    <style>
        :root {
            --primary-color: #4a6bff;
            --secondary-color: #f8f9fa;
            --text-color: #333;
            --light-text: #6c757d;
            --white: #ffffff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7ff;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header Styles */
        header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        nav a {
            color: var(--text-color);
            text-decoration: none;
            margin-left: 25px;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav a:hover, nav a.active {
            color: var(--primary-color);
        }
        
        .auth-buttons a {
            padding: 8px 20px;
            border-radius: 5px;
            margin-left: 15px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-login {
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-login:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-signup {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-signup:hover {
            background-color: #3a5bd9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                padding: 15px;
            }
            
            nav {
                margin: 15px 0;
            }
            
            nav a {
                margin: 0 10px;
            }
            
            .auth-buttons {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">
                    <img src="/assets/img/logo.png" alt="Canteen Logo">
                    Anne's Canteen
                </a>
                
                <nav>
                    <a href="/" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Home</a>
                    <a href="/menu.php" class="<?= $currentPage === 'menu' ? 'active' : '' ?>">Menu</a>
                    <?php if (isset($user)): ?>
                        <a href="/profile.php" class="<?= $currentPage === 'profile' ? 'active' : '' ?>">My Profile</a>
                        <?php if ($user && $user['role'] === 'admin'): ?>
                            <a href="/admin/" class="<?= $currentPage === 'admin' ? 'active' : '' ?>">Admin Panel</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </nav>
                
                <div class="auth-buttons">
                    <?php if (isset($user)): ?>
                        <a href="/profile.php" class="btn-login">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($user['full_name'] ?? 'My Account') ?>
                        </a>
                        <a href="/logout.php" class="btn-signup">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="/signin.php" class="btn-login">Login</a>
                        <a href="/signup.php" class="btn-signup">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container" style="margin-top: 80px;">
        }

        .logo-title-container {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .logo-container {
            width: 40px;
            height: 40px;
            margin-right: 12px;
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .site-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: #2c3e50;
        }

        /* Navigation */
        .main-nav ul {
            display: flex;
            gap: 5px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .main-nav a {
            display: block;
            padding: 12px 18px;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #333;
            white-space: nowrap;
        }
        
        .main-nav a:hover, 
        .main-nav a.active {
            background: #f0f0f0;
            transform: translateY(-2px);
        }
        
        /* Auth Section */
        .auth-section {
            display: flex;
            align-items: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
        }

        
        .profile-link,
        .logout-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            margin-left: 8px;
            font-size: 0.95rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        
        .profile-link {
            background: #f0f0f0;
            color: #333;
        }
        
        .logout-btn {
            background: #ff5e00;
            color: white !important;
        }
        
        .profile-link:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }
        
        .logout-btn:hover {
            background: #ff8c00;
            transform: translateY(-2px);
        }
        
        .auth-buttons {
            display: flex;
            gap: 10px;
        }
        
        .signin-btn, 
        .signup-btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }
        
        .signin-btn {
            background: #f0f0f0;
            color: #333;
        }
        
        .signup-btn {
            background: #fc9309;
            color: white;
        }
        
        .signin-btn:hover, 
        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .admin-badge {
            background: #28a745;
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 6px;
            vertical-align: middle;
            display: inline-block;
            line-height: 1.2;
        }
        
        .signout-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            margin-left: 8px;
            background: #dc3545;
            color: white !important;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            height: 36px;
        }
        
        .signout-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
            text-decoration: none;
        }
        
        .signout-btn i {
            margin-right: 6px;
            font-size: 0.9em;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .header-content {
                padding: 0 15px;
            }
            
            .main-nav a {
                padding: 10px 14px;
                font-size: 0.95rem;
            }
            
        }

        @media (max-width: 992px) {
            .site-title {
                font-size: 1.3rem;
            }
            
            .main-nav {
                display: none;
            }
            
            .menu-toggle {
                display: block !important;
            }
        }
    </style>
</head>
<body>
<header class="top-nav">
    <div class="header-content">
        <div class="logo-title-container">
            <div class="logo-container">
                <img src="/canteen_portal/assets/img/logo.png" alt="Anne's Canteen Logo" class="site-logo" onerror="this.style.display='none';">
            </div>
            <h1 class="site-title">Anne's Canteen</h1>
        </div>
        
        <nav class="main-nav">
            <ul>
                <?php if ($user && ($user['role'] ?? 'customer') === 'admin'): ?>
                    <li><a href="admin_orders.php" class="<?= $currentPage === 'admin-orders' ? 'active' : '' ?>">View Orders</a></li>
                    <li><a href="admin_menu.php" class="<?= $currentPage === 'admin-menu' ? 'active' : '' ?>">Menu Management</a></li>
                    <li><a href="admin_prepare.php" class="<?= $currentPage === 'admin-prepare' ? 'active' : '' ?>">Prepare</a></li>
                    <li><a href="admin_serve.php" class="<?= $currentPage === 'admin-serve' ? 'active' : '' ?>">Serve</a></li>
                <?php else: ?>
                    <li><a href="index.php" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Home</a></li>
                    <li><a href="menu.php" class="<?= $currentPage === 'menu' ? 'active' : '' ?>">Menu</a></li>
                    <li><a href="cart.php" class="<?= $currentPage === 'cart' ? 'active' : '' ?>">Cart</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="auth-section">
            <?php if (isLoggedIn()): ?>
                <div class="user-profile">
                    <a href="profile.php" class="profile-link">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="logout.php" class="signout-btn">
                        <i class="fas fa-sign-out-alt"></i> Sign Out
                    </a>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="signin.php" class="signin-btn">Sign In</a>
                    <a href="signup.php" class="signup-btn">Sign Up</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <button class="menu-toggle" aria-label="Open navigation" style="display: none;">
        <i class="fas fa-bars"></i>
    </button>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            mainNav.style.display = mainNav.style.display === 'none' || !mainNav.style.display ? 'block' : 'none';
        });
        
        // Handle window resize
        function handleResize() {
            if (window.innerWidth > 992) {
                mainNav.style.display = '';
                menuToggle.style.display = 'none';
            } else {
                menuToggle.style.display = 'flex';
                mainNav.style.display = 'none';
            }
        }
        
        // Initial check
        handleResize();
        
        // Add event listener for window resize
        window.addEventListener('resize', handleResize);
    }
});
</script>
<main>
