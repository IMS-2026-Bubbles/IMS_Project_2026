<?php
// Retrieve project ID for the experiment

// This script fetches the project ID for the specified experiment ID
// It expects the variable $exp_ID to be set before inclusion, which is used to query the database for the project ID associated with that experiment.
// Returns the project ID in $proj_ID, which can be used to link back to the parent project page.

//  Retieve project ID
    // Create query
$sql_exp_parent = "SELECT Project_ID FROM Proj_Experiment WHERE Experiment_ID = ?";
    // Prepare query
$stmt_exp_parent = $conn->prepare($sql_exp_parent);
    // Bind the experiment ID parameter
$stmt_exp_parent->bind_param("s", $exp_ID);
    // Execute query
if ($stmt_exp_parent->execute()) {
    // Get the result set from the executed query
    $result_exp_parent = $stmt_exp_parent->get_result(); // get_result() returns a mysqli_result object
    // Fetch the project ID from the result set
    $proj_ID = $result_exp_parent->fetch_assoc()['Project_ID']; // fetch_assoc() fetches a single row as an associative array, needed to access the value
} else {
    echo "Error retrieving project ID for experiment " . $exp_ID . " : " . $stmt_exp_parent->error . "<br>";
}

// Continue with page
?>