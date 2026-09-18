<?php
// Variables from POST (or GET?)
$exp_ID = $_POST['exp_ID'];

// Connect to database
include "/Database_related/db.php";

echo " Experiment :)";
?>

<!-- Navbar? -->

<!-- Link back to parent project -->
 <?php
// Retrieve project ID for the experiment
include "/Functional_php/exp_fetch_proj_ID.php"; // This script fetches the project ID for the specified experiment ID
// Create link back to parent project page
echo "<a href='project.php?proj_ID=" . $proj_ID . "'>Back to parent project</a><br><br>";
?>

<!-- tags field/form -->
<?php
// Retrieve experiment tags from the database
include "/Functional_php/exp_fetch_tags.php"; // Fetches the tags for the specified experiment ID
// Display current tags
echo "Experiment tags: " . implode(", ", $exp_tags_array) . "<br><br>";
?>
<form action="Functional_php/exp_edit_tags.php" method="post">
    <input type="text" name="new_tags" placeholder="Add new tags (comma separated)">
    <input type="text" name="remove_tags" placeholder="Remove tags (comma separated)">
    <input type="hidden" name="exp_ID" value="<?php echo $exp_ID; ?>">
    <input type="submit" value="Add Tags">
</form>

<!-- markers for partial/total progress -->

<!-- Create links for sub-pages, plan/log/result -->

<?php
// Close connection when done
include "/Database_related/closeDB.php";
?>