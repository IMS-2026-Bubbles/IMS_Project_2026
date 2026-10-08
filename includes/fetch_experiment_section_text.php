<?php
// Retrieve experiment section text

// Retrieve the text content for a specific section of an experiment.
// Expects an experiment ID as $experiment_id, and a section name as $experiment_section.
// Returns a string of text content in $experiment_section_text, which can be used to display the text on the page.

// Retrieve the text content for the specified section

    // Create query
$sql_experiment_section_text = "SELECT " . $experiment_section . "_text FROM experiments WHERE experiment_id = ?";
    // Prepare query
$stmt_experiment_section_text = $conn->prepare($sql_experiment_section_text);
    // Bind the experiment ID parameter
$stmt_experiment_section_text->bind_param("s", $experiment_id);

    // Execute query
if ($stmt_experiment_section_text->execute()) {
    // Get the result set from the executed query
    $result_experiment_section_text = $stmt_experiment_section_text->get_result(); // get_result() returns a mysqli_result object
    // Fetch the text content into a string
    $experiment_section_text = $result_experiment_section_text->fetch_assoc(); // fetch_assoc() fetches a single row as an associative array, needed to access the value
    $experiment_section_text = $experiment_section_text[$experiment_section . '_text'] ?? ""; // Use null coalescing operator to provide a default value if the key does not exist
} elseif (isset($messages)) {
    $messages[] = "Error retrieving text for experiment " . $experiment_id . " section " . $experiment_section . " : " . $stmt_experiment_section_text->error . "<br>";
} else {
    echo "Error retrieving text for experiment " . $experiment_id . " section " . $experiment_section . " : " . $stmt_experiment_section_text->error . "<br>";
}


// Decrypt the text content
    // Require the encryption functions to decrypt the text content after fetching from the database
require "encryption.php";

$experiment_section_text = decrypt_text($experiment_section_text, $encryption_key);

?>