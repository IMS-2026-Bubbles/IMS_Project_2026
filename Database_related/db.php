<?php
// This is code to connect the server to the database
$servername = "localhost";
$username = "root"; 
$password = "root";
$dbname = ""; # add the name of our database here

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
}
// Commenting if successful, not necessary after a while
// echo " <br> Connected successfully :)<br>";
?>