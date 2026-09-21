<?php
// Fetch timestamp for last update of experiment

// Fetch the timestamp for the last update of the specified experiment.
// It expects $exp_ID which is used to query the database for the last 
// update timestamps associated with that experiment. Format the timestamps
// into year-month-day hour:minute format for display.
// Return an associative array $exp_last_update with keys 
// 'Date_Created', 'Date_Updated', 'Plan_Updated', 'Log_Updated', and 'Result_Updated'.

// Retrieve last update timestamps
    // Create query
$sql_exp_last_update = "SELECT Date_Created, Date_Updated, Plan_Updated, Log_Updated, Result_Updated FROM Proj_Experiment WHERE Exp_ID = ?";
    // Prepare query
$stmt_exp_last_update = $conn->prepare($sql_exp_last_update);
    // Bind the experiment ID parameter
$stmt_exp_last_update->bind_param("s", $exp_ID);
    // Execute query
if ($stmt_exp_last_update->execute()) {
    // Get the result set from the executed query
    $result_exp_last_update = $stmt_exp_last_update->get_result(); // get_result() returns a mysqli_result object
    // Fetch the last update timestamps into an associative array
    $exp_last_update = $result_exp_last_update->fetch_assoc(); // fetch_assoc() fetches a single row as an associative array
    $exp_last_update = $exp_last_update ?? []; // Use null coalescing operator to provide a default empty array if the result is null
    // Format the timestamps into a more readable format
    $exp_last_update = array_map(function($timestamp) {
        return $timestamp ? date("Y-m-d H:i", strtotime($timestamp)) : null;
    }, $exp_last_update);
} elseif (isset($messages)) {
    $messages[] = "Error retrieving last update timestamps for experiment " . $exp_ID . " : " . $stmt_exp_last_update->error . "<br>";
} else {
    echo "Error retrieving last update timestamps for experiment " . $exp_ID . " : " . $stmt_exp_last_update->error . "<br>";
}
?>