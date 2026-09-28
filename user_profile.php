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
    // actions/logout.php (after deleting account)


// starts the session
require_once 'session/init.php';
//check user is logged in 
require_once 'session/check_user_logged_in.php';
// Connect to database
require_once 'database/db.php';

// Get the profile ID from the session
$profile_id = $_SESSION['profile_id'];
//echo "This is the user profile page for profile ID: " . htmlspecialchars($profile_id) . "<br>";

// Join lab action message
if (isset($_SESSION['error_join_lab'])) {
    $error_join_lab = $_SESSION['error_join_lab'];
    unset($_SESSION['error_join_lab']);
} else {
    $error_join_lab = "";
}

// Join company action message
if (isset($_SESSION['error_join_company'])) {
    $error_join_company = $_SESSION['error_join_company'];
    unset($_SESSION['error_join_company']);
} else {
    $error_join_company = "";
}

// Delete account action message
if (isset($_SESSION['delete_account_message'])) {
    $delete_account_message = $_SESSION['delete_account_message'];
    unset($_SESSION['delete_account_message']);
} else {
    $delete_account_message = "";
}

// -------- handle POST methods -------- 

// join lab
// if(isset($_POST['join_lab']))
//     {
//     # fetch data from POST request
//     $lab_code = $_POST['unique_code_lab'];

//     # check so that this code actually exists:
//     $stmt = $conn->prepare(
//         "SELECT lab_id 
//         FROM lab_members 
//         WHERE lab_id = ?"
//         );
//     $stmt->bind_param("s", $lab_code);
//     $stmt->execute();
//     $exists = $stmt->get_result()->fetch_assoc();
//     $stmt->close();

//     # if the lab doesn't exist
//     if (!$exists){
//         $error_join_lab = "The lab group you want to join doesn't exist. Make sure your code is correct.";
//     }
//     else{
//         # use placeholders to protect against sql injection
//         $sql = "INSERT INTO lab_members (lab_id, profile_id, role) VALUES (?, ?, 'member')";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("si", $lab_code, $profile_id);
//         $result = $stmt->execute();
//         $stmt->close();
//         header("Location: user_profile.php");
//         exit;}
//     }



// if(isset($_POST['join_company']))
//     {
//     # fetch data from POST request
//     $company_code = $_POST['unique_code'];

//     # check so that this code actually exists:
//     $stmt = $conn->prepare(
//     "SELECT company_id FROM companies WHERE company_id = ?");
//     $stmt->bind_param("s", $company_code);
//     $stmt->execute();
//     $exists = $stmt->get_result()->fetch_assoc();
//     $stmt->close();

//     # if the company doesn't exist
//     if (!$exists){
//         $error_join_company = "The company you want to join doesn't exist. Make sure your code is correct.";
//     }
//     else{
//         # use placeholders to protect against sql injection
//         $sql = "INSERT INTO company_members (company_id, profile_id, role) VALUES (?, ?, 'member')";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("si", $company_code, $profile_id);
//         $result = $stmt->execute();
//         $stmt->close();
//         header("Location: user_profile.php");   // use your real filename
//         exit;
//         }
//     }





# get all info about a user, this will be displayed later on
# retrieve names + email + company + labgroup
$stmt = $conn->prepare( //statement
    "SELECT profiles.email, profiles.first_name, profiles.last_name
    FROM profiles 
    WHERE profiles.profile_id = ?"
);

// binds based on profile_id
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();  
$user = $result->fetch_assoc(); 
$stmt->close();




//selecting points for the user 
$stmt = $conn->prepare(
    "SELECT scriba_points FROM profile_points WHERE profile_id = ?"
);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$points = $result->fetch_assoc();
$stmt->close();




//selecting company
$stmt = $conn->prepare(
    "SELECT companies.name FROM companies JOIN company_members ON companies.company_id = company_members.company_id WHERE company_members.profile_id = ?"
);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();
$stmt->close();


// lab aswell 
$stmt = $conn->prepare(
    "SELECT labs.name FROM labs JOIN lab_members ON labs.lab_id = lab_members.lab_id WHERE lab_members.profile_id = ?"
);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$lab = $result->fetch_assoc();
$stmt->close();






// //delete user account with SQL DELETE statement
// //i know i should use the is_deleted from profiles but rn its what it is 
// // UPDATE profiles SET is_delted = TRUE smth like this 
// if (isset($_POST["delete_account"])) {

//     $stmt = $conn->prepare("DELETE FROM profiles WHERE profile_id = ?"); 
//     $stmt->bind_param("i", $profile_id);

//     if ($stmt->execute()) {
//         echo "Record deleted successfully";
//     } else {
//         echo "Error deleting user profile: " . $conn->error;
//     }
// }




?>


<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- loads a CSS library, bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- <title>Document</title> -->

    <!-- i want nav bar here -->
    <?php include "includes/navbar.php";?>

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
    /* cursor: pointer; */ /* FOR CORNELIA: i removed this as a button/ Tilda */
    }



    </style>
</head>
<body style="background-image: url('assets/website_background.jpg');">


<!-- readfile() - reads a file and writes it to the output buffer -->


<!-- Här e mina bästa fina design buttons-->

<div class="welcome">
    <!-- I added so that the display name is showed up here instead / Tilda -->
<h2>Welcome to your user page <?php echo htmlspecialchars($user["first_name"] . " " . $user["last_name"]); ?> </h2>
<p>You have possibilities to overwiev your profile, add experiments and wiev your points. Log it or it didnt happen! </p>
</div>

<div class="user">
<h2>Company & Department</h2>
<p>

        Lab:
        <?php 
        // if you don't belong to a company, have the possibility to join one
        if (empty($lab["name"])){ ?>
            <br>No lab group<br>
            <form action="" method= "POST" class = "join_lab"> 
            <label for="companies">Join a lab group</label><br>
            <input type="text" class="" name="unique_code_lab" required><br>
            <input type="submit" class="" name="join_lab" value="Join"><br><br>
            </form>
            <?php 
            // if the lab already exist
            if (!empty($error_join_lab)){echo htmlspecialchars($error_join_lab);}}
       
        // if user has a company it just shows
        else {
            echo htmlspecialchars($lab["name"]);
        } ?> 
        <br>

        Company:
        <?php 
        // if you don't belong to a company, have the possibility to join one
        if (empty($company["name"])){ ?>
            <br>No company <br>
            <form action="" method= "POST" class = "join_company"> 
            <label for="companies">Join a company</label><br>
            <input type="text" class="" name="unique_code" required><br>
            <input type="submit" class="" name="join_company" value="Join"><br><br>
            </form>
            <?php 
            // if the company already exist
            if (!empty($error_join_company)){ echo htmlspecialchars($error_join_company);}}
       
        // if user has a company it just shows
        else {
            echo htmlspecialchars($company["name"]);
        } ?>
        <br>
        

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

<!-- I SUGGEST THAT WE REMOVE THIS PART -->
<!--
<div class="user">
<h2>Display name</h2>
<p>Your display name is:
<?php echo htmlspecialchars($user["first_name"] . " " . $user["last_name"]); ?>
</p>
</div> -->


<div class="user">
<h2>Delete account</h2>
<p>You can choose to delete your account here </p>
<!--  this is a form, thought abou making a button but it is better to send a form to PHP that answers with f (isset($_POST["delete_account"])) { --> 
   <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account?');">
    <button type="submit" name="delete_account">Delete account</button>
</form>
<?php 
    if (!empty($delete_account_message)) {
        echo "<br>";
        echo htmlspecialchars($delete_account_message);
        echo "<br>";
    }
?>

</div>


<!-- I SUGGEST THAT WE REMOVE THIS PART AS THE PERSONAL INFORMATION IS DISPLAYED ABOVE -->
<!-- not a button now but in the future a button + some kind of new page --> 
<!--<div class="user">
<h2>Information Scriba has about me</h2>
<p>Click here to see what information scriba has about you </p>
 <p>Email: <?php echo htmlspecialchars($user["email"]); ?></p>
    <p>First name: <?php echo htmlspecialchars($user["first_name"]); ?></p>
    <p>Last name: <?php echo htmlspecialchars($user["last_name"]); ?></p>
    <p>Saved changes: <?php echo htmlspecialchars($user["saved_changes"]); ?></p>
    <p>Last login: <?php echo htmlspecialchars($user["last_login_at"]); ?></p>
    <p>Streak: <?php echo htmlspecialchars($user["streak"]); ?></p>
</div>--> 


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


