<?php
// Session initialization
include "Session/init.php"; // Make the session available
    // Retreive messages from exp_edit_tags.php if they exist
if (isset($_SESSION['messages_exp_edit_tags'])) {
    $messages_exp_edit_tags = $_SESSION['messages_exp_edit_tags'];
    // Clear the messages from the session after retrieving them
    unset($_SESSION['messages_exp_edit_tags']);
}

// Variables from POST (or GET?) or URL parameters
$exp_ID = $_POST['exp_ID'];

// Connect to database
include "Database_related/db.php";

echo " Experiment :)";
?>

<!-- Navbar? -->

<!-- Link back to parent project -->
 <?php
// Retrieve project ID for the experiment
include "/Functional_php/exp_helpers/exp_fetch_proj_ID.php"; // This script fetches the project ID for the specified experiment ID
// Create link back to parent project page
echo "<a href='project.php?proj_ID=" . $proj_ID . "'>Back to parent project</a><br><br>";
?>

<!-- tags field/form -->
<?php
// Retrieve experiment tags from the database
include "/Functional_php/exp_helpers/exp_fetch_tags.php"; // Fetches the tags for the specified experiment ID
// Display current tags
echo "Experiment tags: " . implode(", ", $exp_tags_array) . "<br><br>";
?>
<form action="Functional_php/exp_helpers/exp_edit_tags.php" method="post">
    <input type="text" name="new_tags" placeholder="Add new tags (comma separated)">
    <input type="text" name="remove_tags" placeholder="Remove tags (comma separated)">
    <input type="hidden" name="exp_ID" value="<?php echo $exp_ID; ?>">
    <input type="submit" value="Add Tags">
</form>
<br>
<?php
// Display messages from exp_edit_tags.php if they exist
if (isset($messages_exp_edit_tags)) {
    foreach ($messages_exp_edit_tags as $message) {
        echo $message;
    }
}
?>

<!-- markers for partial/total progress -->
<?php
// Retrieve and display progress flags for the experiment
include "Functional_php/exp_helpers/exp_fetch_progress.php"; // Fetches the progress flags as $exp_progress_flags

?>


<!-- Create links for sub-pages, plan/log/result -->

<?php
// Close connection when done
include "/Database_related/closeDB.php";
?>