<?php
// Delete profile action

// Arrive from:
// user_profile.php (delete profile form)
// Action:
// Delete the user profile from the database
// Personal information is removed, but profile_id stays so the projects and so on are still there
// Redirect to:
// actions/logout.php

// starts the session
require_once '../session/init.php';
// check user is logged in
require_once '../session/check_user_logged_in.php';
// Connect to database
require_once '../database/db.php';

require_once '../includes/log_activity.php';  // Include the log_activity function

// Get the profile ID from the session
$profile_id = $_SESSION['profile_id'];

// DELETE ACCOUNT
if (isset($_POST['delete_account'])) {
    // Delete the user profile from the database (soft delete)
    $sql = "UPDATE profiles
            SET email = CONCAT(profile_id, '@deleted.invalid'), # .invalid is reserved so noone has this as email
                first_name = NULL,
                last_name = NULL,
                password =  '*', # this is not a valid hash, use any characater but special ones are prefered
                saved_changes = 0, # this is set to 0 originally
                last_login_at = NULL, # otherwise it might be considered personal info??
                streak = 0, # set to 0, originally it is 1, but a deleted acoount won't have a streak
                is_scriba_admin = FALSE,
                is_deleted = TRUE
            WHERE profile_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $profile_id);
    $stmt->execute();

    if ($stmt->execute()) {
        $_SESSION['delete_account_message'] = 'Record deleted successfully';
        // Log the deletion action
        log_activity(
            $conn,
            $profile_id,
            'profile',
            $profile_id,
            'delete',
            'User requested account deletion: successfully deleted profile information'
        );
    } else {
        $_SESSION['delete_account_message'] = 'Error deleting user profile: ' . $conn->error;
        // Log the deletion action failed
        log_activity(
            $conn,
            $profile_id,
            'profile',
            $profile_id,
            'delete',
            'User requested account deletion: failed to delete profile information: ' . $conn->error
        );
    }

    // once you have deleted your account you should be logged out
    header('Location: ../actions/logout.php');
    exit;
}

?>

