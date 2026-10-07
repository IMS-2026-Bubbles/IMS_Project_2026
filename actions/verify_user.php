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



//displaying all errors
ini_set('display_errors', true);
ini_set('log_errors', true);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

//sessions
require_once "../session/init.php"; // Start the session and initialize session variables
// No check for user logged in here, verification page
require '../database/db.php';

// require logging functions
require_once '../includes/log_activity.php'; // Log login attempts

$message = "";
$toastClass = "";

$token = $_GET['token'];




if ($token == "NULL" || strlen($token) < 32 || strlen($token) > 32) {
    //check for a valid token
    header("Location: ../index.php");
    exit();
}

//retrieving if the user is verified from the database
$sql = "SELECT is_verified, profile_id FROM profiles WHERE verify_token = ?";
$checkVerifiedStmt = $conn->prepare($sql);
$checkVerifiedStmt->bind_param("s", $token);
$checkVerifiedStmt->execute();
$checkVerifiedStmt->store_result();

if ($checkVerifiedStmt == "0") { // zero rows == token not found => invalid token OR NULL because user already verified
    // Don't we need to check the value of is_verified too?
    //if user is already verified, return to index page with an message

    $message = "Verified user";
    $toastClass = "#007bff"; // Primary color
    $_SESSION['verify_user'] = $message;
    $_SESSION['toastClass'] = $toastClass;

    // Log the verification attempt
    // log_login(
    //     $conn,
        
    // )

    header("Location: ../index.php");
    exit();
    }
$checkVerifiedStmt->close();


//otherwise the user will now get verified
$sql = "UPDATE profiles SET is_verified=1 WHERE verify_token = ?";
$verifyStmt = $conn->prepare($sql);
$verifyStmt->bind_param("s", $token);
$verifyStmt->execute();
$verifyStmt->store_result();

if ($verifyStmt) {
    $verifyStmt->close();
    //user is now verified!

    $message = "You have now been verified!";
    $toastClass = "#007bff"; // Primary color
    $_SESSION['verify_user'] = $message;
    $_SESSION['toastClass'] = $toastClass;

    header("Location: ../index.php");
    exit();
    }

else {
    //something went wrong!
    $message = "Something went wrong!";
    $toastClass = "#dc3545"; // Danger color
    header("Location: ../index.php");
    $_SESSION['verify_user'] = $message;
    $_SESSION['toastClass'] = $toastClass;
    exit();

}
$verifyStmt->close();
?>

