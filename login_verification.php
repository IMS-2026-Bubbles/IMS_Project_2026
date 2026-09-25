<?php

if(isset($_POST['Log in']))
    {
        # fetch data from POST request
        $email = $_POST['email'];
        $password = $_POST['password1']; //Also unsure of how to send password

        

        // code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/
        // Check if email already exists
        // TODO(schema-migration): Profiles table becomes profiles; email becomes email
        $checkemailStmt = $conn->prepare("SELECT email FROM profiles WHERE email = ?");
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
            $sql = "SELECT password FROM profiles WHERE profiles['email'] = ?";
            $stmt = $conn->prepare($sql);
            $checkemailStmt->bind_param("s", $email); //unsure
            $checkemailStmt->execute();
            $checkemailStmt->store_result();
            $result = password_verify($password, $checkemailStmt);

            if ($result) {
                    //correct log in info
                    header ("Location: Project_library.php");
                    exit();
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
            exit;
        }
    }
    
?>