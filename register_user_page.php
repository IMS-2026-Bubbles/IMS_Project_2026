
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
    <title>Register account</title> <!-- This is what you see on the tab in safari/chrome -->
</head>
<body>
    <h1>Welcome to Scriba!</h1>
    <h2>Add the following information to create an account</h2>
    
     <!-- Create the action-->
    <form action="" method= "POST">  <!-- change action so you end up somewhere! -->

        <!-- forms for all free text info that is needed-->
        <!-- required so that the field is mandatory before registering -->
        <label for="first_name">First name</label><br>
        <input type="text" class="" name="first_name" required><br>

        <label for="last_name">Last name</label><br>
        <input type="text" class="" name="last_name" required><br>

        <label for="email">Email adress</label><br>
        <input type="text" class="" name="email" required><br>

        <!-- fix so it is not hardcoded once database is up!! -->
        <label for="company">Company/Workplace</label><br>
        <select name="company" class="" required>
            <option value="" selected disabled>Select a company</option> <!-- so you have to choose -->
            <option> Company A </option>
            <option> Company B </option>
            <option> Company C </option>
            <option> Company D </option>
            <option> Company E </option>
        </select><br>

        <!-- fix so it is not hardcoded once database is up!! -->
        <!-- this one depends on company chosen -->
        <label for="lab_group">Lab group</label><br>
        <select name="lab_group" class="" required>
            <option value="" selected disabled>Select lab group</option> <!-- so you have to choose -->
            <option> Group A </option>
            <option> Group B </option>
            <option> Group C </option>
            <option> Group D </option>
            <option> Group E </option>
            <option> No group </option>
        </select><br><br>

        <!-- setting type as password makes characters hidden + supports password control -->
        <label for="password">Password</label><br>
        <input type="password" class="" name="password1" minlength= "8" required> <br> <!-- must use 8 characters -->

        <label for="password2">Repeat password</label><br>
        <input type="password" class="" name="password2" minlength= "8" required><br><br>

        <input type="submit" class="btn btn-dark rounded-pill" name="register" value="Register"><br><br>
    </div>
    </form>

    
</body>
</html>


