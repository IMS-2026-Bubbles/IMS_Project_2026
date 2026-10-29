```php
<?php

require_once '../session/init.php';
require_once '../session/check_user_logged_in.php';
require_once '../database/db.php';
require_once '../includes/log_activity.php';
require_once '../includes/check_user_permission.php';

// Get the current user ID.
$profile_id = (int) $_SESSION['profile_id'];

// Allow deletion requests only through POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../library.php');
    exit();
}

// Validate the submitted experiment and project IDs.
$experiment_id = filter_input(
    INPUT_POST,
    'experiment_id',
    FILTER_VALIDATE_INT
);

$project_id = filter_input(
    INPUT_POST,
    'project_id',
    FILTER_VALIDATE_INT
);

if (
    !$experiment_id ||
    $experiment_id < 1 ||
    !$project_id ||
    $project_id < 1
) {
    $_SESSION['create_renamed_message'] =
        'Invalid experiment.';
    $_SESSION['create_renamed_toastClass'] = '#b42318';

    header('Location: ../library.php');
    exit();
}

// Check whether the current user owns the parent project.
$user_access = check_user_permission(
    $conn,
    (string) $profile_id,
    'project',
    (string) $project_id
);

// Only the project owner can delete its experiments.
if ($user_access !== 3) {
    $_SESSION['create_renamed_message'] =
        'Only the project owner can delete this experiment.';
    $_SESSION['create_renamed_toastClass'] = '#b42318';

    header(
        'Location: ../project.php?project_id=' . $project_id
    );
    exit();
}

// Retrieve the experiment name and verify its parent project.
$sql = 'SELECT name
        FROM experiments
        WHERE experiment_id = ?
          AND project_id = ?';

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'ii',
    $experiment_id,
    $project_id
);
$stmt->execute();

$experiment = $stmt->get_result()->fetch_assoc();

$stmt->close();

// Stop if the experiment does not exist in the specified project.
if (!$experiment) {
    $_SESSION['create_renamed_message'] =
        'Experiment not found.';
    $_SESSION['create_renamed_toastClass'] = '#b42318';

    header(
        'Location: ../project.php?project_id=' . $project_id
    );
    exit();
}

$experiment_name = $experiment['name'];

// Start a database transaction.
$conn->begin_transaction();

try {
    // Record the deletion before removing the experiment.
    $logged = log_activity(
        $conn,
        $profile_id,
        'experiment',
        $experiment_id,
        'delete',
        'Deleted experiment: ' . $experiment_name
    );

    // Stop the deletion if the activity log could not be saved.
    if (!$logged) {
        throw new RuntimeException(
            'Failed to record experiment deletion.'
        );
    }

    // Delete the experiment.
    // Related tags and memberships are removed through ON DELETE CASCADE.
    $stmt = $conn->prepare(
        'DELETE FROM experiments
         WHERE experiment_id = ?
           AND project_id = ?'
    );

    $stmt->bind_param(
        'ii',
        $experiment_id,
        $project_id
    );

    $stmt->execute();

    // Ensure that exactly one experiment was deleted.
    if ($stmt->affected_rows !== 1) {
        throw new RuntimeException(
            'Experiment was not deleted.'
        );
    }

    $stmt->close();

    // Commit the transaction after all operations succeed.
    $conn->commit();

    $_SESSION['create_renamed_message'] =
        'Experiment deleted successfully.';
    $_SESSION['create_renamed_toastClass'] = '#198754';

} catch (Throwable $e) {
    // Roll back the transaction if any operation fails.
    $conn->rollback();

    error_log(
        'Delete experiment failed: ' . $e->getMessage()
    );

    $_SESSION['create_renamed_message'] =
        'Could not delete the experiment. Please try again.';
    $_SESSION['create_renamed_toastClass'] = '#b42318';
}

// Return to the parent project's experiment list.
header(
    'Location: ../project.php?project_id=' . $project_id
);
exit();

?>
```