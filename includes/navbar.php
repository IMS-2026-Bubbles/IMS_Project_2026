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


        <!-- Add buttons for all pages -->

        <ul class="navbar-nav scriba-nav-links">

            <!-- Company/lab admin page = company/lab admin only -->

            <?php

            // $company_id = $_SESSION['company_id'] ?? null;

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

            $scriba_admin_access_level =
                check_user_permission(
                    $conn,
                    $_SESSION['profile_id'],
                    'scriba',
                    NULL
                );

            if ($scriba_admin_access_level == 1) {
                echo "<li class='nav-item'>
                        <a class='nav-link active' href='scriba_admin.php'>
                            Scriba Admin
                        </a>
                      </li>";
            } else {
                // Home Page/Projects

                echo "<li class='nav-item'>
                        <a class='nav-link active' href='library.php'>
                            Home Page/Projects
                        </a>
                      </li>";

                // Leaderboard

                echo "<li class='nav-item'>
                        <a class='nav-link active' href='leaderboard.php'>
                            Leaderboard
                        </a>
                      </li>";

                // Profile page

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