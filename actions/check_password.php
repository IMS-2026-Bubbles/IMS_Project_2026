
<?php
// Check_password Action

// Arrive from:
// user have filled in new passwords and sent in form

// Action:
// Checking the number of tries done at this IP-adress
// Checking if the token in the URL is valid,
// Checking if user is verified
// changing the password
// Redirect to:
// ../Index.php (Invalid token, no message given)
// ../Index.php (User's password is changed, message given)
// ../Index.php (Something went wrong!, Message given)

// displaying all errors
ini_set('display_errors', true);
ini_set('log_errors', true);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// sessions
require_once '../session/init.php';  // Start the session and initialize session variables
require_once '../cookies/cookie.php'; //Include cookies
// No check for user logged in here, check password page
require '../database/db.php';

// require logging functions
require_once '../includes/log_activity.php';  // Log login attempts

// require ip address functions
require_once '../includes/fetch_ip_address.php';  // provides $ip_address and $ip_address_proxy

//set message and toastclass so messages can be conveyed to other pages
$message = '';
$toastClass = '';


$token = $_GET['token'] ?? '';  // set to empty to prevent possible undefined variable error

// IP-address
require_once '../includes/fetch_ip_address.php';  // provides $ip_address and $ip_address_proxy

// Rate limiting and lockout to prevent brute-force attacks
// If 5 failed login attempts from the same IP address within 15 minutes (running), lock out for max 15 minutes
$sql_failed_attempts =
    'SELECT COUNT(*) as failed_attempts 
    FROM login_log 
    WHERE ip_address = ? 
        AND success = 0 
        AND login_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)';
$stmt_failed_attempts = $conn->prepare($sql_failed_attempts);
$stmt_failed_attempts->bind_param('s', $ip_address);
$stmt_failed_attempts->execute();
$result_failed_attempts = $stmt_failed_attempts->get_result();

if ($result_failed_attempts) {
    $row = $result_failed_attempts->fetch_assoc();
    $failed_attempts = $row['failed_attempts'];
}

if ($failed_attempts >= 5) {
    echo "too many failed attempts";
    $message = 'Too many failed token entries. Click "forgot password" and get a new link.';
    $toastClass = '#dc3545';  // Danger color
    // Log a failed verification attempt (verification locked)
    log_login(
        $conn,
        NULL,  // No profile_id since login is locked
        NULL,
        $ip_address,
        0,  // success = 0
        'Password change locked due to too many recent failed attempts: ' . $token
    );

    // Redirect back to the change password page with an error message
    $_SESSION['change_password'] = $message;
    $_SESSION['toastClass'] = $toastClass;
    header('Location: ../index.php');
    exit();
}

if (!isset($token) || empty($token) || strlen($token) != 32) {
    // If the token is invalid, send the user to the homepage
    header('Location: ../index.php');
    exit();
}

echo" token is $token";
echo" transaction started";
$conn->begin_transaction();  // Start a transaction to ensure atomicity of the verification process
$transaction_ok = true;  // Flag to track if the transaction is successful

if ($transaction_ok) {
    // A first check to see if the token is verified and the token is actually valid
    $sql = ('SELECT profile_id, is_verified, verify_token
                                FROM profiles
                                WHERE verify_token = ?');
    $checkStmt = $conn->prepare($sql);
    $checkStmt->bind_param('s', $token);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $profile = $result->fetch_assoc();

    // check if the number of rows >0 => token exists
    if ($checkStmt->num_rows > 0) {
        //the token exists, check the following
        if ((int) $profile['is_verified'] == 0) {
            //user is not verified
            $message = 'Verify your email before chaning the password.';
            $toastClass = '#007bff';  // Primary color
            $_SESSION['change_password'] = $message;
            $_SESSION['toastClass'] = $toastClass;

            // Log failed registration due to existing email
            log_login(
                $conn,
                NULL,  // profile_id is NULL since registration failed
                NULL,
                $ip_address,
                0,  // success = 0 for failure
                'Failed: Password change, User not verified'
            );
            $transaction_ok = False;
        }
        elseif ($profile['verify_token']== NULL) {
            $message = 'You have already changed your password.';
            $toastClass = '#007bff';  // Primary color
            $_SESSION['change_password'] = $message;
            $_SESSION['toastClass'] = $toastClass;

            // Log failed registration due to existing email
            log_login(
                $conn,
                NULL,  // profile_id is NULL since registration failed
                NULL,
                $ip_address,
                0,  // success = 0 for failure
                'Failed: Token does not exists'
            );
            $transaction_ok = False;
        }
    $checkStmt->close();
    }
}
if ($transaction_ok) {
    // otherwise the user will now get their pasword changed
    $password = $_POST['password1']; //fetch the password from the HTML form
    $hashed_password = password_hash($password, PASSWORD_BCRYPT); //hash the password with BCRYPT

    //Insert the new password into profiles, and update pwd_change to todays date and time
    $sql = 'UPDATE profiles SET password = ?, pwd_change=NOW() WHERE verify_token = ?';  // set the token to NULL after verification to prevent re-use?
    // Then the earlier check for already verified users would merge with the check for invalid token.
    $pwdStmt = $conn->prepare($sql);
    $pwdStmt->bind_param('ss', $hashed_password, $token);
    $transaction_ok = $pwdStmt->execute();
    if (!$transaction_ok) {
        // On failure, save error
        $error_update_pwd = $pwdStmt->error;
    }
    $pwdStmt->close();
}

if ($transaction_ok) {
    //the change of password went ok, continue with nullifying the token
    $sql_token_to_null = 'UPDATE profiles SET verify_token=NULL WHERE verify_token = ?';
    $stmt_token_to_null = $conn->prepare($sql_token_to_null);
    $stmt_token_to_null->bind_param('s', $token);
    $transaction_ok = $stmt_token_to_null->execute();
    if (!$transaction_ok) {
        // On failure, save error
        $error_token_to_null = $stmt_token_to_null->error;
    }
    $stmt_token_to_null->close();
}

if ($transaction_ok) {
    // All updates succeded, commit the transaction
    $conn->commit();

    $message = 'Your password is now changed.';
    $toastClass = '#007bff';  // Primary color
    $_SESSION['change_password'] = $message;
    $_SESSION['toastClass'] = $toastClass;

    // Log successful verification
    log_login(
        $conn,
        null,  // TODO: add profile_id retrieval based on token if needed
        null,  // TODO: add email retrieval based on token if needed
        $ip_address,
        1,  // success = 1 because password change succeeded
        'User successfully changed their password.'
    );

    // Redirect to index.php after successful verification
    header('Location: ../index.php');
    exit();
} else {
    // Something went wrong, rollback the transaction
    $conn->rollback();

    // $message = 'Something went wrong!';
    // $toastClass = '#dc3545';  // Danger color
    // // header("Location: ../index.php"); // redirect before adding messages to session makes them never be set
    // $_SESSION['change_password'] = $message;
    // $_SESSION['toastClass'] = $toastClass;

    if (isset($error_update_pwd)) {
        $details = 'Error updating password status: ' . $error_update_pwd;
    } elseif (isset($error_token_to_null)) {
        $details = 'Error setting token to NULL: ' . $error_token_to_null;
    } else {
        $details = 'Unknown error during password change process.';
    }

    // Log the failed verification attempt
    log_login(
        $conn,
        NULL,  // profile_id is NULL since verification failed
        NULL,
        $ip_address,
        0,  // success = 0 because verification failed
        $details
    );

    // Redirect to index.php after failed verification
    header('Location: ../index.php');
    exit();
}

?>