<?php
// Login action

// Arrive from: 
    // index.php (login form)
// Action: 
    // Check login credentials, 
    // set session variables, 
    // and redirect to the appropriate page based on user type
// Redirect to: 
    // scriba_admin.php, project_library.php, or user_profile.php (depending on user type)
    // index.php (invalid login credentials)

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require_once "../session/init.php"; // Start the session and initialize session variables
// No check for user logged in here, login action
require_once '../database/db.php';

require_once '../includes/log_activity.php'; // Include the log_activity function

$message = "";
$toastClass = "";
$logincredentials = False;

if(isset($_POST["login"])){
    # fetch data from POST request
    $email = $_POST['email'];
    $password = $_POST['password'];

    // code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/
    // Check if email already exists
    // TODO(schema-migration): Profiles table becomes profiles; email becomes email
    $sql = "SELECT email FROM profiles WHERE email = ?";
    $checkemailStmt = $conn->prepare($sql);
    $checkemailStmt->bind_param("s", $email);
    $checkemailStmt->execute();
    $checkemailStmt->store_result();
    error_log("Checking email [$email], num_rows = " . $checkemailStmt->num_rows);

    // IP-address
    require_once '../includes/get_ip_address.php'; // provides $ip_address and $ip_address_proxy 

    // Rate limiting and lockout to prevent brute-force attacks
    // If 5 failed login attempts from the same IP address within 15 minutes (running), lock out for max 15 minutes
    $sql_failed_attempts = 
        "SELECT COUNT(*) as failed_attempts 
        FROM login_log 
        WHERE ip_address = ? 
            AND success = 0 
            AND login_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
    $stmt_failed_attempts = $conn->prepare($sql_failed_attempts);
    $stmt_failed_attempts->bind_param("s", $ip_address);
    $stmt_failed_attempts->execute();
    $result_failed_attempts = $stmt_failed_attempts->get_result();

    if ($result_failed_attempts) {
        $row = $result_failed_attempts->fetch_assoc();
        $failed_attempts = $row['failed_attempts'];
    }

    if ($failed_attempts >= 5) {
        $message = "Too many failed login attempts. Please try again later.";
        $toastClass = "#dc3545"; // Danger color

        // Log a failed login attempt (login locked)
        log_login(
            $conn,
            NULL, // No profile_id since login is locked
            $email,
            $ip_address,
            0, // success = 0
            'Login locked due to too many recent failed attempts'
        );

        // Redirect back to the login page with an error message
        $_SESSION['login_error'] = $message;
        $_SESSION['toastClass'] = $toastClass;
        header("Location: ../index.php");
        exit();
    }

    // check if the number of rows are more than 0 => email exists
    if ($checkemailStmt->num_rows < 1) {
        $message = "Invalid email or password.";
        $toastClass = "#dc3545"; // Danger color

        // Log a failed login attempt (email not found)
        log_login(
            $conn, 
            NULL, // profile_id is NULL since login failed
            $email, 
            $ip_address, 
            0, // success = 0 for failure
            'Login failed: Email not found'
        );
    } else {
        # use placeholders to protect against sql injection
        // TODO: write a login_log row (email, success/failure) for rate limiting
        // and lockout, and set profiles.last_login_at on successful login,
        // per the ARCHITECTURE.md TODO items.

        $sql = "SELECT password, profile_id, is_verified FROM profiles WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $profile = $result->fetch_assoc();

        if ((int)$profile['is_verified'] == 1) {
            // Check if the password matches the hashed password in the database
            if (password_verify($password, $profile['password'])) {
                //user is verfied and can now log in
                $logincredentials = True;

            } else {
                $message = "Invalid email or password.";
                $toastClass = "#dc3545"; // Danger color

                // Log a failed login attempt (incorrect password)
                log_login(
                    $conn, 
                    $profile['profile_id'], 
                    $email, 
                    $ip_address, 
                    0, // success = 0 for failure
                    'Login failed: Incorrect password'
                );
            }
            
            // // $stmt->close();
            
            // else {
            // $message = "Invalid email or password";
            // $toastClass = "#dc3545"; // Danger color
            // }

            // }
                
            // else {
            //     $message = "User is not verified";
            //     $toastClass = "#dc3545"; // Danger color
            // }
        
            $stmt->close();
        }
    

        // Adding a decision tree for what type of account you are logging into,
            // and redirecting accordingly. Using $_SESSION['profile_id'] (see above) and $_SESSION['company_id'].
            // Scriba admin => scriba_admin.php
            // User with company_id => project_library.php
            // User that doesn't belong to a company => user_profile.php
        if ($logincredentials) {
            // Log successful login attempt
            log_login(
                $conn, 
                $profile['profile_id'], 
                $email, 
                $ip_address, 
                1, // success = 1 for success
                'Successful login'
            );

            // Update last login timestamp in profiles table
            $sql_update_last_login = "UPDATE profiles SET last_login_at = NOW() WHERE profile_id = ?";
            $stmt_update_last_login = $conn->prepare($sql_update_last_login);
            $stmt_update_last_login->bind_param("i", $profile['profile_id']);
            $result_update_last_login = $stmt_update_last_login->execute();

            if ($result_update_last_login) {
                // Log the successful update of last login timestamp
                log_activity(
                    $conn, 
                    $profile['profile_id'], 
                    'profile', 
                    $profile['profile_id'], 
                    'update', 
                    'Updated last login timestamp'
                );
            } else {
                // Log the failed update of last login timestamp
                log_activity(
                    $conn, 
                    $profile['profile_id'], 
                    'profile', 
                    $profile['profile_id'], 
                    'update', 
                    'Failed to update last login timestamp: ' . $result_update_last_login->error
                );
            }

            $stmt_update_last_login->close();

            // Since login = success, add profile_id to session so we can access it on other pages.
            $_SESSION['profile_id'] = $profile['profile_id'];

            // Fetch the user's affiliations from the database. Need if they are scriba admin
            // and if they belong to at least one company.
            $profile_id = $_SESSION['profile_id'];
            require_once '../includes/fetch_profile_affiliation.php'; // expects $conn and $profile_id
            
            // Add company_id to session if user has one.
            $_SESSION['company_id'] = $user_affiliations['companies'][0] ?? NULL; // If user has no company, set to NULL

            // Scriba admin
            $user_is_scriba_admin = $user_affiliations['is_scriba_admin'] ?? 0; // One value, 1 or 0

            // User with any company_id
            // $user_company_ids = $user_affiliations['companies']; // array of company_ids, empty if none
            if ($user_is_scriba_admin == 1) { // Scriba admin => scriba_admin.php
                header ("Location:../scriba_admin.php");
                exit();

            } elseif (isset($_SESSION['company_id'])) { // User with company_id => project_library.php
                header ("Location:../project_library.php");
                exit();

            } else { // User that doesn't belong to a company => user_profile.php
                header ("Location:../user_profile.php");
                exit();
            }

        } else {
            // If login credentials are invalid, redirect back to the login page with an error message
            $_SESSION['login_error'] = $message;
            $_SESSION['toastClass'] = $toastClass;

            // Log a failed login attempt (invalid credentials)
            log_login(
                $conn, 
                $profile['profile_id'] ?? NULL, // profile_id is NULL if email not found
                $email, 
                $ip_address, 
                0, // success = 0 for failure
                'Login failed: Invalid credentials'
            );

            header("Location: ../index.php");
            exit();
        }
    } else {
        // If login credentials are invalid, redirect back to the login page with an error message
        $_SESSION['login_error'] = $message;
        $_SESSION['toastClass'] = $toastClass;
        header("Location: ../index.php");
        exit();
    }

}
?>