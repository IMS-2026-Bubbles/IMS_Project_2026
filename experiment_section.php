<?php
// Experiment sub-section page

// Arrive from:
// TODO: experiment_section.php (click on section link)
// experiment.php (click on section link)
// actions/save_experiment_section.php (after saving changes to a section)
// Action:
// Display experiment section information, including tags, progress flags, and last updated timestamps
// Allow users with sufficient permissions to edit the section text and mark it as done
// Redirect to:
// navbar options
// experiment.php (click on section link)
// actions/save_experiment_section.php (after saving changes to a section)

// Session initialization
require_once 'session/init.php';  // Make the session available
// Check if the user is logged in
require_once 'session/check_user_logged_in.php';

// Variables
// $profile_id from session
// $experiment_id from URL (URL is always a GET request)
$experiment_id = $_GET['experiment_id'] ?? NULL;
if ($experiment_id === null) {
    $messages[] = 'Error: No experiment ID provided.<br>';
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;
    // Redirect back to project library page
    header('Location: library.php');
    exit();
}
$experiment_section = $_GET['section'] ?? NULL;  // Section name from URL (URL is always a GET request)
// Check for NULL section name
if ($experiment_section === null) {
    $messages[] = 'Error: No experiment section provided.<br>';
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;
    // Redirect back to experiment.php with the same experiment_id
    header('Location: experiment.php?experiment_id=' . urlencode($experiment_id));
    exit();
}
// Whitelist section names
// TODO(schema-migration): section values become lowercase ('plan', 'log', 'result'),
// matching the new column names plan_text/plan_is_done/plan_updated_at, etc.
$valid_sections = ['plan', 'log', 'result'];
if (!in_array($experiment_section, $valid_sections)) {
    $messages[] = 'Error: Invalid experiment section provided.<br>';
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;
    // Redirect back to experiment.php with the same experiment_id
    header('Location: experiment.php?experiment_id=' . urlencode($experiment_id));
    exit();
}

// Connect to database
require_once 'database/db.php';

require_once 'includes/log_activity.php';  // Include the log_activity function

// Check if the user has permission to view this experiment
include 'includes/check_user_permission.php';  // Include the user permission check function
// TODO(schema-migration): $_SESSION['user_id'] becomes $_SESSION['profile_id'];
// $experiment_id becomes $experiment_id; the array keys used below
// ($project_experiment_name, $experiment_progress, $experiment_updated_at) follow the new
// column names
$user_access = check_user_permission($conn, $_SESSION['profile_id'], 'experiment', $experiment_id);
if ($user_access < 1) {
    // Access level 0 means no access
    echo 'You do not have permission to view this content.';

    // Log the access denied event
    log_activity(
        $conn,
        $_SESSION['profile_id'],
        'experiment',
        $experiment_id,
        'access_denied',
        'User attempted to access experiment without permission'
    );

    exit();
}

// Retrieve messages from save_experiment_section.php if they exist
if (isset($_SESSION['messages_save_experiment_section'])) {
    $messages_save_experiment_section = $_SESSION['messages_save_experiment_section'];
    // Clear the messages from the session after retrieving them
    unset($_SESSION['messages_save_experiment_section']);
}
?>



<!Doctype html>
<html>

<!--  -->
<!-- Front end starts here -->
<!--  -->


<?php
// Navbar
include 'includes/navbar.php';  // Include the navbar

// Display project name, experiment name, and experiment section name
include 'includes/fetch_project_experiment_names.php';  // fetch the name and ID for project and experiment as $project_experiment_name

// Diplay the project, experiment, and section name
echo 'Project: ' . htmlspecialchars($project_experiment_name['project_name']) . '<br>';
echo 'Experiment: ' . htmlspecialchars($project_experiment_name['experiment_name']) . '<br>';
echo 'Section: ' . htmlspecialchars($experiment_section) . '<br><br>';
?>

<!-- Link back to main experiment page -->
<a href="experiment.php?experiment_id=<?php echo urlencode($experiment_id); ?>">Back to: <?php echo htmlspecialchars($project_experiment_name['experiment_name']); ?></a><br><br>

<!-- Tags (static), Done toggle, save/submit -->
<?php
// Display current tags for the experiment
include 'includes/fetch_experiment_tags.php';  // Fetches the tags for the specified experiment ID
echo 'Experiment tags: ' . implode(', ', $experiment_tags) . '<br><br>';

// Display last updated timestamp for the experiment section
include 'includes/fetch_experiment_timestamps.php';  // Fetches the last updated timestamp for the specified experiment ID and section
echo 'Last updated: ' . $experiment_updated_at[$experiment_section . '_updated_at'] . '<br><br>';

// Display the "Done" toggle for the experiment plan
include 'includes/fetch_experiment_progress.php';  // Fetches the progress status for the specified experiment ID
// Define the progress flag display helper.
include 'includes/render_progress_badge.php';
echo "<div class='exp_progress_flags'>"  // String structured vertically for code readability.
    . experiment_progress_badge($experiment_section, $experiment_progress[$experiment_section . '_is_done'])
    . '</div>';
echo '<br>';

// Retrieve the text content for the experiment plan section
include 'includes/fetch_experiment_section_text.php';  // Fetches the text content for the specified experiment ID and section

if ($user_access >= 2 && $experiment_progress[$experiment_section . '_is_done'] == 0) {
    // User has edit permission, display the form for editing the experiment plan
    // ALSO section is not marked as done => allow editing. If section is marked as done, only allow read-only view.
    echo "<form action='actions/save_experiment_section.php' method='post'>"
        // Hidden inputs for the experiment ID and section
        . "<input type='hidden' name='experiment_id' value='" . htmlspecialchars($experiment_id) . "'>"
        . "<input type='hidden' name='section' value='" . htmlspecialchars($experiment_section) . "'>"
        // Progress flag
        . "<input type='checkbox' name='done_flag' value='1' " . ($experiment_progress[$experiment_section . '_is_done'] ? 'checked' : '') . '> Mark as Done<br>'
        . '<br>'
        // Save button
        . "<input type='submit' value='Save Changes'>"
        . '<br><br>'
        // Textbox for the experiment plan
        . "<textarea name='text' rows='10' cols='50'>" . htmlspecialchars($experiment_section_text) . '</textarea><br>'
        . '</form>';
} else {
    // We already check for access level < 1 at the start and >=2 here,
    // so only access level = 1 remains, which is read-only access.
    // User has read only access OR section is marked as done, display as static text
    echo '<h3>Experiment ' . htmlspecialchars($experiment_section) . ' (Read Only)</h3>';
    echo "<div class='experiment_section_text'>" . nl2br(htmlspecialchars($experiment_section_text)) . '</div>';
}

// Close the database connection when done
include 'database/close_db.php';
// Display messages from save_experiment_section.php if they exist
if (isset($messages_save_experiment_section)) {
    foreach ($messages_save_experiment_section as $message) {
        echo $message . '<br>';
    }
}
?>
</html>