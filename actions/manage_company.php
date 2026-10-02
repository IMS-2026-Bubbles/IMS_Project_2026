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


    # HANDLE POST METHOD TO CHANGE ADMIN STATUS
    # post methods should be at top in order to reload page directly
    if(isset($_POST['make_admin'])) {
        $profile_id = $_POST['profile_id'];
        $sql_admin = "UPDATE company_members SET role = 'admin' WHERE  profile_id = ?";
        $stmt = $conn->prepare($sql_admin);
        $stmt->bind_param("i", $profile_id);
        $result = $stmt->execute();
        if ($result) {
            $_SESSION['manage_company_message'] = "User promoted to admin successfully.";
        } else {
            $_SESSION['manage_company_message'] = "Error promoting user to admin: " . $stmt->error;
        }
    }

    if(isset($_POST['admin_removal'])) {
        $profile_id = $_POST['profile_id'];
        $sql_admin = "UPDATE company_members SET role = 'member' WHERE  profile_id = ?";
        $stmt = $conn->prepare($sql_admin);
        $stmt->bind_param("i", $profile_id);
        $result = $stmt->execute();
        if ($result) {
            $_SESSION['manage_company_message'] = "Admin rights removed successfully.";
        } else {
            $_SESSION['manage_company_message'] = "Error removing admin rights: " . $stmt->error;
        }
    }

    # for admin to add a person to company: 
    if(isset($_POST['add_to_company'])){
        echo "wohooo";
        $profile_id = $_POST['profile_id'];
        $company_id = $_POST['add_to_company'];
        $sql = "INSERT INTO company_members (company_id, profile_id, role) VALUES (?, ?, 'member')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $company_id, $profile_id);
        $result = $stmt->execute();
        $stmt->close();
        }
    // if(isset($_POST['admin_removal'])){
    //     $profile_id = $_POST['profile_id'];
    //     $sql_admin = "UPDATE company_members SET role = 'member' WHERE  profile_id = ?";
    //     $stmt = $conn->prepare($sql_admin);
    //     $stmt->bind_param("i", $profile_id);
    //     $result = $stmt->execute();}



    # if button to register new:
    if(isset($_POST['register_company']))
    {
    # fetch data from POST request
    $name = $_POST['name'];

    # write a function that creates unique ID
    function createUniqueCompanyID($conn) {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789'; # these are possible char to choose from
        $code = "C"; # all company ids start with C
        # for loop that generates a random number and pick the char with that position
        for ($i = 0; $i < 14; $i++) {
            $random_number = random_int(0, strlen($characters) - 1);
            $code .= $characters[$random_number];}
        return $code;}

    $company_id = createUniqueCompanyID($conn);

    # add a check here as well so that the same companies isn't added twice
    $checkCompStmt = $conn->prepare("SELECT name FROM companies WHERE name = ?");
    $checkCompStmt->bind_param("s", $name);
    $checkCompStmt->execute();
    $checkCompStmt->store_result();
    error_log("Checking company name: [$name], num_rows = " . $checkCompStmt->num_rows);


    // check if the number of rows are more than 0 => Email exists
    if ($checkCompStmt->num_rows > 0) {
        $_SESSION['manage_company_message'] = "Company already exists";
        $_SESSION['manage_company_toastClass'] = "#ff0019"; // Primary color
    } 
    
    else {
    # use placeholders to protect against sql injection
    $sql = "INSERT INTO companies(name, company_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $company_id);
    $result = $stmt->execute();

    # echos how it went
    if ($result) {
        $_SESSION['manage_company_message'] = "Company created";
        $_SESSION['manage_company_toastClass'] = "#1ea324"; // Primary color
    } else {
        echo "Error: " . $stmt->error;
    }

    }
    
    }
    header("Location: ../scriba_admin.php");
    exit;

?>