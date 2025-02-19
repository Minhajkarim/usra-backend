<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Allow CORS for frontend
header("Access-Control-Allow-Origin: http://localhost:3000"); // Change this if frontend is hosted elsewhere
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// ✅ Handle preflight request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Include database connection
require_once __DIR__ . '/db/db.php';

// ✅ Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// ✅ Read JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// ✅ Check if JSON is valid
if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON input."]);
    exit;
}

// ✅ Validate input fields
if (empty($data['username']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Username and password are required."]);
    exit;
}

$username = trim($data['username']);
$password = trim($data['password']);

try {
    // ✅ Fetch user from database
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // ✅ Check if user exists and password is correct
    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid username or password."]);
        exit;
    }

    // ✅ Successful login
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Login successful!", "user_id" => $user['id']]);
} catch (PDOException $e) {
    // ✅ Log error and return a proper response
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal server error."]);
}
?>
