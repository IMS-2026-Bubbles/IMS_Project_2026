<?php
// Session initialization
include "Session/init.php"; // Make the session available
    // Retreive messages from exp_edit_tags.php if they exist
if (isset($_SESSION['messages_exp_edit_tags'])) {
    $messages_exp_edit_tags = $_SESSION['messages_exp_edit_tags'];
    // Clear the messages from the session after retrieving them
    unset($_SESSION['messages_exp_edit_tags']);
}

// Variables from POST (or GET?) or URL parameters
$exp_ID = $_POST['exp_ID'];

// Connect to database
include "Database_related/db.php";

echo " Experiment :)";
?>

<!-- Check for user permission -->
<?php
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view this page.";
    exit();
}
// Check if the user has permission to view this experiment
include "Functional_php/user_permission.php"; // Include the user permission check function
$permission_level = check_user_permission($_SESSION['user_id'], 'experiment', $exp_ID);
if ($permission_level === 'none') {
    echo "You do not have permission to view this experiment.";
    exit();
}
?>

<!-- Navbar? -->

<!-- Link back to parent project -->
 <?php
// Retrieve project ID for the experiment
include "/Functional_php/exp_helpers/exp_fetch_proj_ID.php"; // This script fetches the project ID for the specified experiment ID
// Create link back to parent project page
echo "<a href='project.php?proj_ID=" . $proj_ID . "'>Back to parent project</a><br><br>";
?>


<!-- tags field/form -->
<?php
// Retrieve experiment tags from the database
include "/Functional_php/exp_helpers/exp_fetch_tags.php"; // Fetches the tags for the specified experiment ID
// Display current tags
echo "Experiment tags: " . implode(", ", $exp_tags_array) . "<br><br>";
?>
<form action="Functional_php/exp_helpers/exp_edit_tags.php" method="post">
    <input type="text" name="new_tags" placeholder="Add new tags (comma separated)">
    <input type="text" name="remove_tags" placeholder="Remove tags (comma separated)">
    <input type="hidden" name="exp_ID" value="<?php echo $exp_ID; ?>">
    <input type="submit" value="Add Tags">
</form>
<br>
<?php
// Display messages from exp_edit_tags.php if they exist
if (isset($messages_exp_edit_tags)) {
    foreach ($messages_exp_edit_tags as $message) {
        echo $message;
    }
}
?>


<!-- markers for partial/total progress -->
<?php
// Retrieve and display progress flags for the experiment
include "Functional_php/exp_helpers/exp_fetch_progress.php"; // Fetches the progress flags as $exp_progress_flags

// Define the progress flag display helper.
include "Functional_php/exp_helpers/exp_progress_fun.php";
echo "<div class='exp_progress_flags'>" // String structured vertically for code readability.
    . exp_progress_flags("Plan", $exp_progress_flags['Plan_Done'])
    . exp_progress_flags("Log", $exp_progress_flags['Log_Done'])
    . exp_progress_flags("Result", $exp_progress_flags['Result_Done'])
    . "</div>";
echo "<br><br>";
?>


<!-- Create links for sub-pages, plan/log/result -->
<a href="experiment_plan.php?exp_ID=<?php echo $exp_ID; ?>">Experiment Plan</a><br>
<a href="experiment_log.php?exp_ID=<?php echo $exp_ID; ?>">Experiment Log</a><br>
<a href="experiment_result.php?exp_ID=<?php echo $exp_ID; ?>">Experiment Result</a><br>


<?php
// Close connection when done
include "/Database_related/closeDB.php";
?>