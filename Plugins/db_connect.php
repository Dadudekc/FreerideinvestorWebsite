<?php
// Load WordPress configuration file
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

// Use WordPress database credentials
$host = DB_HOST;
$user = DB_USER;
$password = DB_PASSWORD;
$database = DB_NAME;

// Connect to MySQL using WordPress credentials
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}
?>
