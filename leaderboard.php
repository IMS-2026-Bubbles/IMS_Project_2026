<?php
// Leaderboard page

// Arrive from:
// // navigation bar (leaderboard button)
// // leaderboard.php (switching between global, company, and lab leaderboards)
// Action:
// // Display the leaderboard based on the selected scope (global, company, or lab)
// Redirect to:
// // leaderboard.php (after selecting a different scope)
// // navigation bar options

require_once 'session/init.php';
require_once 'session/check_user_logged_in.php';
require_once 'session/check_user_in_company.php';
require_once 'database/db.php';

// Get current logged-in user
$profile_id = $_SESSION['profile_id'];

// Get selected leaderboard type
$scope = 'global';

if (isset($_GET['scope'])) {
    $scope = $_GET['scope'];
}

// Only allow valid leaderboard types, moved if statement to the html section
$leaderboard_types = ['global', 'company'];
if (!in_array($scope, $leaderboard_types)) {
    $scope = 'global';
}

// --------------------------------------------------
// GLOBAL LEADERBOARD
// --------------------------------------------------
if ($scope === 'global') {
    $sql_global =
        'SELECT
                profiles.profile_id,
                profiles.first_name,
                profile_points.scriba_points,
                RANK() OVER (ORDER BY profile_points.scriba_points DESC) AS global_rank, -- Calculate rank based on scriba_points
                PERCENT_RANK() OVER (ORDER BY profile_points.scriba_points DESC) AS global_percent -- Calculate percentile based on scriba_points
            FROM profile_points
            JOIN profiles
                ON profile_points.profile_id = profiles.profile_id
            ORDER BY
                profile_points.scriba_points DESC
        ';
    $stmt_global = $conn->prepare($sql_global);
    $stmt_global->execute();
    $result_global = $stmt_global->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_global->close();

    // Store users in an associative array with profile_id as the key
    $users_global = [];
    foreach ($result_global as $row) {
        $users_global[$row['profile_id']] = [
            'rank' => (int) $row['global_rank'],
            'percent' => round((float) $row['global_percent'] * 100, 2),  // 0.0 = best, Convert to percentage and round to 2 decimal places
            'name' => $row['first_name'],
            'points' => (int) $row['scriba_points']
        ];
    }
    // Filter for user logged in
    $user_viewing_global = $users_global[$profile_id] ?? null;  // Should never be NULL
    // Filter for top 3 users
    $users_global_top3 = array_slice($users_global, 0, 3);
}

// --------------------------------------------------
// COMPANY LEADERBOARD
// --------------------------------------------------
if ($scope === 'company') {
    $sql_company =
        'SELECT DISTINCT
                profiles.profile_id,
                profiles.first_name,
                profiles.last_name,
                profile_points.scriba_points,
                RANK() OVER (ORDER BY profile_points.scriba_points DESC) AS company_rank, -- Calculate rank based on scriba_points
                PERCENT_RANK() OVER (ORDER BY profile_points.scriba_points DESC) AS company_percent -- Calculate percentile based on scriba_points
            FROM profile_points
            JOIN profiles
                ON profile_points.profile_id = profiles.profile_id
            JOIN company_members AS user_company
                ON profile_points.profile_id = user_company.profile_id
            JOIN company_members AS current_user_company
                ON user_company.company_id = current_user_company.company_id
            WHERE current_user_company.profile_id = ?
            ORDER BY
                profile_points.scriba_points DESC
        ';
    $stmt_company = $conn->prepare($sql_company);
    $stmt_company->bind_param('i', $profile_id);
    $stmt_company->execute();
    $result_company = $stmt_company->get_result();
    $stmt_company->close();

    // Store users in an associative array with profile_id as the key
    $users_company = [];
    foreach ($result_company as $row) {
        $users_company[$row['profile_id']] = [
            'rank' => (int) $row['company_rank'],
            'percent' => round((float) $row['company_percent'] * 100, 2),  // 0.0 = best, Convert to percentage and round to 2 decimal places
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'points' => (int) $row['scriba_points']
        ];
    }

    // Calculate stats for the logged-in user
    $user_viewing_company = $users_company[$profile_id] ?? null;  // Should never be NULL
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

<!-- and here aswell, the link to the stylesheet /cornelia  -->
<link rel="stylesheet" href="assets/style.css">

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
        </div>


    <!-- Leaderboard display section -->
    <?php if ($scope === 'global'): ?>
        <!-- Display global leaderboard -->
        <table class="leaderboard-table">

            <thead>
                <tr><th>Rank</th><th>Name</th><th>Points</th></tr>
            </thead>

            <tbody>
                <!-- Display global user leaderboard table -->
                <?php if (count($users_global_top3) > 0): ?>
                    <!-- Display top three users -->
                    <?php foreach ($users_global_top3 as $user): ?>
                        <tr>
                            <td class="rank">
                                <?php echo $user['rank']; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['name']); ?>
                            </td>
                            <td class="points">
                                <?php echo $user['points']; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (isset($user_viewing_global) && !array_key_exists($profile_id, $users_global_top3)): ?>
                        <!-- Display logged-in user (not in top 3) -->
                        <tr><td colspan="3"><hr></td></tr>
                        <tr>
                            <td class="rank">
                                <?php echo $user_viewing_global['rank']; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user_viewing_global['name']); ?>
                            </td>
                            <td class="points">
                                <?php echo $user_viewing_global['points']; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- No users found -->
                    <tr>
                        <td colspan="3">
                            No users found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php endif; ?>
    <?php if ($scope === 'company' && isset($user_viewing_company['percent'])): ?>
        <!-- Display company percentile -->
         <?php if ((int) $user_viewing_company['rank'] === 1): ?>
                <?php echo 'You are ranked ' . $user_viewing_company['rank'] . ' in your company with ' . $user_viewing_company['points'] . ' points.'; ?>
        <?php else: ?>
            <!-- Ugly percentile -->
            <?php echo 'You are ahead of ' . $user_viewing_company['percent'] . '% of your company!'; ?>
    <?php endif; ?>
    <?php elseif ($scope === 'company'): ?>
        <!-- Display message if user is not in on the leaderboard -->
        <p>
            You are not currently on the leaderboard.
        </p>
    <?php endif; ?>
    

    </main>
</body>

</html>