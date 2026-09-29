<?php
// Scriba admin page

// Arrive from: 
    // index.php (after logging in as a Scriba admin, redirected from actions/login.php)
    // scriba_admin.php (after making/removing a user as admin)
    // scriba_admin.php (after registering a new company)
// Action:
    // Display all users and their company affiliations
    // Display all companies and their unique IDs
    // Allow admins to make users admins or remove admin rights
    // Allow admins to register new companies
// Redirect to:
    // actions/manage_company.php (register company, make/remove admin rights)


require_once "session/init.php"; // Start the session and initialize session variables
require_once "session/check_user_logged_in.php"; // Check if the user is logged in
require_once 'database/db.php';



if (isset($_SESSION['manage_company_message'])) {
    $manage_company_message = $_SESSION['manage_company_message'];
    unset($_SESSION['manage_company_message']);
} else {
    $manage_company_message = "";
}

if (isset($_SESSION['manage_company_toastClass'])) {
    $manage_company_toastClass = $_SESSION['manage_company_toastClass'];
    unset($_SESSION['manage_company_toastClass']);
} else {
    $manage_company_toastClass = "";
}
    // # HANDLE POST METHOD TO CHANGE ADMIN STATUS
    // # post methods should be at top in order to reload page directly
    // if(isset($_POST['make_admin'])){
    //     $profile_id = $_POST['profile_id'];
    //     $sql_admin = "UPDATE company_members SET role = 'admin' WHERE  profile_id = ?";
    //     $stmt = $conn->prepare($sql_admin);
    //     $stmt->bind_param("i", $profile_id);
    //     $result = $stmt->execute();}

    // if(isset($_POST['admin_removal'])){
    //     $profile_id = $_POST['profile_id'];
    //     $sql_admin = "UPDATE company_members SET role = 'member' WHERE  profile_id = ?";
    //     $stmt = $conn->prepare($sql_admin);
    //     $stmt->bind_param("i", $profile_id);
    //     $result = $stmt->execute();}




    // # if button to register new:
    // if(isset($_POST['register_company']))
    // {
    // # fetch data from POST request
    // $name = $_POST['name'];

    // # write a function that creates unique ID
    // function createUniqueCompanyID($conn) {
    //     $characters = 'abcdefghijklmnopqrstuvwxyz0123456789'; # these are possible char to choose from
    //     $code = "C"; # all company ids start with C
    //     # for loop that generates a random number and pick the char with that position
    //     for ($i = 0; $i < 9; $i++) {
    //         $random_number = random_int(0, strlen($characters) - 1);
    //         $code .= $characters[$random_number];}
    //     return $code;}

    // $company_id = createUniqueCompanyID($conn);

    // # add a check here as well so that the same companies isn't added twice
    // $checkCompStmt = $conn->prepare("SELECT name FROM companies WHERE name = ?");
    // $checkCompStmt->bind_param("s", $name);
    // $checkCompStmt->execute();
    // $checkCompStmt->store_result();
    // error_log("Checking company name: [$name], num_rows = " . $checkCompStmt->num_rows);


    // // check if the number of rows are more than 0 => Email exists
    // if ($checkCompStmt->num_rows > 0) {
    //     $message = "Company already exists";
    //     $toastClass = "#ff0019"; // Primary color
    // } 
    
    // else {
    // # use placeholders to protect against sql injection
    // $sql = "INSERT INTO companies(name, company_id) VALUES (?, ?)";
    // $stmt = $conn->prepare($sql);
    // $stmt->bind_param("ss", $name, $company_id);
    // $result = $stmt->execute();

    // # echos how it went
    // if ($result) {
    //     $message = "Company created";
    //     $toastClass = "#1ea324"; // Primary color
    // } else {
    //     echo "Error: " . $stmt->error;
    // }
    // }}



    # DISPLAY TABLE WITH USERS AND COMPANIES
    $sql = "SELECT profiles.profile_id, profiles.first_name, profiles.last_name, companies.name, company_members.role
            FROM profiles
            LEFT JOIN company_members ON profiles.profile_id = company_members.profile_id
            LEFT JOIN companies ON company_members.company_id = companies.company_id
            ORDER BY companies.name ASC";
    
    $result = $conn->query($sql);

    $rows_to_display = "";
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $role = htmlspecialchars($row["role"] ?? '');
            $rows_to_display .= "<tr>" .
                "<td>" . htmlspecialchars($row["first_name"]) . " " . htmlspecialchars($row["last_name"]) ."</td>" .
                "<td>" . htmlspecialchars($row["name"] ?? '') . "</td>" .
                "<td>" . htmlspecialchars($row["role"] ?? '') . "</td>" .
                "<td>";
                
                if($role == "member"){
                    $rows_to_display .= 

                    "<form action='actions/manage_company.php' method='POST' style='display:inline;'>" .
                    # make it hidden so that the user id is saved in the post - is needed to change in database
                    "<input type='hidden' name='profile_id' value='" . htmlspecialchars($row["profile_id"]) . "'>" .
                    "<input type='submit' name='make_admin' value='Make admin'>" .
                    "</form>";}


                if($role == "admin"){
                    $rows_to_display .= 
                    "<form action='actions/manage_company.php' method= 'POST' style='display:inline;'>" .
                    "<input type='hidden' name='profile_id' value='" . htmlspecialchars($row["profile_id"]) . "'>" .
                    "<input type='submit' name='admin_removal' value='Remove as admin' >" .
                    "</form>";}
            
            $rows_to_display .= "</td></tr>";
                

        }

    } else {
        $rows_to_display = "No members of Scriba.";
    }


    # DISPLAY TABLE WITH COMPANIES AND companies ID
    $sql_company = "SELECT *
                    FROM companies
                    ORDER BY name ASC"; # order by compny name, otherwise it is after id
    
    $result_company = $conn->query($sql_company);

    $rows_to_display_company = "";
    if ($result_company->num_rows > 0) {
        while($row = $result_company->fetch_assoc()) {
            $rows_to_display_company .= "<tr> <td>" . $row["name"] .
                "</td><td>" . $row["company_id"] .  "</td></tr>";
        }
    } else {
        $rows_to_display_company = "No added companies";
    }
   
    ?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Loads Bootstrap -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >


    <!-- When improving front end, this is where updates can be made -->
    <style>
        /* For tables */
        .table{
            --bs-table-hover-bg: #D6EEEE; /* Added so that colour changes over a row */

        }
        
        /* If div class = container, the tables will be next to each other */
        .container{ 
            display: flex; 
            justify-content: center;  /* the tables are in the center of page */
            gap: 50px;}

        /* I don't manage to make this look nice, but this is the start :) */
        .register_form{
            padding: 10px ; /* adds 50px of space between the content of the container and its edges */
            margin: 0 auto; /* centers the container in the web browser */ 
        } 
        
    </style>



    <title>Admin</title>
</head>

<!-- i want nav bar here -->
    <?php 
    include "includes/navbar.php";
    ?>
<body>
    <h1>Admin Page</h1>
    <!-- Makes sure that the message is actually displayed -->
    <?php if (!empty($manage_company_message)): ?>
        <div style="background-color: <?php echo $manage_company_toastClass; ?>; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <?php echo htmlspecialchars($manage_company_message); ?>
        </div>
    <?php endif; ?>

    

    

<!--
    <p>This is the page that ONLY Scriba admin (superadmin) will be able to see. This is also the only page this type of admin will have access to. </p>

        Look at this link on how you sort a table by clicking on header:<br> 
        https://www.w3schools.com/howto/howto_js_sort_table.asp
    </p> -->




    <!-- adding a company -->
     
    <form action="actions/manage_company.php" method= "POST" class = "register_form"> 

        <!-- forms for all free text info that is needed-->
        <!-- required so that the field is mandatory before registering -->
        <label for="companies">Register a new company</label><br>
        <input type="text" class="" name="name" required><br><br>

    <input type="submit" class="btn btn-dark rounded-pill" name="register_company" value="Register"><br><br>
    </form>


    <!-- Table 1 -->
    <div class = "container">
    <div>
        <!--<table class = "table table-striped">  update to another class maybe -->
        <table class="table table-striped table-hover">

        <thead>
            <tr>
                <!-- specifying the column names names -->
            <th scope="col">Name</th>
            <th scope="col">Company</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
            </tr>
    </thead> 
        <tbody>
            <?php echo $rows_to_display; ?>
        </tbody>
        
        </table>
    </div>
    
    <!-- Table 2 -->
    <div>
        <!-- <table class = "table table-striped">  update to another class maybe -->
            <table class="table table-striped table-hover">
            <thead>
            <tr>
                <!-- specifying the column names names -->
            <th scope="col">Company</th>
            <th scope="col">Company ID</th>  <!-- Come up with a way for connecting to companies table -->
            </tr>
        <thead> 
        <tbody>
            <?php echo $rows_to_display_company; ?>
        </tbody>
        
        </table>
    </div>
</div>
    
</body>
</html>


<?php


 // disconnect from database
    include "database/close_db.php";

?>