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
require_once '../session/init.php';  // Make the session available
require_once '../session/check_user_logged_in.php';  // Check if the user is logged in

// Log activity helper function
require_once '../includes/log_activity.php';  // Include the log_activity function

$messages = array();  // Create message array

// Variables from POST request
$experiment_id = $_POST['experiment_id'] ?? null;
if ($experiment_id === null) {
    $messages[] = 'Error: No experiment ID provided.<br>';
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;
    // Redirect back to project library page
    header('Location: ../library.php');
    exit();
}
$experiment_section = $_POST['section'] ?? null;
$text = $_POST['text'] ?? null;
$done_flag = $_POST['done_flag'] ?? 0;  // Default to 0 (not done) if not set
$done_flag = (int) $done_flag;  // Cast to int for comparison

// Whitelist the section name ('plan', 'log', 'result')
$valid_sections = ['plan', 'log', 'result'];
if (!in_array($experiment_section, $valid_sections)) {
    $messages[] = 'Invalid section name: ' . htmlspecialchars($experiment_section) . '<br>';
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;
    // Redirect back to experiment.php with the same experiment_id because the section name is invalid
    header('Location: ../experiment.php?experiment_id=' . urlencode($experiment_id));
    exit();
}

// Connect to database
require_once '../database/db.php';

// Encryption
require_once '../includes/encryption.php';

// Check if the user has permission to edit progress for this experiment
include '../includes/check_user_permission.php';  // Include the user permission check function

$user_access = check_user_permission($conn, $_SESSION['profile_id'], 'experiment', $experiment_id);
if ($user_access < 2) {
    // Log the access denied event
    log_activity(
        $conn,
        $_SESSION['profile_id'],
        'experiment',
        $experiment_id,
        'access_denied',
        'User attempted to edit experiment section without permission'
    );

    $messages[] = 'You do not have permission to edit this experiment section.<br>';
    // Store messages in session to display
    $_SESSION['messages_save_experiment_section'] = $messages;

    // Redirect back to experiment section with the same experiment_id
    header('Location: ../experiment_section.php?experiment_id=' . urlencode($experiment_id) . '&section=' . urlencode($experiment_section));
    exit();
}

// Check if $done_flag differs from database value, and if so, update the database
// Fetch current progress flag from the database
include '../includes/fetch_experiment_progress.php';  // Fetches the progress status for the specified experiment ID

// Compare $done_flag with the current value in the database
$experiment_progress = array_map('intval', $experiment_progress);  // Ensure all values are integers

if ($done_flag !== $experiment_progress[$experiment_section . '_is_done']) {
    $sql_update_progress = 'UPDATE experiments SET ' . $experiment_section . '_is_done = ? WHERE experiment_id = ?';
    $stmt_update_progress = $conn->prepare($sql_update_progress);
    $stmt_update_progress->bind_param('ii', $done_flag, $experiment_id);

    if ($stmt_update_progress->execute()) {
        // Log the progress update
        log_activity(
            $conn,
            $_SESSION['profile_id'],
            'experiment',
            $experiment_id,
            'update',
            'User updated progress flag for section ' . $experiment_section . ' to ' . $done_flag
        );
        $messages[] = 'Progress flag for section ' . $experiment_section . ' updated successfully.<br>';
    } else {
        // Log the error
        log_activity(
            $conn,
            $_SESSION['profile_id'],
            'experiment',
            $experiment_id,
            'update',
            'Error updating progress flag for section ' . $experiment_section . ': ' . $stmt_update_progress->error
        );
        $messages[] = 'Error updating progress flag for section ' . $experiment_section . ' : ' . $stmt_update_progress->error . '<br>';
    }
}

// Update the text content for the specified section in the database
// Encrypt the text content before saving to the database
$encrypted_text = encrypt_text($text, $encryption_key);

// Create query to update the text content for the specified section
$sql_update_text = 'UPDATE experiments SET ' . $experiment_section . '_text = ? WHERE experiment_id = ?';
// Prepare query
$stmt_update_text = $conn->prepare($sql_update_text);
// Bind parameters
$stmt_update_text->bind_param('si', $encrypted_text, $experiment_id);
// Execute query

if ($stmt_update_text->execute()) {
    // Log the text update
    log_activity(
        $conn,
        $profile_id,
        'experiment',
        $experiment_id,
        'update',
        'User updated text content for section ' . $experiment_section
    );
    $messages[] = 'Text content for section ' . $experiment_section . ' updated successfully.<br>';
} else {
    // Log the error
    log_activity(
        $conn,
        $profile_id,
        'experiment',
        $experiment_id,
        'update',
        'Error updating text content for section ' . $experiment_section . ': ' . $stmt_update_text->error
    );
    $messages[] = 'Error updating text content for section ' . $experiment_section . ' : ' . $stmt_update_text->error . '<br>';
}

// Store messages in session to display
$_SESSION['messages_save_experiment_section'] = $messages;

// Close connection when done
include '../database/close_db.php';

// Redirect back to experiment section with the same experiment_id and section
header('Location: ../experiment_section.php?experiment_id=' . urlencode($experiment_id) . '&section=' . urlencode($experiment_section));
exit();
?>