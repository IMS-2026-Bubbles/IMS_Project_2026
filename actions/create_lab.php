<?php
// Create lab action

// Arrive from: 
    // company_admin.php (after clicking register lab group button)
// Action:
    // Register a new lab group in the database
// Redirect to: 
    // company_admin.php (with message indicating success or failure)



    
require_once "session/init.php"; // Make the session available
require_once "session/check_user_logged_in.php"; // Check if the user is logged in
// connect to database
require_once "database/db.php";




# if button to register new:
    if(isset($_POST['register_lab_group']))
    {
        // company_id from session
        $admin_company_ID = $_SESSION['company_id'];
    # fetch data from POST request
    $name = $_POST['name'];

    # write a function that creates unique ID
    function createUniqueLabID($conn) {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789'; # these are possible char to choose from
        $code = "L"; # all company ids start with C
        # for loop that generates a random number and pick the char with that position
        for ($i = 0; $i < 9; $i++) {
            $random_number = random_int(0, strlen($characters) - 1);
            $code .= $characters[$random_number];}
        return $code;}

    $lab_id = createUniqueLabID($conn);

    # add a check here as well so that the same companies isn't added twice
    $checkLabStmt = $conn->prepare("SELECT name FROM labs WHERE name = ?");
    $checkLabStmt->bind_param("s", $name);
    $checkLabStmt->execute();
    $checkLabStmt->store_result();
    error_log("Checking lab group name: [$name], num_rows = " . $checkLabStmt->num_rows);


    // check if the number of rows are more than 0 => Email exists
    if ($checkLabStmt->num_rows > 0) {
        $message = "Lab group already exists";
        $toastClass = "#ff0019"; // Primary color
    } 
    
    else {
    # use placeholders to protect against sql injection
    $sql = "INSERT INTO labs(name, lab_id, company_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $lab_id, $admin_company_ID);
    $result = $stmt->execute();

    # echos how it went
    if ($result) {
        $message = "Lab group created";
        $toastClass = "#1ea324"; // Primary color
    } else {
        error_log("Error: " . $stmt->error);
    }
    }

    $_SESSION['create_lab_message'] = $message;
    $_SESSION['create_lab_toastClass'] = $toastClass;

    header("Location: ../company_admin.php");
    exit();

    }

?>