

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require_once "session/init.php"; // Start the session and initialize session variables
// No check for user logged in here, front page
    require_once 'database/db.php';

    $message = "";
    $toastClass = "";
    $logincredentials = False;

    if(isset($_POST["login"]))
        {
            # fetch data from POST request
            $email = $_POST['email'];
            $password = $_POST['password'];

            // code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/
            // Check if email already exists
            // TODO(schema-migration): Profiles table becomes profiles; email becomes email
            $sql = "SELECT * FROM profiles WHERE email = ?";
            $checkemailStmt = $conn->prepare($sql);
            $checkemailStmt->bind_param("s", $email);
            $checkemailStmt->execute();
            $checkemailStmt->store_result();
            error_log("Checking email [$email], num_rows = " . $checkemailStmt->num_rows);


            // check if the number of rows are more than 0 => email exists
            if ($checkemailStmt->num_rows < 1) {
                $message = "Invalid email or password.";
                $toastClass = "#dc3545"; // Danger color
            } 

            else {
                    # use placeholders to protect against sql injection
                    // TODO(schema-migration): becomes INSERT INTO profiles
                    // for the new columns (agreed_to_toc, saved_changes, streak) and write
                    // a login_log row / set last_login_at per ARCHITECTURE.md TODOs

                    $sql = "SELECT password, profile_id FROM profiles WHERE email = ?";
                                // adding profile_id so I can get/use it later. How do we
                                // make it so I can use it later? -RH
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $profile = $result->fetch_assoc();

                    if (password_verify($password, $profile['password'])) {
                            //correct log in info
                            $logincredentials = True;
                        }
                        
                    else {
                        $message = "Invalid email or password.";
                        $toastClass = "#dc3545"; // Danger color
                        }
                
                    $stmt->close();
                    
                }
        
            # redirect here instead of in the form down below
            # now the form is sent as a post, it would not be otherwise
            // Note from Rasmus
            // Adding a decision tree for what type of account you are logging into,
                // and redirecting accordingly. Using $profile['profile_id'] (see above).
                // Scriba admin => scriba_admin.php
                // User with company_id => project_library.php
                // User that doesn't belong to a company => user_profile.php
            if ($logincredentials) {
                // Since login = success, add profile_id to session so we can access it on other pages.
                $_SESSION['profile_id'] = $profile['profile_id'];
                // Add company_id to session if user has one. 

                // Fetch the user's affiliations from the database. Need if they are scriba admin
                // and if they belong to at least one company.
                $profile_id = $profile['profile_id'];
                require_once 'includes/fetch_user_affiliation.php'; // expects $conn and $profile_id

                // Scriba admin
                $user_is_scriba_admin = $user_affiliations['is_scriba_admin'] ?? 0; // One value, 1 or 0

                // User with any company_id
                $user_company_ids = $user_affiliations['companies']; // array of company_ids, empty if none

                if ($user_is_scriba_admin == 1) { // Scriba admin => scriba_admin.php
                    header ("Location:scriba_admin.php");
                    exit();
                } elseif (count($user_company_ids) > 0) { // User with company_id => project_library.php
                    header ("Location:project_library.php");
                    exit();
                } else { // User that doesn't belong to a company => user_profile.php
                    header ("Location:user_profile.php");
                    exit();
                }


                // header ("Location:project_library.php");
                // exit();
            }

            $checkemailStmt->close();
            include 'database/close_db.php';
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
            min-height: 100vh;
            background: linear-gradient(120deg, #7794b6, #d4f6fd);
            color: #263c55;
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
            <input type="submit" class="btn btn-dark rounded-pill" name="login" value="Log in"><br><br>
    
        </div>
    </form>
    
</body>
</html>


