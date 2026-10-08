<?php
// Fetch timestamp for last update of experiment

// Fetch the timestamp for the last update of the specified experiment.
// It expects $experiment_id which is used to query the database for the last
// update timestamps associated with that experiment. Format the timestamps
// into year-month-day hour:minute format for display.
// Return an associative array $experiment_updated_at with keys
// 'Date_Created', 'Date_Updated', 'Plan_Updated', 'Log_Updated', and 'Result_Updated'.

// Retrieve last update timestamps
// TODO(schema-migration): old column names. Becomes created_at, updated_at,
// plan_updated_at, log_updated_at, result_updated_at (keys of $experiment_updated_at too).
// Do not display the generated experiments.updated_at — it defaults to the
// 1970-01-01 sentinel until a section is edited.
// Create query
$sql_experiment_last_update = 'SELECT created_at, updated_at, plan_updated_at, log_updated_at, result_updated_at FROM experiments WHERE experiment_id = ?';
// Prepare query
$stmt_experiment_last_update = $conn->prepare($sql_experiment_last_update);
// Bind the experiment ID parameter
$stmt_experiment_last_update->bind_param('s', $experiment_id);
// Execute query
if ($stmt_experiment_last_update->execute()) {
    // Get the result set from the executed query
    $result_experiment_last_update = $stmt_experiment_last_update->get_result();  // get_result() returns a mysqli_result object
    // Fetch the last update timestamps into an associative array
    $experiment_updated_at = $result_experiment_last_update->fetch_assoc();  // fetch_assoc() fetches a single row as an associative array
    $experiment_updated_at = $experiment_updated_at ?? [];  // Use null coalescing operator to provide a default empty array if the result is null
    // Format the timestamps into a more readable format
    $experiment_updated_at = array_map(function ($timestamp) {
        return $timestamp ? date('Y-m-d H:i', strtotime($timestamp)) : null;
    }, $experiment_updated_at);
} elseif (isset($messages)) {
    $messages[] = 'Error retrieving last update timestamps for experiment ' . $experiment_id . ' : ' . $stmt_experiment_last_update->error . '<br>';
} else {
    echo 'Error retrieving last update timestamps for experiment ' . $experiment_id . ' : ' . $stmt_experiment_last_update->error . '<br>';
}
?>