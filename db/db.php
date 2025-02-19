<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$host = "localhost";
$dbname = "dashboard_db"; // Change this if your database name is different
$username = "root";  // Update if using a different database user
$password = "";      // Update if you have a password for MySQL

try {
    // Create a new PDO instance with error handling
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions for errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch as associative array
        PDO::ATTR_EMULATE_PREPARES   => false, // Use real prepared statements
    ];

    // Establish database connection
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage()); // Log error for debugging

    // Return JSON error response
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}
?>
