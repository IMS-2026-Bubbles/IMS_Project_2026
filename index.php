




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

        <form action="" method= "POST">  <!-- change action so you end up somewhere! -->

                    <!-- Throws an error if something is wrong -->
             <?php 
                if(isset($errors) && count($errors) > 0){
                    foreach ($errors as $error) {
                        # code...
                        echo $error . "<br>";
                    }
                }
		     ?>

            <!-- forms for all free text info that is needed-->
            
            <!-- required so that the field is mandatory before registering -->
            <label for="email">Email adress</label><br>
            <input type="email" class="" name="email" required value="<?=isset($_POST['email']) ? $_POST['email'] : '';?>"><br>

            <label for="password">Password</label><br>
            <input type="password" class="" name="password" minlength="8"  required value="<?=isset($_POST['password']) ? $_POST['password'] : '';?>"><br><br>
            
            
            <input type="submit" class="btn btn-dark rounded-pill" name="Log in" value="Log in"><br><br>
    
        </div>
    </form>

    
</body>
</html>


