<?php
// Check if the user is logged in

// Helper to check if the user is logged in. Include in the beginning of pages 
// that require user authentication. If the user is not logged in, 
// it will display a message and exit the script. 


// Check if the user is logged in
// TODO(schema-migration): $_SESSION['user_id'] becomes $_SESSION['profile_id']
// (set at login, new schema keys on profile_id)
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view this page.";
    exit();
}
?>
