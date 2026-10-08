<?php
// Project Library (change to library.php?)

// Arrive from: 
    // actions/login.php (after logging in)
    // navigation bar (project library button)
    // project_library.php (after adding a new project)
// Action:
    // Display all projects user has view access to
    // Add new project
// Redirect to:
    // navigation bar options
    // experiment_library.php (after clicking on a project)


# what is this??? - tilda
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session
require_once "session/init.php";
require_once "session/check_user_logged_in.php";

// Connect to database
require_once "database/db.php";


// Get current user
$profile_id = (int) $_SESSION["profile_id"];


// TODO: Refactor search into an include?
// Search projects
$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}



// Get labs from the user's companies
$labs = [];

$sql = "SELECT profiles.profile_id, lab_members.lab_id, labs.name
        FROM profiles
        LEFT JOIN lab_members on profiles.profile_id = lab_members.profile_id
        LEFT JOIN labs on lab_members.lab_id = labs.lab_id
        WHERE profiles.profile_id = ?";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $profile_id
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $labs[] = $row;
}

$stmt->close();


// Get projects from the user's companies
$projects = [];



$sql = "SELECT projects.project_id, projects.lab_id, projects.name, project_members.role, project_members.profile_id  
        FROM projects
        JOIN project_members ON project_members.project_id = projects.project_id
        WHERE project_members.role = 'owner' AND project_members.profile_id = ?";

if ($search != "") {
    $sql .= " AND projects.name LIKE ?";
}

$sql .= " ORDER BY projects.name";


$stmt = $conn->prepare($sql);


if ($search != "") {

    $searchValue = "%" . $search . "%";

    $stmt->bind_param(
        "is",
        $profile_id,
        $searchValue
    );

} else {

    $stmt->bind_param(
        "i",
        $profile_id
    );
}


$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

$stmt->close();

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

    <title>Project Library</title>

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

        /* Add project */

        .add-project {
            height: 48px;
            width: 100%;
            border: 2px solid #263c55;
            border-radius: 18px;
            background: linear-gradient(100deg, #cbd8f2, #ffffff);
            color: #263c55;
            font-size: 20px;
            cursor: pointer;
        }

        .add-project:hover {
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

        .add-select {
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

        /* Project card */

        .project-card {
            position: relative;
            height: 165px;
            display: block;
            background-color: white;
            border: 2px solid #263c55;
            border-radius: 18px;
            text-decoration: none;
            color: #263c55;
        }

        .project-title {
            position: absolute;
            top: -18px;
            left: -18px;
            width: calc(100% - 30px);
            height: 48px;
            display: flex;
            align-items: center;
            padding-left: 18px;
            background: linear-gradient(100deg, #c4d3ee, #ffffff);
            border: 2px solid #263c55;
            border-radius: 17px;
            font-size: 19px;
            font-weight: bold;
        }

        .project-title::before {
            content: "•";
            margin-right: 12px;
            font-size: 22px;
        }

        .project-card:hover {
            background-color: #f8faff;
            color: #263c55;
        }

        /* Lab name */

        .project-lab {
            position: absolute;
            bottom: 15px;
            left: 18px;
            font-size: 15px;
        }

        /* No results */

        .no-results {
            text-align: center;
            font-size: 18px;
            padding: 40px;
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

    <!-- Project Library -->

    <main class="library-container">

        <!-- Search -->

        <form
            action="project_library.php"
            method="GET"
            class="mb-4"
        >

            <input
                type="text"
                name="search"
                class="form-control search-box"
                placeholder="search project"
                value="<?php echo htmlspecialchars($search); ?>"
            >

        </form>

        <!-- Add project button -->

        <button
            type="button"
            class="add-project d-flex justify-content-center align-items-center mb-4"
            onclick="showAddForm()"
        >
            add project
        </button>

        <!-- Add project form -->

        <div
            id="addForm"
            class="add-form"
        >

            <form
                action="actions/create_project.php"
                method="POST"
            >

                <!-- Project name -->

                <div class="mb-3">

                    <input
                        type="text"
                        name="project_name"
                        class="form-control add-input"
                        placeholder="project name"
                        required
                    >

                </div>

                <!-- Lab -->

                <div class="mb-3">

                    <select
                        name="lab_id"
                        class="form-select add-select"
                        required
                    >

                        <option value="">
                            select lab
                        </option>

                        <?php foreach ($labs as $lab): ?>

                            <option
                                value="<?php echo $lab["lab_id"]; ?>"
                            >
                                <?php
                                echo htmlspecialchars($lab["name"]);
                                ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Add button -->

                <button
                    type="submit"
                    name="add_project"
                    class="add-button"
                >
                    Add
                </button>

            </form>

        </div>

        <!-- Project list -->

        <div class="row g-5">

            <?php if (count($projects) > 0): ?>

                <?php foreach ($projects as $project): ?>

                    <div class="col-md-6">

                        <a
                            href="experiment_library.php?project_id=<?php echo $project["project_id"]; ?>"
                            class="project-card"
                        >

                            <div class="project-title">

                                <?php
                                echo htmlspecialchars(
                                    $project["name"]
                                );
                                ?>

                            </div>

                            <!-- <div class="project-lab">

                                <?php
                                // echo htmlspecialchars(
                                //     $project["lab_name"]
                                // );
                                ?>

                            </div> -->

                        </a>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12">

                    <div class="no-results">
                        No projects found.
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