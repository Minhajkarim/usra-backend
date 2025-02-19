<?php
require_once __DIR__ . '/db/db.php';

if ($conn) {
    echo "Database connected successfully!";
} else {
    echo "Database connection failed!";
}
?>
