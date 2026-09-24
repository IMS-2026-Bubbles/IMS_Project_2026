<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company admin</title>
</head>



<body>
    <p>This is the page that the company admin will be able to see. </p>
    <p>Functions: <br>
        - View all users in the company and the lab groups <br>
        - Create new lab groups <br>
        - Create the unique codes <br>
        - See the unique codes connected to a lab group (PK for Lab_group)
    </p>

   

    <table class = "table table-striped"> <!-- update to another class maybe -->
        <thead>
        <tr>
            <!-- specifying the column names names -->
        <th scope="col">Name</th>
        <th scope="col">Email</th>
        <th scope="col">Company ID?</th>  <!-- Come up with a way for connecting to company table -->
        <th scope="col">Lab group</th>
        <th scope="col">Lab group ID</th>
        </tr>
    <thead> 
    
    <?php
    // connect to database
    include "database/db.php";

    
    $admin_ID = ""; // FIX THIS: track this from the login session somehow

    // we want to choose all users that has the same company id as the company admin
    // select all from table user, match that table with company_member so that the user table gets more info
    // select all those that share company with the person logged in as admin

    // OBS i don't know if this works
    // TODO(schema-migration): old table/column names throughout — becomes
    // profiles/companies/labs/lab_members/company_members with profile_id, name,
    // email; the row keys used below (First_Name, ...) change with them.
    // Also resolve $admin_ID above from $_SESSION['profile_id'] instead of "".
    $sql = "SELECT User.User_ID, User.First_Name, User.Last_Name, User.Email, Company.Comp_Name, Lab_Group.Lab_Name, Lab_Group_Member.Lab_Group_ID
            FROM User
            JOIN Company_Member ON User.User_ID = Company_Member.User_ID
            JOIN Company ON Company_Member.Company_ID = Company.Company_ID
            LEFT JOIN Lab_Group_Member ON User.User_ID = Lab_Group_Member.User_ID
            LEFT JOIN Lab_Group ON Lab_Group_Member.Lab_Group_ID = Lab_Group.Lab_Group_ID
            WHERE Company_Member.company_ID = (
                SELECT company_ID
                FROM Company_Member
                WHERE user_ID = ?)";
    
    $stmt = $link->prepare($sql);
    // s for string
    $stmt->bind_param("s", $admin_ID);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr> <td>" . $row["First_Name"] . " " . $row["Last_Name"] .
                "</td><td>" . $row["Email"] . 
                "</td><td>" . $row["Comp_Name"] . 
                "</td><td>" . $row["Lab_Name"] .
                "</td><td>" . $row["Lab_Group_ID"] . "</td></tr>";
        }
    } else {
        echo "No members of this company.";
    }

    
    // disconnect from database
    include "database/close_db.php";
    ?>
    </table>

   
    
</body>
</html>