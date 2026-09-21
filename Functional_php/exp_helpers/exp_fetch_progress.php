<?php
// Retrieve progress flags for experiment

// Retrieve progress flags for the experiment. 
// Expects an experiment ID as $exp_ID and database connection as $conn.
// Returns an associative array $exp_progress_flags with keys 'Plan_Done', 'Log_Done', and 'Result_Done'.

// Retrieve flags
    // Create query
$sql_exp_progress = "SELECT Plan_Done, Log_Done, Result_Done FROM Proj_Experiments WHERE Exp_ID = ?";
    // Prepare query
$stmt_exp_progress = $conn->prepare($sql_exp_progress);
    // Bind the experiment ID parameter
$stmt_exp_progress->bind_param("s", $exp_ID);
    // Execute query
if ($stmt_exp_progress->execute()) {
    // Get the result set from the executed query
    $result_exp_progress = $stmt_exp_progress->get_result(); // get_result() returns a mysqli_result object
    // Fetch the progress flags into an associative array
    $exp_progress_flags = $result_exp_progress->fetch_assoc(); // fetch_assoc() fetches a single row as an associative array
} elseif (isset($messages)) {
    $messages[] = "Error retrieving progress flags for experiment " . $exp_ID . " : " . $stmt_exp_progress->error . "<br>";
} else {
    echo "Error retrieving progress flags for experiment " . $exp_ID . " : " . $stmt_exp_progress->error . "<br>";
}
?>