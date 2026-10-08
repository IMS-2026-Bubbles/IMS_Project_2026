<?php
// Retrieve experiment tags

// Retrieve the tags for the specified experiment.
// It expects the variable $experiment_id to be set before inclusion, which is used to query the database for tags associated with that experiment.
// Returns an array of tags in $experiment_tags, which can be used to display the tags on the page.

// Retrieve current tags
// TODO(schema-migration): old table/column names. Becomes:
// SELECT tag FROM experiment_tags WHERE experiment_id = ? — the array_column key
// below ('Exp_Tag') becomes 'tag'.
// Create query
$sql_experiment_tags = 'SELECT tag FROM experiment_tags WHERE experiment_id = ?';
// Prepare query
$stmt_experiment_tags = $conn->prepare($sql_experiment_tags);
// Bind the experiment ID parameter
$stmt_experiment_tags->bind_param('s', $experiment_id);
// Execute query
if ($stmt_experiment_tags->execute()) {
    // Get the result set from the executed query
    $result_experiment_tags = $stmt_experiment_tags->get_result();  // get_result() returns a mysqli_result object
    // Fetch all tags into an associative array
    $experiment_tags = $result_experiment_tags->fetch_all(MYSQLI_ASSOC);  // fetch all rows as an associative array
    // Unnest the array to get a simple array of tags
    $experiment_tags = array_column($experiment_tags, 'tag');  // Extract the 'tag' column from the associative array
} elseif (isset($messages)) {
    $messages[] = 'Error retrieving tags for experiment ' . $experiment_id . ' : ' . $stmt_experiment_tags->error . '<br>';
} else {
    echo 'Error retrieving tags for experiment ' . $experiment_id . ' : ' . $stmt_experiment_tags->error . '<br>';
}

// Continue with page.
?>