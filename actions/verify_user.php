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

if ($token == 'NULL' || strlen($token) < 32 || strlen($token) > 32) {
    // check for a valid token
    header('Location: ../index.php');
    exit();
}

// retrieving if the user is verified from the database
$sql = 'SELECT is_verified, profile_id, email FROM profiles WHERE verify_token = ?';
$checkVerifiedStmt = $conn->prepare($sql);
$checkVerifiedStmt->bind_param('s', $token);
$checkVerifiedStmt->execute();
// $checkVerifiedStmt->store_result(); // use get_results() and fetch_assoc() instead so I can use ID and email for logging
$check_verified_result = $checkVerifiedStmt->get_result()->fetch_assoc();  // should only be one row

// Check if the token is valid (i.e., if a user with this token exists)
// if ($checkVerifiedStmt->num_rows === 0) {
if ($check_verified_result === false) {  // $check_verified_result === false if no row was returned, the token is invalid
    // Invalid token, redirect to index.php without a message
    header('Location: ../index.php');
    exit();
}

// Check if the user is already verified
if ((int) $check_verified_result['is_verified'] == 1) {  // 0 => not verified, 1 => verified (changed from 0 -> 1)
    // if user is already verified, return to index page with an message

    $message = 'Verified user';
    $toastClass = '#007bff';  // Primary color
    $_SESSION['verify_user'] = $message;
    $_SESSION['toastClass'] = $toastClass;

    // Log the verification attempt
    log_login(
        $conn,
        $check_verified_result['profile_id'],
        $check_verified_result['email'],
        $ip_address,
        0,  // success = 0 because verification failed
        'User already verified but tried to verify again.'
    );

    header('Location: ../index.php');
    exit();
}
$checkVerifiedStmt->close();

// otherwise the user will now get verified
$sql = 'UPDATE profiles SET is_verified=1 WHERE verify_token = ?';  // set the token to NULL after verification to prevent re-use?
// Then the earlier check for already verified users would merge with the check for invalid token.
$verifyStmt = $conn->prepare($sql);
$verifyStmt->bind_param('s', $token);
$verifyStmt->execute();
// $verifyStmt->store_result(); // results are never used?

if (!($verifyStmt->error)) {  // if ($verifyStmt) is always true! but if (!($verifyStmt->error)) is false (inverted) if there is an error
    $verifyStmt->close();
    // user is now verified!

    $message = 'You have now been verified!';
    $toastClass = '#007bff';  // Primary color
    $_SESSION['verify_user'] = $message;
    $_SESSION['toastClass'] = $toastClass;

    // Log the successful verification
    log_login(
        $conn,
        $check_verified_result['profile_id'],
        $check_verified_result['email'],
        $ip_address,
        1,  // success = 1 because verification succeeded
        'User successfully verified their account.'
    );

    header('Location: ../index.php');
    exit();
} else {
    // something went wrong!
    $message = 'Something went wrong!';
    $toastClass = '#dc3545';  // Danger color
    // header("Location: ../index.php"); // redirect before adding messages to session makes them never be set
    $_SESSION['verify_user'] = $message;
    $_SESSION['toastClass'] = $toastClass;

    // Log the failed verification attempt
    log_login(
        $conn,
        $check_verified_result['profile_id'],
        $check_verified_result['email'],
        $ip_address,
        0,  // success = 0 because verification failed
        'User failed to verify their account due to an error: ' . $verifyStmt->error
    );

    header('Location: ../index.php');
    exit();
}
$verifyStmt->close();
?>

