<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set page variables
$pageTitle = 'Anne\'s Canteen | Fresh & Fast';
$currentPage = 'home';

// Debug information
echo '<!-- Current directory: ' . __DIR__ . ' -->';
echo '<!-- Including: ' . __DIR__ . '/includes/header.php' . ' -->';

// Include header with error handling
try {
    $headerPath = __DIR__ . '/includes/header.php';
    if (file_exists($headerPath)) {
        require_once $headerPath;
    } else {
        throw new Exception('Header file not found at: ' . $headerPath);
    }
} catch (Exception $e) {
    // If header fails to load, show a basic header
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($pageTitle) ?></title>
        <link rel="stylesheet" href="/assets/css/styles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body>
    <header>
        <div class="container">
            <h1>Anne's Canteen</h1>
            <nav>
                <a href="/">Home</a>
                <a href="/menu.php">Menu</a>
                <a href="/login.php">Login</a>
            </nav>
        </div>
    </header>
    <main class="container">
    <?php
    // Log the error
    error_log('Header include failed: ' . $e->getMessage());
}
?>
<section class="hero">
    <div class="hero-content">
        <p class="badge">Anne's Canteen</p>
        <h1>Fresh meals and drinks, ready when you are.</h1>
        <p>Browse our delicious menu, add items to your cart, and manage your profile in one clean dashboard.</p>
        <div class="hero-actions">
            <a class="btn" href="menu.php">Explore Menu</a>
        </div>
    </div>
    
</section>
<section class="features">
    <article>
        <h3>Menu</h3>
        <p>Floating cards showcase snacks, hot meals, and signature drinks.</p>
    </article>
    <article>
        <h3>Cart & Checkout</h3>
        <p>Review items, adjust quantities, and confirm payment.</p>
    </article>
    <article>
        <h3>Profile</h3>
        <p>Track your previous orders and edit personal info.</p>
    </article>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
