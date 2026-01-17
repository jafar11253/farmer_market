<?php
// Database connection settings
$host = "127.0.0.1";   // Use IP to avoid socket issues
$user = "root";        // Your MySQL username
$pass = "12345678";            // Your MySQL password (empty for default XAMPP)
$db   = "farm_market"; // Your database name
$port = 3307;          // IMPORTANT: your MySQL port

// Create connection
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: /farmer_market/login.php");
        exit;
    }
}
?>
