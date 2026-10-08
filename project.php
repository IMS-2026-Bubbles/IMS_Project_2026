<?php
// Experiment Library Page (change to project.php?)

// Arrive from:
    // library.php (click on project link)
    // actions/create_experiment.php (after creating a new experiment)
// Action:
    // Display a list of experiments for the selected project
    // Add experiments to the project if the user has permission
// Redirect to:
    // navbar options
    // experiment.php (click on experiment link)
    // actions/create_experiment.php (add experiment form submission)
    // library.php (click on "Back to library" link)



error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session
require_once "session/init.php";
require_once "session/check_user_logged_in.php";
// Connect to database
require_once "database/db.php";

require_once "includes/log_activity.php"; // Include the log_activity function

// Get current user
$profile_id = (int) $_SESSION["profile_id"];


// Get project ID
$project_id = 0;

if (isset($_GET["project_id"])) {
    $project_id = (int) $_GET["project_id"];
}

if (isset($_POST["project_id"])) {
    $project_id = (int) $_POST["project_id"];
}


// Check that the project belongs to the user's company
if ($project_id > 0) {

    $checkSql = "
        SELECT projects.project_id
        FROM projects
        JOIN labs
            ON projects.lab_id = labs.lab_id
        JOIN company_members
            ON labs.company_id = company_members.company_id
        WHERE projects.project_id = ?
          AND company_members.profile_id = ?
    ";

    $checkStmt = $conn->prepare($checkSql);

    $checkStmt->bind_param(
        "ii",
        $project_id,
        $profile_id
    );

    $checkStmt->execute();

    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows == 0) {
        // Log the access denied attempt
        log_activity(
            $conn,
            $profile_id,
            'project',
            $project_id,
            'access_denied',
            'User attempted to access project without permission'
        );

        die("You do not have permission to access this project.");
    }

    $checkStmt->close();
}


// // Add experiment
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_experiment"])) {

//     $experimentName = trim($_POST["experiment_name"]);

//     if ($project_id > 0 && $experimentName != "") {

//         // Add new experiment
//         $sql = "
//             INSERT INTO experiments
//             (name, project_id)
//             VALUES (?, ?)
//         ";

//         $stmt = $conn->prepare($sql);

//         $stmt->bind_param(
//             "si",
//             $experimentName,
//             $project_id
//         );

//         $stmt->execute();

//         $stmt->close();


//         // Return to current project
//         header(
//             "Location: project.php?project_id=" . $project_id
//         );

//         exit();
//     }
// }


// Search experiments
$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}


// Get experiments
$projectExperiments = [];

if ($project_id > 0) {

    $sql = "
        SELECT
            experiments.experiment_id,
            experiments.name
        FROM experiments
        JOIN projects
            ON experiments.project_id = projects.project_id
        JOIN labs
            ON projects.lab_id = labs.lab_id
        JOIN company_members
            ON labs.company_id = company_members.company_id
        WHERE company_members.profile_id = ?
          AND projects.project_id = ?
    ";

    if ($search != "") {
        $sql .= " AND experiments.name LIKE ?";
    }

    $sql .= " ORDER BY experiments.name";


    $stmt = $conn->prepare($sql);


    if ($search != "") {

        $searchValue = "%" . $search . "%";

        $stmt->bind_param(
            "iis",
            $profile_id,
            $project_id,
            $searchValue
        );

    } else {

        $stmt->bind_param(
            "ii",
            $profile_id,
            $project_id
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

        <!-- Back -->

        <a
            href="library.php"
            class="back-button"
        >
            ← Back to Projects
        </a>

        <h1 class="library-title">
            EXPERIMENT LIBRARY
        </h1>

        <!-- Search -->

        <form
            action="project.php"
            method="GET"
            class="mb-4"
        >

            <input
                type="hidden"
                name="project_id"
                value="<?php echo htmlspecialchars($project_id); ?>"
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
                action="actions/create_experiment.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="project_id"
                    value="<?php echo htmlspecialchars($project_id); ?>"
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

                    <!-- Experiment -->

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