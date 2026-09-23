<?php
    include 'Database_related/db.php';

    $message = "";
    $toastClass = "";

    # if button to register new:
    if(isset($_POST['register']))
    {
        # fetch data from POST request
        $First_Name = $_POST['First_Name'];
        $Last_Name = $_POST['Last_Name'];
        $Email = $_POST['Email'];
        //generate salt and add to database
        $Password = $_POST['Password1']; # IMPLEMENT SECURITY HERE
        $hashedPassword = Password_hash($Password, Password_DEFAULT); # ???

        // code from https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/
        // Check if Email already exists
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
            $sql = "INSERT INTO User(First_Name, Last_Name, Email, Password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $First_Name, $Last_Name, $Email, $hashedPassword);
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