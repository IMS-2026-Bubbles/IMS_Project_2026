<?php
// Retrieve experiment tags

// This script is included to retrieve the MySQL result object with tags for the specified experiment.
// It expects the variable $exp_ID to be set before inclusion, which is used to query the database for tags associated with that experiment.
// Returns a MySQLi result object in $result_exp_tags, which can be used to fetch the tags.


// Retrieve current tags
    // Create query
$sql_exp_tags = "SELECT Exp_Tag FROM Exp_Tag WHERE Experiment_ID = ?";
    // Prepare query
$stmt_exp_tags = $conn->prepare($sql_exp_tags);
    // Bind the search term parameter
$stmt_exp_tags->bind_param("s", $exp_ID);
    // Execute query
$stmt_exp_tags->execute();
    // Get the result set from the executed query
$result_exp_tags = $stmt_exp_tags->get_result(); // get_result() returns a mysqli_result object

// Continue with page.
?>