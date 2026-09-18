<?php
// Retrieve experiment tags

// Retrieve the tags for the specified experiment.
// It expects the variable $exp_ID to be set before inclusion, which is used to query the database for tags associated with that experiment.
// Returns an array of tags in $exp_tags_array, which can be used to display the tags on the page.


// Retrieve current tags
    // Create query
$sql_exp_tags = "SELECT Exp_Tag FROM Exp_Tag WHERE Experiment_ID = ?";
    // Prepare query
$stmt_exp_tags = $conn->prepare($sql_exp_tags);
    // Bind the search term parameter
$stmt_exp_tags->bind_param("s", $exp_ID);
    // Execute query
if ($stmt_exp_tags->execute()) {
    // Get the result set from the executed query
    $result_exp_tags = $stmt_exp_tags->get_result(); // get_result() returns a mysqli_result object
    // Fetch all tags into an associative array
    $exp_tags_array = fetch_assoc($result_exp_tags); // fetch_assoc() fetches all rows as an associative array
    // Unnest the array to get a simple array of tags
    $exp_tags_array = $exp_tags_array['Exp_Tag'];
} elseif (isset($messages)) {
    $messages[] = "Error retrieving tags for experiment " . $exp_ID . " : " . $stmt_exp_tags->error . "<br>";
} else {
    echo "Error retrieving tags for experiment " . $exp_ID . " : " . $stmt_exp_tags->error . "<br>";
}

// Continue with page.
?>