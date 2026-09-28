<?php
// Doesn't need session, login check, or database connection
    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: ../index.php");
    exit();
?>