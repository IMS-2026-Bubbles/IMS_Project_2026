<?php

if (isset($_POST["register"])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $SSSN = $_POST['SSSN'];
    $company = $_POST['company'];
    $lab_group = $_POST['lab_group'];
    $password = $_POST['password'];

    require_once 'Database_related/db.php';
    require_once 'Functional_php/functions.php';

    if (condition) {
        # code...
    }


}
else {
    header("location: register_user_page.php");
    die();
}
?>
