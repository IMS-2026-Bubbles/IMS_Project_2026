<?php

echo " Experiment :)";
?>

<!-- Navbar? -->

<!-- Link back to parent project -->

<!-- tags field/form -->
<?php
// Variables from POST
$exp_ID = $_POST['exp_ID']

// Connect to database
include "/Database_related/db.php";

// Retrieve current tags
    // Create query
$sql_exp_tags = "SELECT Exp_Tag FROM Exp_Tag WHERE Experiment_ID = ?";
    // Prepare query
$stmt_exp_tags = $conn->prepare($sql_exp_tags);
    // Bind the search term parameter
$stmt_exp_tags->bind_param("s", $exp_ID);
    // Execute query
$stmt_exp_tags->execute();
    // Get the result set from the executed query
$result_exp_tags = $stmt_exp_tags->get_result();

// Close connection when done
include "/Database_related/closeDB.php";
?>

<!-- markers for partial/total progress -->

<!-- Create links for sub-pages, plan/log/result -->
