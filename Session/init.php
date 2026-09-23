<?php
// Start a session

// Start a session if one hasn't been started yet. Guarded against session already existing.
// Call this at the top of every page that uses session (i.e. all of them).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>