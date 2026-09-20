<?php
// Process the AJAX request to update the experiment progress

// Recieves the experiment ID and the new progress status (done or not_done) from the AJAX request.
// Update the database with the new progress status for the specified experiment ID.
// Return a success or failure message.

// Check for and retrieve the experiment ID and progress status from the POST request
if (isset($_POST['exp_ID']) && isset($_POST['section']) && isset($_POST['progress'])) {
    $exp_ID = $_POST['exp_ID'];
    $exp_section = $_POST['section'];
    $progress = $_POST['progress'];

    // Convert progress to a boolean value for database storage
    if ($progress === 'done') {
        $progress = 1; // Mark as done
    } else {
        $progress = 0; // Mark as not done
    }

    // Connect to the database
    include "../../Database_related/db.php";

    // Update the progress status in the database
        // Create query
    $sql_update_progress = "UPDATE Proj_Experiment SET ? = ? WHERE Experiment_ID = ?";
        // Prepare query
    $stmt_update_progress = $conn->prepare($sql_update_progress);
        // Bind parameters (boolean is 0 or 1, cast as integer)
    $stmt_update_progress->bind_param("sis", $exp_section, $progress, $exp_ID);
        // Execute query
    $stmt_update_progress->execute();

    // Check for errors
    if ($stmt_update_progress->error) {
        echo "Error updating progress";
    } else {
        echo "Progress updated successfully";
    }

    // Close connection when done
    include "../../Database_related/closeDB.php";
} else {
    echo "Invalid request: Missing parameters";
}
?>