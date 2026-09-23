
<!DOCTYPE html>
<html lang="en">
    <link rel="stylesheet" href="functional_php/style.css"> <!-- We can define our own design in this -->
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
        <h3>Enter Log in Credentials</h3>
        <!-- Create the action-->

        <form action="" method= "POST">  <!-- change action so you end up somewhere! -->

            <!-- forms for all free text info that is needed-->
            <!-- required so that the field is mandatory before registering -->

            <label for="email">Email adress</label><br>
            <input type="text" class="" name="email" required><br>

            <!-- setting type as password makes characters hidden + supports password control -->
            <label for="password">Password</label><br>
            <input type="password" class="" name="password" minlength= "8" required> <br><br>

            <input type="submit" class="btn btn-dark rounded-pill" name="Llog in" value="Log in"><br><br>
    
        </div>
    </form>

    
</body>
</html>


