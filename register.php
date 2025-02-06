<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow requests from frontend
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow only POST and OPTIONS
header("Access-Control-Allow-Headers: Content-Type"); // Allow Content-Type header
header("Access-Control-Allow-Credentials: true"); // Allow credentials
header("Content-Type: application/json"); // Ensure JSON response

// Include the database connection
require_once __DIR__ . '/db/db.php';

// Handle preflight (OPTIONS) request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// Read and decode JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Invalid JSON input."]);
    exit;
}

// Validate required fields
if (empty($data['username']) || empty($data['password']) || empty($data['age'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing username, password, or age."]);
    exit;
}

$username = trim($data['username']);
$password = trim($data['password']);
$age = intval($data['age']); // Ensure age is an integer

// Validate username format (only letters, numbers, and underscores, 3-20 characters)
if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid username format."]);
    exit;
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        http_response_code(409); // Conflict
        echo json_encode(["success" => false, "message" => "Username already taken."]);
        exit;
    }

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (username, password, age) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$username, $hashed_password, $age])) {
        http_response_code(201); // Created
        echo json_encode(["success" => true, "message" => "User registered successfully!"]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["success" => false, "message" => "Database error."]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal server error."]);
}
?>
