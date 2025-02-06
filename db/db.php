<?php
// Enable error reporting (useful for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$dbname = "dashboard_db";
$username = "root";
$password = "";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage()); // Log error for debugging

    // Return JSON error response for frontend
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}
?>
