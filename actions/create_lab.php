<?php
// Create lab action
// Arrive from:
// company_admin.php (after clicking register lab group button)
// Action:
// Register a new lab group in the database
// Redirect to:
// company_admin.php (with message indicating success or failure)

require_once '../session/init.php';  // Make the session available
require_once '../session/check_user_logged_in.php';  // Check if the user is logged in
// connect to database
require_once '../database/db.php';

require_once '../includes/log_activity.php';  // provides log_activity() function

// get the current companies company_id
$admin_company_ID = $_SESSION['company_id'];

// -------- REGISTER NEW LAB GROUP --------
// if button to register new:
if (isset($_POST['register_lab_group'])) {
    // check so that the same lab isn't added twice
    $proposed_lab_name = $_POST['name'];  // get proposed name from the text form
    // get all lab names in the company
    $sql_check_lab = 'SELECT labs.name, companies.name
                    FROM labs 
                    LEFT JOIN companies ON companies.company_id = labs.company_id
                    WHERE labs.company_id = ? AND labs.name = ?';

    $checkLabStmt = $conn->prepare($sql_check_lab);
    $checkLabStmt->bind_param('is', $admin_company_ID, $proposed_lab_name);
    $checkLabStmt->execute();
    $checkLabStmt->store_result();

    // check if the number of rows are more than 0 => lab group already exists in this company
    if ($checkLabStmt->num_rows > 0) {
        $message = 'Lab group already exists';  // message is displayed
        $toastClass = '#ff0019';  // Primary color
        // Log failed to register new lab group due to existing name
        log_activity(
            $conn,
            $_SESSION['profile_id'],  // admin's profile ID who performed the action
            'lab',  // entity type
            NULL,  // entity ID (lab ID) not applicable since lab creation failed
            'add_lab',  // activity type
            "Failed to create lab group with name $proposed_lab_name: Lab group already exists in company $admin_company_ID"  // detail
        );
    }
    // if the lab group doesn't exist in company - create it
    else {
        // use placeholders to protect against sql injection
        $sql = 'INSERT INTO labs(name, company_id) VALUES (?, ?)';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $proposed_lab_name, $admin_company_ID);
        $result = $stmt->execute();

        // updates message variable accordingly
        if ($result) {
            $message = 'Lab group successfully created';
            $toastClass = '#1ea324';  // Primary color
            // Log successful lab group creation
            log_activity(
                $conn,
                $_SESSION['profile_id'],  // admin's profile ID who performed the action
                'lab',  // entity type
                $conn->insert_id,  // entity ID (lab ID) of the newly created lab group
                'add_lab',  // activity type
                "Successfully created lab group with name $proposed_lab_name"  // detail
            );
        }
    }

    // saves in session, they are displayed from company_admin
    $_SESSION['create_lab_message'] = $message;
    $_SESSION['create_lab_toastClass'] = $toastClass;

    header('Location: ../company_admin.php');
    exit();
}

?>