<?php




require_once '../session/init.php'; // Start the session and initialize session variables
require_once '../session/check_user_logged_in.php'; // Check if the user is logged in
require_once '../database/db.php';


if(isset($_POST['register']))
{
    # fetch data from POST request
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password1']; //Also unsure of how to send password
    $agreed_to_tos = (int)$_POST['agreed_to_tos'] ?? 0; // checkbox for Terms of Service and GDPR agreement

    

    // code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/
    // Check if email already exists
    $checkemailStmt = $conn->prepare("SELECT email FROM profiles WHERE email = ?");
    $checkemailStmt->bind_param("s", $email);
    $checkemailStmt->execute();
    $checkemailStmt->store_result();
    error_log("Checking email [$email], num_rows = " . $checkemailStmt->num_rows);


    // check if the number of rows are more than 0 => email exists
    if ($checkemailStmt->num_rows > 0) {
        $message = "email ID already exists";
        $toastClass = "#007bff"; // Primary color
    } 

    else {
            # use placeholders to protect against sql injection
            // TODO(schema-migration): becomes INSERT INTO profiles
            // (email, first_name, last_name, password) — also decide values
            // for the new columns (agreed_to_tos, saved_changes, streak) and write
            // a login_log row / set last_login_at per ARCHITECTURE.md TODOs
            $sql = "INSERT INTO profiles (email, first_name, last_name, password, agreed_to_tos) VALUES (?, ?, ?, ?, ?)";
                                                                        // I know, I made a typo in the db, toc should be tos.
                                                                        // This has been changed in the db schema file. -RH
            $stmt = $conn->prepare($sql);
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt->bind_param("ssssi", $email, $first_name, $last_name, $hashedPassword, $agreed_to_tos);
            $result = $stmt->execute();


            if ($result) {
                    $message = "Account created successfully";
                    $toastClass = "#28a745"; // Success color
                }
                
            else {
                $message = "Error: " . $stmt->error;
                $toastClass = "#dc3545"; // Danger color
            }

            $stmt->close();
        }

    $checkemailStmt->close();

    # redirect here instead of in the form down below
    # now the form is sent as a post, it would not be otherwise
    if (isset($result) && $result) {
        $_SESSION['register_user_message'] = $message;
        $_SESSION['register_user_toastClass'] = $toastClass;
        header("Location: index.php");
        session_destroy();
        exit();
    }
    include 'database/close_db.php';
}



    // $message = "";
    // $toastClass = "";

    // # if button to register new:
    // if(isset($_POST['register']))
    // {
    //     # fetch data from POST request
    //     $First_Name = $_POST['First_Name'];
    //     $Last_Name = $_POST['Last_Name'];
    //     $Email = $_POST['Email'];
    //     $Password = $_POST['Password1']; //Also unsure of how to send password

        

    //     // code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/
    //     // Check if Email already exists
    //     // TODO(schema-migration): User table becomes profiles; Email becomes email
    //     $checkEmailStmt = $conn->prepare("SELECT Email FROM users WHERE Email = ?");
    //     $checkEmailStmt->bind_param("s", $Email);
    //     $checkEmailStmt->execute();
    //     $checkEmailStmt->store_result();
    //     error_log("Checking Email [$Email], num_rows = " . $checkEmailStmt->num_rows);


    //     // check if the number of rows are more than 0 => Email exists
    //     if ($checkEmailStmt->num_rows > 0) {
    //         $message = "Email ID already exists";
    //         $toastClass = "#007bff"; // Primary color
    //     } 
    
    //     else {
    //         # use placeholders to protect against sql injection
    //         $sql = "INSERT INTO users (Email, First_Name, Last_Name, Password) VALUES (?, ?, ?, ?)";
    //         $stmt = $conn->prepare($sql);
    //         //$Salt = random_bytes($numberOfDesiredBytes); 
    //         $hashedPassword = password_hash($Password, PASSWORD_BCRYPT);
    //         $stmt->bind_param("ssss", $Email, $First_Name, $Last_Name, $hashedPassword);
    //         $result = $stmt->execute();


    //         if ($result) {
    //                 $message = "Account created successfully";
    //                 $toastClass = "#28a745"; // Success color
    //             }
                    
    //                 else {
    //                     $message = "Error: " . $stmt->error;
    //                     $toastClass = "#dc3545"; // Danger color
    //                 }

    //                 $stmt->close();
    //         }
  
    //     $checkEmailStmt->close();
    //     include '../database/close_db.php';
    
    //     # redirect here instead of in the form down below
    //     # now the form is sent as a post, it would not be otherwise
    //     if (isset($result) && $result) {
    //         header("Location: ../user_profile.php");
    //         exit;
    //     }
    // }
    
?>