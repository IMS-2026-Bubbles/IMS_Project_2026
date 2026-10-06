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

# ADD SOMEONE TO LABGROUP
# here there is no need for invite, the admin decides
if(isset($_POST['add_to_lab']))
    {
    # fetch data from POST request (the dropdown table)
    $labcode_to_join = $_POST['add_to_lab'];
    $profile_id = $_POST['profile_id'];

    # join the lab group 
    $sql = "INSERT INTO lab_members (lab_id, profile_id, role) VALUES (?, ?, 'member')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $labcode_to_join, $profile_id);
    $result = $stmt->execute();
    $stmt->close();
    header("Location: ../company_admin.php");
    exit;
    }


# INVITE A MEMBER TO COMPANY
if(isset($_POST['invite_person'])){
    $email_to_invite = $_POST['invite_person'];
    # get profile id from the entered email and check so that it us not in another company
    $get_profile_id = "SELECT profiles.profile_id, company_members.company_id 
                    FROM profiles
                    LEFT JOIN company_members ON profiles.profile_id = company_members.profile_id
                    WHERE company_members.company_id IS NULL AND email = ?;";
    $stmt = $conn->prepare($get_profile_id);
    $stmt->bind_param("s", $email_to_invite);
    $result = $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    # if the email doesn't exist or the email belong to another company
    if (!$exists){
        # Display email + error message
        $message = $email_to_invite . " is not valid or it has already been invited to join your company.";
        $toastClass = "#ff0019";

        # add to message
        $_SESSION['create_invite_message'] = $message;
        $_SESSION['create_invite_toastClass'] = $toastClass;
        header("Location: ../company_admin.php");
        exit;}
    
    # store the profile id of person to invite    
    $invited_profile_id = $exists['profile_id'];

    # you only end up here if email exist and is not connected to company
    # use placeholders to protect against sql injection
    $sql = "INSERT INTO company_members (company_id, profile_id, role) VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $admin_company_ID, $invited_profile_id);
    $result = $stmt->execute();
    $stmt->close();

    # Message saying it was successfull to invite
    $message = "You have successfully invited " . $email_to_invite . ".";
    $toastClass = "#1ea324";

    # add to message
    $_SESSION['create_invite_message'] = $message;
    $_SESSION['create_invite_toastClass'] = $toastClass;
    header("Location: ../company_admin.php");    
    exit;
    }


# ASSIGN A PROJECT TO SOMEONE
if(isset($_POST['orphan_proj']))
    {

    # fetch data from POST request
    $project_id = $_POST['orphan_proj'];
    $profile_id = $_POST['new_owner'];
    $old_profile_id = $_POST['old_owner'];

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

# REPLY TO AN INVITATION
# THIS ONLY WORKS IF WE HAVE ONE PERSON PER COMPANY
if(isset($_POST['submit_type'])){
    # if user accept, change it's company belonging to member
    if ($_POST['submit_type'] == "accept"){
        $sql_accept = "UPDATE company_members 
                SET role = 'member' 
                WHERE company_members.profile_id = ?"; # THIS ONLY WORKS IF WE HAVE ONE PERSON PER COMPANY
        $stmt = $conn->prepare($sql_accept);
        $stmt->bind_param("i", $profile_id);
        $result = $stmt->execute();
        $stmt->close();
        header("Location: ../user_profile.php");    
        exit;
    }

    # if you don't want to join company - then delete entry in table with all users part of company
    if ($_POST['submit_type'] == "decline"){
        $sql_decline = "DELETE FROM company_members 
                    WHERE company_members.profile_id = ?"; # THIS ONLY WORKS IF WE HAVE ONE PERSON PER COMPANY
        $stmt = $conn->prepare($sql_decline);
        $stmt->bind_param("i", $profile_id);
        $result = $stmt->execute();
        $stmt->close();
        header("Location: ../user_profile.php");    
        exit;


    }
    

# ----------------------------------------------------- 

}




?>