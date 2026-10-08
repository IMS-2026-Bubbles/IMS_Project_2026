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

//RETRIEVE ERRORS/GOOD MESSAGES FROM ACTIONS/VERIFY_USER
if (isset($_SESSION['verify_user'])) {
    $message = $_SESSION['verify_user'];
    
    // Clear the messages from the session after retrieving them
    unset($_SESSION['verify_user']);
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
<!--    <link rel="stylesheet" href="assets/style.css">  We can define our own design in this -->
    <!-- To get premade buttons -->
   <!--  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> --> 
  <!--Deleted all css code in the INDEX.php page cause its going to be linked in style.css -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in account</title> <!-- This is what you see on the tab in safari/chrome -->
<!-- old CSS in this page just commented it so it doesnt show 
    <style> div.a {
        position: absolute;
        center;
    }  </style>
    <style> div.b {
        position: absolute;
        right: 25px;
    }  </style>

-->

<!-- Linked to bootstrap -->
 <!-- According to w3 schools:-->
  <!-- Bootstrap also gives you the ability to easily create responsive designs, Bootstrap is a free front-end framework for faster and easier web development  -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<!-- Linked to our stylesheet, linked is often used to link to the stylesheet but also other files  -->
 <link rel="stylesheet" href="assets/style.css">
</head>










<body>

<!-- Plan to have the nav bar in the nav element for all pages and maybe the cookies thing in the footer-->
 <!-- Main class is just for all main content in the page-->
   <!-- Can also make section elements but i think that all information we have is in the main?? we'll see-->

<!--  https://www.youtube.com/watch?v=pAl7TrV7gpg  this series of making a webpage by Dani Krossing-->


<!-- <nav> </nav> 
<main> </main> 
<footer></footer>  -->
   <!--The div tag means absolutely nothing more than strucutring the webpage up-->
      <!--Ratjer use the heather tag for the things at the top of the webpage and also the footer for content further down, but for fucntinality i can use div for everything :)-->
<!-- <div></div>  -->

   <!--Note for myself; can always have elements in the elements but i need to make sure i have a open and closing tag for everythign -->

    

  <!-- Want the title + logo on the left side, making a class for left side and further a class for right side for login forms etc-->
    
  <main class = "login_page " > <!-- Specify the main content of the document:-->
  <section class = "left_side_loginpage">
  <h1 class = "page_title" > Welcome to Scriba</h1>


    <img
                src="assets/logonobackground.png"
                alt="Scriba logo"
                class="loginpage_logo"
            >
      </section>


<!-- Divide right side-->
 <!-- Login + make new account-->

    <section class = "right_side_loginpage">
          <h1 class = "page_title" > Sign up or log in </h1>

       <!-- These are for error messages: code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/ -->
       <!-- If password wrong / email doesnt exist, this enables error text to show on the screen, or reutrning an error message -->
        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif;?>


<!-- Create login class-->
 <!-- for element belongs to both classes and gets css style from both -->
    <form class="scriba_card login_form" action = "actions/login.php" method="POST">
        <!-- think email address could be required https://www.w3schools.com/jsref/prop_email_required.asp on both -->
    <label>Email address:</label> <input class = "scriba_input" type="email" name="email" required>

   
    <label>Password:</label> <input class = "scriba_input" type="password" name="password" required>
    <input type="submit" class="login_button" name="login" value="Log in"> 

    <!-- get directed to register user page if the person wants to make an account  -->
    </form>


 <div class="scriba_card register_user">
    <p class = "text " > Do you want to make an account?</p>
     <a class=" login_button" href="register_user.php"> Register new user
    </a>
    </div> <!-- stänger register_user -->
        </section> <!-- stänger right_side_loginpage -->

         <!-- Apply global buttons in css and reuse everything -->


 



</main>


        








</body>
</html>

















<!--
        <form action="actions/login.php" method= "POST">   change action so you end up somewhere! 

             forms for all free text info that is needed
            required so that the field is mandatory before registering 
            <label for="email">Email adress</label><br>
            <input type="email" class="" name="email" required ><br>

            <label for="password">Password</label><br>
            <input type="password" class="" name="password" minlength="8"  required><br><br>
            <input type="submit" class="btn btn-dark rounded-pill" name="login" value="Log in"><br><br>
    
        </div>
    </form>
    
</body>
</html>

-->
