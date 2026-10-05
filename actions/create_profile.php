<?php
// Create profile action

// Arrive from:
    // register_user.php (register new user form submission)
// Action:
    // Register a new profile in the database,
    // destroy the session, and set a success/failure message
// Redirect to:
    // index.php (with message indicating success or failure)




require_once '../session/init.php'; // Start the session and initialize session variables
//require_once '../session/check_user_logged_in.php'; // Check if the user is logged in
require_once '../database/db.php';


if(isset($_POST['register']))
{
    # fetch data from POST request
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password1']; 
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
        $_SESSION['register_user_message'] = $message;

        $toastClass = "#007bff"; // Primary color
        $_SESSION['register_user_toastClass'] = $toastClass;

        header("Location: ../register_user.php");
    } 

    else {
            # use placeholders to protect against sql injection
            // TODO: decide values for the remaining new columns at registration time
            // (saved_changes, streak — rely on schema defaults or set explicitly).
            // TODO: write a login_log row / set last_login_at after registration,
            // per the ARCHITECTURE.md TODO items.
            $sql = "INSERT INTO profiles (email, first_name, last_name, password, agreed_to_tos) VALUES (?, ?, ?, ?, ?)";
                                                                        // I know, I made a typo in the db, toc should be tos.
                                                                        // This has been changed in the db schema file. -RH
            $stmt = $conn->prepare($sql);
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt->bind_param("ssssi", $email, $first_name, $last_name, $hashedPassword, $agreed_to_tos);
            $result = $stmt->execute();

            if ($result==False) {
                $message = "Error: " . $stmt->error;
                $_SESSION['register_user_message'] = $message;

                $toastClass = "#dc3545"; // Danger color
                $_SESSION['register_user_toastClass'] = $toastClass;

                header("Location: ../register_user.php");
            }

            $stmt->close();
        }

    $checkemailStmt->close();

    # redirect here instead of in the form down below
    # now the form is sent as a post, it would not be otherwise
    if (isset($result) && $result) {
        $_SESSION['register_user_message'] = $message;
        $_SESSION['register_user_toastClass'] = $toastClass;
        header("Location: ../index.php");
        session_destroy();
        exit();
    }
    include '../database/close_db.php';
}
    
?>