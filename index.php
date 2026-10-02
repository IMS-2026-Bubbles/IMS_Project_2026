<?php
// index/Front page/login page

// Arrive from: 
    // outside Scriba (first page you see when you go to the site)
    // actions/login.php (invalid login credentials)
    // actions/create_profile.php (after registering a new user)
// Action: Display login form and any error messages from previous login attempts
// Redirect to: 
    // actions/login.php (on form submission)
    // register_user.php (if user clicks "Register new user" button)

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require_once "session/init.php"; // Start the session and initialize session variables
// No check for user logged in here, front page
require_once 'database/db.php';

// Retrieve login error messages from the session if they exist
if (isset($_SESSION['login_error'])) {
    $message = $_SESSION['login_error'];
    
    // Clear the messages from the session after retrieving them
    unset($_SESSION['login_error']);
} else {
    $message = "";
}

if (isset($_SESSION['toastClass'])) {
    $toastClass = $_SESSION['toastClass'];

    // Clear the toast class from the session after retrieving it
    unset($_SESSION['toastClass']);
} else {
    $toastClass = "";
}
 
?>


<!DOCTYPE html>
<html lang="en">
    <link rel="stylesheet" href="assets/style.css"> <!-- We can define our own design in this -->
    <!-- To get premade buttons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    
    <style>
        body{
            padding: 70px ; /* Adds 50px of space around the inside of the web browser*/
            min-height: 100vh;
            background: linear-gradient(120deg, #7794b6, #d4f6fd);
            color: #263c55;
        }
    </style>
    

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in account</title> <!-- This is what you see on the tab in safari/chrome -->
    <style> div.a {
        position: absolute;
        center;
    }  </style>
    <style> div.b {
        position: absolute;
        right: 25px;
    }  </style>
</head>
<body>
    <h1>Welcome to Scriba!</h1>
        <div class="b"> 
        <h3>Register new user</h3>

        <a href="register_user.php" class="btn btn-dark rounded-pill"> Register new user</a>
        <br><br>
        
        <h3>Log in</h3>
        <!-- Create the action-->

            <!-- These are for error messages: code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/ -->
        <?php if ($message): ?>
            <div style="background-color: <?php echo htmlspecialchars($toastClass); ?>; 
                        color: white; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif;?>

        <form action="actions/login.php" method= "POST">  <!-- change action so you end up somewhere! -->

            <!-- forms for all free text info that is needed-->
            <!-- required so that the field is mandatory before registering -->
            <label for="email">Email adress</label><br>
            <input type="email" class="" name="email" required ><br>

            <label for="password">Password</label><br>
            <input type="password" class="" name="password" minlength="8"  required><br><br>
            <input type="submit" class="btn btn-dark rounded-pill" name="login" value="Log in"><br><br>
    
        </div>
    </form>
    
</body>
</html>


