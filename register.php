<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers (Adjust for production)
header("Access-Control-Allow-Origin: http://localhost:3000"); // Change this if frontend is hosted elsewhere
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Include the database connection
require_once __DIR__ . '/db/db.php';

// Handle preflight (OPTIONS) request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// Read and decode input (support JSON & form-data)
$data = null;
if (strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
} else {
    $data = $_POST; // Handle form-data submission
}

// Debugging: Log received request
file_put_contents("request.log", print_r($data, true) . PHP_EOL, FILE_APPEND);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input format.", "received" => $_SERVER["CONTENT_TYPE"]]);
    exit;
}

// Validate required fields
if (empty($data['username']) || empty($data['password']) || empty($data['age'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing username, password, or age."]);
    exit;
}

// Sanitize and validate input
$username = trim($data['username']);
$password = trim($data['password']);
$age = intval($data['age']); // Ensure age is an integer

// Validate username format (only letters, numbers, and underscores, 3-20 characters)
if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid username format."]);
    exit;
}

// Hash the password securely
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
        error_log("Database insert error"); // Log issue
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error."]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage()); // Log SQL error
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal server error.", "error" => $e->getMessage()]);
}
?>
