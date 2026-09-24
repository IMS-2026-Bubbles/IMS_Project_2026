<?php
session_start(); # should this be here? 

// connect to database
    include "database/db.php";

    $profile_ID = ""; // FIX THIS: track this from the login session somehow
    $profile_ID = 1; // just for testing

    # to get the company_id of thius person
    $admin_company_ID = "SELECT company_ID
                        FROM company_members
                        WHERE profile_id = $profile_ID";

    # get the company_id of this person
    $stmt = $conn->prepare("SELECT company_id FROM company_members WHERE profile_id = ?");
    $stmt->bind_param("i", $profile_ID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $admin_company_ID = $row["company_id"];

    echo "current company id for the admin: ";
    var_dump($admin_company_ID);

    // -------------- CREATING NEW LAB GROUP BUTTON -----------------

    # if button to register new:
    if(isset($_POST['register_lab_group']))
    {
    # fetch data from POST request
    $name = $_POST['name'];

    # write a function that creates unique ID
    function createUniqueLabID($conn) {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789'; # these are possible char to choose from
        $code = "L"; # all company ids start with C
        # for loop that generates a random number and pick the char with that position
        for ($i = 0; $i < 9; $i++) {
            $random_number = random_int(0, strlen($characters) - 1);
            $code .= $characters[$random_number];}
        return $code;}

    $lab_id = createUniqueLabID($conn);

    # add a check here as well so that the same companies isn't added twice
    $checkLabStmt = $conn->prepare("SELECT name FROM labs WHERE name = ?");
    $checkLabStmt->bind_param("s", $name);
    $checkLabStmt->execute();
    $checkLabStmt->store_result();
    error_log("Checking lab group name: [$name], num_rows = " . $checkLabStmt->num_rows);


    // check if the number of rows are more than 0 => Email exists
    if ($checkLabStmt->num_rows > 0) {
        $message = "Lab group already exists";
        $toastClass = "#ff0019"; // Primary color
    } 
    
    else {
    # use placeholders to protect against sql injection
    $sql = "INSERT INTO labs(name, lab_id, company_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $lab_id, $admin_company_ID);
    $result = $stmt->execute();

    # echos how it went
    if ($result) {
        $message = "Lab group created";
        $toastClass = "#1ea324"; // Primary color
    } else {
        echo "Error: " . $stmt->error;
    }
    }}

    // -------------------------------
    
    

    // ------------ TABLE SHOWING PEOPLE AND LAB GROUPS ------------
    $sql = "SELECT profiles.profile_id, profiles.first_name, profiles.last_name, labs.name
            FROM profiles
            JOIN company_members ON profiles.profile_id = company_members.profile_id
            JOIN companies ON company_members.company_id = companies.company_id
            LEFT JOIN lab_members ON profiles.profile_id = lab_members.profile_id
            LEFT JOIN labs ON lab_members.lab_id = labs.lab_id
            WHERE company_members.company_ID = '$admin_company_ID'"; # this is hardcoded

    
    $result = $conn->query($sql);

    $rows_to_display = "";

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {

            $rows_to_display .= "<tr>" .
                "<td>" . htmlspecialchars($row["first_name"]) . " " . htmlspecialchars($row["last_name"]) ."</td>" .
                "<td>" . htmlspecialchars($row["name"] ?? '') . "</td>" .
                "<td>";
        }
    } else {
        echo "No members of this company.";
    }

    // ------------



    # DISPLAY TABLE WITH COMPANIES AND companies ID

    
    $sql_lab = "SELECT name, lab_id
            FROM labs
            WHERE company_id = '$admin_company_ID'";
    
    $result_lab = $conn->query($sql_lab);

    $rows_to_display_lab = "";
    if ($result_lab->num_rows > 0) {
        while($row = $result_lab->fetch_assoc()) {
            $rows_to_display_lab .= "<tr> <td>" . $row["name"] .
                "</td><td>" . $row["lab_id"] .  "</td></tr>";
        }
    } else {
        $rows_to_display_lab = "No added companies";
    }

    
    // disconnect from database
    include "database/close_db.php";
    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company admin</title>
    <h1>Company admin page</h1>
    
</head>

<style>
        /* For tables */
        table{
            text-align: left;
            border-collapse: collapse; /* Make underline to be connected */
            padding: 10px;
        }
        
        /* For table header and data */
        th, td {
            text-align: left;
            border-bottom: 1px solid #ddd; /* Have underlines */
            padding: 10px;
        }

        /* Hover is so that if you have your mouse on row, colour changes on that row */
        tr:hover {background-color: #D6EEEE;}

        /* If div class = container, the tables will be next to each other */
        .container{ 
            display: flex; 
            justify-content: center;  /* the tables are in the center of page */
            gap: 50px;}

        /* I don't manage to make this look nice, but this is the start :) */
        .register_form{
            padding: 50px ; /* adds 50px of space between the content of the container and its edges */
            margin: 0 auto; /* centers the container in the web browser */ 
        } 
        
    </style>


<body>
    <p>This is the page that the company admin will be able to see. </p>
    <p>Functions: <br>
        - View all users in the company and the lab groups <br>
        - Create new lab groups <br>
        - Create the unique codes <br>
        - See the unique codes connected to a lab group (PK for Lab_group)
    </p>


    <!-- adding a lab group -->
     
    <form action="" method= "POST" class = "register_form"> 

        <!-- forms for all free text info that is needed-->
        <!-- required so that the field is mandatory before registering -->
        <label for="companies">Register a new lab group</label><br>
        <input type="text" class="" name="name" required><br>

    <input type="submit" class="btn btn-dark rounded-pill" name="register_lab_group" value="Register"><br><br>
    </form>

    <!-- Table 1 -->
    <div class = "container">
    <div>
    <table class = "table table-striped"> <!-- update to another class maybe -->
        <thead>
        <tr>
            <!-- specifying the column names names -->
        <th scope="col">Name</th>
        <th scope="col">Lab group</th>
        </tr>
        <thead> 
        <tbody>
            <?php echo $rows_to_display; ?>
        </tbody>
    
    
    </table>
    </div>

    <!-- Table 2 -->
    <div>
        <table class = "table table-striped"> <!-- update to another class maybe -->
            <thead>
            <tr>
                <!-- specifying the column names names -->
            <th scope="col">Lab group</th>
            <th scope="col">Lab group ID</th>  <!-- Come up with a way for connecting to companies table -->
            </tr>
        <thead> 
        <tbody>
            <?php echo $rows_to_display_lab; ?>
        </tbody>
        
        </table>
    </div>

   
    
</body>
</html>