<?php
// Register user page

// Arrive from:
// index.php (register new user form)
// Action:
// Register a new user in the database
// Redirect to:
// index.php (return to login button)
// actions/create_profile.php (register new user form submission)

ini_set('display_errors', true);
ini_set('log_errors', true);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'session/init.php';  // Start the session and initialize session variables
// No check for user logged in here, registration page
require_once 'database/db.php';

if (isset($_SESSION['register_user_message'])) {
    $message = $_SESSION['register_user_message'];
    unset($_SESSION['register_user_message']);
} else {
    $message = '';
}

if (isset($_SESSION['register_user_toastClass'])) {
    $toastClass = $_SESSION['register_user_toastClass'];
    unset($_SESSION['register_user_toastClass']);
} else {
    $toastClass = '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register account</title> <!-- This is what you see on the tab in safari/chrome -->
    <link rel="stylesheet" href="assets/style.css"> <!-- We can define our own design in this -->
    <!-- To get premade buttons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">



</head>







<body>
     <main class="register_user_page"> <!-- makes the form centered from css class  -->
     <h1 class = "page_title" > Welcome to Scriba!</h1>
  


    <!-- These are for error messages: code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/ -->
    <?php if ($message): ?>
        <p class="error_message"><?php echo htmlspecialchars($message); ?></p>
  
    <?php endif; ?>


    <!-- put all elemnts in one box..  -->

    <div class = scriba_card >
          <h2 style = "h2"> Add the following information to create an account</h2>
    
     <!-- Create the action + call function to check if Passwords match-->
    <form class = "register_form" action ="actions/create_profile.php" method= "POST" onsubmit ="return checkPassword(this)">  <!-- change action so you end up somewhere! -->

        <!-- forms for all free text info that is needed-->
        <!-- required so that the field is mandatory before registering -->
        <label for="first_name">First name</label>
        <input type="text" class="scriba_input" name="first_name" required>

        <label for="last_name">Last name</label>
        <input type="text" class="scriba_input" name="last_name" required>

        <label for="email">Email adress</label>
        <input type="email" class="scriba_input" name="email" required> <!-- @ is needed -->

        <!-- setting type as Password makes characters hidden + supports Password control -->
        <label for="password">Password</label>
        <input type="password" class="scriba_input" name="password1" minlength= "8" required>  <!-- must use 8 characters -->

        <label for="password2">Repeat Password</label>
        <input type="password" class="scriba_input" name="password2" minlength= "8" required>

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



    
        <!-- GDPR button -->
        <label class="switch">
            <input type="checkbox" for="agreed_to_tos" name="agreed_to_tos" value="1" required>
            <span class="slider round" name=agreed_to_tos></span>
            <!-- create hyperlink (<a>) so you can view GDPR rules-->
            <!-- # so that you don't change page -->


        I accept the <a class = "" href=# onclick="return GDPR();">Terms of Service and GDPR policy</a> 
        </label>
        <a class = "" href="docs/terms_of_service.pdf?file=terms_of_service" download=>Download Terms of Service</a>

        <script>
        function GDPR() {
            alert("Write a fancy text about GDPR usage here")
            return false;;
        }
        </script>

        <input type="submit" class="login_button" name="register" value="Register">
    </form>
  


    </main>
    
</body>
</html>



