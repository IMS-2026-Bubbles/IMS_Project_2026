<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session
require_once "session/init.php";
require_once "session/check_user_logged_in.php"; // Check if the user is logged in

// Connect to database
require_once "database/db.php";

// Get project ID
$projectId = 0;

if (isset($_GET["project_id"])) {
    $projectId = (int) $_GET["project_id"];
}

if (isset($_POST["project_id"])) {
    $projectId = (int) $_POST["project_id"];
}

// Add experiment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_experiment"])) {

    $experimentName = trim($_POST["experiment_name"]);

    if ($projectId > 0 && $experimentName != "") {

        $sql = "
            INSERT INTO experiments
            (name, project_id)
            VALUES (?, ?)
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "si",
            $experimentName,
            $projectId
        );

        $stmt->execute();

        $stmt->close();

        // Return to current project
        header(
            "Location: experiment_library.php?project_id=" . $projectId
        );

        exit();
    }
}

// Search experiments
$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

// Get experiments
$projectExperiments = [];

if ($projectId > 0) {

    $sql = "
        SELECT
            experiment_id,
            name
        FROM experiments
        WHERE project_id = ?
    ";

    if ($search != "") {
        $sql .= " AND name LIKE ?";
    }

    $sql .= " ORDER BY name";

    $stmt = $conn->prepare($sql);

    if ($search != "") {

        $searchValue = "%" . $search . "%";

        $stmt->bind_param(
            "is",
            $projectId,
            $searchValue
        );

    } else {

        $stmt->bind_param(
            "i",
            $projectId
        );
    }

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $projectExperiments[] = $row;
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
            width: 100%;
            border: 2px solid #263c55;
            border-radius: 18px;
            background: linear-gradient(100deg, #cbd8f2, #ffffff);
            color: #263c55;
            font-size: 20px;
            cursor: pointer;
        }

        .add-experiment:hover {
            background: #dce6f8;
            color: #263c55;
        }

        /* Add form */

        .add-form {
            display: none;
            margin-top: 15px;
            margin-bottom: 35px;
            padding: 20px;
            border: 2px solid #263c55;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.8);
        }

        .add-input {
            height: 48px;
            border: 2px solid #263c55;
            border-radius: 15px;
            font-size: 18px;
            color: #263c55;
        }

        .add-button {
            height: 48px;
            border: 2px solid #263c55;
            border-radius: 15px;
            background: #7794b6;
            color: white;
            font-size: 18px;
            padding-left: 25px;
            padding-right: 25px;
        }

        .add-button:hover {
            background: #263c55;
            color: white;
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

        /* Mobile */

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

        <!-- Back -->

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

        <button
            type="button"
            class="add-experiment d-flex justify-content-center align-items-center mb-4"
            onclick="showAddForm()"
        >
            add experiment
        </button>

        <!-- Add form -->

        <div
            id="addForm"
            class="add-form"
        >

            <form
                action="experiment_library.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="project_id"
                    value="<?php echo htmlspecialchars($projectId); ?>"
                >

                <input
                    type="text"
                    name="experiment_name"
                    class="form-control add-input mb-3"
                    placeholder="experiment name"
                    required
                >

                <button
                    type="submit"
                    name="add_experiment"
                    class="add-button"
                >
                    Add
                </button>

            </form>

        </div>

        <!-- Experiment list -->

        <div class="row g-5">

            <?php if (count($projectExperiments) > 0): ?>

                <?php foreach ($projectExperiments as $experiment): ?>

                    <div class="col-md-6">

                        <a
                            href="experiment.php?experiment_id=<?php echo $experiment["experiment_id"]; ?>"
                            class="experiment-card"
                        >

                            <div class="experiment-title">

                                <?php
                                echo htmlspecialchars(
                                    $experiment["name"]
                                );
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

    <script>

        function showAddForm() {

            var form = document.getElementById("addForm");

            if (form.style.display === "none" || form.style.display === "") {

                form.style.display = "block";

            } else {

                form.style.display = "none";

            }

        }

    </script>

</body>

</html>