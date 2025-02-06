<?php
header("Access-Control-Allow-Origin: http://localhost:3000"); // Adjust in production
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit; // Handle CORS preflight
}

require_once 'db.php';
require_once 'jwt.php'; // JWT functions (explained below)

// Read input data
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || empty($data['username']) || empty($data['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Missing username or password."]);
    exit;
}

$username = trim($data['username']);
$password = trim($data['password']);

try {
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Generate JWT token
        $token = createJWT($user['id'], $user['username']);

        echo json_encode([
            "success" => true,
            "message" => "Login successful.",
            "token" => $token
        ]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(["success" => false, "message" => "Invalid username or password."]);
    }
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal server error."]);
}
?>
