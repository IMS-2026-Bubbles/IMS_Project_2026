<?php
// Page which validates the email for password recovery

// Arrive from:
// forgot_password.php (submitted form)

// Action: if email is valid generate a token for the user, send an email to user

// Redirect to:
// index.php (with message that an email has been sent if user exist)

// showing all error messages on the page
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../session/init.php';  // Start the session and initialize session variables
// No check for user logged in here, login action
require_once '../cookies/cookie.php';  // Include cookies
require_once '../database/db.php';  // Include database
require_once '../includes/log_activity.php';  // Include the log_activity function

// include to send the varification mail
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

$message = '';
$toastClass = '';
$usercredentials = False;
echo "arrived at the page \n";
if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];
    echo 'saw that button was pressed';
    $sql = 'SELECT email FROM profiles WHERE email = ?';
    $checkemailStmt = $conn->prepare($sql);
    $checkemailStmt->bind_param('s', $email);
    $checkemailStmt->execute();
    $checkemailStmt->store_result();
    error_log("Checking email [$email], num_rows = " . $checkemailStmt->num_rows);

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

    if ($failed_attempts >= 50) {
        echo 'too many failed attempts';
        $message = 'Too many failed login attempts. Please try again later.';
        $toastClass = '#dc3545';  // Danger color

        // Log a failed login attempt (login locked)
        log_login(
            $conn,
            NULL,  // No profile_id since login is locked
            $email,
            $ip_address,
            0,  // success = 0
            'Login locked due to too many recent failed attempts'
        );

        // Redirect back to the login page with an error message
        $_SESSION['forgot_password'] = $message;
        $_SESSION['toastClass'] = $toastClass;
        header('Location: ../index.php');
        exit();
    }

    // check if the number of rows are more than 0 => email exists
    // if email is not in database:
    if ($checkemailStmt->num_rows < 1) {
        // do not say that email does not exist, but no mail sent
        $message = 'Email sent if user exists';
        $toastClass = '#007bff';

        // Log a failed login attempt (email not found)
        log_login(
            $conn,
            NULL,  // profile_id is NULL since login failed
            $email,
            $ip_address,
            0,  // success = 0 for failure
            'Login failed: Email not found'
        );
        // Redirect back to the login page with an error message
        $_SESSION['forgot_password'] = $message;
        $_SESSION['toastClass'] = $toastClass;
        header('Location: ../index.php');
        exit();
    }
    // email is in database:
    else {
        // use placeholders to protect against sql injection

        // Email exists, fetch the hashed password, profile_id, and is_verified from the database
        $sql = 'SELECT profile_id, is_verified 
                FROM profiles 
                WHERE email = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $profile = $result->fetch_assoc();
        if ((int) $profile['is_verified'] == 1) {
            // verified + correct password -> user can now log in
            $logincredentials = True;
        } else {
            // not verified
            // safe to display error message with specific reason as correct info is already entered
            $message = 'Please verify your email before changing your password.';
            $toastClass = '#dc3545';  // Danger color
            $_SESSION['forgot_password'] = $message;
            $_SESSION['toastClass'] = $toastClass;

            // Log a failed login attempt (user not verified)
            log_login(
                $conn,
                $profile['profile_id'],
                $email,
                $ip_address,
                0,  // success = 0 for failure
                'Password change failed: User not verified'
            );
        }
        $stmt->close();
    }

    if ($logincredentials === True) {
        // User is verified and correct email => $logincredentials === True

        // Set session variables for the logged-in user
        // Since login = success, add profile_id to session so we can access it on other pages.
        echo 'logincredentials are true';
        $_SESSION['profile_id'] = $profile['profile_id'];
        $profile_id = $_SESSION['profile_id'];

        $token = bin2hex(random_bytes(16));  // generate a token to use to change password
        echo "\n email is : $email";

        // insert then token into the database
        $sql = 'UPDATE profiles SET verify_token = ? WHERE email = ?';
        // conecting to the database
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $token, $email);
        $result = $stmt->execute();
        echo ' sql query passed';

        // if the insertion of the token into the database failed
        if ($result == False) {
            $message = 'Error: ' . $stmt->error;
            $_SESSION['forgot_password'] = $message;
            $toastClass = '#dc3545';  // Danger color
            $_SESSION['toastClass'] = $toastClass;
        }

        // Log initiation of password change
        log_login(
            $conn,
            $profile['profile_id'],
            $email,
            $ip_address,
            1,  // success = 1 for success
            'Password change initiated'
        );

        function send_mail_by_PHPMailer($to, $from, $subject, $body)
        {
            $mail = new PHPMailer();
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;  // shows the full SMTP conversation
            $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;  // or DEBUG_SERVER for more detail
            $mail->SMTPDebug = SMTP::DEBUG_OFF;  // Disable debug output for production
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();  // Use SMTP protocol
            $mail->Host = $_ENV['MAIL_HOST'] ?? die('MAIL_HOST not set in .env file');  // Specify  SMTP server
            $mail->SMTPAuth = true;  // Auth. SMTP
            $mail->Username = $_ENV['MAIL_USERNAME'] ?? die('MAIL_USERNAME not set in .env file');  // Mail who send by PHPMailer
            $mail->Password = $_ENV['MAIL_PASSWORD'] ?? die('MAIL_PASSWORD not set in .env file');  // your pass mail box
            $mail->SMTPSecure = 'ssl';  // Accept SSL
            $mail->Port = 465;  // port of your out server
            $mail->setFrom($from);  // Mail to send at
            $mail->addAddress($to);  // Add sender
            $mail->addReplyTo($from);  // Adress to reply
            $mail->isHTML(true);  // use HTML message
            $mail->Subject = $subject;
            $mail->Body = $body;

            // SEND
            if (!$mail->send()) {
                // error message if email failed to send
                // REMOVE FOR SECURITY RESONS??
                $message = 'Error: ' . $mail->ErrorInfo;
                $_SESSION['forgot_password'] = $message;

                $toastClass = '#dc3545';  // Danger color
                $_SESSION['toastClass'] = $toastClass;

                header('Location: ../index.php');
                exit();
            } else {
                // return true if message is send
                return true;
            }
        }

        /*
         * END send_mail_by_PHPMailer($to, $from, $subject, $message)
         * send a mail by PHPMailer method
         */

        // Use mail-function and actually sending mail to the user:
        $to = $email;
        $link = "http://localhost/change_password.php?token=$token";
        $from = 'Scribaadmin@gmail.com';
        $subject = 'Recover password for Scriba account';
        $body = 'Hi! <br /> Click here to recover your password for Scriba: ';
        $body .= "<a href=$link>Recover Password";

        send_mail_by_PHPMailer($to, $from, $subject, $body);

        // If registration is successful, log the successful registration
        // Get the profile_id of the newly registered user
        // Replace the next query with $conn->insert_id to get the last inserted ID = new profile ID
        $profile_id = $conn->insert_id;  // last inserted ID = new profile ID

        echo 'mail should have been sent';
        // Log email for successful initiation of password recovery
        log_login(
            $conn,
            $profile['profile_id'],
            $email,
            $ip_address,
            1,  // success = 1 for success
            'Password recovery initiated successfully'
        );

        $stmt->close();

        $message = 'A mail have been sent to the user, if they exist.';
        $_SESSION['forgot_password'] = $message;

        $toastClass = '#007bff';  // Primary color
        $_SESSION['toastClass'] = $toastClass;

        header('Location: ../index.php');
        exit();
    }
}
?>