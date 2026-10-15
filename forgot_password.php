<?php
// Page which starts the recovering password-process

// Arrive from:
// homepage (index.php)

// Action: send in form with email to check if valid

// Redirect to:
// actions/check_email.php (on form submission)

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

if (isset($_SESSION['forgot_password'])) {
    $message = $_SESSION['forgot_password'];

    // Clear the messages from the session after retrieving them
    unset($_SESSION['forgot_password']);
}

if (isset($_SESSION['toastClass'])) {
    $toastClass = $_SESSION['toastClass'];

    // Clear the toast class from the session after retrieving it
    unset($_SESSION['toastClass']);
}


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
<main class = "forgot_password " >
<h1 class = "page_title" > Enter the Email for your account</h1>
<body>
    <form class="scriba_card forgot_password" action = "actions/check_email.php" method="POST">
            <!-- think email address could be required https://www.w3schools.com/jsref/prop_email_required.asp on both -->
        <label>Email address:</label> <input class = "scriba_input" type="email" name="email" required><br><br>

        <input type="submit" class="login_button" name="forgot_password" value="forgot password"> 

        <!-- get directed to register user page if the person wants to make an account  -->
    </form>
</main>

</body>
</html>




