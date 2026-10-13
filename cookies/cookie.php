<?php
//save the cookie with profile_id
$cookie_name = "profile_id";

//set value as the profile_id in the session or set it as blank
$cookie_value = $_SESSION['profile_id'] ?? null;

//if the cookie is not already created, create it and make it valid for 1 day
if(!isset($_COOKIE[$cookie_name])) {
    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "../"); // 86400 = 1 day
}
//echo "$cookie_value";

?>
