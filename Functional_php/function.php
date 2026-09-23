<?php 


function emptyInputSignup($first_name, $last_name, $email, $password) {
    $result;
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $result = true;
    }
    else {
        $result = false;
    }
    return $result;
}

function emailExists($connection, $email) {
    $sql = "SELECT * FROM users WHERE email = ?;";
}