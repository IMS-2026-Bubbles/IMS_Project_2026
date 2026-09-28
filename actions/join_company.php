<?php
// Join lab action

// Arrive from: 
    // index.php (join lab form)
// Action:

// Redirect to: 
    // user_profile.php (with message indicating lab join success or failure)


// starts the session
require_once 'session/init.php';
//check user is logged in 
require_once 'session/check_user_logged_in.php';
// Connect to database
require_once 'database/db.php';

// Get the profile ID from the session
$profile_id = $_SESSION['profile_id'];


if(isset($_POST['join_company']))
    {
    # fetch data from POST request
    $company_code = $_POST['unique_code'];

    # check so that this code actually exists:
    $stmt = $conn->prepare(
    "SELECT company_id FROM companies WHERE company_id = ?");
    $stmt->bind_param("s", $company_code);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    # if the company doesn't exist
    if (!$exists){
        $_SESSION['error_join_company'] = "The company you want to join doesn't exist. Make sure your code is correct.";
        header("Location: user_profile.php");
        exit;
    }
    else{
        # use placeholders to protect against sql injection
        $sql = "INSERT INTO company_members (company_id, profile_id, role) VALUES (?, ?, 'member')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $company_code, $profile_id);
        $result = $stmt->execute();
        $stmt->close();
        header("Location: user_profile.php");   // use your real filename
        exit;
        }
    }

?>