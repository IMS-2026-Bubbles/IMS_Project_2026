<?php
// Create profile action

// Arrive from:
// register_user.php (register new user form submission)
// Action:
// Register a new profile in the database,
// destroy the session, and set a success/failure message
// Redirect to:
// index.php (with message indicating success or failure)

// include to send the varification mail
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

require_once '../session/init.php';  // Start the session and initialize session variables
// require_once '../session/check_user_logged_in.php'; // Check if the user is logged in
require_once '../database/db.php';

// require logging functions
require_once '../includes/log_activity.php';  // Log login attempts
// require ip address functions
require_once '../includes/fetch_ip_address.php';  // provides $ip_address and $ip_address_proxy

if (isset($_POST['register'])) {
    // fetch data from POST request
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password1'];
    $agreed_to_tos = (int) ($_POST['agreed_to_tos'] ?? 0);  // checkbox for Terms of Service and GDPR agreement

    // Rate limit registration attempts to prevent abuse
    // If 5 failed registration attempts from the same IP address within 15 minutes (running), lock out for max 15 minutes
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
    $failed_attempts = $result_failed_attempts->fetch_assoc()['failed_attempts'];

    if ($failed_attempts >= 5) {
        $message = 'Too many failed registration attempts. Please try again later.';
        $_SESSION['register_user_message'] = $message;

        $toastClass = '#dc3545';  // Danger color
        $_SESSION['register_user_toastClass'] = $toastClass;

        header('Location: ../register_user.php');
        exit();
    }

    // code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/
    // Check if email already exists
    $checkemailStmt = $conn->prepare('SELECT email FROM profiles WHERE email = ?');
    $checkemailStmt->bind_param('s', $email);
    $checkemailStmt->execute();
    $checkemailStmt->store_result();
    error_log("Checking email [$email], num_rows = " . $checkemailStmt->num_rows);

    // check if the number of rows are more than 0 => email exists
    if ($checkemailStmt->num_rows > 0) {
        $message = 'email ID already exists';
        $_SESSION['register_user_message'] = $message;

        $toastClass = '#007bff';  // Primary color
        $_SESSION['register_user_toastClass'] = $toastClass;

        // Log failed registration due to existing email
        log_login(
            $conn,
            NULL,  // profile_id is NULL since registration failed
            $email,
            $ip_address,
            0,  // success = 0 for failure
            'Registration failed: Email already exists'
        );

        header('Location: ../register_user.php');
        exit();
    } else {
        // use placeholders to protect against sql injection
        // generate a 32-character token to verify the user
        $token = bin2hex(random_bytes(16));

        // insering the new user to the database
        $sql = 'INSERT INTO profiles (email, first_name, last_name, password, agreed_to_tos, verify_token) VALUES (?, ?, ?, ?, ?, ?)';

        // conecting to the database
        $stmt = $conn->prepare($sql);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bind_param('ssssis', $email, $first_name, $last_name, $hashedPassword, $agreed_to_tos, $token);
        $result = $stmt->execute();

        if ($result == False) {
            $message = 'Error: ' . $stmt->error;
            $_SESSION['register_user_message'] = $message;
            $toastClass = '#dc3545';  // Danger color
            $_SESSION['register_user_toastClass'] = $toastClass;

            // Log "login" for failed registration due to database error
            log_login(
                $conn,
                NULL,  // profile_id is NULL since registration failed
                $email,
                $ip_address,
                0,  // success = 0 for failure
                'Registration failed: Database error'
            );

            header('Location: ../register_user.php');
            exit();
        }

        // Defining a function send mail to the user via Scriba-Gmail
        function send_mail_by_PHPMailer($to, $from, $subject, $message)
        {
            $mail = new PHPMailer();
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;  // shows the full SMTP conversation
            $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;  // or DEBUG_SERVER for more detail
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();  // Use SMTP protocol
            $mail->Host = 'smtp.gmail.com';  // Specify  SMTP server
            $mail->SMTPAuth = true;  // Auth. SMTP
            $mail->Username = 'scribaadmin@gmail.com';  // Mail who send by PHPMailer
            $mail->Password = 'fpqv wzef bpjq jipz';  // your pass mail box
            $mail->SMTPSecure = 'ssl';  // Accept SSL
            $mail->Port = 465;  // port of your out server
            $mail->setFrom($from);  // Mail to send at
            $mail->addAddress($to);  // Add sender
            $mail->addReplyTo($from);  // Adress to reply
            $mail->isHTML(true);  // use HTML message
            $mail->Subject = $subject;
            $mail->Body = $message;

            // SEND
            if (!$mail->send()) {
                // error message if email failed to send
                $message = 'Error: ' . $mail->ErrorInfo;
                $_SESSION['register_user_message'] = $message;

                $toastClass = '#dc3545';  // Danger color
                $_SESSION['register_user_toastClass'] = $toastClass;
                exit;
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
        $link = "http://localhost/actions/verify_user.php?token=$token";
        $from = 'Scribaadmin@gmail.com';
        $subject = 'Verify your Scriba account';
        $message = 'Hi! Verify your Scriba account by clicking the following link: ';
        $message .= "<a href=$link >Verify my scriba account";

        send_mail_by_PHPMailer($to, $from, $subject, $message);

        // If registration is successful, log the successful registration
        // Get the profile_id of the newly registered user
        // Replace the next query with $conn->insert_id to get the last inserted ID = new profile ID
        // $sql_get_profile_id = "SELECT profile_id FROM profiles WHERE email = ?";
        // $stmt_get_profile_id = $conn->prepare($sql_get_profile_id);
        // $stmt_get_profile_id->bind_param("s", $email);
        // $stmt_get_profile_id->execute();
        // $result = $stmt_get_profile_id->get_result();
        // $profile = $result->fetch_assoc();
        // $profile_id = $profile['profile_id'];
        $profile_id = $conn->insert_id;  // last inserted ID = new profile ID

        // Log "login" for successful registration
        log_login(
            $conn,
            $profile_id,
            $email,
            $ip_address,
            1,  // success = 1 for success
            'Registration successful'
        );

        $stmt->close();
    }

    $checkemailStmt->close();

    // Directing the user back to the index page
    // user should get email, where they have to click the link and verify
    if (isset($result) && $result) {
        $_SESSION['register_user_message'] = $message;
        $_SESSION['register_user_toastClass'] = $toastClass;
        header('Location: ../index.php');
        // session_destroy(); // don't destroy session to allow the message to be displayed on the index.php page
        exit();
    }
    include '../database/close_db.php';
}

?>