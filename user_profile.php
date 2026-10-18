<?php
// User profile page

// Arrive from:
// actions/login.php (if user is not a scriba admin and does not belong to a company)
// inside Scriba via navigation bar
// Action:
// Display user profile information,
// allow user to join a lab or company,
// and delete account
// Redirect to:
// navigation bar options
// actions/join_company_lab.php (join company or lab forms)
// actions/logout.php (delete account -> logout and redirect to index.php)

// preparations
// starts the session
require_once 'session/init.php';
// check user is logged in
require_once 'session/check_user_logged_in.php';
require_once 'cookies/cookie.php';  // cookies
// Connect to database
require_once 'database/db.php';

// Get the profile ID from the session
$profile_id = $_SESSION['profile_id'];

// get all info about a user, this will be displayed later on
// retrieve names + email + company + labgroup
$stmt = $conn->prepare(  // statement
    'SELECT profiles.email, profiles.first_name, profiles.last_name
    FROM profiles 
    WHERE profiles.profile_id = ?'
);

// binds based on profile_id
// ? is a placeholder, protects against sql injection
$stmt->bind_param('i', $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// selecting points for the user
$stmt = $conn->prepare(
    'SELECT scriba_points FROM profile_points WHERE profile_id = ?'
);
$stmt->bind_param('i', $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$points = $result->fetch_assoc();
$stmt->close();

// selecting company
$stmt = $conn->prepare(
    'SELECT companies.name, company_members.role
     FROM companies 
     JOIN company_members 
     ON companies.company_id = company_members.company_id 
     WHERE company_members.profile_id = ?'
);
$stmt->bind_param('i', $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();
$stmt->close();

// lab aswell
$stmt = $conn->prepare(
    'SELECT labs.name FROM labs JOIN lab_members ON labs.lab_id = lab_members.lab_id WHERE lab_members.profile_id = ?'
);
$stmt->bind_param('i', $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$lab = $result->fetch_assoc();
$stmt->close();

?>


<!DOCTYPE html>
<html lang="en">

 <!-- The head tag has information we give the browser and that we dont see at the website -->

<head>

 <!-- Theese are called empty elements and they dont need a closing tag, they dont have a closing tag-->

 <!-- -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- loads a CSS library, bootstrap -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

<!-- the icons from official open source SVG https://www.jsdelivr.com/package/npm/bootstrap-icons -->
<link href="
https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.2/font/bootstrap-icons.min.css
" rel="stylesheet">
    <!-- <title>Document</title> -->


     <!-- Link HTML elements that needs attributes, this is a stylesheet type and a path to the file we link to, the css.style-->
    <link rel="stylesheet" href="assets/style.css">
    <!-- i want nav bar here -->
     <!-- moved navbar to body : )  -->
    

    <!-- <body style="background-image: url('assets/website_background.jpg');"> -->

    <title>User Profile</title>





</head>


<!-- Puts visuable elemnts inside the body tag to crete content in this body -->
 <!-- When creating a website, everything is really inside boxes.. boxes inside of boxes etc -->
  <!-- Our body tag is also a box-->

<body>
<!-- readfile() - reads a file and writes it to the output buffer -->


<header>  <?php include 'includes/navbar.php'; ?>  </header> 


<main class="profile_container"> 

<!-- left side of the page  -->
 <!-- using section to make it easier to read the page and the code instead of using div everywhere -->
<section class="profile_left">
    <div class="profile_card">
    <!-- I added so that the display name is showed up here instead / Tilda -->
    <h2>Welcome  <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> </h2>
    <p>You have possibilities to overview your profile, see which company and lab gorup you belong to, and follow your Scriba points. Log it or it didn't happen! </p>

</div>


<div class = "profile_card" >
<h2>Points</h2>
    <!-- boostrap icons from https://icons.getbootstrap.com and scriba colour -->

<div class="text-center"> <i class="bi bi-flask-florence-fill" style="font-size:200px; color:var(--scriba-lightblue);"></i> </div>
<p>
        You have
        <?php echo htmlspecialchars($points['scriba_points']); ?> points. Your earn points by saving changes, logging in and completing experiments (10 points each) and projects (100 points each)
        
    </p>



    
</div>

</section>


<!-- right side of page  -->
<section class="profile_right">
    <div class="profile_card">
        <!-- amp means & in HTML -->
    <h2>Company &amp; Lab Group</h2>

    

<p>
        Lab:
        <?php
        // if you don't belong to a company, have the possibility to join one
        // tried ternary operator https://www.geeksforgeeks.org/php/php-ternary-operator/
        // just makes code simpler
        echo empty($lab['name']) ? 'Belongs to no lab group' : htmlspecialchars($lab['name']);
        ?>
        <!-- where ? = is this is true, do this! And : = otherwise, do that  -->  
       
 </p>      
    

<p>   Company:
        <?php
        if (empty($company['name'])) {
            ?> No company <?php
        } elseif ($company['role'] == 'pending') {
            ?>
            You have been invited to join <?php echo htmlspecialchars($company['name']); ?> 
            <form action="actions/join_company_lab.php" method="POST"> 
            <!-- Accept button -->
            <button class = "admin_button" type="submit" name="submit_type" value="accept">Accept</button>

            <!-- Decline Button -->
            <button class "admin_button" type="submit" name="submit_type" value="decline">Decline</button>
            </form> 
            <?php
        } else {
            // if user has a company it just shows
            echo htmlspecialchars($company['name']);
        }
        ?>
       
        

</p>
 <!-- closing company  -->
</div> 



<div class="profile_card">
<h2>Email</h2>
<p>Your Email adress: <?php echo htmlspecialchars($user['email']); ?>
    </p>
  
</div>







<!-- suns project card  -->
<div class="profile_card">
    <h2>Delete Account</h2>
    <p>By deleting your account the personal information is removed from Scriba. Your projects and experiments are not deleted but are no longer linked to your name. This step is irreversible.   </p>
    <p>You can choose to delete your account here: </p>
<!--  this is a form, thought abou making a button but it is better to send a form to PHP that answers with f (isset($_POST["delete_account"])) { --> 
   <form method="POST" action="actions/delete_profile.php" onsubmit="return confirm('Are you sure you want to delete your account?');">
    <button type="submit" class = "btn-81"  name="delete_account">Delete account</button>
</form>


<?php
if (!empty($delete_account_message)) {
    echo '<br>';
    echo htmlspecialchars($delete_account_message);
    echo '<br>';
}
?>
</div>

</section>
</main>
</body>







<!-- I SUGGEST THAT WE REMOVE THIS PART AS THE PERSONAL INFORMATION IS DISPLAYED ABOVE -->
<!-- not a button now but in the future a button + some kind of new page --> 
<!--<div class="user"> 
<h2>Information Scriba has about me</h2> -->
<!-- <p>Click here to see what information scriba has about you </p> -->


<!-- JavaScript Operators are used to assign values, compare values, perform arithmetic operations, and much more, did never use..  -->






    

</html>


