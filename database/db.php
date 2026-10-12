<?php
// This is code to connect the server to the database
$servername = $_ENV['DB_SERVERNAME'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
// // Commenting if successful, not necessary after a while
// if (isset($messages)) {
//     $messages[] = "Connected successfully :) <br>";
// } else {
//     echo "Connected successfully :) <br>";
// }
?>