<?php
// Connect to database
include "/Database_related/db.php";

echo " Experiment :)";
?>

<!-- Navbar? -->

<!-- Link back to parent project -->

<!-- tags field/form -->
<?php
// Variables from POST (or GET?)
$exp_ID = $_POST['exp_ID'];

// Retrieve experiment tags from the database
include "/Functional_php/exp_fetch_tags.php"; // This script fetches the tags for the specified experiment ID
// Display current tags
    // Fetch all tags into an array
$exp_tags_array = fetch_assoc($result_exp_tags); // fetch_assoc() fetches all rows as an associative array, needed to access the values
    // Convert the array of tags into a comma-separated string
$tag_text = implode(", ", $exp_tags_array['Exp_Tag']);
    // Display the tags
echo "Experiment tags: " . $tag_text . "<br><br>";



?>

<!-- markers for partial/total progress -->

<!-- Create links for sub-pages, plan/log/result -->

<?php
// Close connection when done
include "/Database_related/closeDB.php";
?>