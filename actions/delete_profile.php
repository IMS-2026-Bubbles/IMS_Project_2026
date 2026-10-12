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
    // want changes to all three tables - not just one
    
    // Begin transaction to ensure atomicity of updates
    $conn->begin_transaction();

    // $transaction_ok is a flag to track if all database operations succeed for a given transaction.
    // If any operation fails, we will roll back the transaction.
    $transaction_ok = true;
    $error_update_progress = '';


    // start of transaction
    if($transaction_ok){ 
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
        

        $transaction_ok = $stmt->execute();  // transaction flag updated
        if (!$transaction_ok) {
            // On failure, capture error for logging
            $error_update_progress = $stmt->error;
        }
        $stmt->close();}
    
    // second query
    if($transaction_ok){ 
        $sql2 = "DELETE FROM company_members
                WHERE profile_id = ?";
        $stmt = $conn->prepare($sql2);
        $stmt->bind_param('i', $profile_id);
        

        $transaction_ok = $stmt->execute();  // transaction flag updated
        if (!$transaction_ok) {
            // On failure, capture error for logging
            $error_update_progress = $stmt->error;
        }
        $stmt->close();}

    // third query
    if($transaction_ok){ 
        $sql3 = "DELETE FROM lab_members
            WHERE profile_id = ?";
        $stmt = $conn->prepare($sql3);
        $stmt->bind_param('i', $profile_id);
        

        $transaction_ok = $stmt->execute();  // transaction flag updated
        if (!$transaction_ok) {
            // On failure, capture error for logging
            $error_update_progress = $stmt->error;
        }
        $stmt->close();}

    if ($transaction_ok) {
        $conn->commit();
        

        $_SESSION['delete_account_message'] = 'User account deleted successfully';
        // Log the deletion action
        log_activity(
            $conn,
            $profile_id,
            'profile',
            $profile_id,
            'delete',
            'User requested account deletion: successfully deleted profile information');

        // once you have deleted your account you should be logged out
        header('Location: ../actions/logout.php');
        exit;

    } else {
        $conn->rollback();
        
        $_SESSION['delete_account_message'] = 'Error deleting user profile';
        // Log the deletion action failed
        log_activity(
            $conn,
            $profile_id,
            'profile',
            $profile_id,
            'delete',
            'User requested account deletion: failed to delete profile information: ' . $error_update_progress
        );
        header('Location: ../user_profile.php');
        exit;
    }


    

    


    // if ($stmt->execute()) {
    //     $_SESSION['delete_account_message'] = 'Record deleted successfully';
    //     // Log the deletion action
    //     log_activity(
    //         $conn,
    //         $profile_id,
    //         'profile',
    //         $profile_id,
    //         'delete',
    //         'User requested account deletion: successfully deleted profile information'
    //     );
    // } else {
    //     $_SESSION['delete_account_message'] = 'Error deleting user profile: ' . $conn->error;
    //     // Log the deletion action failed
    //     log_activity(
    //         $conn,
    //         $profile_id,
    //         'profile',
    //         $profile_id,
    //         'delete',
    //         'User requested account deletion: failed to delete profile information: ' . $conn->error
    //     );
    // }

    // once you have deleted your account you should be logged out
    //header('Location: ../actions/logout.php');
    //exit;
}

?>

