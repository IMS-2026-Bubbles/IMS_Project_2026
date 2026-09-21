<?php
// Fetch timestamp for last update of experiment

// Fetch the timestamp for the last update of the specified experiment.
// It expects $exp_ID which is used to query the database for the last 
// update timestamps associated with that experiment.
// Return an associative array $exp_last_update with keys 
// 'Plan_Update', 'Log_Update', and 'Result_Update'.

// Retrieve last update timestamps
    // Create query
$sql_exp_last_update = "SELECT Plan_Update, Log_Update, Result_Update FROM Proj_Experiments WHERE Exp_ID = ?";
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
} elseif (isset($messages)) {
    $messages[] = "Error retrieving last update timestamps for experiment " . $exp_ID . " : " . $stmt_exp_last_update->error . "<br>";
} else {
    echo "Error retrieving last update timestamps for experiment " . $exp_ID . " : " . $stmt_exp_last_update->error . "<br>";
}
?>