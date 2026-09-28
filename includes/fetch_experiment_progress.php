<?php
// Retrieve progress flags for experiment

// Retrieve progress flags for the experiment. 
// Expects an experiment ID as $experiment_id and database connection as $conn.
// Returns an associative array $experiment_progress with keys 'Plan_Done', 'Log_Done', and 'Result_Done'.

// Retrieve flags
// TODO(schema-migration): table name is a typo (Proj_Experiments) and columns are
// old style. Becomes: SELECT plan_is_done, log_is_done, result_is_done FROM experiments
// WHERE experiment_id = ? — so the $experiment_progress keys become plan_is_done, etc.
    // Create query
$sql_experiment_progress = "SELECT plan_is_done, log_is_done, result_is_done FROM experiments WHERE experiment_id = ?";
    // Prepare query
$stmt_experiment_progress = $conn->prepare($sql_experiment_progress);
    // Bind the experiment ID parameter
$stmt_experiment_progress->bind_param("s", $experiment_id);
    // Execute query
if ($stmt_experiment_progress->execute()) {
    // Get the result set from the executed query
    $result_experiment_progress = $stmt_experiment_progress->get_result(); // get_result() returns a mysqli_result object
    // Fetch the progress flags into an associative array
    $experiment_progress = $result_experiment_progress->fetch_assoc(); // fetch_assoc() fetches a single row as an associative array
} elseif (isset($messages)) {
    $messages[] = "Error retrieving progress flags for experiment " . $experiment_id . " : " . $stmt_experiment_progress->error . "<br>";
} else {
    echo "Error retrieving progress flags for experiment " . $experiment_id . " : " . $stmt_experiment_progress->error . "<br>";
}
?>