<?php
// Edit experiment section

// Update the experiment section in the database by saving changes to the
// text area and optionally updating the progress flag for the section.
// Expects an experiment ID as $exp_ID, a section name as $exp_section, text content
// as $text, and a boolean $done_flag indicating whether the section is done or not.
// Message history is stored in $messages array and returned to the calling 
// script in $messages_exp_edit_section.
// Returns nothing, but redirects back to the experiment section page 
// with the same exp_ID after processing.

// Session initialization
include "../../Session/init.php"; // Make the session available
$messages = array(); // Create message array

// Variables from POST request
$exp_ID = $_POST['exp_ID'] ?? null;
if ($exp_ID === null) {
    $messages[] = "Error: No experiment ID provided.<br>";
    // Store messages in session to display
    $_SESSION['messages_exp_edit_section'] = $messages;
    // Redirect back to project library page
    header("Location: ../../project_library.php");
    exit();
}
$exp_section = $_POST['section'] ?? null;
$text = $_POST['text'] ?? null;
$done_flag = $_POST['done_flag'] ?? 0; // Default to 0 (not done) if not set
    // Whitelist the section name ('Plan', 'Log', 'Result')
$valid_sections = ['Plan', 'Log', 'Result'];
if (!in_array($exp_section, $valid_sections)) {
    $messages[] = "Invalid section name: " . htmlspecialchars($exp_section) . "<br>";
    // Store messages in session to display
    $_SESSION['messages_exp_edit_section'] = $messages;
    // Redirect back to experiment.php with the same exp_ID because the section name is invalid
    header("Location: ../../experiment.php?exp_ID=" . urlencode($exp_ID));
    exit();
}

// Connect to database
include "../../Database_related/db.php";

// Check if the user has permission to edit progress for this experiment
include "../../Functional_php/user_permission.php"; // Include the user permission check function
$user_access = check_user_permission($conn, $_SESSION['user_id'], 'experiment', $exp_ID);
if ($user_access < 2) {
    $messages[] = "You do not have permission to edit this experiment section.<br>";
    // Store messages in session to display
    $_SESSION['messages_exp_edit_section'] = $messages;
    // Redirect back to experiment section with the same exp_ID
    header("Location: ../../experiment_section.php?exp_ID=" . urlencode($exp_ID) . "&section=" . urlencode($exp_section));
    exit();
}

// Check if $done_flag differs from database value, and if so, update the database
    // Fetch current progress flag from the database
include "exp_fetch_progress.php"; // Fetches the progress status for the specified experiment ID
    // Compare $done_flag with the current value in the database
    $done_flag = (int)$done_flag; // Cast to int for comparison
    $exp_progress_flags = array_map('intval', $exp_progress_flags); // Ensure all values are integers
if ($done_flag !== $exp_progress_flags[$exp_section . '_Done']) {
    // Update the progress flag in the database
    $sql_update_progress = "UPDATE Proj_Experiment SET " . $exp_section . "_Done = ? WHERE Exp_ID = ?";
    $stmt_update_progress = $conn->prepare($sql_update_progress);
    $stmt_update_progress->bind_param("is", $done_flag, $exp_ID);
    if ($stmt_update_progress->execute()) {
        $messages[] = "Progress flag for section " . $exp_section . " updated successfully.<br>";
    } else {
        $messages[] = "Error updating progress flag for section " . $exp_section . " : " . $stmt_update_progress->error . "<br>";
    }
}

// Update the text content for the specified section in the database
    // Create query to update the text content for the specified section
$sql_update_text = "UPDATE Proj_Experiment SET " . $exp_section . "_Text = ? WHERE Exp_ID = ?";
    // Prepare query
$stmt_update_text = $conn->prepare($sql_update_text);
    // Bind parameters
$stmt_update_text->bind_param("ss", $text, $exp_ID);
    // Execute query
if ($stmt_update_text->execute()) {
        $messages[] = "Text content for section " . $exp_section . " updated successfully.<br>";
} else {
        $messages[] = "Error updating text content for section " . $exp_section . " : " . $stmt_update_text->error . "<br>";
}

// Update the last update timestamp for the experiment and the specified section in the database
    // Create query to update the last update timestamp for the specified section
$sql_update_timestamp = "UPDATE Proj_Experiment SET " . $exp_section . "_Updated = NOW() WHERE Exp_ID = ?";
    // Prepare query
$stmt_update_timestamp = $conn->prepare($sql_update_timestamp);
    // Bind parameters
$stmt_update_timestamp->bind_param("s", $exp_ID);
    // Execute query
if ($stmt_update_timestamp->execute()) {
        $messages[] = "Last update timestamp for section " . $exp_section . " updated successfully.<br>";
} else {
        $messages[] = "Error updating last update timestamp for section " . $exp_section . " : " . $stmt_update_timestamp->error . "<br>";
}

// Store messages in session to display
$_SESSION['messages_exp_edit_section'] = $messages;

// Close connection when done
include "../../Database_related/closeDB.php";

// Redirect back to experiment section with the same exp_ID and section
header("Location: ../../experiment_section.php?exp_ID=" . urlencode($exp_ID) . "&section=" . urlencode($exp_section));
exit();
?>