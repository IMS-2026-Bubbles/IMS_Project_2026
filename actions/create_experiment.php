<?php
// Create experiment action

// Arrive from:
    // experiment_library.php (create new experiment form)
// Action:
    // Create a new experiment in the database
// Redirect to:
    // experiment_library.php (after creating a new experiment)




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


// Add experiment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_experiment"])) {
    // Get current project
    $project_id = (int) $_POST["project_id"] ?? NULL;

    $experimentName = trim($_POST["experiment_name"]);

    if (!is_null($project_id) && $experimentName != "") {

        // Add new experiment
        $sql = 
        "INSERT INTO experiments
            (name, project_id)
            VALUES (?, ?)
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "si",
            $experimentName,
            $project_id
        );

        $stmt->execute();

        $stmt->close();


        // Return to current project
        header(
            "Location: ../experiment_library.php?project_id=" . urlencode($project_id)
        );

        exit();
    }
}

?>