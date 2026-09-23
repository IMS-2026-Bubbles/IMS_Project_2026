<?php

echo " This is the user profile page :)";
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
    <?php include "functional_php/navbar.php";?>

    <body style="background-image: url('website_background.jpg');">

<title>User Page</title>

<style>
.user {
  background-color: lightblue;
  color: white;
  border: 2px solid white;
  margin: 20px;
  padding: 20px;
}

</head>




</style>
</head>
<body>

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

    
</body>
</html>


