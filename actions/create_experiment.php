<?php
// Create experiment action

// Arrive from:
    // project.php (create new experiment form)
// Action:
    // Create a new experiment in the database
// Redirect to:
    // project.php (after creating a new experiment)




error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session
require_once "../session/init.php";
require_once "../session/check_user_logged_in.php";
// Connect to database
require_once "../database/db.php";

require_once '../includes/log_activity.php'; // provides log_activity() function

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

        // Log experiment creation
        if ($stmt->affected_rows > 0) {
            log_activity(
                $conn,
                $_SESSION['profile_id'], // profile ID who performed the action
                'experiment', // entity type
                $conn->insert_id, // entity ID (experiment ID) of the newly created experiment
                'create', // activity type
                "Successfully created experiment $conn->insert_id with name $experimentName in project $project_id" // detail
            );
        } else {
            log_activity(
                $conn,
                $_SESSION['profile_id'], // profile ID who performed the action
                'experiment', // entity type
                NULL, // entity ID (experiment ID) not available since creation failed
                'create', // activity type
                "Failed to create experiment with name $experimentName in project $project_id: " . $stmt->error // detail
            );
        }

        $stmt->close();


        // Return to current project
        header(
            "Location: ../project.php?project_id=" . urlencode($project_id)
        );

        exit();
    }
}

?>