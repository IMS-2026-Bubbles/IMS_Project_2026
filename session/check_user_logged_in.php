<?php
// Check if the user is logged in

// Helper to check if the user is logged in. Include in the beginning of pages 
// that require user authentication. If the user is not logged in, 
// it will display a message and exit the script. 


// Check if the user is logged in
if (!isset($_SESSION['profile_id'])) {
    // Log the access denied event
        // This gets triggered by bots, so may want to turn off if botting becomes a problem and massively clutters the logs.
    require_once "../database/db.php"; // Include the database connection
    require_once "../includes/log_activity.php"; // Include the log_activity function
    log_activity(
        $conn,
        $_SESSION['profile_id'] ?? NULL, // Use NULL if profile_id is not set
        'profile',
        NULL, // No specific entity ID for this action
        'access_denied',
        'User attempted to access a page without being logged in'
    );

    echo "You must be logged in to view this page.";
    exit();
}

// Set standard variables for logged-in user
$profile_id = $_SESSION['profile_id'];
$company_id = $_SESSION['company_id'] ?? null; // Get company_id from session if it exists
?>
