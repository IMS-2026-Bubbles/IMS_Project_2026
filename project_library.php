<?php

// Temporary project data
$projects = [
    [
        "id" => 1,
        "name" => "Project 1"
    ],
    [
        "id" => 2,
        "name" => "Project 2"
    ],
    [
        "id" => 3,
        "name" => "Project 3"
    ],
    [
        "id" => 4,
        "name" => "Project 4"
    ]
];

// Search projects
$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

// Filter projects
if ($search != "") {

    $filteredProjects = [];

    foreach ($projects as $project) {

        if (stripos($project["name"], $search) !== false) {
            $filteredProjects[] = $project;
        }
    }

    $projects = $filteredProjects;
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

    <title>Project Library</title>


    <style>

        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #7794b6, #d4f6fd);
            color: #263c55;
        }

        .library-container {
            width: 75%;
            max-width: 950px;
            margin: 70px auto;
        }

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

        .add-project {
            height: 48px;
            border: 2px solid #263c55;
            border-radius: 18px;
            background: linear-gradient(100deg, #cbd8f2, #ffffff);
            color: #263c55;
            font-size: 20px;
            text-decoration: none;
        }

        .add-project:hover {
            background-color: #dce6f8;
            color: #263c55;
        }

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

        .no-results {
            text-align: center;
            font-size: 18px;
            padding: 40px;
        }

        @media (max-width: 800px) {

            .library-container {
                width: 90%;
            }

        }

    </style>

</head>


<body>


    <!-- Navigation -->

    <?php include "functional_php/navbar.php"; ?>


    <!-- Library -->

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


        <!-- Add project -->

        <a
            href="#"
            class="add-project d-flex justify-content-center align-items-center mb-5"
        >
            add project
        </a>


        <!-- Project list -->

        <div class="row g-5">


            <?php if (count($projects) > 0): ?>


                <?php foreach ($projects as $project): ?>


                    <!-- Project -->

                    <div class="col-md-6">

                        <a
                            href="experiment_library.php?project_id=<?php echo $project["id"]; ?>"
                            class="project-card"
                        >

                            <div class="project-title">

                                <?php
                                echo htmlspecialchars($project["name"]);
                                ?>

                            </div>

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


</body>

</html>