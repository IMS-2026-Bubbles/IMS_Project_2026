<?php
// Leaderboard page

// Arrive from:
// navigation bar (leaderboard button)
// leaderboard.php (switching between global, company, and lab leaderboards)
// Action:
// Display the leaderboard based on the selected scope (global, company, or lab)
// Redirect to:
// leaderboard.php (after selecting a different scope)
// navigation bar options

require_once 'session/init.php';
require_once 'session/check_user_logged_in.php';
require_once 'database/db.php';

// Get current logged-in user
$profile_id = (int) $_SESSION['profile_id'];

// Get selected leaderboard type
$scope = 'global';

if (isset($_GET['scope'])) {
    $scope = $_GET['scope'];
}

// Only allow valid leaderboard types
if (
    $scope !== 'global' &&
    $scope !== 'company' &&
    $scope !== 'lab'
) {
    $scope = 'global';
}

// Store leaderboard users
$users = [];

// --------------------------------------------------
// GLOBAL LEADERBOARD
// --------------------------------------------------

if ($scope === 'global') {
    $sql = '
        SELECT
            profiles.profile_id,
            profiles.first_name,
            profiles.last_name,
            profile_points.scriba_points
        FROM profile_points
        JOIN profiles
            ON profile_points.profile_id = profiles.profile_id
        ORDER BY
            profile_points.scriba_points DESC,
            profiles.first_name ASC,
            profiles.last_name ASC
    ';

    $stmt = $conn->prepare($sql);

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'name' =>
                $row['first_name'],
            'points' =>
                (int) $row['scriba_points']
        ];
    }

    $stmt->close();
}
// --------------------------------------------------
// COMPANY LEADERBOARD
// --------------------------------------------------
elseif ($scope === 'company') {
    $sql = '
        SELECT DISTINCT
            profiles.profile_id,
            profiles.first_name,
            profiles.last_name,
            profile_points.scriba_points
        FROM profile_points

        JOIN profiles
            ON profile_points.profile_id = profiles.profile_id

        JOIN company_members AS user_company
            ON profile_points.profile_id = user_company.profile_id

        JOIN company_members AS current_user_company
            ON user_company.company_id = current_user_company.company_id

        WHERE current_user_company.profile_id = ?

        ORDER BY
            profile_points.scriba_points DESC,
            profiles.first_name ASC,
            profiles.last_name ASC
    ';

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'i',
        $profile_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'name' =>
                $row['first_name'],
            'points' =>
                (int) $row['scriba_points']
        ];
    }

    $stmt->close();
}
// --------------------------------------------------
// LAB GROUP LEADERBOARD
// --------------------------------------------------
elseif ($scope === 'lab') {
    $sql = '
        SELECT DISTINCT
            profiles.profile_id,
            profiles.first_name,
            profiles.last_name,
            profile_points.scriba_points
        FROM profile_points

        JOIN profiles
            ON profile_points.profile_id = profiles.profile_id

        JOIN lab_members AS user_lab
            ON profile_points.profile_id = user_lab.profile_id

        JOIN lab_members AS current_user_lab
            ON user_lab.lab_id = current_user_lab.lab_id

        WHERE current_user_lab.profile_id = ?

        ORDER BY
            profile_points.scriba_points DESC,
            profiles.first_name ASC,
            profiles.last_name ASC
    ';

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'i',
        $profile_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'name' =>
                $row['first_name'],
            'points' =>
                (int) $row['scriba_points']
        ];
    }

    $stmt->close();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Loads Bootstrap -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >

    <title>Leaderboard</title>


    <style>

        /* Page */

        body {

            min-height: 100vh;

            background:
                linear-gradient(
                    120deg,
                    #7794b6,
                    #d4f6fd
                );

            color: #263c55;

        }


        /* Leaderboard */

        .leaderboard-container {

            width: 80%;

            max-width: 1000px;

            margin: 60px auto;

        }


        .leaderboard-title {

            text-align: center;

            font-size: 32px;

            font-weight: bold;

            margin-bottom: 35px;

        }


        /* Leaderboard buttons */

        .leaderboard-buttons {

            display: flex;

            justify-content: center;

            gap: 20px;

            margin-bottom: 35px;

        }


        .leaderboard-button {

            min-width: 150px;

            padding: 10px 25px;

            border: 2px solid #263c55;

            border-radius: 20px;

            background:
                linear-gradient(
                    100deg,
                    #cbd8f2,
                    #ffffff
                );

            color: #263c55;

            font-size: 18px;

            text-decoration: none;

            text-align: center;

        }


        .leaderboard-button:hover {

            background-color: #dce6f8;

            color: #263c55;

        }


        /* Ranking table */

        .leaderboard-table {

            width: 100%;

            background-color: white;

            border: 2px solid #263c55;

            border-radius: 15px;

            overflow: hidden;

        }


        .leaderboard-table th {

            background-color: #cbd8f2;

            color: #263c55;

            padding: 15px;

            text-align: center;

        }


        .leaderboard-table td {

            padding: 15px;

            border-top: 1px solid #d5dce8;

            text-align: center;

        }


        .leaderboard-table th:nth-child(1),
        .leaderboard-table td:nth-child(1) {

            width: 20%;

        }


        .leaderboard-table th:nth-child(2),
        .leaderboard-table td:nth-child(2) {

            width: 50%;

        }


        .leaderboard-table th:nth-child(3),
        .leaderboard-table td:nth-child(3) {

            width: 30%;

        }


        /* Rank and points */

        .rank {

            font-weight: bold;

            text-align: center;

        }


        .points {

            font-weight: bold;

            text-align: center;

        }


        /* Mobile layout */

        @media (max-width: 800px) {

            .leaderboard-container {

                width: 95%;

            }


            .leaderboard-buttons {

                flex-direction: column;

                align-items: center;

            }

        }

    </style>

</head>


<body>


    <!-- Navigation -->

    <?php include 'includes/navbar.php'; ?>


    <!-- Leaderboard -->

    <main class="leaderboard-container">


        <h1 class="leaderboard-title">

            LEADERBOARD

        </h1>


        <!-- Leaderboard filters -->

        <div class="leaderboard-buttons">


            <a
                href="leaderboard.php?scope=global"
                class="leaderboard-button"
            >
                Global
            </a>


            <a
                href="leaderboard.php?scope=company"
                class="leaderboard-button"
            >
                Company
            </a>


            <a
                href="leaderboard.php?scope=lab"
                class="leaderboard-button"
            >
                Lab Group
            </a>


        </div>


        <!-- Ranking table -->

        <table class="leaderboard-table">


            <thead>

                <tr>

                    <th>
                        Rank
                    </th>

                    <th>
                        Name
                    </th>

                    <th>
                        Points
                    </th>

                </tr>

            </thead>


            <tbody>


                <?php if (count($users) > 0): ?>


                    <?php foreach ($users as $index => $user): ?>


                        <tr>


                            <td class="rank">

                                <?php
                                echo $index + 1;
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $user['name']
                                );
                                ?>

                            </td>


                            <td class="points">

                                <?php
                                echo $user['points'];
                                ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <tr>

                        <td colspan="3">

                            No users found.

                        </td>

                    </tr>


                <?php endif; ?>


            </tbody>


        </table>


    </main>


</body>

</html>