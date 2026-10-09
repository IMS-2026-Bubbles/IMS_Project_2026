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

// Begin transaction to ensure atomicity of updates
$conn->begin_transaction();

// $transaction_ok is a flag to track if all database operations succeed for a given transaction.
// If any operation fails, we will roll back the transaction.
$transaction_ok = true;

if ($done_flag !== $experiment_progress[$experiment_section . '_is_done']) {
    if ($transaction_ok) {  // technically redundant, but keeps the logic clear
        // Update the progress flag in the database if it has changed
        $sql_update_progress = 'UPDATE experiments SET ' . $experiment_section . '_is_done = ? WHERE experiment_id = ?';
        $stmt_update_progress = $conn->prepare($sql_update_progress);
        $stmt_update_progress->bind_param('ii', $done_flag, $experiment_id);
        $transaction_ok = $stmt_update_progress->execute();  // transaction flag updated
        if (!$transaction_ok) {
            // On failure, capture error for logging
            $error_update_progress = $stmt_update_progress->error;
        }
        $stmt_update_progress->close();
    }
}

// Update the text content for the specified section in the database
// Compare the hash of the new text with the hash of the existing text (from POST) to determine if an update is necessary
$text_fingerprint = $_POST['text_fingerprint'] ?? '';
if (hash('sha256', $text) === $text_fingerprint) {
    // No changes detected in the text content, skip updating the database
    $messages[] = 'No changes detected in the text content for section ' . $experiment_section . '. Skipping database update.<br>';
} else {
    // Changes detected, proceed to update the database

    // Encrypt the text content before saving to the database
    $encrypted_text = encrypt_text($text, $encryption_key);

    if ($transaction_ok) {
        // Update the text content for the specified section
        $sql_update_text = 'UPDATE experiments SET ' . $experiment_section . '_text = ? WHERE experiment_id = ?';
        $stmt_update_text = $conn->prepare($sql_update_text);
        $stmt_update_text->bind_param('si', $encrypted_text, $experiment_id);
        $transaction_ok = $stmt_update_text->execute();  // transaction flag updated
        if (!$transaction_ok) {
            // On failure, capture error for logging
            $error_update_text = $stmt_update_text->error;
        }
        $stmt_update_text->close();
    }

    if ($transaction_ok) {
        // Increment saved_changes counter
        $sql_increment_saved_changes = 'UPDATE profiles SET saved_changes = saved_changes + 1 WHERE profile_id = ?';
        $stmt_increment_saved_changes = $conn->prepare($sql_increment_saved_changes);
        $stmt_increment_saved_changes->bind_param('i', $profile_id);
        $transaction_ok = $stmt_increment_saved_changes->execute();  // transaction flag updated
        if (!$transaction_ok) {
            // On failure, capture error for logging
            $error_increment_saved_changes = $stmt_increment_saved_changes->error;
        }
        $stmt_increment_saved_changes->close();
    }
}

// Commit or rollback the transaction based on the success of the operations
if ($transaction_ok) {
    // All operations succeeded, commit the transaction
    $conn->commit();
    $messages[] = 'Experiment section ' . $experiment_section . ' updated successfully.<br>';

    if ($done_flag !== $experiment_progress[$experiment_section . '_is_done']) {
        // Log the successful progress flag update if it was changed
        log_activity(
            $conn,
            $_SESSION['profile_id'],
            'experiment',
            $experiment_id,
            'update',
            'User updated progress flag for section ' . $experiment_section . ' to ' . $done_flag
        );
    }

    if (hash('sha256', $text) !== $text_fingerprint) {
        // Log the successful text update if it was changed
        log_activity(
            $conn,
            $_SESSION['profile_id'],
            'experiment',
            $experiment_id,
            'update',
            'User updated text content for section ' . $experiment_section
        );

        // Log the successful saved_changes increment
        log_activity(
            $conn,
            $_SESSION['profile_id'],
            'profile',
            $profile_id,
            'update',
            'User successfully incremented saved_changes counter for experiment ' . $experiment_id . ' text update in section ' . $experiment_section
        );
    }
} else {
    // An error occurred during one of the operations, rollback the transaction
    $conn->rollback();
    $messages[] = 'Error updating experiment section ' . $experiment_section . '. Transaction rolled back.<br>';

    // Determine which operation failed and log the appropriate error message
    if (isset($error_update_progress)) {
        // Detail description of the error for progress flag update if it was changed
        $details = 'Error updating progress flag for section ' . $experiment_section . ': ' . $error_update_progress;
    } elseif (isset($error_update_text)) {
        // Detail description of the error for text update if it was changed
        $details = 'Error updating text content for section ' . $experiment_section . ': ' . $error_update_text;
    } elseif (isset($error_increment_saved_changes)) {
        // Detail description of the error for saved_changes increment
        $details = 'Error incrementing saved_changes counter for experiment ' . $experiment_id . ' text update in section ' . $experiment_section . ': ' . $error_increment_saved_changes;
    } else {
        // Generic error message if no specific error was captured
        $details = 'Unknown error occurred during transaction.';
    }

    // Log the error with the appropriate details
    log_activity(
        $conn,
        $_SESSION['profile_id'],
        'experiment',
        $experiment_id,
        'update',
        $details
    );
}

// Store messages in session to display
$_SESSION['messages_save_experiment_section'] = $messages;

// Close connection when done
include '../database/close_db.php';

// Redirect back to experiment section with the same experiment_id and section
header('Location: ../experiment_section.php?experiment_id=' . urlencode($experiment_id) . '&section=' . urlencode($experiment_section));
exit();
?>

Pattern for transactions:

1. $conn->begin_transaction();

2. $transaction_ok = true;  // Initialize transaction success flag
// technically reduntant, but keeps the logic clear

3. Do query with:
if ($transaction_ok) {
    // Query code here
    $transaction_ok = $stmt->execute();  // transaction flag updated
    if (!$transaction_ok) {
        // On failure, capture error for logging
        $error_variable = $stmt->error;
    }
    $stmt->close();
}

4. Repeat step 3 for each query in the transaction.

5. Commit or rollback the transaction based on the success of the operations
if ($transaction_ok) {
    // All operations succeeded, commit the transaction
    $conn->commit();
    // Log success messages here
} else {
    // An error occurred during one of the operations, rollback the transaction
    $conn->rollback();
    // Log error messages here based on which operation failed
}