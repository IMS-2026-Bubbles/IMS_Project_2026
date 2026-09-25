

<?php
    ini_set('display_errors', true);
    ini_set('log_errors', true);
    error_reporting(E_ALL);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $message = "";
    $toastClass = "";

    if(isset($_POST["Log in"]))
        {
            # fetch data from POST request
            $email = $_POST['email'];
            $password = $_POST['password']; //Also unsure of how to send password

            // code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/
            // Check if email already exists
            // TODO(schema-migration): Profiles table becomes profiles; email becomes email
            $sql = "SELECT email FROM profiles WHERE email = ?";
            $checkemailStmt = $conn->prepare($sql);
            $checkemailStmt->bind_param("s", $email);
            $checkemailStmt->execute();
            $checkemailStmt->store_result();
            error_log("Checking email [$email], num_rows = " . $checkemailStmt->num_rows);


            // check if the number of rows are more than 0 => email exists
            if ($checkemailStmt->num_rows < 1) {
                $message = "Invalid email or password.";
                $toastClass = "#007bff"; // Primary color
            } 
        
            else {
                # use placeholders to protect against sql injection
                // TODO(schema-migration): becomes INSERT INTO profiles
                // (email, first_name, last_name, salt, password) — also decide values
                // for the new columns (agreed_to_toc, saved_changes, streak) and write
                // a login_log row / set last_login_at per ARCHITECTURE.md TODOs

                $sql = "SELECT password FROM profiles WHERE email = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();

                $checkemailStmt->get_result();
                $profile = $result->fetch_assoc();

                if ($user && password_verify($password, $user['password'])) {
                        //correct log in info
                        header ("Location: Project_library.php");
                        //exit();
                    }
                        
                        else {
                            $message = "Error: " . $stmt->error;
                            $toastClass = "#dc3545"; // Danger color
                        }

                        $stmt->close();
                }
    
            $checkemailStmt->close();
            include 'database/close_db.php';
        
            # redirect here instead of in the form down below
            # now the form is sent as a post, it would not be otherwise
            if (isset($result) && $result) {
                header("Location: Project_library.php");
                //exit;
            }
        }
?>


<!DOCTYPE html>
<html lang="en">
    <link rel="stylesheet" href="assets/style.css"> <!-- We can define our own design in this -->
    <!-- To get premade buttons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    
    <style>
        body{
            padding: 70px ; /* Adds 50px of space around the inside of the web browser*/
        }
    </style>
    

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in account</title> <!-- This is what you see on the tab in safari/chrome -->
    <style> div.a {
        position: absolute;
        center;
    }  </style>
    <style> div.b {
        position: absolute;
        right: 25px;
    }  </style>
</head>
<body>
    <h1>Welcome to Scriba!</h1>
        <div class="b"> 
        <h3>Register new user</h3>

        <a href="register_user.php" class="btn btn-dark rounded-pill"> Register new user</a>
        <br><br>
        
        <h3>Log in</h3>
        <!-- Create the action-->

            <!-- These are for error messages: code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/ -->
        <?php if ($message): ?>
            <div style="background-color: <?php echo htmlspecialchars($toastClass); ?>; 
                        color: white; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif;?>

        <form method= "POST">  <!-- change action so you end up somewhere! -->

            <!-- forms for all free text info that is needed-->
            <!-- required so that the field is mandatory before registering -->
            <label for="email">Email adress</label><br>
            <input type="email" class="" name="email" required ><br>

            <label for="password">Password</label><br>
            <input type="password" class="" name="password" minlength="8"  required><br><br>
            <input type="submit" class="btn btn-dark rounded-pill" name="Log in" value="Log in"><br><br>
    
        </div>
    </form>
    
</body>
</html>


