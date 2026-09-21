<?php
// Edit experiment section

// Update the experiment section in the database by saving changes to the
// text area and optionally updating the progress flag for the section.
// Expects an experiment ID as $exp_ID, a section name as $section, text content
// as $text, and a boolean $done_flag indicating whether the section is done or not.
// Message history is stored in $messages array and returned to the calling 
// script in $messages_exp_edit_progress.
// Returns true on success, false on failure.

// Session initialization
include "../../Session/init.php"; // Make the session available
$messages = array(); // Create message array

// Variables from POST request
$exp_ID = $_POST['exp_ID'] ?? null;
$section = $_POST['section'] ?? null;
$text = $_POST['text'] ?? null;
$done_flag = $_POST['done_flag'] ?? null;

// Connect to database
include "../../Database_related/db.php";

// Check if the user has permission to edit progress for this experiment
// Is it fine to inherit the access level from experiment.php? Or should we check it again here?

// Check if $done_flag differs from database value, and if so, update the database
    // Fetch current progress flag from the database
include "exp_fetch_progress.php"; // Fetches the progress status for the specified experiment ID
    // Compare $done_flag with the current value in the database
if ($done_flag !== $exp_progress_flags[$section . '_Done']) {
    // Update the progress flag in the database
    $sql_update_progress = "UPDATE Proj_Experiment SET " . $section . "_Done = ? WHERE Exp_ID = ?";
    $stmt_update_progress = $conn->prepare($sql_update_progress);
    $stmt_update_progress->bind_param("is", $done_flag, $exp_ID);
    if ($stmt_update_progress->execute()) {
        $messages[] = "Progress flag for section " . $section . " updated successfully.<br>";
    } else {
        $messages[] = "Error updating progress flag for section " . $section . " : " . $stmt_update_progress->error . "<br>";
    }
}

// Update the text content for the specified section in the database
    // Create query to update the text content for the specified section
$sql_update_text = "UPDATE Proj_Experiment SET " . $section . "_Text = ? WHERE Exp_ID = ?";
    // Prepare query
$stmt_update_text = $conn->prepare($sql_update_text);
    // Bind parameters
$stmt_update_text->bind_param("ss", $text, $exp_ID);
    // Execute query
if ($stmt_update_text->execute()) {
        $messages[] = "Text content for section " . $section . " updated successfully.<br>";
} else {
        $messages[] = "Error updating text content for section " . $section . " : " . $stmt_update_text->error . "<br>";
}

// Update the last update timestamp for the experiment and the specified section in the database
    // Create query to update the last update timestamp for the specified section
$sql_update_timestamp = "UPDATE Proj_Experiment SET " . $section . "_Update = NOW() WHERE Exp_ID = ?";
    // Prepare query
$stmt_update_timestamp = $conn->prepare($sql_update_timestamp);
    // Bind parameters
$stmt_update_timestamp->bind_param("s", $exp_ID);
    // Execute query
if ($stmt_update_timestamp->execute()) {
        $messages[] = "Last update timestamp for section " . $section . " updated successfully.<br>";
} else {
        $messages[] = "Error updating last update timestamp for section " . $section . " : " . $stmt_update_timestamp->error . "<br>";
}

// Store messages in session to display on experiment.php
$_SESSION['messages_exp_edit_section'] = $messages;

// Close connection when done
include "../../Database_related/closeDB.php";

// Redirect back to experiment.php with the same exp_ID
header("Location: ../../experiment.php?exp_ID=" . urlencode($exp_ID));
exit();
?>
<!-- Link back to experiment.php with the same exp_ID in case of redirect failure -->
<a href="../../experiment.php?exp_ID=<?php echo urlencode($exp_ID); ?>">Back to experiment page</a><br><br>
