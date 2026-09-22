<?php
// Session initialization
include "Session/init.php"; // Make the session available

// Check if the user is logged in
include "Session/check_user_logged_in.php";

// Variables
    // $exp_ID from URL (URL is always a GET request)
$exp_ID = $_GET['exp_ID'] ?? NULL;

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


// Retrieve messages from exp_edit_tags.php if they exist
if (isset($_SESSION['messages_exp_edit_tags'])) {
    $messages_exp_edit_tags = $_SESSION['messages_exp_edit_tags'];
    // Clear the messages from the session after retrieving them
    unset($_SESSION['messages_exp_edit_tags']);
}

echo " Experiment :)";
?>

<!-- Navbar? -->

<?php
// Display project name, experiment name, and experiment section name
include "Functional_php/exp_helpers/exp_fetch_name.php"; // fetch the name and ID for project and experiment as $proj_exp_name_array

// Diplay the project, experiment, and section name
echo "Project: " . htmlspecialchars($proj_exp_name_array['Project_Name']) . "<br>";
echo "Experiment: " . htmlspecialchars($proj_exp_name_array['Experiment_Name']) . "<br><br>";
?>

<!-- Link back to parent project -->
 <?php
// Retrieve project ID for the experiment
include "Functional_php/exp_helpers/exp_fetch_proj_ID.php"; // This script fetches the project ID for the specified experiment ID
// Create link back to parent project page
echo "<a href='project.php?proj_ID=" . urlencode($proj_ID) . "'>Back to parent project</a><br><br>";
?>


<!-- tags field/form -->
<?php
// Retrieve experiment tags from the database
include "Functional_php/exp_helpers/exp_fetch_tags.php"; // Fetches the tags for the specified experiment ID
// Display current tags
echo "Experiment tags: " . implode(", ", $exp_tags_array) . "<br><br>";
?>

<?php
// If user has permission to edit tags, display the form for adding/removing tags
if ($user_access >= 2) {
    echo "<form action='Functional_php/exp_helpers/exp_edit_tags.php' method='post'>"
    // Input fields for new and remove tags
    . "<input type='text' name='new_tags' placeholder='Add new tags (comma separated)'>"
    . "<input type='text' name='remove_tags' placeholder='Remove tags (comma separated)'>"
    // Experiment_ID
    . "<input type='hidden' name='exp_ID' value='" . htmlspecialchars($exp_ID) . "'>"
    // Submit button
    . "<input type='submit' value='Add Tags'>"
    . "</form>"
    . "<br>";
}
?>

<?php
// Display messages from exp_edit_tags.php if they exist
if (isset($messages_exp_edit_tags)) {
    foreach ($messages_exp_edit_tags as $message) {
        echo $message;
    }
}
?>


<!-- markers for partial/total progress and last update -->
<?php
// Retrieve and display progress flags for the experiment
include "Functional_php/exp_helpers/exp_fetch_progress.php"; // Fetches the progress flags as $exp_progress_flags
// Retrieve the last update timestamp for the experiment
include "Functional_php/exp_helpers/exp_fetch_updated.php"; // Fetches the last updated timestamp for the experiment as $exp_last_update
echo "Experiment created: " . $exp_last_update['Date_Created'] . "<br>";
echo "Experiment updated: " . $exp_last_update['Date_Updated'] . "<br><br>";

// Define the progress flag display helper.
include "Functional_php/exp_helpers/exp_progress_fun.php";
echo "<div class='exp_progress_flags'>" // String structured vertically for code readability.
    . exp_progress_badge("Plan", $exp_progress_flags['Plan_Done'])
    . "(" . $exp_last_update['Plan_Updated'] . ")<br>"
    . exp_progress_badge("Log", $exp_progress_flags['Log_Done'])
    . "(" . $exp_last_update['Log_Updated'] . ")<br>"
    . exp_progress_badge("Result", $exp_progress_flags['Result_Done'])
    . "(" . $exp_last_update['Result_Updated'] . ")<br>"
    . "</div>";
echo "<br><br>";
?>


<!-- Create links for sub-pages, plan/log/result -->
<a href="experiment_section.php?exp_ID=<?php echo urlencode($exp_ID); ?>&section=Plan">Experiment Plan</a><br>
<a href="experiment_section.php?exp_ID=<?php echo urlencode($exp_ID); ?>&section=Log">Experiment Log</a><br>
<a href="experiment_section.php?exp_ID=<?php echo urlencode($exp_ID); ?>&section=Result">Experiment Result</a><br>


<?php
// Close connection when done
include "Database_related/closeDB.php";
?>