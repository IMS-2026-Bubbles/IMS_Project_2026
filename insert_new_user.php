<?php

if (isset($_POST["register"])) {
    //Checking if the person was accually clicking the submit button

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    //Require will throw an error if unreachable unlike include
    require_once 'Database_related/db.php';
    require_once 'Functional_php/functions.php';

    //Here all error-handeling functions will be

    //Unsure if needed, we have "required" on html-form
    //If the HTML-form was empty, then direct them back to the form
    if (emptyInputSignup($first_name, $last_name, $email, $password) !== False) {
        //should it be emptyinputsignup or emptyinputregister
        header("location: register_user_page.php?error=emptyinput");
        exit();
    }

    if (emailExists($connection, $email) !== False) {
        header("location: register_user_page.php?error=usedemail");
        exit();
    }

    createUser($connection, $first_name, $last_name, $email, $password)
}
else {//Send person back, should not be here
    header("location: register_user_page.php");
    die();
}
?>
