<?php
// Create a new project action

// Arrive from:
// library.php (create new project form)
// project.php

// Action:
// Create a new project in the database
// Rename existing project

// Redirect to:
// library.php (after creating a new project)
// project.php (after renaming)


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session
require_once '../session/init.php';
require_once '../session/check_user_logged_in.php';
// Connect to database
require_once '../database/db.php';

require_once '../includes/log_activity.php';  // Include the log_activity function

// RENAME PROJECT
if (isset($_POST['change_project_name'])){
    # get project id and the new name from the forms
    $project_id = $_POST['project_id'];
    $new_name = $_POST['new_project_name'];

    # only do the name change if it is a name
    if ($new_name !== ' '){
        $sql = "UPDATE projects 
                SET name = ? 
                WHERE projects.project_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $new_name, $project_id);
        $result = $stmt->execute();

        # generate succeed message
        $message =  'Project renamed to: ' . $new_name . '.';
        $toastClass = '#1ea324';

        // add to message
        $_SESSION['create_renamed_message'] = $message;
        $_SESSION['create_renamed_toastClass'] = $toastClass;

        // redirect back to admin page
        header('Location: ../project.php?project_id=' . $project_id);
        exit;}
    
    # end up here if you entered ' '
    # generate error message
    $message =  'Renaming of project failed. Enter a real name.';
    $toastClass = '#ff0019';

    // add to message
    $_SESSION['create_renamed_message'] = $message;
    $_SESSION['create_renamed_toastClass'] = $toastClass;

    # then you will stay at original page
    header('Location: ../project.php?project_id=' . $project_id);
    exit;
}




// Add project
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_project'])) {
    $projectName = trim($_POST['project_name']);
    $labId = trim($_POST['lab_id']);

    if ($projectName != '' && $labId != '') {
        // Check that the selected lab belongs to one of the user's companies
        $checkSql =
            'SELECT labs.lab_id
            FROM labs
            JOIN company_members
                ON labs.company_id = company_members.company_id
            WHERE labs.lab_id = ?
              AND company_members.profile_id = ?';

        $checkStmt = $conn->prepare($checkSql);

        $checkStmt->bind_param(
            'si',
            $labId,
            $profile_id
        );

        $checkStmt->execute();

        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows == 0) {
            // Log the access denied event
            log_activity(
                $conn,
                $_SESSION['profile_id'],
                'project',
                NULL,  // No project ID yet
                'access_denied',
                'User attempted to create a project in a lab they do not have access to'
            );

            die('You do not have permission to use this lab.');
        }

        $checkStmt->close();

        // Add new project
        $sql =
            "INSERT INTO projects (name, lab_id) -- Missing the 'description' column
                VALUES (?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            'ss',
            $projectName,
            $labId
        );

        $stmt->execute();
        if ($stmt->error) {
            // Log the error
            log_activity(
                $conn,
                $_SESSION['profile_id'],
                'project',
                NULL,  // No project ID yet
                'create',
                'Error creating project: ' . $stmt->error
            );
        } else {
            // Log successful project creation
            log_activity(
                $conn,
                $_SESSION['profile_id'],
                'project',
                $stmt->insert_id,
                'create',
                'User created a new project'
            );
        }

        $newProjectId = $stmt->insert_id;

        $stmt->close();

        // Add current user as project owner
        $sql =
            "INSERT INTO project_members
            (project_id, profile_id, role)
            VALUES (?, ?, 'owner')
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            'ii',
            $newProjectId,
            $profile_id
        );

        $stmt->execute();
        if ($stmt->error) {
            // Log the error
            log_activity(
                $conn,
                $_SESSION['profile_id'],
                'project',
                $newProjectId,
                'add_member',
                'Error adding user as project owner: ' . $stmt->error
            );
        } else {
            // Log successful addition of user as project owner
            log_activity(
                $conn,
                $_SESSION['profile_id'],
                'project',
                $newProjectId,
                'add_member',
                'User ' . $_SESSION['profile_id'] . ' added as project owner'
            );
        }

        $stmt->close();

        // Redirect back to project library
        header('Location: ../library.php');
        exit();
    }
}

?>