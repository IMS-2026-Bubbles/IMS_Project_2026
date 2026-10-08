<?php
// Create lab action
// Arrive from: 
    // company_admin.php (after clicking register lab group button)
// Action:
    // Register a new lab group in the database
// Redirect to: 
    // company_admin.php (with message indicating success or failure)



require_once "../session/init.php"; // Make the session available
require_once "../session/check_user_logged_in.php"; // Check if the user is logged in
// connect to database
require_once "../database/db.php";

# get the current companies company_id
$admin_company_ID = $_SESSION["company_id"];


# -------- REGISTER NEW LAB GROUP --------
# if button to register new:
    if(isset($_POST['register_lab_group']))
    {

    # check so that the same lab isn't added twice
    $proposed_lab_name = $_POST['name']; # get proposed name from the text form
    # get all lab names in the company
    $sql_check_lab = "SELECT labs.name, companies.name
                    FROM labs 
                    LEFT JOIN companies ON companies.company_id = labs.company_id
                    WHERE labs.company_id = ? AND labs.name = ?";

    $checkLabStmt = $conn->prepare($sql_check_lab);
    $checkLabStmt->bind_param("is", $admin_company_ID, $proposed_lab_name);
    $checkLabStmt->execute();
    $checkLabStmt->store_result();    

    # check if the number of rows are more than 0 => lab group already exists in this company
    if ($checkLabStmt->num_rows > 0) {
        $message = "Lab group already exists"; # adding text to message
        $toastClass = "#ff0019"; 
    } 
    
    # if the lab group doesn't exist in company - create it
    else {
    # use placeholders to protect against sql injection
    $sql = "INSERT INTO labs(name, company_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $proposed_lab_name, $admin_company_ID);
    $result = $stmt->execute();

        # updates message variable accordingly
        if ($result) {
            $message = "Lab group successfully created";
            $toastClass = "#1ea324"; 
        } 
    }

    # saves in session, they are displayed from company_admin
    $_SESSION['create_lab_message'] = $message;
    $_SESSION['create_lab_toastClass'] = $toastClass;

    header("Location: ../company_admin.php");
    exit();

    }
    

?>