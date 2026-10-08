<?php
// Company admin page

// Arrive from:
// navigation bar (click on "Company admin" link)
// Action:
// Display company admin information, including lab groups and members
// Create new labs inside company
// Invite members to company
// Assign members to lab groups
// Assign project without owner to another person
// Redirect to:
// navigation bar options
// actions/join_company_lab.php (invite member, assign to lab, assign new project owner)
// actions/create_lab.php (after clicking "Register" button to create a new lab group)

require_once 'session/init.php';  // Make the session available
require_once 'session/check_user_logged_in.php';  // Check if the user is logged in
// connect to database
require_once 'database/db.php';

$profile_ID = $_SESSION['profile_id'];
$admin_company_ID = $_SESSION['company_id'];

// preparing to show error messages for creating lab
// only shows if they are actually filled (done in actions/create_lab.php)
if (isset($_SESSION['create_lab_message'])) {
    $message = $_SESSION['create_lab_message'];
    unset($_SESSION['create_lab_message']);
} else {
    $message = '';
}
if (isset($_SESSION['create_lab_toastClass'])) {
    $toastClass = $_SESSION['create_lab_toastClass'];
    unset($_SESSION['create_lab_toastClass']);
} else {
    $toastClass = '';
}

// # preparing to show error messages for creating lab
// only shows if they are actually filled (done in actions/join_company_lab.php)
if (isset($_SESSION['create_invite_message'])) {
    $message = $_SESSION['create_invite_message'];
    unset($_SESSION['create_invite_message']);
} else {
    $message = '';
}

if (isset($_SESSION['create_invite_toastClass'])) {
    $toastClass = $_SESSION['create_invite_toastClass'];
    unset($_SESSION['create_invite_toastClass']);
} else {
    $toastClass = '';
}

// ------------ TABLE SHOWING PEOPLE AND LAB GROUPS ------------
$sql = 'SELECT profiles.profile_id, profiles.first_name, profiles.last_name, labs.name, company_members.role
            FROM profiles
            JOIN company_members ON profiles.profile_id = company_members.profile_id
            JOIN companies ON company_members.company_id = companies.company_id
            LEFT JOIN lab_members ON profiles.profile_id = lab_members.profile_id
            LEFT JOIN labs ON lab_members.lab_id = labs.lab_id
            WHERE company_members.company_ID = ? AND profiles.is_deleted = 0';

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $admin_company_ID);
$stmt->execute();
$result = $stmt->get_result();

$rows_to_display = '';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows_to_display .= '<tr>'
            . '<td>' . htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) . '</td>'
            . '<td>' . htmlspecialchars($row['name'] ?? '') . '</td>'
            . '<td>' . htmlspecialchars($row['role'] ?? '') . '</td>'
            . '<td>';

        // if you don't belong to a lab group - there is no name of the company from query above
        if (empty($row['name'])) {
            $lab_options = '';
            $sql_show_lab = 'SELECT *
                                    FROM labs
                                    WHERE company_id = ?
                                    ORDER BY name ASC';
            $stmt_lab = $conn->prepare($sql_show_lab);
            $stmt_lab->bind_param('i', $admin_company_ID);

            $stmt_lab->execute();
            $result_lab = $stmt_lab->get_result();
            while ($r = $result_lab->fetch_assoc()) {
                $lab_options .= "<option value='" . htmlspecialchars($r['lab_id']) . "'>"
                    . htmlspecialchars($r['name']) . '</option>';
            }

            $rows_to_display .=
                "<form action='actions/join_company_lab.php' method= 'POST' style='display:inline;'>"
                . "<input type='hidden' name='profile_id' value='" . htmlspecialchars($row['profile_id']) . "'>"
                . "<select name='add_to_lab'>"
                . "<option value='' disabled selected >Assign to a lab</option>"
                . $lab_options
                . '</select> '
                . "<button type='submit' class = 'admin_button' >Add</button>"
                . '</form>';
        }
        // $rows_to_display .= '</td></tr>'; here??
    }
}
// ------------

// DISPLAY TABLE WITH lab groups
$sql_lab = 'SELECT name, lab_id
            FROM labs
            WHERE company_id = ?
            ORDER BY name ASC';

$stmt = $conn->prepare($sql_lab);
$stmt->bind_param('i', $admin_company_ID);
$stmt->execute();
$result_lab = $stmt->get_result();

$rows_to_display_lab = '';
if ($result_lab->num_rows > 0) {
    while ($row = $result_lab->fetch_assoc()) {
        $rows_to_display_lab .= '<tr> <td>' . htmlspecialchars($row['name']) . '</td></tr>';
    }
}

// ------------ TABLE SHOWING ORPHANED PROJECTS ------------
// shows projects in admins comp that is owned by a profile that has been deleted
// this query uses the FK in projects (lab_id) and can retrieve the orphaned project without using company_members or lab_members 
// this is because then we can delete all "personal" info about a person except for the project it worked on
$sql_orphan_proj = "SELECT labs.lab_id, labs.name, projects.project_id, projects.name, project_members.profile_id, profiles.first_name
	FROM labs
	JOIN projects ON labs.lab_id = projects.lab_id
    JOIN project_members ON  projects.project_id = project_members.project_id
    JOIN profiles ON project_members.profile_id = profiles.profile_id
	WHERE labs.company_id = ? AND project_members.role = 'owner' AND profiles.is_deleted = 1
	ORDER BY labs.name";

$stmt = $conn->prepare($sql_orphan_proj);
$stmt->bind_param('i', $admin_company_ID);
$stmt->execute();
$result_proj = $stmt->get_result();


// get all people in lab group to choose from depending on the 
$sql = 'SELECT profiles.profile_id, profiles.first_name, profiles.last_name
            FROM profiles
            JOIN company_members ON profiles.profile_id = company_members.profile_id
            JOIN companies ON company_members.company_id = companies.company_id
            WHERE company_members.company_ID = ? AND profiles.is_deleted = 0';

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $admin_company_ID);
$stmt->execute();
$result_people = $stmt->get_result();

$people_in_comp = '';
while ($r = $result_people->fetch_assoc()) {  // using the query used to display people in company
    $people_in_comp .= "<option value='" . htmlspecialchars($r['profile_id']) . "'>"
        . htmlspecialchars($r['first_name']) . ' ' . htmlspecialchars($r['last_name']) . '</option>';
}

// prepare what to show in table
$rows_to_display_proj = '';
if ($result_proj->num_rows > 0) {
    while ($row = $result_proj->fetch_assoc()) {
        $rows_to_display_proj .= '<tr> <td>' . htmlspecialchars($row['name']) . '</td>';
        $rows_to_display_proj .= '<td>'
            . "<form action='actions/join_company_lab.php' method= 'POST' style='display:inline;'>"
            . "<input type='hidden' name='orphan_proj' value='" . htmlspecialchars($row['project_id']) . "'>"
            . "<input type='hidden' name='old_owner' value='" . htmlspecialchars($row['profile_id']) . "'>"
            . "<select name='new_owner'>"
            . "<option value='' disabled selected >Choose new project owner</option>"
            . $people_in_comp
            . '</select> '
            . "<button type='submit' class = 'admin_button' >Add</button>"
            . '</form>'
            . '</td></tr>';
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company admin</title>

    <!-- Loads Bootstrap -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
        
    >
    <link rel="stylesheet" href="assets/style.css">



</head>





<body>

    <!-- i want nav bar here -->
    <?php
    include 'includes/navbar.php';
    ?>


<main class="company_admin_page">
     

<!-- For displaying error message -->
    <?php if ($message !== ''): ?>
    <div class="toast-message" style="background-color: <?php echo htmlspecialchars($toastClass); ?>; color: white; padding: 10px; margin: 10px 0;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- i want nav bar here -->
    <?php
    // include "includes/navbar.php";
    ?>

<h1 class="page_title">Company admin page</h1>

    <!-- adding a lab group -->
      <!-- Theese were under each other before, but i would like them to be ebside each other  -->
<div class="company_admin_forms">  <!-- gathering them and giivng them heir own style -->
    <form action="actions/create_lab.php" method= "POST" class = "admin_form"> 

        <!-- forms for all free text info that is needed-->
        <!-- required so that the field is mandatory before registering -->
         <label>Register a new lab group</label>
        <input type="text" class="scriba_input" name="name" required>

    <input type="submit" class="login_button" name="register_lab_group" value="Register">
    </form>

<form action="actions/join_company_lab.php" method="POST" class="admin_form">
    <!-- adding a new person to company group -->
       <label>Invite a new member</label>
   <!-- forms for all free text info that is needed-->
        <!-- required so that the field is mandatory before registering -->
        <input type="text" class="scriba_input" name="invite_person" required>

    <input type="submit" class="login_button"  value="Invite">
    </form>
</div>

    <!-- Table 1 -->
    <div class = "admin_container">
    <div>
    <table class = "table table-striped table-hover  "> <!-- update to another class maybe -->
        <thead>
        <tr>
            <!-- specifying the column names names -->
        <th scope="col">Name</th>
        <th scope="col">Lab group</th>
        <th scope="col">Status</th>
        <th scope="col">Action</th>
        </tr>
        <thead> 
        <tbody>
            <?php echo $rows_to_display; ?>
        </tbody>
    
    
    </table>
    </div>

    <!-- Table 2 -->
    <div>
        <table class = "table table-striped table-hover "> <!-- update to another class maybe -->
            <thead>
            <tr>
                <!-- specifying the column names names -->
            <th scope="col">Lab group</th>
            </tr>
        <thead> 
        <tbody>
            <?php echo $rows_to_display_lab; ?>
        </tbody>
        
        </table>
    </div>


    <!-- Table 3 -->
    <div>
        <table class = "table table-striped table-hover "> <!-- update to another class maybe -->
            <thead>
            <tr>
                <!-- specifying the column names names -->
            <th scope="col">Projects without owner</th>
            <th scope="col">Action</th>
            </tr>
        <thead> 
        <tbody>
            <?php echo $rows_to_display_proj; ?>
            
        </tbody>
        
        </table>
        
    </div>

   
    
</body>
</html>

<?php

// disconnect from database
include 'database/close_db.php';

?>

