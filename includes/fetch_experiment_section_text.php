<?php
// Retrieve experiment section text

// Retrieve the text content for a specific section of an experiment.
// Expects an experiment ID as $exp_ID, and a section name as $exp_section.
// Returns a string of text content in $exp_section_text, which can be used to display the text on the page.

// Retrieve the text content for the specified section
// TODO(schema-migration): old table/column names. Section values become lowercase
// (plan/log/result), so the interpolated column becomes $exp_section . '_text',
// the table becomes experiments, and the WHERE key becomes experiment_id.
    // Create query
$sql_exp_section_text = "SELECT " . $exp_section . "_Text FROM Proj_Experiment WHERE Exp_ID = ?";
    // Prepare query
$stmt_exp_section_text = $conn->prepare($sql_exp_section_text);
    // Bind the experiment ID parameter
$stmt_exp_section_text->bind_param("s", $exp_ID);
    // Execute query
if ($stmt_exp_section_text->execute()) {
    // Get the result set from the executed query
    $result_exp_section_text = $stmt_exp_section_text->get_result(); // get_result() returns a mysqli_result object
    // Fetch the text content into a string
    $exp_section_text = $result_exp_section_text->fetch_assoc(); // fetch_assoc() fetches a single row as an associative array, needed to access the value
    $exp_section_text = $exp_section_text[$exp_section . '_Text'] ?? ""; // Use null coalescing operator to provide a default value if the key does not exist
} elseif (isset($messages)) {
    $messages[] = "Error retrieving text for experiment " . $exp_ID . " section " . $exp_section . " : " . $stmt_exp_section_text->error . "<br>";
} else {
    echo "Error retrieving text for experiment " . $exp_ID . " section " . $exp_section . " : " . $stmt_exp_section_text->error . "<br>";
}
?>