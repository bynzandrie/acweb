<?php
// Database configuration with fallback values for local development
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'canteen_portal';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$port = getenv('DB_PORT') ?: '3306';

// Set character set to UTF-8
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 5, // 5 second timeout
];

// Initialize connections as null
$pdo = null;
$mysqli = null;

try {
    // Create PDO instance
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // For backward compatibility, maintain the mysqli connection
    $mysqli = @new mysqli($host, $user, $pass, $db, $port);
    
    if ($mysqli->connect_errno) {
        // Log the error but don't stop execution
        error_log('MySQLi connection warning: ' . $mysqli->connect_error);
        $mysqli = null; // Set to null to indicate connection failed
    } else {
        $mysqli->set_charset('utf8mb4');
    }
    
} catch (PDOException $e) {
    // Log the error but don't stop execution
    error_log('PDO Connection Error: ' . $e->getMessage());
    $pdo = null;
} catch (Exception $e) {
    // Log other exceptions
    error_log('Database Error: ' . $e->getMessage());
}

// Set default timezone
date_default_timezone_set('Asia/Manila');

// Function to check if database is connected
function isDbConnected() {
    global $pdo;
    try {
        return $pdo !== null && $pdo->query('SELECT 1');
    } catch (PDOException $e) {
        return false;
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

// Set timezone
date_default_timezone_set('Asia/Manila');  // Change to your timezone