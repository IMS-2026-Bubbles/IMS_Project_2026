<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require_once "session/init.php"; // Start the session and initialize session variables
// No check for user logged in here, front page
require_once 'database/db.php';

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


        // check if the number of rows are more than 0 => email exists
        if ($checkemailStmt->num_rows < 1) {
            $message = "Invalid email or password.";
            $toastClass = "#dc3545"; // Danger color
        } 

        else {
                # use placeholders to protect against sql injection
                // TODO(schema-migration): becomes INSERT INTO profiles
                // for the new columns (agreed_to_tos, saved_changes, streak) and write
                // a login_log row / set last_login_at per ARCHITECTURE.md TODOs

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
                    }
            
                $stmt->close();
                
            }
    
        # redirect here instead of in the form down below
        # now the form is sent as a post, it would not be otherwise
        // Adding a decision tree for what type of account you are logging into,
            // and redirecting accordingly. Using $profile['profile_id'] (see above).
            // Scriba admin => scriba_admin.php
            // User with company_id => project_library.php
            // User that doesn't belong to a company => user_profile.php
        if ($logincredentials) {
            // Since login = success, add profile_id to session so we can access it on other pages.
            $_SESSION['profile_id'] = $profile['profile_id'];

            // Fetch the user's affiliations from the database. Need if they are scriba admin
            // and if they belong to at least one company.
            $profile_id = $profile['profile_id'];
            require_once 'includes/fetch_user_affiliation.php'; // expects $conn and $profile_id
            // Add company_id to session if user has one.
            $_SESSION['company_id'] = $user_affiliations['companies'][0] ?? NULL; // If user has no company, set to NULL

            // Scriba admin
            $user_is_scriba_admin = $user_affiliations['is_scriba_admin'] ?? 0; // One value, 1 or 0

            // User with any company_id
            // $user_company_ids = $user_affiliations['companies']; // array of company_ids, empty if none

            if ($user_is_scriba_admin == 1) { // Scriba admin => scriba_admin.php
                header ("Location:scriba_admin.php");
                exit();
            } elseif (isset($_SESSION['company_id'])) { // User with company_id => project_library.php
                header ("Location:project_library.php");
                exit();
            } else { // User that doesn't belong to a company => user_profile.php
                header ("Location:user_profile.php");
                exit();
            }

        } else {
            // If login credentials are invalid, redirect back to the login page with an error message
            $_SESSION['login_error'] = $message;
            $_SESSION['toastClass'] = $toastClass;
            header("Location: index.php");
            exit();
        }

        // $checkemailStmt->close();
        // include 'database/close_db.php';
    }
?>