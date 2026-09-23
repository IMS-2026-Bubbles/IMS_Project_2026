<?php
    include 'Database_related/db.php';

    $message = "";
    $toastClass = "";

    # if button to register new:
    if(isset($_POST['register']))
    {
        # fetch data from POST request
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        //generate salt and add to database
        $password = $_POST['password1']; # IMPLEMENT SECURITY HERE
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); # ???

        // code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/
        // Check if email already exists
        $checkEmailStmt = $conn->prepare("SELECT Email FROM User WHERE Email = ?");
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $checkEmailStmt->store_result();
        error_log("Checking email [$email], num_rows = " . $checkEmailStmt->num_rows);


        // check if the number of rows are more than 0 => email exists
        if ($checkEmailStmt->num_rows > 0) {
            $message = "Email ID already exists";
            $toastClass = "#007bff"; // Primary color
        } 
    
        else {
            # use placeholders to protect against sql injection
            $sql = "INSERT INTO User(First_Name, Last_Name, Email, Password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashedPassword);
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
        include 'Database_related/closeDB.php';
    
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
    <link rel="stylesheet" href="functional_php/style.css"> <!-- We can define our own design in this -->
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


     <!-- These are for error messages: 
    code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/ -->
    <?php if ($message): ?>
    <div style="background-color: <?php echo htmlspecialchars($toastClass); ?>; 
                color: white; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    
     <!-- Create the action + call function to check if passwords match-->
    <form action="" method= "POST" onsubmit ="return checkPassword(this)">  <!-- change action so you end up somewhere! -->

        <!-- forms for all free text info that is needed-->
        <!-- required so that the field is mandatory before registering -->
        <label for="first_name">First name</label><br>
        <input type="text" class="" name="first_name" required><br>

        <label for="last_name">Last name</label><br>
        <input type="text" class="" name="last_name" required><br>

        <label for="email">Email adress</label><br>
        <input type="email" class="" name="email" required><br> <!-- @ is needed -->

        <!-- setting type as password makes characters hidden + supports password control -->
        <label for="password">Password</label><br>
        <input type="password" class="" name="password1" minlength= "8" required> <br> <!-- must use 8 characters -->

        <label for="password2">Repeat password</label><br>
        <input type="password" class="" name="password2" minlength= "8" required><br><br>

        <!-- https://www.geeksforgeeks.org/javascript/password-matching-using-javascript/ -->
        <script>
            // Function to check Whether both passwords is same or not.
            function checkPassword(form) {
                password1 = form.password1.value;
                password2 = form.password2.value;

                // If Not same return False.    
                if (password1 != password2) {
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

