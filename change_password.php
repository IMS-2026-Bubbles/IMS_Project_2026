<?php
// Page for entering new passwords

// Arrive from:
// link from email, cannot enter this page without valid token in URL

// Action: check that the token in URL is valid
// Action: send in form with passwords to database

// Redirect to:
// actions/check_password.php (on form submission)

//showing all error messages on the page
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require_once 'session/init.php';  // Start the session and initialize session variables
// No check for user logged in here
require_once 'cookies/cookie.php'; //Include cookies
require_once 'database/db.php'; //Include database
require_once 'includes/log_activity.php';  // Include the log_activity function

// Retrieve login error messages from the session if they exist
$message = '';
// Set the color of the error messages
$toastClass = '';

if (isset($_SESSION['change_password'])) {
    $message = $_SESSION['change_password'];

    // Clear the messages from the session after retrieving them
    unset($_SESSION['change_password']);
}

if (isset($_SESSION['toastClass'])) {
    $toastClass = $_SESSION['toastClass'];

    // Clear the toast class from the session after retrieving it
    unset($_SESSION['toastClass']);
}

$token = $_GET['token'] ?? '';  // set to empty to prevent possible undefined variable error
$link = "actions/check_password.php?token=$token";


?>
<!DOCTYPE html>

<html lang="en">
<!--    <link rel="stylesheet" href="assets/style.css">  We can define our own design in this -->
    <!-- To get premade buttons -->
   <!--  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> --> 
  <!--Deleted all css code in the INDEX.php page cause its going to be linked in style.css -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email for your account</title> <!-- This is what you see on the tab in safari/chrome -->
<!-- old CSS in this page just commented it so it doesnt show 
    <style> div.a {
        position: absolute;
        center;
    }  </style>
    <style> div.b {
        position: absolute;
        right: 25px;
    }  </style>
<!-- Linked to bootstrap -->
 <!-- According to w3 schools:-->
  <!-- Bootstrap also gives you the ability to easily create responsive designs, Bootstrap is a free front-end framework for faster and easier web development  -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<!-- Linked to our stylesheet, linked is often used to link to the stylesheet but also other files  -->
 <link rel="stylesheet" href="assets/style.css">
    <p><a href="http://localhost/index.php"> ↩ Go back</a></p> 
</head>
<main class = "change_password " >
<h1 class = "page_title" > Change password for your account</h1>
<body>
    <form class="scriba_card change_password" action="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" method="POST" onsubmit ="return checkPassword(this)">
            <!-- think email address could be required https://www.w3schools.com/jsref/prop_email_required.asp on both -->
        <label for="password">Password</label>
        <input type="password" class="scriba_input" name="password1" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" required>
        
        <label for="password2">Repeat Password</label>
        <input type="password" class="scriba_input" name="password2" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" required>
        <input type="submit" class="login_button" name="change password" value="change password">


        <!-- https://www.geeksforgeeks.org/javascript/Password-matching-using-javascript/ -->
        <script>
            // Function to check Whether both Passwords is same or not.
            function checkPassword(form) {
                password1 = form.password1.value;
                password2 = form.password2.value;

                // If Not same return False.    
                if (password1 != password2) {
                    // This pops up and the request is not submitted
                    alert("\nPassword did not match: Please try again");
                    return false;
                }
            }
        </script>
    </form>
</main>

</body>
</html>




