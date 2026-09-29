<?php
// Delete profile action

// Arrive from: 
    // user_profile.php (delete profile form)
// Action:
    // Delete the user profile from the database
// Redirect to: 
    // user_profile.php (with message indicating success or failure)


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

    $stmt = $conn->prepare("DELETE FROM profiles WHERE profile_id = ?"); 
    $stmt->bind_param("i", $profile_id);

    if ($stmt->execute()) {
        $_SESSION['delete_account_message'] = "Record deleted successfully";
    } else {
        $_SESSION['delete_account_message'] = "Error deleting user profile: " . $conn->error;
    }

    # once you have deleted your account you should be logged out
    header("Location: ../actions/logout.php")
    #header("Location: ../user_profile.php");
    exit;
}

?>