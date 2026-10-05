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
//check user is logged in 
require_once '../session/check_user_logged_in.php';
// Connect to database
require_once '../database/db.php';

// Get the profile ID from the session
$profile_id = $_SESSION['profile_id'];


//delete user account with SQL DELETE statement
//i know i should use the is_deleted from profiles but rn its what it is 
// UPDATE profiles SET is_delted = TRUE smth like this 
if (isset($_POST["delete_account"])) {
    // Log the deletion action
    $sql_log_deletion = 
        "INSERT INTO activity_log (profile_id, entity_type, entity_id, activity_type, detail)
            VALUES (?, 'profile', ?, 'delete', 'User requested account deletion')";
        $stmt_log_deletion = $conn->prepare($sql_log_deletion);
        $stmt_log_deletion->bind_param("ii", $profile_id, $profile_id);
        $stmt_log_deletion->execute();
        $stmt_log_deletion->close();


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
    $stmt->bind_param("i", $profile_id);

    if ($stmt->execute()) {
        $_SESSION['delete_account_message'] = "Record deleted successfully";
    } else {
        $_SESSION['delete_account_message'] = "Error deleting user profile: " . $conn->error;
    }

    # once you have deleted your account you should be logged out
    header("Location: ../actions/logout.php");
    #header("Location: ../user_profile.php");
    exit;
}

?>

