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

    
    require_once '../includes/get_ip_address.php'; // provides $ip_address and $ip_address_proxy

    

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
        // Log "login" for failed registration due to existing email
        $sql_register_failed = 
            "INSERT INTO login_log (profile_id, email, ip_address, success, detail)
                VALUES (NULL, ?, ?, 0, 'Registration failed: Email already exists')";
        $stmt_register_failed = $conn->prepare($sql_register_failed);
        $stmt_register_failed->bind_param("ss", $email, $ip_address);
        $stmt_register_failed->execute();
        $stmt_register_failed->close();

        header("Location: ../register_user.php");
    } 

    else {
            # use placeholders to protect against sql injection
            // TODO: write a login_log row / set last_login_at after registration,
            // per the ARCHITECTURE.md TODO items.
            $sql = 
                "INSERT INTO profiles (email, first_name, last_name, password, agreed_to_tos) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt->bind_param("ssssi", $email, $first_name, $last_name, $hashedPassword, $agreed_to_tos);
            $result = $stmt->execute();

            if ($result==False) {
                $message = "Error: " . $stmt->error;
                $_SESSION['register_user_message'] = $message;
                $toastClass = "#dc3545"; // Danger color
                $_SESSION['register_user_toastClass'] = $toastClass;

                // Log "login" for failed registration due to database error
                $sql_register_failed = 
                    "INSERT INTO login_log (profile_id, email, ip_address, success, detail)
                        VALUES (NULL, ?, ?, 0, 'Registration failed: Database error')";
                $stmt_register_failed = $conn->prepare($sql_register_failed);
                $stmt_register_failed->bind_param("ss", $email, $ip_address);
                $stmt_register_failed->execute();
                $stmt_register_failed->close();

                header("Location: ../register_user.php");
            }

            // If registration is successful, log the successful registration
                // Get the profile_id of the newly registered user
            $sql_get_profile_id = "SELECT profile_id FROM profiles WHERE email = ?";
            $stmt_get_profile_id = $conn->prepare($sql_get_profile_id);
            $stmt_get_profile_id->bind_param("s", $email);
            $stmt_get_profile_id->execute();
            $result = $stmt_get_profile_id->get_result();
            $profile = $result->fetch_assoc();
            $profile_id = $profile['profile_id'];
                // Log "login" for successful registration
            $sql_register_success = 
                "INSERT INTO login_log (profile_id, email, ip_address, success, detail)
                    VALUES (?, ?, ?, 1, 'Registration successful')";
            $stmt_register_success = $conn->prepare($sql_register_success);
            $stmt_register_success->bind_param("iss", $profile_id, $email, $ip_address);
            $stmt_register_success->execute();
            $stmt_register_success->close();

            $stmt->close();
        }

    $checkemailStmt->close();

    # redirect here instead of in the form down below
    # now the form is sent as a post, it would not be otherwise
    if (isset($result) && $result) {
        $_SESSION['register_user_message'] = $message;
        $_SESSION['register_user_toastClass'] = $toastClass;
        header("Location: ../index.php");
        // session_destroy(); // don't destroy session to allow the message to be displayed on the index.php page
        // exit();
    }
    include '../database/close_db.php';
}
    
?>