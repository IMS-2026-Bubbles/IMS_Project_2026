<?php
// Scriba admin page

// Arrive from:
// index.php (after logging in as a Scriba admin, redirected from actions/login.php)
// scriba_admin.php (after making/removing a user as admin)
// scriba_admin.php (after registering a new company)
// Action:
// Display all users and their company affiliations
// Display all companies
// Allow admins to make users admins or remove admin rights
// Allow admins to register new companies
// Redirect to:
// actions/manage_company.php (register company, make/remove admin rights)

require_once 'session/init.php';  // Start the session and initialize session variables
require_once 'session/check_user_logged_in.php';  // Check if the user is logged in
require_once 'database/db.php';

$manage_company_message = '';
// if these variables have gotten updated messages - they are displayed (see where down in HTML)
// they are updated when you (try to) register new company or when you change role on a person in company
if (isset($_SESSION['manage_company_message'])) {
    $manage_company_message = $_SESSION['manage_company_message'];
    unset($_SESSION['manage_company_message']);
};
if (isset($_SESSION['manage_company_toastClass'])) {
    $manage_company_toastClass = $_SESSION['manage_company_toastClass'];
    unset($_SESSION['manage_company_toastClass']);
};

// DISPLAY TABLE WITH USERS AND COMPANIES
// get all users names and their companies
$sql = 'SELECT profiles.profile_id, profiles.first_name, profiles.last_name, companies.name, company_members.role
        FROM profiles
        LEFT JOIN company_members ON profiles.profile_id = company_members.profile_id
        LEFT JOIN companies ON company_members.company_id = companies.company_id
        WHERE profiles.is_deleted = 0 AND profiles.is_scriba_admin = 0 AND profiles.is_verified = 1
        ORDER BY companies.name ASC';

// store in variable result
$result = $conn->query($sql);

// create empty string to fill up while going through result from query
// choose names of person + company name + role for each person
$rows_to_display = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // if there is a role, choose it, otherwise it is null
        $role = htmlspecialchars($row['role'] ?? '');
        // add info to string
        $rows_to_display .= '<tr>'
            . '<td>' . htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) . '</td>'
            . '<td>' . htmlspecialchars($row['name'] ?? '') . '</td>'
            . '<td>' . htmlspecialchars($row['role'] ?? '') . '</td>'
            . '<td>';

        // if you are only a member - show button to update role to admin
        if ($role == 'member') {
            $rows_to_display .=
                "<form action='actions/manage_company.php' method='POST' style='display:inline;'>"
                // make it hidden so that the user id is saved in the post - is needed to change in database
                . "<input type='hidden' name='profile_id' value='" . htmlspecialchars($row['profile_id']) . "'>"  // get the profile id for the person that will be affected
                . "<input type='submit' class = 'admin_button' name='make_admin' value='Make admin'>"
                . '</form>';
        }

        // if you are admin - show button to update role to member
        if ($role == 'admin') {
            $rows_to_display .=
                "<form action='actions/manage_company.php' method= 'POST' style='display:inline;'>"
                // make it hidden so that the user id is saved in the post - is needed to change in database
                . "<input type='hidden' name='profile_id' value='" . htmlspecialchars($row['profile_id']) . "'>"  // get the profile id for the person that will be affected
                . "<input type='submit' class = 'admin_button' name='admin_removal'  value='Remove as admin' >"
                . '</form>';
        }

        // if the user doesn't belong to a company
        // assign the person to a company
        if (empty($row['name'])) {
            $company_options = '';  // create an empty string to be filled with all current companies
            $sql_show_comp = 'SELECT *
                            FROM companies
                            ORDER BY name ASC';  // getting all companies
            $result_com = $conn->query($sql_show_comp);
            // adding the companies to the previous empty string,
            // if they are chosen later on in the dropdown meny, the company_id are saved and passed
            while ($r = $result_com->fetch_assoc()) {
                $company_options .= "<option value='" . htmlspecialchars($r['company_id']) . "'>"
                    . htmlspecialchars($r['name']) . '</option>';
            }

            $rows_to_display .=
                "<form action='actions/manage_company.php' method= 'POST' style='display:inline;'>"  // so that it can be next to other stuff
                // make it hidden so that the user id is saved in the post - is needed to change in database
                . "<input type='hidden' name='profile_id' value='" . htmlspecialchars($row['profile_id']) . "'>"
                . "<select name='add_to_company'>"  // open dropdown
                . "<option value='' disabled selected >Assign to a company</option>"  // so that you can't choose this option
                . $company_options  // add the companies
                . '</select> '
                . "<button type='submit'>Add</button>"
                . '</form>';
        }
        $rows_to_display .= '</td></tr>';
    }
}

// DISPLAY TABLE WITH COMPANIES
$sql_company = 'SELECT *
                FROM companies
                ORDER BY name ASC';  // order by company name
$result_company = $conn->query($sql_company);

$rows_to_display_company = '';
if ($result_company->num_rows > 0) {
    while ($row = $result_company->fetch_assoc()) {
        $rows_to_display_company .= '<tr> <td>' . $row['name']
            . '</td></tr>';
    }
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

<!-- Scriba CSS  -->
<link rel="stylesheet" href="assets/style.css">
        

    



    <!-- When improving front end, this is where updates can be made -->
   


    <title>Admin</title>
</head>

<!-- i want nav bar here -->
    <?php
    include 'includes/navbar.php';
    ?>








<body>
    <main class = "company_admin_page">

    <h1 class = "page_title" >Admin Page </h1>

    <!-- Makes sure that the message is actually displayed -->
    <?php if (!empty($manage_company_message)): ?>
        <div style="background-color: <?php echo $manage_company_toastClass; ?>; color: white; padding: 10px; margin: 10px 0;" >
            <?php echo htmlspecialchars($manage_company_message); ?>
        </div>
    <?php endif; ?>
<!--
    <p>This is the page that ONLY Scriba admin (superadmin) will be able to see. This is also the only page this type of admin will have access to. </p>

        Look at this link on how you sort a table by clicking on header:<br> 
        https://www.w3schools.com/howto/howto_js_sort_table.asp
    </p> -->

    <!-- adding a company -->
    
    <form action="actions/manage_company.php" method= "POST" class = ""> 

        <!-- forms for all free text info that is needed-->
        <!-- required so that the field is mandatory before registering -->
        <label for="companies">Register a new company</label><br>
        <input type="text" class="scriba_input" name="name" required><br><br>

    <input type="submit" class="login_button" name="register_company" value="Register"><br><br>
    </form>


    


    <!-- Table 1 -->

    <div class = "admin_container">
    <div>
        
        <!--<table class = "table table-striped">  update to another class maybe -->
        <table class="table table-striped table-hover border border-dark" >  <!-- moved hover to css style document in class table  -->

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
            <!-- https://getbootstrap.com/docs/4.0/utilities/borders/ -->
            <table class="table table-striped table-hover admin_table border border-dark">
            <thead>
            <tr>
                <!-- specifying the column names names -->
            <th scope="col">Companies</th>
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
include 'database/close_db.php';

?>