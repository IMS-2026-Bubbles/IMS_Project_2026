<?php
// Complete project action

// Arrive from:
// - project.php (mark project as done / re-open project)

// Action:
// - Only the project owner (access level 3) may change is_done.
// - Flip projects.is_done to the requested state.

// Redirect to:
// - project.php (same project, with message)

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../session/init.php';
require_once '../session/check_user_logged_in.php';
require_once '../database/db.php';
require_once '../includes/log_activity.php';
require_once '../includes/check_user_permission.php';

$project_id = (int) ($_POST['project_id'] ?? 0);
$done_flag = (int) ($_POST['done_flag'] ?? 1);  // 1 = mark done, 0 = re-open

if ($project_id <= 0) {
    header('Location: ../library.php');
    exit();
}

// Owner check: access level 3 is required to change the done state
$user_access = check_user_permission($conn, $_SESSION['profile_id'], 'project', $project_id);
if ($user_access < 3) {
    log_activity(
        $conn,
        $_SESSION['profile_id'],
        'project',
        $project_id,
        'access_denied',
        'Non-owner attempted to change project done status'
    );

    $_SESSION['create_renamed_message'] = 'Only the project owner can mark the project as done.';
    $_SESSION['create_renamed_toastClass'] = '#dc3545';
    header('Location: ../project.php?project_id=' . $project_id);
    exit();
}

$sql = 'UPDATE projects SET is_done = ? WHERE project_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $done_flag, $project_id);
$transaction_ok = $stmt->execute();
$stmt->close();

if ($transaction_ok) {
    // Success: the project owner gets Scriba points for a done project (see profile_points view)
    log_activity(
        $conn,
        $_SESSION['profile_id'],
        'project',
        $project_id,
        'update',
        $done_flag ? 'Project marked as done' : 'Project re-opened'
    );

    $message = $done_flag ? '🎉 Project marked as done!' : 'Project re-opened.';
    $toastClass = '#1ea324';
} else {
    log_activity(
        $conn,
        $_SESSION['profile_id'],
        'project',
        $project_id,
        'update',
        'Error changing project done status'
    );

    $message = 'Something went wrong. The project status was not changed.';
    $toastClass = '#ff0019';
}

$_SESSION['create_renamed_message'] = $message;
$_SESSION['create_renamed_toastClass'] = $toastClass;

header('Location: ../project.php?project_id=' . $project_id);
exit();
