<?php
// Check if the user is in a company

// Helper to check if the user is in a company. Include in the beginning of pages
// that require user authentication. If the user is not in a company,
// it will send the user to the user profile and display a message there.

//for displaying error message if user is not in a company

$message = '';
$toastClass = '';

// Check if the user is logged in
if (!isset($_SESSION['company_id'])) {
    // Log the access denied event
    // This gets triggered by bots, so may want to turn off if botting becomes a problem and massively clutters the logs.

    require_once __DIR__ . '/../database/db.php';
    require_once __DIR__ . '/../includes/log_activity.php';
    //require '../database/db.php';  // Include the database connection
    //require '../includes/log_activity.php';  // Include the log_activity function
    log_activity(
        $conn,
        $_SESSION['company_id'] ?? NULL,  // Use NULL if company_id is not set
        'company',
        NULL,  // No specific entity ID for this action
        'access_denied',
        'User attempted to access a page without being in a company'
    );

    $message = 'You must be in a company to view this page.';
    $toastClass = '#dc3545';  // Danger color

    $_SESSION['view_page_message'] = $message;
    $_SESSION['toastClass'] = $toastClass;

    header('Location:/../user_profile.php');
    exit();
}

// Set standard variables for logged-in user
$profile_id = $_SESSION['profile_id'];
$company_id = $_SESSION['company_id'] ?? null;  // Get company_id from session if it exists
?>
