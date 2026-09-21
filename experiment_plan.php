<!Doctype html>
<html>
<?php
// Session initialization
include "Session/init.php"; // Make the session available

// Check if the user is logged in
include "Session/check_user_logged_in.php";

// Variables
    // $exp_ID from URL (URL is always a GET request)
    $exp_ID = $_GET['exp_ID'] ?? NULL;
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

// Retrieve messages from exp_edit_section.php if they exist
if (isset($_SESSION['messages_exp_edit_section'])) {
    $messages_exp_edit_section = $_SESSION['messages_exp_edit_section'];
    // Clear the messages from the session after retrieving them
    unset($_SESSION['messages_exp_edit_section']);
}
?>

<!-- Link back to main experiment page -->
<a href="experiment.php?exp_ID=<?php echo urlencode($exp_ID); ?>">Back to experiment page</a><br><br>

<!-- Tags (static), Done toggle, save/submit -->
<?php
// Display current tags for the experiment
include "Functional_php/exp_helpers/exp_fetch_tags.php"; // Fetches the tags for the specified experiment ID
echo "Experiment tags: " . implode(", ", $exp_tags_array) . "<br><br>";

// Display last updated timestamp for the experiment plan section
include "Functional_php/exp_helpers/exp_fetch_updated.php"; // Fetches the last updated timestamp for the specified experiment ID and section
echo "Last updated: " . $exp_last_update[$exp_section . '_Updated'] . "<br><br>";

// Display the "Done" toggle for the experiment plan
include "Functional_php/exp_helpers/exp_fetch_progress.php"; // Fetches the progress status for the specified experiment ID
// Define the progress flag display helper.
include "Functional_php/exp_helpers/exp_progress_fun.php";
echo "<div class='exp_progress_flags'>" // String structured vertically for code readability.
    . exp_progress_flags($exp_section, $exp_progress_flags[$exp_section . '_Done'])
    . "</div>";
echo "<br>";

// Retrieve the text content for the experiment plan section
include "Functional_php/exp_helpers/exp_fetch_text.php"; // Fetches the text content for the specified experiment ID and section

if ($user_access >= 2) {
    // User has edit permission, display the form for editing the experiment plan
    echo "<form action='Functional_php/exp_helpers/exp_edit_section.php' method='post'>"
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
} elseif ($user_access < 2 && $user_access >= 1) {
    // User has read only access, display as static text
    echo "<div class='exp_plan_text'>" . nl2br(htmlspecialchars($exp_section_text)) . "</div>";
}

// Display messages from exp_edit_section.php if they exist
if (isset($messages_exp_edit_section)) {
    foreach ($messages_exp_edit_section as $message) {
        echo $message . "<br>";
    }
}
?>
</html>