<?php
// Join company/lab action

// Arrive from: 
    // user_profile.php (join company or lab form)
// Action:
    // Update company_members table to add the user to the specified company
    // Update lab_members table to add the user to the specified lab
// Redirect to: 
    // user_profile.php (with message indicating success or failure)


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
        header("Location: ../user_profile.php");
        exit;
    }
    else{
        # use placeholders to protect against sql injection
        $sql = "INSERT INTO company_members (company_id, profile_id, role) VALUES (?, ?, 'member')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $company_code, $profile_id);
        $result = $stmt->execute();
        $stmt->close();
        header("Location: ../user_profile.php");   // use your real filename
        exit;
        }
    }


    
if(isset($_POST['join_lab']))
    {
    # fetch data from POST request
    $lab_code = $_POST['unique_code_lab'];

    # check so that this code actually exists:
    $stmt = $conn->prepare(
        "SELECT lab_id 
        FROM lab_members 
        WHERE lab_id = ?"
        );
    $stmt->bind_param("s", $lab_code);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    # if the lab doesn't exist
    if (!$exists){
        $error_join_lab = "The lab group you want to join doesn't exist. Make sure your code is correct.";
        $_SESSION['error_join_lab'] = $error_join_lab;
        header("Location: ../user_profile.php");
    }
    else{
        # use placeholders to protect against sql injection
        $sql = "INSERT INTO lab_members (lab_id, profile_id, role) VALUES (?, ?, 'member')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $lab_code, $profile_id);
        $result = $stmt->execute();
        $stmt->close();
        header("Location: ../user_profile.php");
        exit;}
    }




?>