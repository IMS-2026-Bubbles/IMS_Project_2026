<!Doctype html>
<html>
<?php
// Session initialization
include "Session/init.php"; // Make the session available

// Check if the user is logged in
include "Session/check_user_logged_in.php";

// Variables
    // $exp_ID from URL?
    $exp_ID = isset($exp_ID) : NULL; // Initialize $exp_ID to NULL if not set
    // $user_id from session
    $exp_section = "Plan"; // Set the section to "Plan" for the experiment plan page

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
?>

<!-- Link back to main experiment page -->
<a href="experiment.php?exp_ID=<?php echo urlencode($exp_ID); ?>">Back to experiment page</a><br><br>

<!-- Tags (static), Done toggle, save/submit -->
<?php
// Display current tags for the experiment
include "Functional_php/exp_helpers/exp_fetch_tags.php"; // Fetches the tags for the specified experiment ID
echo "Experiment tags: " . implode(", ", $exp_tags_array) . "<br><br>";

// Display the "Done" toggle for the experiment plan
include "Functional_php/exp_helpers/exp_fetch_progress.php"; // Fetches the progress status for the specified experiment ID
?>



<!-- Textbox -->


