<?php
// verify_user Action

// Arrive from:
// user's own mail, when clicking a link
// Action:
// Checking if the token in the URL is valid,
// Checking if user is already verified,
// Verifying user
// Redirect to:
// ../Index.php (Invalid token, no message given)
// ../Index.php (Already verified user, message given)
// ../Index.php (User is now verified, message given)
// ../Index.php (Something went wrong!, Message given)

// displaying all errors
ini_set('display_errors', true);
ini_set('log_errors', true);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// sessions
require_once '../session/init.php';  // Start the session and initialize session variables
// No check for user logged in here, verification page
require '../database/db.php';

// require logging functions
require_once '../includes/log_activity.php';  // Log login attempts

// require ip address functions
require_once '../includes/fetch_ip_address.php';  // provides $ip_address and $ip_address_proxy

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
    $message = 'Too many failed login attempts. Please try again later.';
    $toastClass = '#dc3545';  // Danger color

    // Log a failed verification attempt (verification locked)
    log_login(
        $conn,
        NULL,  // No profile_id since login is locked
        NULL,
        $ip_address,
        0,  // success = 0
        'Verification locked due to too many recent failed attempts: ' . $token
    );

    // Redirect back to the login page with an error message
    $_SESSION['verify_user'] = $message;
    $_SESSION['toastClass'] = $toastClass;
    header('Location: ../index.php');
    exit();
}

if (!isset($token) || empty($token) || strlen($token) != 32) {
    // If the token is invalid, send the user to the homepage
    header('Location: ../index.php');
    exit();
}

$conn->begin_transaction();  // Start a transaction to ensure atomicity of the verification process
$transaction_ok = true;  // Flag to track if the transaction is successful

if ($transaction_ok) {
    // otherwise the user will now get verified
    $sql = 'UPDATE profiles SET is_verified=1 WHERE verify_token = ?';  // set the token to NULL after verification to prevent re-use?
    // Then the earlier check for already verified users would merge with the check for invalid token.
    $verifyStmt = $conn->prepare($sql);
    $verifyStmt->bind_param('s', $token);
    $transaction_ok = $verifyStmt->execute();
    if (!$transaction_ok) {
        // On failure, save error
        $error_update_verification = $verifyStmt->error;
    }
    $verifyStmt->close();
    // $verifyStmt->store_result(); // results are never used?
}

if ($transaction_ok) {
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

    $message = 'You have now been verified!';
    $toastClass = '#007bff';  // Primary color
    $_SESSION['verify_user'] = $message;
    $_SESSION['toastClass'] = $toastClass;

    // Log successful verification
    log_login(
        $conn,
        null,  // TODO: add profile_id retrieval based on token if needed
        null,  // TODO: add email retrieval based on token if needed
        $ip_address,
        1,  // success = 1 because verification succeeded
        'User successfully verified their account.'
    );

    // Redirect to index.php after successful verification
    header('Location: ../index.php');
    exit();
} else {
    // Something went wrong, rollback the transaction
    $conn->rollback();

    $message = 'Something went wrong!';
    $toastClass = '#dc3545';  // Danger color
    // header("Location: ../index.php"); // redirect before adding messages to session makes them never be set
    $_SESSION['verify_user'] = $message;
    $_SESSION['toastClass'] = $toastClass;

    if (isset($error_update_verification)) {
        $details = 'Error updating verification status: ' . $error_update_verification;
    } elseif (isset($error_token_to_null)) {
        $details = 'Error setting token to NULL: ' . $error_token_to_null;
    } else {
        $details = 'Unknown error during verification process.';
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

