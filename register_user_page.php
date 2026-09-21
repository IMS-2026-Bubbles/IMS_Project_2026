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
    
     <!-- Create the action + call function to check if passwords match-->
    <form action="/insert_new_user.php" method= "POST" onsubmit ="return checkPassword(this)">  <!-- change action so you end up somewhere! -->
        
        <!-- forms for all free text info that is needed-->
        <!-- required so that the field is mandatory before registering -->
        <label for="first_name">First name</label><br>
        <input type="text" class="" name="first_name"  required><br>

        <label for="last_name">Last name</label><br>
        <input type="text" class="" name="last_name"  required><br>

        <label for="email">Email adress</label><br>
        <input type="email" class="" name="email"  required><br> <!-- @ is needed -->

        
        <label for="text">Swedish social security number</label><br>
        <input type="number" class="" name="SSSN" placeholder = "YYMMDD-XXXX" pattern = "[0-9]{6}-[0-9]{4}" required><br><br> <!-- fix so that the correct style is used -->

        <!-- fix so it is not hardcoded once database is up!! -->
        <p>this hardcoded approach will be fixed once database is up and we have decided on what approach to labgroups and companies</p>
        <label for="company">Place of work</label><br>
        <select name="company" class="" required>
            <option value="" selected disabled>Select an option</option> <!-- so you have to choose -->
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

        <input type="submit" class="btn btn-dark rounded-pill" name="register" value="regsiter"><br><br>
    </form>

    <!-- https://www.geeksforgeeks.org/javascript/password-matching-using-javascript/ -->
    <script>
        // Function to check Whether both passwords is same or not.
        function checkPassword(form) {
            password1 = form.password1.value;
            password2 = form.password2.value;

            // If Not same return False.    
            else if (password1 != password2) {
                // This pops up and the request is not submitted
                alert("\nPassword did not match: Please try again...")
                return false;
            }

        }
    </script>

    
</body>
</html>

