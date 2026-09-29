<?php
// Create a new project action

// Arrive from:
    // project_library.php (create new project form)
// Action:
    // Create a new project in the database
// Redirect to:
    // project_library.php (after creating a new project)



error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session
require_once "session/init.php";
require_once "session/check_user_logged_in.php";
// Connect to database
require_once "database/db.php";


// Add project
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_project"])) {

    $projectName = trim($_POST["project_name"]);
    $labId = trim($_POST["lab_id"]);

    if ($projectName != "" && $labId != "") {

        // Check that the selected lab belongs to one of the user's companies
        $checkSql = "
            SELECT labs.lab_id
            FROM labs
            JOIN company_members
                ON labs.company_id = company_members.company_id
            WHERE labs.lab_id = ?
              AND company_members.profile_id = ?
        ";

        $checkStmt = $conn->prepare($checkSql);

        $checkStmt->bind_param(
            "si",
            $labId,
            $profile_id
        );

        $checkStmt->execute();

        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows == 0) {
            die("You do not have permission to use this lab.");
        }

        $checkStmt->close();


        // Add new project
        $sql = 
            "INSERT INTO projects
            (name, lab_id)
            VALUES (?, ?)
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ss",
            $projectName,
            $labId
        );

        $stmt->execute();

        $newProjectId = $stmt->insert_id;

        $stmt->close();


        // Add current user as project owner
        $sql = 
            "INSERT INTO project_members
            (project_id, profile_id, role)
            VALUES (?, ?, 'owner')
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ii",
            $newProjectId,
            $profile_id
        );

        $stmt->execute();

        $stmt->close();


        // Redirect back to project library
        header("Location: ../project_library.php");
        exit();
    }
}


?>