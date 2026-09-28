


<?php

//startar sessionen och kopplar till min exempel profil
session_start();
$_SESSION["profile_id"] = 1;
include "database/db.php";
$profile_ID = 1;



//email knapp 
/*
$stmt = $conn->prepare("SELECT email FROM profiles WHERE profile_id = ?");
$stmt->bind_param("i", $profile_ID);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

//gör samma för att få för och efetrnamn i profilsidan 

$stmt = $conn->prepare("SELECT first_name, last_name FROM profiles WHERE profile_id = ?");
$stmt->bind_param("i", $profile_ID);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

*/

//selecting everything from profiles 
//PHP MySQL prepared statements from W3 schools 

$stmt = $conn->prepare( //statement
    "SELECT * FROM profiles WHERE profile_id = ?"
);


//gets the profile form scriba database 
$stmt->bind_param("i", $profile_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();









//selecting points for the user 

$stmt = $conn->prepare(
    "SELECT scriba_points FROM profile_points WHERE profile_id = ?"
);

//gets the profile form scriba database 
$stmt->bind_param("i", $profile_ID);
$stmt->execute();

$result = $stmt->get_result();
$points = $result->fetch_assoc();
//






?>


<!DOCTYPE html>
<html lang="en">
<head>

<!--tilda la in detta --> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- loads a CSS library, bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- <title>Document</title> -->

    <!-- i want nav bar here -->
    <?php// include "includes/navbar.php";?>

    <!-- <body style="background-image: url('assets/website_background.jpg');"> -->

    <title>User Page</title>


    <style>

    .welcome {
    background-color: lightblue;
    color: white;
    border: 4px solid white;
    margin: 50px 45% 50px 5%; /* top, right, bottom, left */
    /* margin-top: 50px;
    margin-bottom: 50px;
    margin-right: 1200px;
    margin-left: 70px; */
    padding: 10px; 
    }


    /* .user {
    background-color: lightblue;
    color: white;
    border: 4px solid white;
    margin: 50px 40% 50px 70%;
    margin-top: 50px;
    margin-bottom: 50px;
    margin-right: 50px;
    margin-left: 1200px;
    padding: 10px;
    } */


    /* Gjorde alla till buttons.. får fixa det sen : )  */
    .user {
    background-color: #8bc1e3;
    color: white;
    font-size: 15px;
    padding: 12px;
    border: none;
    margin: 50px 45% 50px 5%; /* top, right, bottom, left */
    border-radius: 5px;
    cursor: pointer;
    }



    </style>
</head>
<body style="background-image: url('assets/website_background.jpg');">

<!-- i want nav bar here -->
    <?php 
    include "includes/navbar.php";
    ?>

<!-- Here is the php scirpt where i link to a bunch of diff -->




<!-- readfile() - reads a file and writes it to the output buffer -->














<!-- Här e mina bästa fina design buttons-->

<div class="welcome">
<h2>Welcome to your user page</h2>
<p>You have possibilities to overwiev your profile, add experiments and wiev your points. Log it or it didnt happen! </p>
</div>

<div class="user">
<h2>Company & Department</h2>
<p>What company and department you belong to
<p>
        Company:
        <?php echo htmlspecialchars($company["name"]); ?>

        Lab: 
        <?php echo htmlspecialchars($lab["name"]); ?>
    </p>
    </p>
</div> 

<div class="user">
<h2>Email</h2>
<p>Your Email adress:
<?php echo htmlspecialchars($user["email"]); ?>
    </p>
  
</div>


<div class="user">
<h2>Points</h2>
<p>
        You have
        <?php echo htmlspecialchars($points["scriba_points"]); ?>
        points
    </p>
</div>


<div class="user">
<h2>Display name</h2>
<p>Your display name is:
<?php echo htmlspecialchars($user["first_name"] . " " . $user["last_name"]); ?>
</p>
</div>


<div class="user">
<h2>Delete account</h2>
<p>You can choose to delete your account here </p>
<!--  this is a form, thought abou making a button but it is better to send a form to PHP that answers with f (isset($_POST["delete_account"])) { --> 
   <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account?');">
    <button type="submit" name="delete_account">Delete account</button>
</form>

</div>

<!-- not a button now but in the future a button + some kind of new page --> 
<div class="user">
<h2>Information Scriba has about me</h2>
<p>Click here to see what information scriba has about you </p>
 <p>Email: <?php echo htmlspecialchars($user["email"]); ?></p>
    <p>First name: <?php echo htmlspecialchars($user["first_name"]); ?></p>
    <p>Last name: <?php echo htmlspecialchars($user["last_name"]); ?></p>
    <p>Saved changes: <?php echo htmlspecialchars($user["saved_changes"]); ?></p>
    <p>Last login: <?php echo htmlspecialchars($user["last_login_at"]); ?></p>
    <p>Streak: <?php echo htmlspecialchars($user["streak"]); ?></p>
</div>


<!-- JavaScript Operators are used to assign values, compare values, perform arithmetic operations, and much more. -->

<script>
function confirmDelete() {
    if (confirm("Are you sure?")) {
        alert("You clicked Yes, your account is now deleted");
    }
}
</script>





    
</body>
</html>


