<?php
// Check if the user is logged in

// Helper to check if the user is logged in. Include in the beginning of pages 
// that require user authentication. If the user is not logged in, 
// it will display a message and exit the script. 


// Check if the user is logged in
<<<<<<< Updated upstream
if (!isset($_SESSION['profile_id'])) {
=======
// TODO(schema-migration): $_SESSION['user_id'] becomes $_SESSION['profile_id']
// (set at login, new schema keys on profile_id)
if (!isset($_SESSION['profile_id'])) {
    echo $_SESSION['profile_id'];
>>>>>>> Stashed changes
    echo "You must be logged in to view this page.";
    exit();
}
?>
