<?php
// verify_user Action

// Arrive from: 
    // user's own mail, when clicking a link
// Action: 
    // Checking if the token in the URL is valid,
    // Checking if user is already verified,
    // Varifying user
// Redirect to: 
    // ../Index.php (Invalid token, no message given)
    // ../Index.php (Already verified user, message given)
    // ../Index.php (User is now verified, message given)
    // ../Index.php (Something wen wrong!, Message given)



//displaying all errors
ini_set('display_errors', true);
ini_set('log_errors', true);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

//sessions
require_once "../session/init.php"; // Start the session and initialize session variables
// No check for user logged in here, verification page
require '../database/db.php';

$message = "";
$toastClass = "";

$token = $_GET['token'];




if ($token == "NULL" || strlen($token) < 32 || strlen($token) > 32) {
    //check for a valid token
    header("Location: ../index.php");
    exit();
}

$sql = "SELECT is_verified FROM profiles WHERE verify_token = ?";

$checkVerifiedStmt = $conn->prepare($sql);
$checkVerifiedStmt->bind_param("s", $token);
$checkVerifiedStmt->execute();
$checkVerifiedStmt->store_result();
//error_log("Checking verifies $checkVerifiedStmt, num_rows = " . $checkVerifiedStmt->num_rows);


if ($checkVerifiedStmt == "0") {
    //if user is already verified, return to index page with an message

    $message = "Verified user";
    $toastClass = "#007bff"; // Primary color
    $_SESSION['verify_user'] = $message;
    $_SESSION['toastClass'] = $toastClass;

    header("Location: ../index.php");
    exit();
    }
$checkVerifiedStmt->close();


//otherwise the user will now get varified
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
    $verifyStmt->close();
    //check for a valid token
    $message = "Something went wrong!";
    $toastClass = "#dc3545"; // Danger color

    header("Location: ../index.php");

    $_SESSION['verify_user'] = $message;
    $_SESSION['toastClass'] = $toastClass;
    exit();

}

?>

