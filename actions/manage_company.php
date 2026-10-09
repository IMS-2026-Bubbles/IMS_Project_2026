<?php
// Manage company action(s)

// Arrive from:
// scriba_admin.php

// Action:
// Register a new company,
// Add admin to a company,
// Remove admin from a company

// Redirect to:
// scriba_admin.php (with message indicating success or failure)

require_once '../session/init.php';  // Start the session and initialize session variables
require_once '../session/check_user_logged_in.php';  // Check if the user is logged in
require_once '../database/db.php';

require_once '../includes/log_activity.php';  // provides log_activity() function

// HANDLE POST METHOD TO CHANGE ADMIN STATUS
// post methods should be at top in order to reload page directly
if (isset($_POST['make_admin'])) {
    // profile_id of the user to be promoted to admin
    $profile_id = $_POST['profile_id'];

    // Fetch the company_id of the user to be promoted to admin
    require '../includes/fetch_profile_affiliation.php';  // expects $conn and $profile_id

    // Update the role of the user in the company_members table to 'admin'
    $sql_admin = "UPDATE company_members SET role = 'admin' WHERE  profile_id = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param('i', $profile_id);
    $result = $stmt->execute();

    if ($result) {
        $_SESSION['manage_company_message'] = 'User promoted to admin successfully.';
        $_SESSION['manage_company_toastClass'] = '#1ea324';
        // Log the activity of promoting a user to admin
        log_activity(
            $conn,
            $_SESSION['profile_id'],  // admin's profile ID who performed the action
            'company',  // entity type
            $user_affiliations['companies'][0] ?? NULL,  // entity ID (company ID) Should be ID for affected company, not user doing the action
            'change_role',  // activity type
            "Promoted user with profile ID $profile_id to admin"  // detail
        );
    } else {
        $_SESSION['manage_company_message'] = 'Error promoting user to admin: ' . $stmt->error;

        // Log the failed attempt to promote a user to admin
        log_activity(
            $conn,
            $_SESSION['profile_id'],  // admin's profile ID who performed the action
            'company',  // entity type
            $user_affiliations['companies'][0] ?? NULL,  // entity ID (company ID)
            'change_role',  // activity type
            "Failed to promote user with profile ID $profile_id to admin: " . $stmt->error  // detail
        );
    }
}
# if you want to remove a person as admin
if (isset($_POST['admin_removal'])) {
    // profile_id of the user to be demoted from admin
    $profile_id = $_POST['profile_id'];

    // Fetch the company_id of the user to be promoted to admin
    require '../includes/fetch_profile_affiliation.php';  // expects $conn and $profile_id

    // Update the role of the user in the company_members table to 'member'
    $sql_admin = "UPDATE company_members SET role = 'member' WHERE  profile_id = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param('i', $profile_id);
    $result = $stmt->execute();

    if ($result) {
        $_SESSION['manage_company_message'] = 'Admin rights removed successfully.';
        $_SESSION['manage_company_toastClass'] = '#1ea324';
        // Log the activity of removing admin rights from a user
        log_activity(
            $conn,
            $_SESSION['profile_id'],  // admin's profile ID who performed the action
            'company',  // entity type
            $user_affiliations['companies'][0] ?? NULL,  // entity ID (company ID)
            'change_role',  // activity type
            "Removed admin rights from user with profile ID $profile_id"  // detail
        );
    } else {
        $_SESSION['manage_company_message'] = 'Error removing admin rights: ' . $stmt->error;

        // Log the failed attempt to remove admin rights from a user
        log_activity(
            $conn,
            $_SESSION['profile_id'],  // admin's profile ID who performed the action
            'company',  // entity type
            $user_affiliations['companies'][0] ?? NULL,  // entity ID (company ID)
            'change_role',  // activity type
            "Failed to remove admin rights from user with profile ID $profile_id: " . $stmt->error  // detail
        );
    }
}

// ADD A PERSON TO COMPANY
if (isset($_POST['add_to_company'])) {

    // profile_id of the user to be added to the company
    $profile_id = $_POST['profile_id'];
    // company_id of the company to which the user is being added
    $company_id = $_POST['add_to_company'];

    // Add the user to the company_members table with role 'member'
    $sql = "INSERT INTO company_members (company_id, profile_id, role) VALUES (?, ?, 'member')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $company_id, $profile_id);
    $result = $stmt->execute();

    if ($result) {
        $_SESSION['manage_company_message'] = 'User added to company successfully.';

        // Log the activity of adding a user to a company
        log_activity(
            $conn,
            $_SESSION['profile_id'],  // admin's profile ID who performed the action
            'company',  // entity type
            $company_id,  // entity ID (company ID)
            'add_member',  // activity type
            "Added user with profile ID $profile_id to company $company_id"  // detail
        );
    } else {
        $_SESSION['manage_company_message'] = 'Error adding user to company: ' . $stmt->error;

        // Log the failed attempt to add a user to a company
        log_activity(
            $conn,
            $_SESSION['profile_id'],  // admin's profile ID who performed the action
            'company',  // entity type
            $company_id,  // entity ID (company ID)
            'add_member',  // activity type
            "Failed to add user with profile ID $profile_id to company $company_id: " . $stmt->error  // detail
        );
    }

    $stmt->close();
}

# REGISTER NEW COMPANY
// if button to register new:
if (isset($_POST['register_company'])) {
    // fetch data from POST request
    $new_comp_name = $_POST['name'];

    // add a check here as well so that the same companies isn't added twice
    $sql_check_comp = 'SELECT name 
                    FROM companies 
                    WHERE name = ?';
    $checkCompStmt = $conn->prepare($sql_check_comp);
    $checkCompStmt->bind_param('s', $new_comp_name);
    $checkCompStmt->execute();
    $checkCompStmt->store_result();

    // check if the number of rows are more than 0 => company exists
    if ($checkCompStmt->num_rows > 0) {
        $_SESSION['manage_company_message'] = 'Company already exists';
        $_SESSION['manage_company_toastClass'] = '#ff0019';  // Primary color
        // Log failed to register new company due to existing name
        log_activity(
            $conn,
            $_SESSION['profile_id'],  // admin's profile ID who performed the action
            'company',  // entity type
            NULL,  // entity ID (company ID) not applicable since company creation failed
            'add_company',  // activity type
            "Failed to create company with name $name: Company already exists"  // detail
        );
    } else {
        // use placeholders to protect against sql injection
        $sql = 'INSERT INTO companies(name) VALUES (?)';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $new_comp_name);
        $result = $stmt->execute();

        // echos how it went
        if ($result) {
            $_SESSION['manage_company_message'] = 'Company created';
            $_SESSION['manage_company_toastClass'] = '#1ea324';  // Primary color
            // Log successful company creation

            log_activity(
                $conn,
                $_SESSION['profile_id'],  // admin's profile ID who performed the action
                'company',  // entity type
                $conn->insert_id,  // entity ID (company ID) of the newly created company,
                // this pulls the id of the last inserted row by this connection
                'add_company',  // activity type
                "Successfully created company with name $name"  // detail
            );
        } else {
            echo 'Error: ' . $stmt->error;
            // Log failed company creation due to database error
            log_activity(
                $conn,
                $_SESSION['profile_id'],  // admin's profile ID who performed the action
                'company',  // entity type
                NULL,  // entity ID (company ID) not applicable since company creation failed
                'add_company',  // activity type
                "Failed to create company with name $name: " . $stmt->error  // detail
            );
        }
    }
}

// redirect back to admin page
header('Location: ../scriba_admin.php');
exit;

?>