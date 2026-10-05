<?php
// Insert and remove tags for an experiment

// Add and remove tags for an experiment. Gives feedback on the success of the operation 
// before redirecting back to experiment.php. Check that tags don't already exist before adding, 
// and check that tags do exist before removing.
// Return to experiment.php with messages stored in session for display.

// Session initialization
require_once "../session/init.php"; // Make the session available
require_once "../session/check_user_logged_in.php"; // Check if the user is logged in


// Log activity helper function
require_once "../includes/log_activity.php"; // Include the log_activity function

    // Create message array
$messages = array();

// Variables from POST request
$experiment_id = $_POST['experiment_id'] ?? null;
if ($experiment_id === null) {
    $messages[] = "Error: No experiment ID provided.<br>";
    // Store messages in session to display on experiment.php
    $_SESSION['messages_save_experiment_tags'] = $messages;
    // Redirect back to project library page
    header("Location: ../project_library.php");
    exit();
}
$add_experiment_tags = $_POST['add_tags'] ?? "";
$remove_experiment_tags = $_POST['remove_tags'] ?? "";

// Connect to database
require_once "../database/db.php";

// Check if the user has permission to edit tags for this experiment
include "../includes/check_user_permission.php"; // Include the user permission check function
// TODO(schema-migration): $_SESSION['user_id'] becomes $_SESSION['profile_id']
$user_access = check_user_permission($conn, $_SESSION['profile_id'], 'experiment', $experiment_id);
if ($user_access < 2) {
    $messages[] = "You do not have permission to edit tags for this experiment.<br>";
    // Store messages in session to display on experiment.php
    $_SESSION['messages_save_experiment_tags'] = $messages;
    // Redirect back to experiment.php with the same experiment_id
    header("Location: ../experiment.php?experiment_id=" . urlencode($experiment_id));
    exit();
}

// Remove tags from the database
if ($remove_experiment_tags !== '') {
    $messages[] = "Removing tags:<br>";

    // Relevant variables
        // $experiment_id
        // $remove_experiment_tags

    // Convert tags to array and sanitize
    $remove_tags_array = explode(',', $remove_experiment_tags);
    $remove_tags_array = array_map('trim', $remove_tags_array); // Trim whitespace
    $remove_tags_array = array_filter($remove_tags_array); // Remove empty values

    // Check if tags exist before removing
        // Fetch existing tags for the experiment
    include "../includes/fetch_experiment_tags.php"; // Fetches the tags for the specified experiment ID
        // Compare remove tags with existing tags
    $nonexistent_tags_to_remove = array_diff($remove_tags_array, $experiment_tags);
    if (!empty($nonexistent_tags_to_remove)) {
        $messages[] = "Nonexistent tags: " . implode(", ", $nonexistent_tags_to_remove) . "<br>";
        // Remove nonexistent tags from the remove tags array
        $remove_tags_array = array_diff($remove_tags_array, $nonexistent_tags_to_remove);
    }

    // Remove tags from the database
        // Create query to delete tag
    $sql_delete_tag = "DELETE FROM experiment_tags WHERE experiment_id = ? AND tag = ?";
        // Prepare query
    $stmt_delete_tag = $conn->prepare($sql_delete_tag);
        // Loop over each tag and bind + execute
    foreach ($remove_tags_array as $tag) {
        // Bind parameters
        $stmt_delete_tag->bind_param("ss", $experiment_id, $tag);
        // Execute query
        if ($stmt_delete_tag->execute()) {
            // Log the tag removal
            log_activity(
                $conn,
                $_SESSION['profile_id'],
                'experiment',
                $experiment_id,
                'update',
                'User removed tag: ' . $tag
            );
            $messages[] = "Tag " . $tag . " removed successfully.<br>";
        } else {
            // Log the error
            log_activity(
                $conn,
                $_SESSION['profile_id'],
                'experiment',
                $experiment_id,
                'update',
                'Error removing tag ' . $tag . ': ' . $stmt_delete_tag->error
            );
            $messages[] = "Error removing tag " . $tag . " : " . $stmt_delete_tag->error . "<br>";
        }
    }
    $messages[] = "<br>";
}


// Insert new tags into the database
if ($add_experiment_tags !== '') {
    $messages[] = "Adding tags:<br>";

    // Relevant variables
        // $experiment_id
        // $add_experiment_tags

    // Convert tags to array and sanitize
    $add_tags_array = explode(',', $add_experiment_tags);
    $add_tags_array = array_map('trim', $add_tags_array); // Trim whitespace
    $add_tags_array = array_filter($add_tags_array); // Remove empty values

    // Check if tags already exist
        // Fetch existing tags for the experiment
    include "../includes/fetch_experiment_tags.php"; // Fetches the tags for the specified experiment ID
        // Compare new tags with existing tags
    $duplicate_new_tags = array_intersect($add_tags_array, $experiment_tags);
    if (!empty($duplicate_new_tags)) {
        $messages[] = "Previously existing tags: " . implode(", ", $duplicate_new_tags) . "<br>";
        // Remove duplicate tags from the new tags array
        $add_tags_array = array_diff($add_tags_array, $duplicate_new_tags);
    }

    // Insert new tags into the database
// TODO(schema-migration): old table/column names. Becomes
// INSERT INTO experiment_tags (experiment_id, tag) VALUES (?, ?)
        // Create query to insert tag
    $sql_insert_tag = "INSERT INTO experiment_tags (experiment_id, tag) VALUES (?, ?)";
        // Prepare query
    $stmt_insert_tag = $conn->prepare($sql_insert_tag);
        // Loop over each tag and bind + execute
    foreach ($add_tags_array as $tag) {
        // Bind parameters
        $stmt_insert_tag->bind_param("ss", $experiment_id, $tag);
        // Execute query
        if ($stmt_insert_tag->execute()) {
            // Log the tag addition
            log_activity(
                $conn,
                $_SESSION['profile_id'],
                'experiment',
                $experiment_id,
                'update',
                'User added tag: ' . $tag
            );
            $messages[] = "Tag " . $tag . " added successfully.<br>";
        } else {
            // Log the error
            log_activity(
                $conn,
                $_SESSION['profile_id'],
                'experiment',
                $experiment_id,
                'update',
                'Error adding tag ' . $tag . ': ' . $stmt_insert_tag->error
            );
            $messages[] = "Error adding tag " . $tag . " : " . $stmt_insert_tag->error . "<br>";
        }
    }
}


// Close connection when done
include "../database/close_db.php";

// Store messages in session to display on experiment.php
$_SESSION['messages_save_experiment_tags'] = $messages;

// Redirect back to experiment.php with the same experiment_id
header("Location: ../experiment.php?experiment_id=" . urlencode($experiment_id));
exit();
// // Links in case redirect fails
//     // Link back to experiment.php with the same experiment_id
// if (isset($_POST['experiment_id'])) {
//     echo "<a href='../experiment.php?experiment_id=" . urlencode($_POST['experiment_id']) . "'>Back to experiment</a><br><br>";
// } else {
//     echo "Error: No experiment ID available.<br>";
//     echo "<a href='../project_library.php'>Back to project library</a><br><br>";
// }
?>