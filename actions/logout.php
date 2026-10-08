<?php
// Doesn't need session, login check, or database connection

// Would still need to pull email from db and add maybe change name of the 'login_at' column to allow for logout as well?
// I'm putting this on ice for now.

// require_once "../session/init.php"; // Make the session available
// require_once "../session/check_user_logged_in.php"; // Check if the user is logged in
// require_once "../includes/db_connect.php"; // Connect to the database

// // log the logout? (suddenly this page uses session, login check(?), and database connection)
//     // Save variables for logging
//     $profile_id = $_SESSION['profile_id'] ?? null;
//     $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
//     $success = 1; // Logout is always successful
//     $detail = "User logged out";

// Destroy the session
session_destroy();

// Redirect to the login page
header('Location: ../index.php');
exit();
?>