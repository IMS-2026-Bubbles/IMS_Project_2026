 <!-- This file will be included everywhere so that the navigation bar is on every page.
  If changes are done here, they will appear everywhere then. 
  Information about navigation bar: https://getbootstrap.com/docs/5.3/components/navbar/ -->
    
<nav class="navbar navbar-expand-lg" style="background-color: #e3f2fd;" data-bs-theme="light">

<?php
// Collect all routine user security checks as one require_once file in the navbar?
    // Then every page "inside" the application uses the the navbar and runs the
    // security checks. This way we don't have to repeat the same code in every page.

// Check if the user is logged in
// include "session/check_user_logged_in.php"; // Include the user login check function

// Database connection
require_once "database/db.php"; // Include the database connection file

// Get user access level for the current page
require_once "check_user_permission.php"; // Include the user permission check function
    // Needs: $conn, $_SESSION['profile_id'], $entity_type, $entity_id
// $entity_type = $entity_type ?? null; // Get entity type from the page that includes the navbar
// $entity_id = $entity_id ?? null; // Get entity ID from the page that includes the navbar
// Currently used for navbar buttons are:
    // $_SESSION['company_id'] for company admin page
    // $lab_id
    // profile_id to check scriba admin status is already in the session variable $_SESSION['profile_id']
?>

<!-- Create special style for log out button 
 https://www.w3schools.com/howto/howto_css_outline_buttons.asp -->
<style>

.logout {
    border: 1px solid black;
    background-color: #9acaed;
    color: black;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
}
/* This is for when you "touch" the button */
.logout:hover {
    border: 1px solid black;
    background-color: #33709c;
    color: black;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
}
</style>

    
    <!-- Adds an image, here we can add scriba logo later on -->
    <div class="container-fluid"> <!-- fluid = full width-->
        <a class="navbar-brand">
        <img src="assets/logo_updated.png" alt="logo" width="30" height="40" class="d-inline-block align-text-middle">
        Scriba    
        </a>


        <!-- Add buttons for all pages -->
            <!-- Order for Scriba admin: scriba_admin.php, user_profile.php, actions/logout.php -->
            <!-- Order for admin user: company/lab admin page, homepage/projects, leaderboard, profile, logout -->
            <!-- Order for normal user: homepage/projects, leaderboard, profile, logout -->
        <ul class="navbar-nav">
        <!-- Company/lab admin page = company/lab admin only -->
            <?php

            // -------------------------- TEMP DEBUG - remove when done -------------
            echo "<pre>";
            echo "company_id set? "; var_dump(isset($company_id));
            echo "company_id value: "; var_dump($company_id ?? null);
            echo "profile_id: "; var_dump($_SESSION['profile_id'] ?? null);
            echo "DEBUG company_id=" . var_export($company_id ?? null, true) . "<br>";

            // -----------------------------------------------------------------

            if (isset($company_id)) {
                $company_admin_access_level = check_user_permission($conn, $_SESSION['profile_id'], 'company', $_SESSION['company_id']); 
                if ($company_admin_access_level >= 3) { // 3 = admin access level
                    echo "<li class='nav-item'>
                            <a class='nav-link active' href='company_admin.php'>Company Admin</a>
                          </li>";
                }
            }

            // if (isset($lab_id)) {
            //     $lab_admin_access_level = check_user_permission($conn, $_SESSION['profile_id'], 'lab', $lab_id);
            //     if ($lab_admin_access_level >= 3) { // 3 = admin access level
            //         echo "<li class='nav-item'>
            //                 <a class='nav-link active' href='lab_admin.php'>Lab Admin</a>
            //               </li>";
            //     }
            // }
            ?>
        <!-- Scriba admin page = scriba admin only -->
            <?php
            $scriba_admin_access_level = check_user_permission($conn, $_SESSION['profile_id'], 'scriba', NULL); // NULL = no specific entity ID for scriba
            if ($scriba_admin_access_level == 1) { // 1 = is_scriba_admin access level
                echo "<li class='nav-item'>
                        <a class='nav-link active' href='scriba_admin.php'>Scriba Admin</a>
                      </li>";
            
            
            } else { // Not scriba admin, show buttons for everyone else
                // <!-- Home Page/Projects = everyone (not scriba) -->
                echo "<li class='nav-item'>
                        <a class='nav-link active' href='project_library.php'>Home Page/Projects</a>
                      </li>";
                // <!-- Leaderboard = everyone (not scriba) -->
                echo "<li class='nav-item'>
                        <a class='nav-link active' href='leaderboard.php'>Leaderboard</a>
                      </li>";
                
                // <!-- Profile page = everyone (not scriba) -->
                echo "<li class='nav-item'>
                        <a class='nav-link active' href='user_profile.php'>User Profile</a> 
                        </li>";
            }
            ?>
       
        
        <!-- Logout = everyone -->
            <li class="nav-item">
                <a class="nav-link active logout" href="actions/logout.php">Log out</a> 
            </li>
        </ul>        

    </div>
</nav>