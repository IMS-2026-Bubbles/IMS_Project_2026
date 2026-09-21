<?php
// Insert and remove tags for an experiment

// Add and remove tags for an experiment. Gives feedback on the success of the operation 
// before redirecting back to experiment.php. Check that tags don't already exist before adding, 
// and check that tags do exist before removing.
// Return to experiment.php with messages stored in session for display.

// Session initialization
include "../../Session/init.php"; // Make the session available
    // Create message array
$messages = array();

// Variables from POST request
$exp_ID = $_POST['exp_ID'] ?? null;
if ($exp_ID === null) {
    $messages[] = "Error: No experiment ID provided.<br>";
    // Store messages in session to display on experiment.php
    $_SESSION['messages_exp_edit_tags'] = $messages;
    // Redirect back to project library page
    header("Location: ../../project_library.php");
    exit();
}
$exp_tags_add = $_POST['new_tags'] ?? "";
$exp_tags_remove = $_POST['remove_tags'] ?? "";

// Connect to database
include "../../Database_related/db.php";

// Check if the user has permission to edit tags for this experiment
include "../../Functional_php/user_permission.php"; // Include the user permission check function
$user_access = check_user_permission($conn, $_SESSION['user_id'], 'experiment', $exp_ID);
if ($user_access < 2) {
    $messages[] = "You do not have permission to edit tags for this experiment.<br>";
    // Store messages in session to display on experiment.php
    $_SESSION['messages_exp_edit_tags'] = $messages;
    // Redirect back to experiment.php with the same exp_ID
    header("Location: ../../experiment.php?exp_ID=" . urlencode($exp_ID));
    exit();
}

// Remove tags from the database
if ($exp_tags_remove !== '') {
    $messages[] = "Removing tags:<br>";

    // Relevant variables
        // $exp_ID
        // $exp_tags_remove

    // Convert tags to array and sanitize
    $remove_tags_array = explode(',', $exp_tags_remove);
    $remove_tags_array = array_map('trim', $remove_tags_array); // Trim whitespace
    $remove_tags_array = array_filter($remove_tags_array); // Remove empty values

    // Check if tags exist before removing
        // Fetch existing tags for the experiment
    include "exp_fetch_tags.php"; // Fetches the tags for the specified experiment ID
        // Compare remove tags with existing tags
    $nonexistent_remove_tags = array_diff($remove_tags_array, $exp_tags_array);
    if (!empty($nonexistent_remove_tags)) {
        $messages[] = "Nonexistent tags: " . implode(", ", $nonexistent_remove_tags) . "<br>";
        // Remove nonexistent tags from the remove tags array
        $remove_tags_array = array_diff($remove_tags_array, $nonexistent_remove_tags);
    }

    // Remove tags from the database
        // Create query to delete tag
    $sql_delete_tag = "DELETE FROM Exp_Tag WHERE Experiment_ID = ? AND Exp_Tag = ?";
        // Prepare query
    $stmt_delete_tag = $conn->prepare($sql_delete_tag);
        // Loop over each tag and bind + execute
    foreach ($remove_tags_array as $tag) {
        // Bind parameters
        $stmt_delete_tag->bind_param("ss", $exp_ID, $tag);
        // Execute query
        if ($stmt_delete_tag->execute()) {
            $messages[] = "Tag " . $tag . " removed successfully.<br>";
        } else {
            $messages[] = "Error removing tag " . $tag . " : " . $stmt_delete_tag->error . "<br>";
        }
    }
    $messages[] = "<br>";
}


// Insert new tags into the database
if ($exp_tags_add !== '') {
    $messages[] = "Adding tags:<br>";

    // Relevant variables
        // $exp_ID
        // $exp_tags_add

    // Convert tags to array and sanitize
    $new_tags_array = explode(',', $exp_tags_add);
    $new_tags_array = array_map('trim', $new_tags_array); // Trim whitespace
    $new_tags_array = array_filter($new_tags_array); // Remove empty values

    // Check if tags already exist
        // Fetch existing tags for the experiment
    include "exp_fetch_tags.php"; // Fetches the tags for the specified experiment ID
        // Compare new tags with existing tags
    $duplicate_new_tags = array_intersect($new_tags_array, $exp_tags_array);
    if (!empty($duplicate_new_tags)) {
        $messages[] = "Previously existing tags: " . implode(", ", $duplicate_new_tags) . "<br>";
        // Remove duplicate tags from the new tags array
        $new_tags_array = array_diff($new_tags_array, $duplicate_new_tags);
    }

    // Insert new tags into the database
        // Create query to insert tag
    $sql_insert_tag = "INSERT INTO Exp_Tag (Experiment_ID, Exp_Tag) VALUES (?, ?)";
        // Prepare query
    $stmt_insert_tag = $conn->prepare($sql_insert_tag);
        // Loop over each tag and bind + execute
    foreach ($new_tags_array as $tag) {
        // Bind parameters
        $stmt_insert_tag->bind_param("ss", $exp_ID, $tag);
        // Execute query
        if ($stmt_insert_tag->execute()) {
            $messages[] = "Tag " . $tag . " added successfully.<br>";
        } else {
            $messages[] = "Error adding tag " . $tag . " : " . $stmt_insert_tag->error . "<br>";
        }
    }
}


// Close connection when done
include "../../Database_related/closeDB.php";

// Store messages in session to display on experiment.php
$_SESSION['messages_exp_edit_tags'] = $messages;

// Redirect back to experiment.php with the same exp_ID
header("Location: ../../experiment.php?exp_ID=" . urlencode($exp_ID));
exit();
// // Links in case redirect fails
//     // Link back to experiment.php with the same exp_ID
// if (isset($_POST['exp_ID'])) {
//     echo "<a href='../../experiment.php?exp_ID=" . urlencode($_POST['exp_ID']) . "'>Back to experiment</a><br><br>";
// } else {
//     echo "Error: No experiment ID available.<br>";
//     echo "<a href='../../project_library.php'>Back to project library</a><br><br>";
// }
?>