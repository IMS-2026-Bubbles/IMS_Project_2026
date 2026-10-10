<?php
// Experiment page

// Arrive from:
// project.php (click on experiment link)
// experiment_section.php (click on plan/log/result link)
// actions/save_experiment_tags.php (after adding/removing tags)

// Action:
// Display experiment information, including tags, progress flags, and last updated timestamps
// Allow users with sufficient permissions to add/remove tags
// Rename experiments

// Redirect to:
// navbar options
// experiment_section.php (click on plan/log/result link)
// actions/save_experiment_tags.php (after adding/removing tags)
// project.php (click on "Back to parent project" link)
// actions/create_experiment.php (to change name)

// Session initialization
require_once 'session/init.php';  // Make the session available
// Check if the user is logged in
require_once 'session/check_user_logged_in.php';

// Variables
// $experiment_id from URL (URL is always a GET request)
$experiment_id = $_GET['experiment_id'] ?? NULL;

// Connect to database
require_once 'database/db.php';

// Check if the user has permission to view this experiment
require_once 'includes/check_user_permission.php';  // Include the user permission check function
// $exp_ID becomes $experiment_id; the $project_experiment_name / $experiment_progress /
// $experiment_updated_at keys used below follow the new column names
$user_access = check_user_permission($conn, $_SESSION['profile_id'], 'experiment', $experiment_id);
if ($user_access < 1) {
    // Access level 0 means no access
    echo 'You do not have permission to view this content.';

    // Log the access denied event
    log_activity(
        $conn,
        $_SESSION['profile_id'],
        'experiment',
        $experiment_id,
        'access_denied',
        'User attempted to access experiment without permission'
    );

    exit();
}

// Retrieve messages from save_experiment_tags.php if they exist
if (isset($_SESSION['messages_save_experiment_tags'])) {
    $messages_save_experiment_tags = $_SESSION['messages_save_experiment_tags'];
    // Clear the messages from the session after retrieving them
    unset($_SESSION['messages_save_experiment_tags']);
}



// to display error messages
if (isset($_SESSION['create_renamed_message'])) {
    $manage_company_message = $_SESSION['create_renamed_message'];
    unset($_SESSION['create_renamed_message']);
};
if (isset($_SESSION['create_renamed_toastClass'])) {
    $manage_company_toastClass = $_SESSION['create_renamed_toastClass'];
    unset($_SESSION['create_renamed_toastClass']);
};?>

<!--  -->
<!-- Front end starts here -->
<!--  -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Same Bootstrap and Scriba styles used by other pages -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/style.css">

    <title>Experiment</title>
</head>

<body class="scriba-experiment-body">

<!-- Navbar -->
<?php
include_once 'includes/navbar.php';
?>

<main class="experiment-page">

    <!-- Experiment heading -->
    <header class="experiment-heading">
        <?php
        // Display project name, experiment name, and experiment section name
        include 'includes/fetch_project_experiment_names.php';  // fetch the name and ID for project and experiment as $project_experiment_name

        // Diplay the project, experiment, and section name
        echo '<p class="experiment-project">'
            . 'Project: '
            . htmlspecialchars($project_experiment_name['project_name'])
            . '</p>';

        echo '<h1 class="experiment-page-title">'
            . 'Experiment: '
            . htmlspecialchars($project_experiment_name['experiment_name'])
            . '</h1>';
        ?>
    </header>

    <!-- Link back to parent project -->
    <?php
    // Retrieve project ID for the experiment
    include 'includes/fetch_experiment_project_id.php';  // This script fetches the project ID for the specified experiment ID
    // The experiment library page doubles as the parent project page (lists the
    // project's experiments), filtered by the project_id parameter.
    echo "<a class='experiment-back' href='project.php?project_id="
        . urlencode($project_id)
        . "'>&larr; Back to project</a>";
    ?>

    <!-- tags field/form -->
    <div class="experiment-content-grid" style="align-items: stretch;">

        <section class="experiment-panel" style="height: 100%; min-height: 340px;">
            <h2>Experiment tags</h2>

            <?php
            // Retrieve experiment tags from the database
            include 'includes/fetch_experiment_tags.php';  // Fetches the tags for the specified experiment ID
            // Display current tags
            echo '<p class="experiment-tag-list">'
                . htmlspecialchars(implode(', ', $experiment_tags))
                . '</p>';
            ?>

            <?php
            // If user has permission to edit tags, display the form for adding/removing tags
            if ($user_access >= 2) {
                echo "<form class='experiment-tag-form' action='actions/save_experiment_tags.php' method='post'>"
                    // Input fields for new and remove tags
                    . "<input type='text' name='add_tags' placeholder='Add new tags (comma separated)'>"
                    . "<input type='text' name='remove_tags' placeholder='Remove tags (comma separated)'>"
                    // Experiment_ID
                    . "<input type='hidden' name='experiment_id' value='" . htmlspecialchars($experiment_id) . "'>"
                    // Submit button
                    . "<input type='submit' value='Save tags'>"
                    . '</form>';
            }
            ?>

            <?php
            // Display messages from save_experiment_tags.php if they exist
            if (isset($messages_save_experiment_tags)) {
                foreach ($messages_save_experiment_tags as $message) {
                    echo '<div class="experiment-message">' . $message . '</div>';
                }
            }
            ?>
        </section>

        <!-- markers for partial/total progress and last update -->
        <section class="experiment-panel" style="height: 100%; min-height: 340px;">
            <h2>Progress and updates</h2>

            <?php
            // Retrieve and display progress flags for the experiment
            include 'includes/fetch_experiment_progress.php';  // Fetches the progress flags as $experiment_progress
            // Retrieve the last update timestamp for the experiment
            include 'includes/fetch_experiment_timestamps.php';  // Fetches the last updated timestamp for the experiment as $experiment_updated_at

            echo '<div class="experiment-meta">';

            echo '<p><strong>Created:</strong> '
                . htmlspecialchars($experiment_updated_at['created_at'])
                . '</p>';

            echo '<p><strong>Last updated:</strong> '
                . htmlspecialchars($experiment_updated_at['updated_at'])
                . '</p>';

            echo '</div>';

            // Define the progress flag display helper.
            include 'includes/render_progress_badge.php';

            echo "<div class='exp_progress_flags' style='text-align: center; font-size: 18px;'>";

            echo experiment_progress_badge(
                'Plan',
                $experiment_progress['plan_is_done']
            ) . ' <span class="progress-status-date">('
                . htmlspecialchars($experiment_updated_at['plan_updated_at'])
                . ')</span><br>';

            echo experiment_progress_badge(
                'Log',
                $experiment_progress['log_is_done']
            ) . ' <span class="progress-status-date">('
                . htmlspecialchars($experiment_updated_at['log_updated_at'])
                . ')</span><br>';

            echo experiment_progress_badge(
                'Result',
                $experiment_progress['result_is_done']
            ) . ' <span class="progress-status-date">('
                . htmlspecialchars($experiment_updated_at['result_updated_at'])
                . ')</span>';

            echo '</div>';
            ?>
        </section>

    </div>

    <!-- Create links for sub-pages, plan/log/result -->
    <section class="experiment-panel experiment-sections-panel">
        <h2>Experiment sections</h2>

        <div class="experiment-section-links">
            <a class="experiment-section-link"
               href="experiment_section.php?experiment_id=<?php echo urlencode($experiment_id); ?>&amp;section=plan">
                Experiment Plan
            </a>

            <a class="experiment-section-link"
               href="experiment_section.php?experiment_id=<?php echo urlencode($experiment_id); ?>&amp;section=log">
                Experiment Log
            </a>

            <a class="experiment-section-link"
               href="experiment_section.php?experiment_id=<?php echo urlencode($experiment_id); ?>&amp;section=result">
                Experiment Result
            </a>
        </div>
    </section>

    <?php
    // Close connection when done
    include 'database/close_db.php';
    ?>

    <!-- Makes sure that the message is actually displayed -->
    <?php if (!empty($manage_company_message)): ?>
        <div class="experiment-message"
             style="background-color: <?php echo htmlspecialchars((string)($manage_company_toastClass ?? '#7794b6'), ENT_QUOTES, 'UTF-8'); ?>; color: white;">
            <?php echo htmlspecialchars($manage_company_message); ?>
        </div>
    <?php endif; ?>

    <!-- CHANGE NAME OF experiment - FRONT END NEEDS TO BE FIXED HERE!!! -->

    <!-- this calls a function ased on id, it opens up field where text field is-->

    <?php if ($user_access >= 2): ?>
        <section class="experiment-panel experiment-rename-panel">

            <h2>Change name of experiment</h2>

            <button type="button"
                    class="experiment-rename-toggle"
                    onclick="showAddForm('change_experiment_name')">
                Change name of experiment
            </button>

            <form id="change_experiment_name"
                  class="experiment-rename-form"
                  action="actions/create_experiment.php"
                  method="POST"
                  style="display: none;">

                <input type="hidden"
                       name="experiment_id"
                       value="<?php echo htmlspecialchars($experiment_id); ?>">

                <input type="text"
                       name="new_experiment_name"
                       class="form-control add-input mb-3"
                       placeholder="Enter new name"
                       required>

                <button type="submit"
                        name="change_experiment_name"
                        class="add-button">
                    Save name
                </button>
            </form>

        </section>
    <?php endif; ?>

</main>

<script>
    function showAddForm(id) {
        var form = document.getElementById(id);

        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    }
</script>

</body>
</html>