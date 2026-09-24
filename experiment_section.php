<!Doctype html>
<html>
<?php
// Session initialization
include "session/init.php"; // Make the session available

// Check if the user is logged in
include "session/check_user_logged_in.php";

// Variables
    // $user_id from session
    // $exp_ID from URL (URL is always a GET request)
$exp_ID = $_GET['exp_ID'] ?? NULL;
if ($exp_ID === null) {
    $messages[] = "Error: No experiment ID provided.<br>";
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;
    // Redirect back to project library page
    header("Location: ../../project_library.php");
    exit();
}
$exp_section = $_GET['section'] ?? NULL; // Section name from URL (URL is always a GET request)
    // Check for NULL section name
if ($exp_section === null) {
    $messages[] = "Error: No experiment section provided.<br>";
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;
    // Redirect back to experiment.php with the same exp_ID
    header("Location: experiment.php?exp_ID=" . urlencode($exp_ID));
    exit();
}
    // Whitelist section names
// TODO(schema-migration): section values become lowercase ('plan', 'log', 'result'),
// matching the new column names plan_text/plan_is_done/plan_updated_at, etc.
$valid_sections = ['Plan', 'Log', 'Result'];
if (!in_array($exp_section, $valid_sections)) {
    $messages[] = "Error: Invalid experiment section provided.<br>";
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;
    // Redirect back to experiment.php with the same exp_ID
    header("Location: experiment.php?exp_ID=" . urlencode($exp_ID));
    exit();
}

// Connect to database
include "database/db.php";

// Check if the user has permission to view this experiment
include "includes/check_user_permission.php"; // Include the user permission check function
// TODO(schema-migration): $_SESSION['user_id'] becomes $_SESSION['profile_id'];
// $exp_ID becomes $experiment_id; the array keys used below
// ($proj_exp_name_array, $exp_progress_flags, $exp_last_update) follow the new
// column names
$user_access = check_user_permission($conn, $_SESSION['user_id'], 'experiment', $exp_ID);
if ($user_access < 1) {
    // Access level 0 means no access
    echo "You do not have permission to view this content.";
    exit();
}

// Retrieve messages from save_experiment_section.php if they exist
if (isset($_SESSION['messages_save_experiment_section'])) {
    $messages_save_experiment_section = $_SESSION['messages_save_experiment_section'];
    // Clear the messages from the session after retrieving them
    unset($_SESSION['messages_save_experiment_section']);
}
?>

<?php
// Display project name, experiment name, and experiment section name
include "includes/fetch_project_experiment_names.php"; // fetch the name and ID for project and experiment as $proj_exp_name_array

// Diplay the project, experiment, and section name
echo "Project: " . htmlspecialchars($proj_exp_name_array['Project_Name']) . "<br>";
echo "Experiment: " . htmlspecialchars($proj_exp_name_array['Experiment_Name']) . "<br>";
echo "Section: " . htmlspecialchars($exp_section) . "<br><br>";
?>

<!-- Link back to main experiment page -->
<a href="experiment.php?exp_ID=<?php echo urlencode($exp_ID); ?>">Back to <?php echo htmlspecialchars($proj_exp_name_array['Experiment_Name']); ?></a><br><br>

<!-- Tags (static), Done toggle, save/submit -->
<?php
// Display current tags for the experiment
include "includes/fetch_experiment_tags.php"; // Fetches the tags for the specified experiment ID
echo "Experiment tags: " . implode(", ", $exp_tags_array) . "<br><br>";

// Display last updated timestamp for the experiment section
include "includes/fetch_experiment_timestamps.php"; // Fetches the last updated timestamp for the specified experiment ID and section
echo "Last updated: " . $exp_last_update[$exp_section . '_Updated'] . "<br><br>";

// Display the "Done" toggle for the experiment plan
include "includes/fetch_experiment_progress.php"; // Fetches the progress status for the specified experiment ID
// Define the progress flag display helper.
include "includes/render_progress_badge.php";
echo "<div class='exp_progress_flags'>" // String structured vertically for code readability.
    . exp_progress_badge($exp_section, $exp_progress_flags[$exp_section . '_Done'])
    . "</div>";
echo "<br>";

// Retrieve the text content for the experiment plan section
include "includes/fetch_experiment_section_text.php"; // Fetches the text content for the specified experiment ID and section

if ($user_access >= 2) {
    // User has edit permission, display the form for editing the experiment plan
    echo "<form action='actions/save_experiment_section.php' method='post'>"
        // Hidden inputs for the experiment ID and section
        . "<input type='hidden' name='exp_ID' value='" . htmlspecialchars($exp_ID) . "'>"
        . "<input type='hidden' name='section' value='" . htmlspecialchars($exp_section) . "'>"
        // Progress flag
        . "<input type='checkbox' name='done_flag' value='1' " . ($exp_progress_flags[$exp_section . '_Done'] ? 'checked' : '') . "> Mark as Done<br>"
        // Save button
        . "<input type='submit' value='Save Changes'>"
        // Textbox for the experiment plan
        . "<textarea name='text' rows='10' cols='50'>" . htmlspecialchars($exp_section_text) . "</textarea><br>"
        . "</form>";
} else {
    // We already check for access level < 1 at the start and >=2 here, 
    // so only access level = 1 remains, which is read-only access.
    // User has read only access, display as static text
    echo "<div class='exp_plan_text'>" . nl2br(htmlspecialchars($exp_section_text)) . "</div>";
}

// Close the database connection when done
include "database/close_db.php";

// Display messages from save_experiment_section.php if they exist
if (isset($messages_save_experiment_section)) {
    foreach ($messages_save_experiment_section as $message) {
        echo $message . "<br>";
    }
}
?>
</html>