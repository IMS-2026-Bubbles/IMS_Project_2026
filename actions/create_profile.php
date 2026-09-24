<?php
    require_once '../database/db.php';

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
        $checkEmailStmt = $conn->prepare("SELECT Email FROM users WHERE Email = ?");
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
            $sql = "INSERT INTO users (Email, First_Name, Last_Name, Password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            //$Salt = random_bytes($numberOfDesiredBytes); 
            $hashedPassword = password_hash($Password, PASSWORD_BCRYPT);
            $stmt->bind_param("ssss", $Email, $First_Name, $Last_Name, $hashedPassword);
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
        include '../database/close_db.php';
    
        # redirect here instead of in the form down below
        # now the form is sent as a post, it would not be otherwise
        if (isset($result) && $result) {
            header("Location: ../user_profile.php");
            exit;
        }
    }
    
?>