<?php
    include 'database/db.php';



    # HANDLE POST METHOD TO CHANGE ADMIN STATUS
    # post methods should be at top in order to reload page directly
    if(isset($_POST['make_admin'])){
        $user_id = $_POST['user_id'];
        $sql_admin = "UPDATE Company_Member SET Role = 'admin' WHERE  User_ID = ?";
        $stmt = $conn->prepare($sql_admin);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();}

    if(isset($_POST['admin_removal'])){
        $user_id = $_POST['user_id'];
        $sql_admin = "UPDATE Company_Member SET Role = 'member' WHERE  User_ID = ?";
        $stmt = $conn->prepare($sql_admin);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();}




    # ---- OBS THIS DOESNT WORK ------
    # if button to register new:
    if(isset($_POST['register_company']))
    {
    # fetch data from POST request
    $company_name = $_POST['company_name'];

    # write a function that creates unique ID
    <script>
        function createUniqueCompanyID() {
            let code = "C"; # creating empty string
            const characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            for (let i = 0; i < 10; i++) {
                const randomInd = Math.floor(Math.random() * characters.length);
                code += characters.charAt(randomInd);
                }
            return code;}
    
    </script>



    # use placeholders to protect against sql injection
    # TODO(schema-migration): becomes INSERT INTO companies (name) — also generate
    # the prefixed VARCHAR(10) company_id ("c1", "c2", ...) per the new schema
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

    # ------------------------------------


    # DISPLAY TABLE WITH USERS AND COMPANIES
    // OBS i don't know if this works
    // the idea is that all info regarding a user will be saved?
    # TODO(schema-migration): old table/column names throughout — becomes
    # profiles/companies/labs/lab_members with profile_id, name, email; the row
    # keys used below (First_Name, Comp_Name, ...) change with them
    $sql = "SELECT User.User_ID, User.First_Name, User.Last_Name, User.Email, Company.Comp_Name, Lab_Group.Lab_Name, Lab_Group_Member.Lab_Group_ID, Company_Member.Role
            FROM User
            LEFT JOIN Company_Member ON User.User_ID = Company_Member.User_ID
            LEFT JOIN Company ON Company_Member.Company_ID = Company.Company_ID
            LEFT JOIN Lab_Group_Member ON User.User_ID = Lab_Group_Member.User_ID
            LEFT JOIN Lab_Group ON Lab_Group_Member.Lab_Group_ID = Lab_Group.Lab_Group_ID";
    
    $result = $conn->query($sql);

    $rows_to_display = "";
    # need to implement button on each row - so in while loop
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $role = htmlspecialchars($row["Role"] ?? '');
            $rows_to_display .= "<tr>" .
                "<td>" . htmlspecialchars($row["First_Name"]) . " " . htmlspecialchars($row["Last_Name"]) ."</td>" .
                "<td>" . htmlspecialchars($row["Comp_Name"] ?? '') . "</td>" .
                "<td>" . htmlspecialchars($row["Role"] ?? '') . "</td>" .
                "<td>";
                
                if($role == "member"){
                    $rows_to_display .= 

                    "<form action='' method='POST' style='display:inline;'>" .
                    # make it hidden so that the user id is saved in the post - is needed to change in database
                    "<input type='hidden' name='user_id' value='" . htmlspecialchars($row["User_ID"]) . "'>" .
                    "<input type='submit' name='make_admin' value='Make admin'>" .
                    "</form>";}


                if($role == "admin"){
                    $rows_to_display .= 
                    "<form action='' method= 'POST' style='display:inline;'>" .
                    "<input type='hidden' name='user_id' value='" . htmlspecialchars($row["User_ID"]) . "'>" .
                    "<input type='submit' name='admin_removal' value='Remove as admin' >" .
                    "</form>";}
            
            $rows_to_display .= "</td></tr>";
                

        }

    } else {
        $rows_to_display = "No members of Scriba.";
    }


    # DISPLAY TABLE WITH COMPANIES AND COMPANY ID
    $sql_company = "SELECT *
                    FROM Company";
    
    $result_company = $conn->query($sql_company);

    $rows_to_display_company = "";
    if ($result_company->num_rows > 0) {
        while($row = $result_company->fetch_assoc()) {
            $rows_to_display_company .= "<tr> <td>" . $row["Comp_Name"] .
                "</td><td>" . $row["Company_ID"] .  "</td></tr>";
        }
    } else {
        $rows_to_display_company = "No added companies";
    }



    
    // disconnect from database
    include "database/close_db.php";
    ?>

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
        - Create unique code for companies <br>
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
    <div style="display: flex; gap: 50px;">
    <div>
        <table class = "table table-striped"> <!-- update to another class maybe -->
            <thead>
            <tr>
                <!-- specifying the column names names -->
            <th scope="col">Name</th>
            <th scope="col">Company</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
            </tr>
        <thead> 
        <tbody>
            <?php echo $rows_to_display; ?>
        </tbody>
        
        </table>
    </div>

    <div>
        <table class = "table table-striped"> <!-- update to another class maybe -->
            <thead>
            <tr>
                <!-- specifying the column names names -->
            <th scope="col">Company</th>
            <th scope="col">Company ID</th>  <!-- Come up with a way for connecting to company table -->
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