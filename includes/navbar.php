<!-- This file will be included everywhere so that the navigation bar is on every page.
  If changes are done here, they will appear everywhere then.
  Information about navigation bar: https://getbootstrap.com/docs/5.3/components/navbar/ -->

<nav class="navbar navbar-expand-lg scriba-navbar"
    data-bs-theme="light">

<?php
// Collect all routine user security checks as one require_once file in the navbar?
// Then every page "inside" the application uses the navbar and runs the
// security checks. This way we don't have to repeat the same code in every page.

// Check if the user is logged in
// include "session/check_user_logged_in.php";

// Database connection
require_once 'database/db.php';

// Get user access level for the current page
require_once 'check_user_permission.php';
$scriba_admin_access_level =
    check_user_permission(
        $conn,
        $_SESSION['profile_id'],
        'scriba',
        NULL
    );

?>


    <!-- Adds an image -->
    <div class="container-fluid">

        <a class="navbar-brand scriba-navbar-brand">

            <img
                src="assets/logo_updated.png"
                alt="logo"
                width="30"
                height="40"
                class="d-inline-block align-text-middle"
            >

            Scriba

        </a>

     <!-- points for user and company admin, excluding scriba admin -->
            <?php
            // requirement for type of user, ! = 1 excludes scriba admin
            // https://www.php.net/manual/en/language.operators.logical.php
            // https://www.w3schools.com/php/func_mysqli_fetch_assoc.asp

            if ($scriba_admin_access_level != 1 && isset($_SESSION['company_id'])) {  // comp id = all users that belongs to company
                // prepare q, protect ag sql injection, fills the place w the users id in the ? place
                $stmt_navbar_points = $conn->prepare('SELECT scriba_points FROM profile_points WHERE profile_id = ?');
                $stmt_navbar_points->bind_param('i', $_SESSION['profile_id']);
                $stmt_navbar_points->execute();
                // get result
                $row_navbar = $stmt_navbar_points->get_result()->fetch_assoc();
                $navbar_points = $row_navbar['scriba_points'] ?? 0;
                // echo

                echo "<span class='navbar-points'>"
                    . htmlspecialchars((string) $navbar_points)
                    . ' points</span>';
            }
            ?>



        <!-- Add buttons for all pages -->

        <ul class="navbar-nav scriba-nav-links">

            <!-- Company/lab admin page = company/lab admin only -->
            <?php
            // Check if the user is a company admin
            if (isset($_SESSION['company_id'])) {
                $company_admin_access_level =
                    check_user_permission(
                        $conn,
                        $_SESSION['profile_id'],
                        'company',
                        $_SESSION['company_id']
                    );
                if ($company_admin_access_level >= 3) {
                    echo "<li class='nav-item'>
                            <a class='nav-link active' href='company_admin.php'>
                                Company Admin
                            </a>
                          </li>";
                }
            }
            ?>

            <!-- Scriba admin page = scriba admin only -->
            <?php

            // Scriba admin page = scriba admin only
            if ($scriba_admin_access_level == 1) {
                echo "<li class='nav-item'>
                        <a class='nav-link active' href='scriba_admin.php'>
                            Scriba Admin
                        </a>
                      </li>";
            }

            // Home Page/Projects page (must be member of company & lab, no scriba admin)
            if (isset($_SESSION['company_id']) &&
                    isset($_SESSION['lab_id']) &&
                    $scriba_admin_access_level != 1) {
                echo "<li class='nav-item'>
                        <a class='nav-link active' href='library.php'>
                            Home Page/Projects
                        </a>
                      </li>";
            }

            // Leaderboard page (must be member of company & lab, no scriba admin)
            if (isset($_SESSION['company_id']) &&
                    isset($_SESSION['lab_id']) &&
                    $scriba_admin_access_level != 1) {
                echo "<li class='nav-item'>
                        <a class='nav-link active' href='leaderboard.php'>
                            Leaderboard
                        </a>
                      </li>";
            }

            // Profile page (everyone, except if you are a scriba admin)
            if ($scriba_admin_access_level != 1) {
                echo "<li class='nav-item'>
                    <a class='nav-link active' href='user_profile.php'>
                        User Profile
                    </a>
                  </li>";
            }
            ?>

   
            <!-- Logout = everyone -->

            <li class="nav-item">

                <a
                    class="nav-link active logout"
                    href="actions/logout.php"
                >
                    Log out
                </a>

            </li>

        </ul>

    </div>

</nav>