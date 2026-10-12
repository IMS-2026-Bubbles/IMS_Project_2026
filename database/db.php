<?php
// This is code to connect the server to the database
$servername = $_ENV['DB_SERVERNAME'] ?? die('DB_SERVERNAME not set in .env file');
$username = $_ENV['DB_USERNAME'] ?? die('DB_USERNAME not set in .env file');
$password = $_ENV['DB_PASSWORD'] ?? die('DB_PASSWORD not set in .env file');
$dbname = $_ENV['DB_NAME'] ?? die('DB_NAME not set in .env file');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

?>