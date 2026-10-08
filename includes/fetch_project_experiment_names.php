<?php
// Retrieve the name and ID for the project and experiment

// Retrieve the project name and ID for a specific experiment.
// Expects an experiment ID as $experiment_id.
// Returns an associative array with keys 'Project_ID', 'Project_Name',
// 'Experiment_ID', and 'Experiment_Name' in $project_experiment_name

// Create query
$sql_project_experiment_name =
    'SELECT 
        experiments.experiment_id, 
        experiments.name AS experiment_name, 
        projects.project_id, 
        projects.name AS project_name
    FROM experiments 
    JOIN projects 
        ON experiments.project_id = projects.project_id 
    WHERE experiments.experiment_id = ?
    ';
// Prepare query
$stmt_project_experiment_name = $conn->prepare($sql_project_experiment_name);
// Bind parameters
$stmt_project_experiment_name->bind_param('i', $experiment_id);
// Execute query
if ($stmt_project_experiment_name->execute()) {
    // Get the result set from the executed query
    $result_project_experiment_name = $stmt_project_experiment_name->get_result();  // get_result() returns a mysqli_result object
    // Fetch the project and experiment names into an associative array
    $project_experiment_name = $result_project_experiment_name->fetch_assoc();  // fetch_assoc() fetches a single row as an associative array
    // Check if the result is empty (i.e., no experiment found with the given ID)
    if (!$project_experiment_name) {
        echo 'No experiment found with ID: ' . htmlspecialchars($experiment_id);
        exit();
    }
} else {
    echo 'Error executing query: ' . $conn->error;
}
?>