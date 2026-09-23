<?php
// Create connection
include '..\Database_related\db.php';

// Check if connection is established
if (mysqli_connect_error()) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch data from POST request
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$SSSN = $_POST['SSSN'];
$company = $_POST['company'];
$lab_group = $_POST['lab_group'];
$password = $_POST['password'];

$sql1 = "INSERT INTO user (first_name, last_name, email, SSSN, password_hash($password)) VALUES (?, ?, ?, ?, ?, ?)";
//MUST FIX SO THAT USER IS PUT INTO COMPANY/LAB GROUP
$stmt = $link->prepare($sql); # Unknown column 'name'

$stmt->bind_param("ssiiis", $first_name, $last_name, $email, $SSSN, $company, $lab_group, $password);
//Have set company and lab groups as ints becuase they are choices right now at register page
$result = $stmt->execute();

// Close the database connection
include '..\Database_related\closeDB.php';

if ($result) {
    echo "New record created successfully";
} else {
    echo "Error: " . $stmt->error;
};

?>
