<?php
// Retrieve project ID for the experiment

// This script fetches the project ID for the specified experiment ID
// It expects the variable $experiment_id to be set before inclusion, which is used to query the database for the project ID associated with that experiment.
// Returns the project ID in $project_id, which can be used to link back to the parent project page.

//  Retieve project ID
// TODO(schema-migration): old table/column names and internally inconsistent
// (Project_ID selected, Experiment_ID in WHERE, Proj_ID/experiment_id in the table).
// Becomes: SELECT project_id FROM experiments WHERE experiment_id = ?
    // Create query
$sql_experiment_parent = "SELECT project_id FROM experiments WHERE experiment_id = ?";
    // Prepare query
$stmt_experiment_parent = $conn->prepare($sql_experiment_parent);
    // Bind the experiment ID parameter
$stmt_experiment_parent->bind_param("s", $experiment_id);
    // Execute query
if ($stmt_experiment_parent->execute()) {
    // Get the result set from the executed query
    $result_experiment_parent = $stmt_experiment_parent->get_result(); // get_result() returns a mysqli_result object
    // Fetch the project ID from the result set
    $project_id = $result_experiment_parent->fetch_assoc()['project_id']; // fetch_assoc() fetches a single row as an associative array, needed to access the value
} else {
    echo "Error retrieving project ID for experiment " . $experiment_id . " : " . $stmt_experiment_parent->error . "<br>";
}

// Continue with page
?>