<?php
// Edit experiment section

// Update the experiment section in the database by saving changes to the
// text area and optionally updating the progress flag for the section.
// Expects an experiment ID as $experiment_id, a section name as $exp_section, text content
// as $text, and a boolean $done_flag indicating whether the section is done or not.
// Message history is stored in $messages array and returned to the calling 
// script in $messages_save_experiment_section.
// Returns nothing, but redirects back to the experiment section page 
// with the same experiment_id after processing.

// Session initialization
require_once "../session/init.php"; // Make the session available
require_once "../session/check_user_logged_in.php"; // Check if the user is logged in

$messages = array(); // Create message array

// Variables from POST request
$experiment_id = $_POST['experiment_id'] ?? null;
if ($experiment_id === null) {
    $messages[] = "Error: No experiment ID provided.<br>";
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;
    // Redirect back to project library page
    header("Location: ../project_library.php");
    exit();
}
$experiment_section = $_POST['section'] ?? null;
$text = $_POST['text'] ?? null;
$done_flag = $_POST['done_flag'] ?? 0; // Default to 0 (not done) if not set
    // Whitelist the section name ('plan', 'log', 'result')
// TODO(schema-migration): section values become lowercase ('plan', 'log', 'result'),
// matching the new column names plan_text/plan_is_done/plan_updated_at, etc.
$valid_sections = ['plan', 'log', 'result'];
if (!in_array($experiment_section, $valid_sections)) {
    $messages[] = "Invalid section name: " . htmlspecialchars($experiment_section) . "<br>";
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;
    // Redirect back to experiment.php with the same experiment_id because the section name is invalid
    header("Location: ../experiment.php?experiment_id=" . urlencode($experiment_id));
    exit();
}

// Connect to database
require_once "../database/db.php";

// Check if the user has permission to edit progress for this experiment
include "../includes/check_user_permission.php"; // Include the user permission check function
// TODO(schema-migration): $_SESSION['user_id'] becomes $_SESSION['profile_id']
$user_access = check_user_permission($conn, $_SESSION['profile_id'], 'experiment', $experiment_id);
if ($user_access < 2) {
    $messages[] = "You do not have permission to edit this experiment section.<br>";
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;
    // Redirect back to experiment section with the same experiment_id
    header("Location: ../experiment_section.php?experiment_id=" . urlencode($experiment_id) . "&section=" . urlencode($experiment_section));
    exit();
}

// Check if $done_flag differs from database value, and if so, update the database
    // Fetch current progress flag from the database
include "../includes/fetch_experiment_progress.php"; // Fetches the progress status for the specified experiment ID
    // Compare $done_flag with the current value in the database
    $done_flag = (int)$done_flag; // Cast to int for comparison
    $experiment_progress = array_map('intval', $experiment_progress); // Ensure all values are integers
if ($done_flag !== $experiment_progress[$experiment_section . '_is_done']) {
    // TODO(schema-migration): old table/column names and old array key. Becomes
    // UPDATE experiments SET <section>_is_done = ? WHERE experiment_id = ?
    // and the key above becomes $experiment_section . '_is_done'
    // Update the progress flag in the database
    $sql_update_progress = "UPDATE experiments SET " . $experiment_section . "_is_done = ? WHERE experiment_id = ?";
    $stmt_update_progress = $conn->prepare($sql_update_progress);
    $stmt_update_progress->bind_param("ii", $done_flag, $experiment_id);
    if ($stmt_update_progress->execute()) {
        $messages[] = "Progress flag for section " . $experiment_section . " updated successfully.<br>";
    } else {
        $messages[] = "Error updating progress flag for section " . $experiment_section . " : " . $stmt_update_progress->error . "<br>";
    }
}

// Update the text content for the specified section in the database
    // Create query to update the text content for the specified section
// TODO(schema-migration): old table/column names. Becomes
// UPDATE experiments SET <section>_text = ? WHERE experiment_id = ?
$sql_update_text = "UPDATE experiments SET " . $experiment_section . "_text = ? WHERE experiment_id = ?";
    // Prepare query
$stmt_update_text = $conn->prepare($sql_update_text);
    // Bind parameters
$stmt_update_text->bind_param("si", $text, $experiment_id);
    // Execute query
if ($stmt_update_text->execute()) {
        $messages[] = "Text content for section " . $experiment_section . " updated successfully.<br>";
} else {
        $messages[] = "Error updating text content for section " . $experiment_section . " : " . $stmt_update_text->error . "<br>";
}

// TODO(schema-migration): DELETE this whole timestamp update — the new schema's
// *_updated_at columns have ON UPDATE CURRENT_TIMESTAMP (they update automatically
// when the text changes) and experiments.updated_at is a generated column that
// cannot be written. Remove this statement and its messages block.
//     // Create query to update the last update timestamp for the specified section
// $sql_update_timestamp = "UPDATE experiments SET " . $experiment_section . "_updated_at = NOW() WHERE experiment_id = ?";
//     // Prepare query
// $stmt_update_timestamp = $conn->prepare($sql_update_timestamp);
//     // Bind parameters
// $stmt_update_timestamp->bind_param("i", $experiment_id);
//     // Execute query
// if ($stmt_update_timestamp->execute()) {
//         $messages[] = "Last update timestamp for section " . $experiment_section . " updated successfully.<br>";
// } else {
//         $messages[] = "Error updating last update timestamp for section " . $experiment_section . " : " . $stmt_update_timestamp->error . "<br>";
// }

// Store messages in session to display
$_SESSION['messages_save_experiment_section'] = $messages;

// Close connection when done
include "../database/close_db.php";

// Redirect back to experiment section with the same experiment_id and section
header("Location: ../experiment_section.php?experiment_id=" . urlencode($experiment_id) . "&section=" . urlencode($experiment_section));
exit();
?>