<?php
require_once 'db/db.php'; // Include database connection file

// Query to fetch some data
$sql = "SELECT DATABASE() AS current_database";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo "Connected to database: " . $row['current_database'];
} else {
    echo "Failed to retrieve database information.";
}
?>
