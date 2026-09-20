<?php
// Session initialization
include "Session/init.php"; // Make the session available

// Check if the user is logged in
include "Session/check_user_logged_in.php";

// Variables
    // $exp_ID from URL?
    $exp_ID = isset($exp_ID) : NULL; // Initialize $exp_ID to NULL if not set
    // $user_id from session

// Connect to database
include "Database_related/db.php";

// Check if the user has permission to view this experiment
include "Functional_php/user_permission.php"; // Include the user permission check function
$user_access = check_user_permission($conn, $_SESSION['user_id'], 'experiment', $exp_ID);
if ($user_access < 1) {
    // Access level 0 means no access
    echo "You do not have permission to view this content.";
    exit();
}

echo " Plan experiment :)";
?>

<!-- Link back to main experiment page -->
<a href="experiment.php?exp_ID=<?php urlencode($exp_ID); ?>">Back to experiment page</a><br><br>

<!-- Tags (static), Done toggle, save/submit -->

<!-- Textbox -->