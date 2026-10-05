<?php
// Join company/lab action

// Arrive from: 
    // company_admin.php (join company or lab form)
    // user_profile.php
// Action:
    // user can accept invite/deny to company
    // company admin can invite someone to company
    // company admin can assign someone to a lab group
    // company admin can create lab groups
    // company admin can assign an orphaned project to a person
// Redirect to: 
    // company_admin.php for company actions
    // user_profile.php for user actions


// starts the session
require_once '../session/init.php';
//check user is logged in 
require_once '../session/check_user_logged_in.php';
// Connect to database
require_once '../database/db.php';

// Get the profile ID from the session
$profile_id = $_SESSION['profile_id'];
$admin_company_ID = $_SESSION["company_id"];


# ----------- THIS PART IS FOR ADMIN ----------- 
# for company admin to make someone join the lab
# here there is no need for invite, the admin decides
if(isset($_POST['add_to_lab']))
    {

    # fetch data from POST request
    $labcode_to_join = $_POST['add_to_lab'];
    $profile_id = $_POST['profile_id'];

    # join the lab group 
    $sql = "INSERT INTO lab_members (lab_id, profile_id, role) VALUES (?, ?, 'member')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $labcode_to_join, $profile_id);
    $result = $stmt->execute();
    $stmt->close();

    header("Location: ../company_admin.php");
    exit;


    }


# invite a member
# 1. check if the mejl is actually a member of company already
# 2. change status to pending
if(isset($_POST['invite_person'])){
    echo "knapp tryckt på";
    $email_to_invite = $_POST['invite_person'];
    # get profile id from entered email + can't be in another company
    $get_profile_id = "SELECT profiles.profile_id, company_members.company_id 
                    FROM profiles
                    LEFT JOIN company_members ON profiles.profile_id = company_members.profile_id
                    WHERE company_members.company_id IS NULL AND email = ?;";
    
    $stmt = $conn->prepare($get_profile_id);
    $stmt->bind_param("s", $email_to_invite);
    $result = $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();


    # if email doesn't exist - no profile id connected to it
    # DOES NOT WORK
    if (!$exists){
        $_SESSION['error_join_company'] = "This email does not match. Make sure it is correct.";
        header("Location: ../company_admin.php");
        exit;}
    
    $invited_profile_id = $exists['profile_id'];

    # if email exist (+ profile id), send invite (assign pending to person)
    # use placeholders to protect against sql injection
    $sql = "INSERT INTO company_members (company_id, profile_id, role) VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $admin_company_ID, $invited_profile_id);
    $result = $stmt->execute();
    $stmt->close();
    header("Location: ../company_admin.php");    
    exit;
    }


# Assign project to someone
if(isset($_POST['orphan_proj']))
    {

    # get old owner - but using sql



    # fetch data from POST request
    $project_id = $_POST['orphan_proj'];
    $profile_id = $_POST['new_owner'];
    $old_profile_id = $_POST['old_owner']; # this is temporary
    

    # change owner of project
    $sql = "UPDATE project_members 
            SET profile_id = ?  # profile_id
            WHERE project_members.project_id = ? AND project_members.profile_id = ?"; # project_id, old_profile_id

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $profile_id, $project_id, $old_profile_id);
    $result = $stmt->execute();
    $stmt->close();

    header("Location: ../company_admin.php");
    exit;


    }


# ----------------------------------------------------- 



# ----------- THIS IS FROM USER PROFILE ----------- 
# reply on invitation
if(isset($_POST['submit_type'])){
    if ($_POST['submit_type'] == "accept"){
        $sql_accept = "UPDATE company_members 
                SET role = 'member' 
                WHERE company_members.company_id = ? AND company_members.profile_id = ?";

        $stmt = $conn->prepare($sql_accept);
        $stmt->bind_param("si", $admin_company_ID, $profile_id);
        $result = $stmt->execute();
        $stmt->close();
        header("Location: ../user_profile.php");    
        exit;
    }

    # if you don't want to join company
    if ($_POST['submit_type'] == "decline"){
        $sql_decline = "DELETE FROM company_members 
                    WHERE company_members.company_id = ? AND company_members.profile_id = ?";

        $stmt = $conn->prepare($sql_decline);
        $stmt->bind_param("si", $admin_company_ID, $profile_id);
        $result = $stmt->execute();
        $stmt->close();
        header("Location: ../user_profile.php");    
        exit;


    }
    



}


    










// # --------- OLD CODE ------------
// if(isset($_POST['join_company']))
//     {
//     # fetch data from POST request
//     $company_code = $_POST['unique_code'];

//     # check so that this code actually exists:
//     $stmt = $conn->prepare(
//     "SELECT company_id FROM companies WHERE company_id = ?");
//     $stmt->bind_param("s", $company_code);
//     $stmt->execute();
//     $exists = $stmt->get_result()->fetch_assoc();
//     $stmt->close();

//     # if the company doesn't exist
//     if (!$exists){
//         $_SESSION['error_join_company'] = "The company you want to join doesn't exist. Make sure your code is correct.";
//         header("Location: ../user_profile.php");
//         exit;
//     }
//     else{
//         # use placeholders to protect against sql injection
//         $sql = "INSERT INTO company_members (company_id, profile_id, role) VALUES (?, ?, 'member')";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("si", $company_code, $profile_id);
//         $result = $stmt->execute();
//         $stmt->close();
//         header("Location: ../user_profile.php");   // use your real filename
//         exit;
//         }
//     }


// # idea:
// # 1. look up company and lab user want to join
// # 2. make sure they exist
// # 3. look up if person already belong to a company 
// # 4. join lab group
// # 5. join company if possible
// if(isset($_POST['join_lab']))
//     {
//     # fetch data from POST request
//     $lab_code = $_POST['unique_code_lab'];

//     # check so that this lab code actually exists:
//     $stmt = $conn->prepare(
//         "SELECT lab_id, company_id 
//         FROM labs 
//         WHERE lab_id = ?"
//         ); # this returns parts of the rows where the specific lab_id exists
//     $stmt->bind_param("s", $lab_code);
//     $stmt->execute();
//     $lab = $stmt->get_result()->fetch_assoc(); # if the lab exists, this will also exist
//     $stmt->close();

//     # if the lab doesn't exist
//     if (!$lab){
//         $error_join_lab = "The lab group you want to join doesn't exist. Make sure your code is correct.";
//         $_SESSION['error_join_lab'] = $error_join_lab;
//         header("Location: ../user_profile.php");
//         exit;
//     }

//     # look up to see if user already belongs to a company
//     $stmt = $conn->prepare(
//         "SELECT company_id 
//         FROM company_members
//         WHERE profile_id = ?"
//         ); 
//     $stmt->bind_param("i", $profile_id);
//     $stmt->execute();
//     $company_belonging = $stmt->get_result()->fetch_assoc(); # if the user belong to a company
//     $stmt->close();

//     # if you belong to a company: check to see if the lab group you want to join is in the same company
//     if ($company_belonging){
//         if ($company_belonging['company_id'] !== $lab['company_id']){
//             $error_join_lab = "Your current company doesn't have the lab group you want to join. Make sure your code is correct.";
//             $_SESSION['error_join_lab'] = $error_join_lab;
//             header("Location: ../user_profile.php");
//             exit;
//         }
//     }

//     # join the lab group 
//     $sql = "INSERT INTO lab_members (lab_id, profile_id, role) VALUES (?, ?, 'member')";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("si", $lab_code, $profile_id);
//     $result = $stmt->execute();
//     $stmt->close();

//     # join company if you don't lready belong to one
//     if (!$company_belonging){
//         $sql = "INSERT INTO company_members (company_id, profile_id, role) VALUES (?, ?, 'member')";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("si", $lab['company_id'], $profile_id);
//         $result = $stmt->execute();
//         $stmt->close();}

//     header("Location: ../user_profile.php");
//     exit;
//     }
    








?>