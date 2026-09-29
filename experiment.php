<?php
// Experiment page

// Arrive from:
    // experiment_library.php (click on experiment link)
    // experiment_section.php (click on plan/log/result link)
    // actions/save_experiment_tags.php (after adding/removing tags)
// Action:
    // Display experiment information, including tags, progress flags, and last updated timestamps
    // Allow users with sufficient permissions to add/remove tags
// Redirect to:
    // navbar options
    // experiment_section.php (click on plan/log/result link)
    // actions/save_experiment_tags.php (after adding/removing tags)
    // experiment_library.php (click on "Back to parent project" link)


// Session initialization
include "session/init.php"; // Make the session available
// Check if the user is logged in
include "session/check_user_logged_in.php";

// Variables
    // $experiment_id from URL (URL is always a GET request)
$experiment_id = $_GET['experiment_id'] ?? NULL;

// Connect to database
require_once "database/db.php";

// Check if the user has permission to view this experiment
include "includes/check_user_permission.php"; // Include the user permission check function
// $exp_ID becomes $experiment_id; the $project_experiment_name / $experiment_progress /
// $experiment_updated_at keys used below follow the new column names
$user_access = check_user_permission($conn, $_SESSION['profile_id'], 'experiment', $experiment_id);
if ($user_access < 1) {
    // Access level 0 means no access
    echo "You do not have permission to view this content.";
    exit();
}


// Retrieve messages from save_experiment_tags.php if they exist
if (isset($_SESSION['messages_save_experiment_tags'])) {
    $messages_save_experiment_tags = $_SESSION['messages_save_experiment_tags'];
    // Clear the messages from the session after retrieving them
    unset($_SESSION['messages_save_experiment_tags']);
}
?>

<!--  -->
<!-- Front end starts here -->
<!--  -->

<!-- Navbar -->
<?php
include "includes/navbar.php";
?>

<?php
// Display project name, experiment name, and experiment section name
include "includes/fetch_project_experiment_names.php"; // fetch the name and ID for project and experiment as $project_experiment_name

// Diplay the project, experiment, and section name
echo "Project: " . htmlspecialchars($project_experiment_name['project_name']) . "<br>";
echo "Experiment: " . htmlspecialchars($project_experiment_name['experiment_name']) . "<br><br>";
?>

<!-- Link back to parent project -->
 <?php
// Retrieve project ID for the experiment
include "includes/fetch_experiment_project_id.php"; // This script fetches the project ID for the specified experiment ID
// Create link back to parent project page
echo "<a href='project.php?proj_ID=" . urlencode($project_id) . "'>Back to parent project</a><br><br>";
?>


<!-- tags field/form -->
<?php
// Retrieve experiment tags from the database
include "includes/fetch_experiment_tags.php"; // Fetches the tags for the specified experiment ID
// Display current tags
echo "Experiment tags: " . implode(", ", $experiment_tags) . "<br><br>";
?>

<?php
// If user has permission to edit tags, display the form for adding/removing tags
if ($user_access >= 2) {
    echo "<form action='actions/save_experiment_tags.php' method='post'>"
    // Input fields for new and remove tags
    . "<input type='text' name='add_tags' placeholder='Add new tags (comma separated)'>"
    . "<br>"
    . "<input type='text' name='remove_tags' placeholder='Remove tags (comma separated)'>"
    . "<br>"
    // Experiment_ID
    . "<input type='hidden' name='experiment_id' value='" . htmlspecialchars($experiment_id) . "'>"
    // Submit button
    . "<input type='submit' value='Add Tags'>"
    . "</form>"
    . "<br>";
}
?>

<?php
// Display messages from save_experiment_tags.php if they exist
if (isset($messages_save_experiment_tags)) {
    foreach ($messages_save_experiment_tags as $message) {
        echo $message;
    }
}
?>


<!-- markers for partial/total progress and last update -->
<?php
// Retrieve and display progress flags for the experiment
include "includes/fetch_experiment_progress.php"; // Fetches the progress flags as $experiment_progress
// Retrieve the last update timestamp for the experiment
include "includes/fetch_experiment_timestamps.php"; // Fetches the last updated timestamp for the experiment as $experiment_updated_at
echo "Experiment created: " . $experiment_updated_at['created_at'] . "<br>";
echo "Experiment updated: " . $experiment_updated_at['updated_at'] . "<br><br>";

// Define the progress flag display helper.
include "includes/render_progress_badge.php";
echo "<div class='exp_progress_flags'>" // String structured vertically for code readability.
    . experiment_progress_badge("Plan", $experiment_progress['plan_is_done'])
    . "(" . $experiment_updated_at['plan_updated_at'] . ")<br>"
    . experiment_progress_badge("Log", $experiment_progress['log_is_done'])
    . "(" . $experiment_updated_at['log_updated_at'] . ")<br>"
    . experiment_progress_badge("Result", $experiment_progress['result_is_done'])
    . "(" . $experiment_updated_at['result_updated_at'] . ")<br>"
    . "</div>";
echo "<br><br>";
?>


<!-- Create links for sub-pages, plan/log/result -->
<a href="experiment_section.php?experiment_id=<?php echo urlencode($experiment_id); ?>&section=plan">Experiment Plan</a><br>
<a href="experiment_section.php?experiment_id=<?php echo urlencode($experiment_id); ?>&section=log">Experiment Log</a><br>
<a href="experiment_section.php?experiment_id=<?php echo urlencode($experiment_id); ?>&section=result">Experiment Result</a><br>


<?php
// Close connection when done
include "database/close_db.php";
?>