<?php
// Retrieve the name and ID for the project and experiment

// Retrieve the project name and ID for a specific experiment.
// Expects an experiment ID as $exp_ID.
// Returns an associative array with keys 'Project_ID', 'Project_Name', 
// 'Experiment_ID', and 'Experiment_Name' in $proj_exp_name_array

// TODO(schema-migration): old table/column names. Tables Proj_Experiment/Project
// become experiments/projects; columns Exp_ID/Exp_Name/Proj_ID/Proj_Name become
// experiment_id/name/project_id/name. The $proj_exp_name_array keys change with them.
// Create query
$sql_proj_exp_name =
    "SELECT 
        Proj_Experiment.Exp_ID, 
        Proj_Experiment.Exp_Name, 
        Project.Proj_ID, 
        Project.Proj_Name 
    FROM Proj_Experiment 
    JOIN Project 
        ON Proj_Experiment.Proj_ID = Project.Proj_ID 
    WHERE Proj_Experiment.Exp_ID = ?
    ";
    // Prepare query
$stmt_proj_exp_name = $conn->prepare($sql_proj_exp_name);
    // Bind parameters
$stmt_proj_exp_name->bind_param("s", $exp_ID);
    // Execute query
if ($stmt_proj_exp_name->execute()) {
    // Get the result set from the executed query
    $result_proj_exp_name = $stmt_proj_exp_name->get_result(); // get_result() returns a mysqli_result object
    // Fetch the project and experiment names into an associative array
    $proj_exp_name_array = $result_proj_exp_name->fetch_assoc(); // fetch_assoc() fetches a single row as an associative array
    // Check if the result is empty (i.e., no experiment found with the given ID)
    if (!$proj_exp_name_array) {
        echo "No experiment found with ID: " . htmlspecialchars($exp_ID);
        exit();
    }
} else {
    echo "Error executing query: " . $conn->error;
}
?>