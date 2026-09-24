<?php
require "Database_related/db.php";

echo "verification page";

if (isset($_POST["Log in"])) {
    $Email = $_POST["Email"];
    $Password = $_POST["Password"];

    if (checkLoginInformation($Email, $Password)) {
        //send to project library
    }}

else {
    //send them back to login page
}

// Function to check Whether both Passwords is same or not.
function checkLoginInformation($Email, $Password) {
    //Can only retrieve information from user if the email is used
    $sql   = "SELECT Password FROM users WHERE Email = ?";
    $query = $mysqli->prepare($sql);
    $query->bind_param("s", $Email);
    $query->execute();

    //Checks if the password is correct
    if (
        !($user = $query->get_result()->fetch_assoc())
        || !password_verify($Password, $user["Password"])
    ) {
        //Return an error message if something is wrong
        alert("\nInvalid login");
        return False;
    }
    }
require_once "Database_related/closeDB.php";
?>