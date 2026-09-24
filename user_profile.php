<?php

echo " This is the user profile page :)";
?>

<?php
// starts the session? needed i dont know 
require_once 'Session/init.php';
//check youre logged in 
require_once 'Session/check_user_logged_in.php';
//kopplad till databasen? 
require_once 'Database_related/db.php';

$user_id = $_SESSION['user_id'];
echo $user_id

?>


<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- loads a CSS library, bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Document</title>

    <!-- i want nav bar here -->
    <?php include "includes/navbar.php";?>

    <body style="background-image: url('assets/website_background.jpg');">

<title>User Page</title>


<style>

.welcome {
  background-color: lightblue;
  color: white;
  border: 4px solid white;
  margin-top: 50px;
  margin-bottom: 50px;
  margin-right: 1200px;
  margin-left: 70px;
  padding: 10px; 
}


.user {
  background-color: lightblue;
  color: white;
  border: 4px solid white;
  margin-top: 50px;
  margin-bottom: 50px;
  margin-right: 50px;
  margin-left: 1200px;
  padding: 10px;
  
}

/* Gjorde alla till buttons.. får fixa det sen : )  */
.user {
  background-color: #8bc1e3;
  color: white;
  font-size: 15px;
  padding: 12px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}



}
</head>








</style>
</head>
<body>

<!-- Here is the php scirpt where i link to a bunch of diff -->




<!-- readfile() - reads a file and writes it to the output buffer -->














<!-- Här e mina bästa fina design buttons-->

<div class="welcome">
<h2>Welcome to your user page</h2>
<p>You have possibilities to overwiev your profile, add experiments and wiev your points. Log it or it didnt happen! </p>
</div>

<div class="user">
<h2>Company & Department</h2>
<p>What company and department you belong to</p>
</div> 

<div class="user">
<h2>Email</h2>
<p>Your Email adress:</p>
</div>

<div class="user">
<h2>Points</h2>
<p>You have _ points</p>
</div>

<div class="user">
<h2>Display name</h2>
<p>Your display name is: </p>
</div>

<div class="user">
<h2>Delete account</h2>
<p>You can choose to delete your account here </p>
</div>

<div class="user">
<h2>Information Scriba has about me</h2>
<p>Click here to see what information scriba has about you </p>
</div>








    
</body>
</html>


