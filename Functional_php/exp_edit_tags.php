<?php
// Insert and remove tags for an experiment

// Add and remove tags for an experiment. Gives feedback on the success of the operation 
// before redirecting back to experiment.php. Sanitize and validate input.

// Connect to database
include "/Database_related/db.php";

// Check if the form was submitted
if (isset($_POST['new_tags']) && isset($_POST['exp_ID'])) {
    // Get user input from POST request
    $new_tags = $_POST['new_tags'];
    $exp_ID = $_POST['exp_ID'];
    // Convert tags to array and sanitize
    $new_tags_array = explode(',', $new_tags);
    $new_tags_array = array_map('trim', $new_tags_array); // Trim whitespace
    $new_tags_array = array_filter($new_tags_array); // Remove empty values

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
            echo "Tag " . $tag . " added successfully.<br>";
        } else {
            echo "Error adding tag " . $tag . " : " . $stmt_insert_tag->error . "<br>";
        }
    }
}
?>