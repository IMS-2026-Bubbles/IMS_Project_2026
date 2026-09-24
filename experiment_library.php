<?php

// Get project ID
$projectId = 1;

if (isset($_GET["project_id"])) {
    $projectId = $_GET["project_id"];
}

// Temporary experiment data
$experiments = [
    [
        "id" => 1,
        "project_id" => 1,
        "name" => "Experiment 1"
    ],
    [
        "id" => 2,
        "project_id" => 1,
        "name" => "Experiment 2"
    ],
    [
        "id" => 3,
        "project_id" => 1,
        "name" => "Experiment 3"
    ],
    [
        "id" => 4,
        "project_id" => 1,
        "name" => "Experiment 4"
    ]
];

// Search experiments
$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

// Filter experiments by project
$projectExperiments = [];

foreach ($experiments as $experiment) {

    if ($experiment["project_id"] == $projectId) {
        $projectExperiments[] = $experiment;
    }
}

// Filter experiments by search
if ($search != "") {

    $filteredExperiments = [];

    foreach ($projectExperiments as $experiment) {

        if (stripos($experiment["name"], $search) !== false) {
            $filteredExperiments[] = $experiment;
        }
    }

    $projectExperiments = $filteredExperiments;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >

    <title>Experiment Library</title>

    <style>

        /* Page */

        body {
            min-height: 100vh;
             background: linear-gradient(120deg, #7794b6, #d4f6fd);
            color: #263c55;
        }

        /* Library */

        .library-container {
            width: 75%;
            max-width: 950px;
            margin: 70px auto;
        }

        .library-title {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 35px;
        }

        /* Search */

        .search-box {
            height: 48px;
            border: 2px solid #263c55;
            border-radius: 18px;
            background: linear-gradient(100deg, #cbd8f2, #ffffff);
            text-align: center;
            font-size: 20px;
            color: #263c55;
        }

        .search-box::placeholder {
            color: #263c55;
        }

        /* Add experiment */

        .add-experiment {
            height: 48px;
            border: 2px solid #263c55;
            border-radius: 18px;
            background: linear-gradient(100deg, #cbd8f2, #ffffff);
            color: #263c55;
            font-size: 20px;
            text-decoration: none;
        }

        .add-experiment:hover {
            background-color: #dce6f8;
            color: #263c55;
        }

        /* Experiment card */

        .experiment-card {
            height: 165px;
            display: block;
            background-color: white;
            border: 2px solid #263c55;
            border-radius: 18px;
            text-decoration: none;
            color: #263c55;
            overflow: hidden;
        }

        .experiment-title {
            width: 100%;
            height: 48px;
            display: flex;
            align-items: center;
            padding-left: 18px;
            background: linear-gradient(100deg, #c4d3ee, #ffffff);
            border-bottom: 2px solid #263c55;
            font-size: 19px;
            font-weight: bold;
            box-sizing: border-box;
        }

        .experiment-title::before {
            content: "•";
            margin-right: 12px;
            font-size: 22px;
        }

        .experiment-card:hover {
            background-color: #f8faff;
            color: #263c55;
        }

        /* No results */

        .no-results {
            text-align: center;
            font-size: 18px;
            padding: 40px;
        }

        /* Back button */

        .back-button {
            display: inline-block;
            margin-bottom: 25px;
            color: #263c55;
            text-decoration: none;
            font-size: 17px;
        }

        .back-button:hover {
            text-decoration: underline;
        }

        /* Mobile layout */

        @media (max-width: 800px) {

            .library-container {
                width: 90%;
            }

        }

    </style>

</head>

<body>

    <!-- Navigation -->

    <?php include "includes/navbar.php"; ?>

    <!-- Experiment Library -->

    <main class="library-container">

        <a
            href="project_library.php"
            class="back-button"
        >
            ← Back to Projects
        </a>

        <h1 class="library-title">
            EXPERIMENT LIBRARY
        </h1>

        <!-- Search -->

        <form
            action="experiment_library.php"
            method="GET"
            class="mb-4"
        >

            <input
                type="hidden"
                name="project_id"
                value="<?php echo htmlspecialchars($projectId); ?>"
            >

            <input
                type="text"
                name="search"
                class="form-control search-box"
                placeholder="search experiment"
                value="<?php echo htmlspecialchars($search); ?>"
            >

        </form>

        <!-- Add experiment -->

        <a
            href="#"
            class="add-experiment d-flex justify-content-center align-items-center mb-5"
        >
            add experiment
        </a>

        <!-- Experiment list -->

        <div class="row g-5">

            <?php if (count($projectExperiments) > 0): ?>

                <?php foreach ($projectExperiments as $experiment): ?>

                    <!-- Experiment -->

                    <div class="col-md-6">

                        <a
                            href="experiment.php?experiment_id=<?php echo $experiment["id"]; ?>"
                            class="experiment-card"
                        >

                            <div class="experiment-title">

                                <?php
                                echo htmlspecialchars($experiment["name"]);
                                ?>

                            </div>

                        </a>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12">

                    <div class="no-results">
                        No experiments found.
                    </div>

                </div>

            <?php endif; ?>

        </div>

    </main>

</body>

</html>