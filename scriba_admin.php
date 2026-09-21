<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
</head>
<body>
    <h1>Admin Page</h1>
    <p>This is the page that ONLY Scriba admin (superadmin) will be able to see. This is also the only page this type of admin will have access to. </p>
    <p>Functions: <br>
        - View all users on Scriba and see the companies and so <br>
        - Create new companies - DONE<br>
        - Make a person a company admin <br>

        Look at this link on how you sort a table by clicking on header:<br> 
        https://www.w3schools.com/howto/howto_js_sort_table.asp
    </p>

    <!-- HTML form for adding a company -->
    <form action="" method= "POST"> 

        <!-- forms for all free text info that is needed-->
        <!-- required so that the field is mandatory before registering -->
        <label for="company">Register a new company</label><br>
        <input type="text" class="" name="company_name" required><br>

    <input type="submit" class="btn btn-dark rounded-pill" name="register_company" value="Register"><br><br>
    </form>



<?php
    include 'Database_related/db.php';

    # if button to register new:
    if(isset($_POST['register_company']))
    {
    # fetch data from POST request
    $company_name = $_POST['company_name'];

    # use placeholders to protect against sql injection
    $sql = "INSERT INTO Company(Company_Name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $company_name);
    $result = $stmt->execute();

    # echos how it went
    if ($result) {
        echo "New company registered to Scriba";
    } else {
        echo "Error: " . $stmt->error;
    }
    }

    # add a check here as well so that the same company isn't added twice

    include 'Database_related/closeDB.php';
?>





    <table class = "table table-striped"> <!-- update to another class maybe -->
        <thead>
        <tr>
            <!-- specifying the column names names -->
        <th scope="col">Name</th>
        <th scope="col">Company</th>  <!-- Come up with a way for connecting to company table -->
        </tr>
    <thead> 
    
    <?php
    // connect to database
    include "database_related/db.php";



    // OBS i don't know if this works
    // the idea is that all info regarding a user will be saved?
    $sql = "SELECT User.User_ID, User.First_Name, User.Last_Name, User.Email, Company.Comp_Name, Lab_Group.Lab_Name, Lab_Group_Member.Lab_Group_ID
            FROM User
            JOIN Company_Member ON User.User_ID = Company_Member.User_ID
            JOIN Company ON Company_Member.Company_ID = Company.Company_ID
            LEFT JOIN Lab_Group_Member ON User.User_ID = Lab_Group_Member.User_ID
            LEFT JOIN Lab_Group ON Lab_Group_Member.Lab_Group_ID = Lab_Group.Lab_Group_ID";
    
    $stmt = $link->prepare($sql);
    // s for string
    $stmt->bind_param("s", $admin_ID);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr> <td>" . $row["First_Name"] . " " . $row["Last_Name"] .
                "</td><td>" . $row["Comp_Name"] .  "</td></tr>";
        }
    } else {
        echo "No members of Scriba.";
    }

    
    // disconnect from database
    include "database_related/closeDB.php";
    ?>
    </table>

    

    
</body>
</html>