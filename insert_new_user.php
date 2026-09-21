<?php

if (isset($_POST["register"])) {
    //Checking if the person was accually clicking the submit button

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $SSSN = $_POST['SSSN'];
    $company = $_POST['company'];
    $lab_group = $_POST['lab_group'];
    $password = $_POST['password'];

    //Require will throw an error if unreachable unlike include
    require_once 'Database_related/db.php';
    require_once 'Functional_php/functions.php';

    //Here all error-handeling functions will be
    if (condition) {
        # code...
    }


}
else {//Send person back, should not be here
    header("location: register_user_page.php");
    die();
}
?>
