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

$message = "";
$toastClass = "";
$logincredentials = False;

if(isset($_POST["login"]))
    {
        # fetch data from POST request
        $email = $_POST['email'];
        $password = $_POST['password'];

        // code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/
        // Check if email already exists
        // TODO(schema-migration): Profiles table becomes profiles; email becomes email
        $sql = "SELECT * FROM profiles WHERE email = ?";
        $checkemailStmt = $conn->prepare($sql);
        $checkemailStmt->bind_param("s", $email);
        $checkemailStmt->execute();
        $checkemailStmt->store_result();
        error_log("Checking email [$email], num_rows = " . $checkemailStmt->num_rows);

        // IP-address
            // Direct
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? NULL;
        $ip_address = filter_var($ip_address, FILTER_VALIDATE_IP) ? $ip_address : NULL; // Validate IP address
            // Proxy (how to use this?)
        $ip_address_proxy = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? NULL;
        $ip_address_proxy = $ip_address_proxy ? explode(',', $ip_address_proxy)[0] : NULL; // Get the first IP in the list if multiple
        $ip_address_proxy = filter_var($ip_address_proxy, FILTER_VALIDATE_IP) ? $ip_address_proxy : NULL; // Validate IP address

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
            $sql_login_failed = 
                "INSERT INTO login_log (profile_id, email, ip_address, success, detail)
                    VALUES (NULL, ?, ?, 0, 'Login locked due to too many recent failed attempts')";
            $stmt_login_failed = $conn->prepare($sql_login_failed);
            $stmt_login_failed->bind_param("ss", $email, $ip_address);
            $stmt_login_failed->execute();

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
            $sql_login_failed = 
                "INSERT INTO login_log (profile_id, email, ip_address, success, detail)
                    VALUES (NULL, ?, ?, 0, 'Email not found')";
            $stmt_login_failed = $conn->prepare($sql_login_failed);
            $stmt_login_failed->bind_param("ss", $email, $ip_address);
            $stmt_login_failed->execute();
        } 

        else {
                # use placeholders to protect against sql injection
                // TODO: write a login_log row (email, success/failure) for rate limiting
                // and lockout, and set profiles.last_login_at on successful login,
                // per the ARCHITECTURE.md TODO items.

                $sql = "SELECT password, profile_id FROM profiles WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $profile = $result->fetch_assoc();

                if (password_verify($password, $profile['password'])) {
                        //correct log in info
                        $logincredentials = True;
                }
                    
                else {
                    $message = "Invalid email or password.";
                    $toastClass = "#dc3545"; // Danger color

                    // Log a failed login attempt (incorrect password)
                    $sql_login_failed = 
                        "INSERT INTO login_log (profile_id, email, ip_address, success, detail)
                            VALUES (?, ?, ?, 0, 'Incorrect password')";
                    $stmt_login_failed = $conn->prepare($sql_login_failed);
                    $stmt_login_failed->bind_param("iss", $profile['profile_id'], $email, $ip_address);
                    $stmt_login_failed->execute();
                }
            
                $stmt->close();
                
            }
    

        // Adding a decision tree for what type of account you are logging into,
            // and redirecting accordingly. Using $_SESSION['profile_id'] (see above) and $_SESSION['company_id'].
            // Scriba admin => scriba_admin.php
            // User with company_id => project_library.php
            // User that doesn't belong to a company => user_profile.php
        if ($logincredentials) {
            // Log successful login attempt
            $sql_login_success = 
                "INSERT INTO login_log (profile_id, email, ip_address, success, detail)
                    VALUES (?, ?, ?, 1, 'Successful login')";
            $stmt_login_success = $conn->prepare($sql_login_success);
            $stmt_login_success->bind_param("iss", $profile['profile_id'], $email, $ip_address);
            $stmt_login_success->execute();

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
            }
            elseif (isset($_SESSION['company_id'])) { // User with company_id => project_library.php
                header ("Location:../project_library.php");
                exit();
            }
            else { // User that doesn't belong to a company => user_profile.php
                header ("Location:../user_profile.php");
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