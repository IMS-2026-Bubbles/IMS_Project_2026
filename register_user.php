<?php
    require 'database/db.php';

    $message = "";
    $toastClass = "";

    # if button to register new:
    if(isset($_POST['register']))
    {
        # fetch data from POST request
        $First_Name = $_POST['First_Name'];
        $Last_Name = $_POST['Last_Name'];
        $Email = $_POST['Email'];
        $Password = $_POST['Password1']; //Also unsure of how to send password

        

        // code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/
        // Check if Email already exists
        // TODO(schema-migration): User table becomes profiles; Email becomes email
        $checkEmailStmt = $conn->prepare("SELECT Email FROM User WHERE Email = ?");
        $checkEmailStmt->bind_param("s", $Email);
        $checkEmailStmt->execute();
        $checkEmailStmt->store_result();
        error_log("Checking Email [$Email], num_rows = " . $checkEmailStmt->num_rows);


        // check if the number of rows are more than 0 => Email exists
        if ($checkEmailStmt->num_rows > 0) {
            $message = "Email ID already exists";
            $toastClass = "#007bff"; // Primary color
        } 
    
        else {
            # use placeholders to protect against sql injection
            // TODO(schema-migration): becomes INSERT INTO profiles
            // (email, first_name, last_name, salt, password) — also decide values
            // for the new columns (agreed_to_toc, saved_changes, streak) and write
            // a login_log row / set last_login_at per ARCHITECTURE.md TODOs
            $sql = "INSERT INTO user(Email, First_Name, Last_Name, Salt, Password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $Salt = random_bytes($numberOfDesiredBytes); 
            $hashedPassword = password_hash($Password, PASSWORD_BCRYPT, $Salt);
            $stmt->bind_param("sssss", $Email, $First_Name, $Last_Name, $Salt, $hashedPassword);
            $result = $stmt->execute();


            if ($result) {
                    $message = "Account created successfully";
                    $toastClass = "#28a745"; // Success color
                }
                    
                    else {
                        $message = "Error: " . $stmt->error;
                        $toastClass = "#dc3545"; // Danger color
                    }

                    $stmt->close();
            }
  
        $checkEmailStmt->close();
        include 'database/close_db.php';
    
        # redirect here instead of in the form down below
        # now the form is sent as a post, it would not be otherwise
        if (isset($result) && $result) {
            header("Location: user_profile.php");
            exit;
        }
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register account</title> <!-- This is what you see on the tab in safari/chrome -->
    <link rel="stylesheet" href="assets/style.css"> <!-- We can define our own design in this -->
    <!-- To get premade buttons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        body{
            padding: 70px ; /* Adds 50px of space around the inside of the web browser*/
        }
        </style>
</head>




<body>
    <h1>Welcome to Scriba!</h1>
    <h2>Add the following information to create an account</h2>


    <!-- These are for error messages: code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/ -->
    <?php if ($message): ?>
    <div style="background-color: <?php echo htmlspecialchars($toastClass); ?>; 
                color: white; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;">
        <?php echo htmlspecialchars($message); ?>
    </div>
    
    <?php endif;?>

    
     <!-- Create the action + call function to check if Passwords match-->
    <form action="" method= "POST" onsubmit ="return checkPassword(this)">  <!-- change action so you end up somewhere! -->

        <!-- forms for all free text info that is needed-->
        <!-- required so that the field is mandatory before registering -->
        <label for="First_Name">First name</label><br>
        <input type="text" class="" name="First_Name" required><br>

        <label for="Last_Name">Last name</label><br>
        <input type="text" class="" name="Last_Name" required><br>

        <label for="Email">Email adress</label><br>
        <input type="Email" class="" name="Email" required><br> <!-- @ is needed -->

        <!-- setting type as Password makes characters hidden + supports Password control -->
        <label for="Password">Password</label><br>
        <input type="Password" class="" name="Password1" minlength= "8" required> <br> <!-- must use 8 characters -->

        <label for="Password2">Repeat Password</label><br>
        <input type="Password" class="" name="Password2" minlength= "8" required><br><br>

        <!-- https://www.geeksforgeeks.org/javascript/Password-matching-using-javascript/ -->
        
        <script>
            // Function to check Whether both Passwords is same or not.
            function checkPassword(form) {
                Password1 = form.Password1.value;
                Password2 = form.Password2.value;

                // If Not same return False.    
                if (Password1 != Password2) {
                    // This pops up and the request is not submitted
                    alert("\nPassword did not match: Please try again...")
                    return false;
                }

            }
        </script>


        <!-- GDPR button -->
        <label class="switch">
            <input type="checkbox" required>
            <span class="slider round"></span>
            <!-- create hyperlink (<a>) so you can view GDPR rules-->
            <!-- # so that you don't change page -->
            I accept the <a href=# onclick="return GDPR();">GDPR policy</a><br><br>
        </label><br><br>


        <script>
        function GDPR() {
            alert("Write a fancy text about GDPR usage here")
            return false;;
        }
        </script>

        <input type="submit" class="btn btn-dark rounded-pill" name="register" value="Register"><br><br>
    </form>


    
</body>
</html>



