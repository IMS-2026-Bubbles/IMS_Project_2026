<?php
// Check if the user is logged in

// Helper to check if the user is logged in. Include in the beginning of pages 
// that require user authentication. If the user is not logged in, 
// it will display a message and exit the script. 


// Check if the user is logged in
if (!isset($_SESSION['profile_id'])) {
    echo "You must be logged in to view this page.";
    exit();
}

// Set standard variables for logged-in user
$profile_id = $_SESSION['profile_id'];
$company_id = $_SESSION['company_id'] ?? null; // Get company_id from session if it exists
?>
