<?php
//save the cookie with profile_id
$cookie_name = "profile_id";

//set value as the profile_id in the session or set it as blank
$cookie_value = $_SESSION['profile_id'] ?? '';

if (($_COOKIE['CookieBy'] ?? '') === 'Scriba') {
//if the cookie is not already created, create it and make it valid for 1 day
    if($cookie_value !== '' && ($_COOKIE['profile_id'] ?? '') !== (string)$cookie_value) {
        setcookie($cookie_name, (string)$cookie_value, time() + (86400 * 30),'/'); // 86400 = 1 day
    }
}
?>
