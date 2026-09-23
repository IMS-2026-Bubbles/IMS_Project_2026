<?php

// Temporary leaderboard data
$users = [
    [
        "name" => "Alice",
        "email" => "alice@example.com",
        "company" => "Company A",
        "lab_group" => "Lab Group 1",
        "points" => 120
    ],
    [
        "name" => "Bob",
        "email" => "bob@example.com",
        "company" => "Company B",
        "lab_group" => "Lab Group 2",
        "points" => 105
    ],
    [
        "name" => "Charlie",
        "email" => "charlie@example.com",
        "company" => "Company A",
        "lab_group" => "Lab Group 1",
        "points" => 95
    ],
    [
        "name" => "David",
        "email" => "david@example.com",
        "company" => "Company B",
        "lab_group" => "Lab Group 3",
        "points" => 80
    ]
];

// Sort users by points
usort($users, function ($a, $b) {
    return $b["points"] <=> $a["points"];
});

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
            background: linear-gradient(110deg, #e7eefc, #ffffff);
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
            background: linear-gradient(100deg, #cbd8f2, #ffffff);
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

    <?php include "functional_php/navbar.php"; ?>


    <!-- Leaderboard -->

    
    <!-- Leaderboard -->

    <main class="leaderboard-container">

        <h1 class="leaderboard-title">
            LEADERBOARD
        </h1>

        <!-- Leaderboard filters -->

        <div class="leaderboard-buttons">

            <a href="leaderboard.php" class="leaderboard-button">
                Global
            </a>

            <a href="#" class="leaderboard-button">
                Company
            </a>

            <a href="#" class="leaderboard-button">
                Lab Group
            </a>

        </div>

        <!-- Ranking table -->

        <table class="leaderboard-table">

            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Points</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($users as $index => $user): ?>

                    <tr>

                        <td class="rank">
                            <?php echo $index + 1; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($user["name"]); ?>
                        </td>

                        <td class="points">
                            <?php echo $user["points"]; ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </main>
</body>

</html>

