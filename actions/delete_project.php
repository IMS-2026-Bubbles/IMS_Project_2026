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

// Validate the submitted project ID.
$project_id = filter_input(
    INPUT_POST,
    'project_id',
    FILTER_VALIDATE_INT
);

if (!$project_id || $project_id < 1) {
    $_SESSION['view_page_message'] = 'Invalid project.';
    $_SESSION['toastClass'] = '#b42318';

    header('Location: ../library.php');
    exit();
}

// Check whether the current user owns the project.
$user_access = check_user_permission(
    $conn,
    (string) $profile_id,
    'project',
    (string) $project_id
);

// Only project owners can delete projects.
if ($user_access !== 3) {
    $_SESSION['view_page_message'] =
        'Only the project owner can delete this project.';
    $_SESSION['toastClass'] = '#b42318';

    header('Location: ../library.php');
    exit();
}

// Retrieve the project name for the activity log.
$sql = 'SELECT name FROM projects WHERE project_id = ?';

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $project_id);
$stmt->execute();

$project = $stmt->get_result()->fetch_assoc();

$stmt->close();

// Stop if the project does not exist.
if (!$project) {
    $_SESSION['view_page_message'] = 'Project not found.';
    $_SESSION['toastClass'] = '#b42318';

    header('Location: ../library.php');
    exit();
}

$project_name = $project['name'];

// Start a database transaction.
$conn->begin_transaction();

try {
    // Record the deletion before removing the project.
    $logged = log_activity(
        $conn,
        $profile_id,
        'project',
        $project_id,
        'delete',
        'Deleted project: ' . $project_name
    );

    // Stop the deletion if the activity log could not be saved.
    if (!$logged) {
        throw new RuntimeException(
            'Failed to record project deletion.'
        );
    }

    // Delete the project.
    // Related experiments, tags and memberships are removed through ON DELETE CASCADE.
    $stmt = $conn->prepare(
        'DELETE FROM projects WHERE project_id = ?'
    );

    $stmt->bind_param('i', $project_id);
    $stmt->execute();

    // Ensure that exactly one project was deleted.
    if ($stmt->affected_rows !== 1) {
        throw new RuntimeException(
            'Project was not deleted.'
        );
    }

    $stmt->close();

    // Commit the transaction after all operations succeed.
    $conn->commit();

    $_SESSION['view_page_message'] =
        'Project deleted successfully.';
    $_SESSION['toastClass'] = '#198754';

} catch (Throwable $e) {
    // Roll back the transaction if any operation fails.
    $conn->rollback();

    error_log(
        'Delete project failed: ' . $e->getMessage()
    );

    $_SESSION['view_page_message'] =
        'Could not delete the project. Please try again.';
    $_SESSION['toastClass'] = '#b42318';
}

// Return to the project library.
header('Location: ../library.php');
exit();

?>
```