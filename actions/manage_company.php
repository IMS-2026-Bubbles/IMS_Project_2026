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


require_once "../session/init.php"; // Start the session and initialize session variables
require_once "../session/check_user_logged_in.php"; // Check if the user is logged in
require_once '../database/db.php';


# CHANGE ADMIN STATUS
if(isset($_POST['make_admin'])) {
    # get profile id for user to change role
    $profile_id_changing_role = $_POST['profile_id'];
    $sql_admin = "UPDATE company_members 
                SET role = 'admin' 
                WHERE profile_id = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param("i", $profile_id_changing_role);
    $result = $stmt->execute();
    
    # store the result to display
    if ($result) {
        $message = "User promoted to admin successfully.";
        $toastClass = "#221ea3";
    } 
    else {
        $message = "Error promoting user to admin";
        $toastClass = "#ff0019";
    }
    }


if(isset($_POST['admin_removal'])) {
    # get profile id for user to change role
    $profile_id_changing_role = $_POST['profile_id'];
    $sql_admin = "UPDATE company_members 
                SET role = 'member' 
                WHERE  profile_id = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param("i", $profile_id_changing_role);
    $result = $stmt->execute();

    # store the result to display
    if ($result) {
        $message = "User set to member.";
        $toastClass = "#221ea3";
    } 
    else {
        $message = "Error setting user to member";
        $toastClass = "#ff0019";
    }
}

# store in session variable
$_SESSION['manage_company_message'] = $message;
$_SESSION['manage_company_toastClass'] = $toastClass;


# ADD PERSON TO COMPANY
if(isset($_POST['add_to_company'])){
    # get profile id for user to be added to company
    $profile_id_to_add = $_POST['profile_id'];
    # get company ID from the dropdown menu
    $company_id = $_POST['add_to_company'];
    $sql = "INSERT INTO company_members (company_id, profile_id, role) VALUES (?, ?, 'member')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $company_id, $profile_id_to_add);
    $result = $stmt->execute();
    $stmt->close();
}


# REGISTER NEW COMPANY
if(isset($_POST['register_company']))
{
    # fetch data from POST request
    $new_comp_name = $_POST['name'];

    # add a check here as well so that the same companies isn't added twice
    $sql_check_comp = "SELECT name 
                    FROM companies 
                    WHERE name = ?";
    $checkCompStmt = $conn->prepare($sql_check_comp);
    $checkCompStmt->bind_param("s", $new_comp_name);
    $checkCompStmt->execute();
    $checkCompStmt->store_result();

    # check if the number of rows are more than 0 => company exists
    if ($checkCompStmt->num_rows > 0) {
        $_SESSION['manage_company_message'] = "Company already exists";
        $_SESSION['manage_company_toastClass'] = "#ff0019"; // Primary color
    } 
    
    else {
    # use placeholders to protect against sql injection
    $sql = "INSERT INTO companies(name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    $result = $stmt->execute();

        # echos how it went
        if ($result) {
            $_SESSION['manage_company_message'] = "Company created";
            $_SESSION['manage_company_toastClass'] = "#1ea324"; // Primary color
        } 
    }
}

# redirect back to admin page
header("Location: ../scriba_admin.php");
exit;

?>