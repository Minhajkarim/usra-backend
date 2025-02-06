<?php
require_once 'db/db.php'; // Include db.php from the db subfolder

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
} else {
    echo "Database connection successful!";
}
?>