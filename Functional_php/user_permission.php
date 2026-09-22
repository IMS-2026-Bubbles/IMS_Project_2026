<?php
// Check user permission level

// Check what permission levels the user (or guest) has.
// This is a helper function to check if the user has the required permission level 
// for a specific action or page. Takes the database connection, user ID, entity type
// (e.g., 'experiment', 'project', 'lab', 'company', 'scriba'), and entity ID as parameters.
// 
// Permission levels for experiment/project:
// - 'owner' (3) => Full access, can edit and manage the entity
// - 'edit'  (2) => Can edit the entity but not manage it (admin for company/lab = 2)
// - 'read'  (1) => Can view the entity but not edit it
// - 'none'  (0) => No access (admin for Scriba = 0 for privacy reasons)
// 
// Permission levels for admin pages:
// - Scriba
//     - 'admin' (2) => Full access to manage Scriba
// - Company
//     - 'admin' (2) => Add users to company; create and manage labs, projects, and users within the company
//     - 'member' (1) => View labs within the company (not projects or users?)
// - Lab group
//     - 'admin' (2) => Add users from company to lab group; manage projects
//     - 'member' (1) => View projects + users within the lab group
// - None (0) => No access
// 
// Return the permission level as an integer. (split into different functions?)

function check_user_permission(mysqli $conn, string $user_ID, string $entity_type, string $entity_ID): int {
    if ($entity_type === 'experiment') {
        // Check permission for an experiment
            // Create query
        $sql_exp_permission = 
            "SELECT MAX(CASE -- Highest permission => access level
                -- Scriba admin => no access for privacy reasons
                -- Company admin => edit (Company member -> no access)
                WHEN Company_Member.Role = 'admin'    THEN 2
                -- Lab group admin => edit
                WHEN Lab_Group_Member.Role = 'admin'  THEN 2
                -- Lab group member => read
                WHEN Lab_Group_Member.Role = 'member' THEN 1
                -- Project
                WHEN Project_Member.Role = 'owner'    THEN 3
                WHEN Project_Member.Role = 'edit'     THEN 2
                WHEN Project_Member.Role = 'read'     THEN 1
                -- Experiment owner is project owner
                WHEN Experiment_Member.Role = 'edit'  THEN 2
                WHEN Experiment_Member.Role = 'read'  THEN 1
                -- None of the above => no access
                ELSE 0
                END) AS `Access` -- New column named Access to hold the highest permission level
            FROM Proj_Experiment 
            -- Add tables for bridging towards member tables
            JOIN Project
                ON Project.Project_ID = Proj_Experiment.Project_ID
            LEFT JOIN Lab_Group 
                ON Lab_Group.Lab_Group_ID = Project.Lab_Group_ID
            -- Add member tables to get roles and filter by user_ID
            LEFT JOIN Company_Member 
                ON Company_Member.Company_ID = Lab_Group.Company_ID
                AND Company_Member.User_ID = ?
            LEFT JOIN Lab_Group_Member
                ON Lab_Group_Member.Lab_Group_ID = Lab_Group.Lab_Group_ID
                AND Lab_Group_Member.User_ID = ?
            LEFT JOIN Project_Member
                ON Project_Member.Project_ID = Project.Project_ID
                AND Project_Member.User_ID = ?
            LEFT JOIN Experiment_Member
                ON Experiment_Member.Experiment_ID = Proj_Experiment.Experiment_ID
                AND Experiment_Member.User_ID = ?
            -- Filter for the specific experiment
            WHERE Proj_Experiment.Experiment_ID = ?";
            // Prepare query
        $stmt_exp_permission = $conn->prepare($sql_exp_permission);
            // Bind parameters
        $stmt_exp_permission->bind_param("sssss", $user_ID, $user_ID, $user_ID, $user_ID, $entity_ID);
            // Execute query
        if ($stmt_exp_permission->execute()) {
                // Get the result set from the executed query
            $result_exp_permission = $stmt_exp_permission->get_result(); // get_result() returns a mysqli_result object
                // Fetch the permission level from the result set
            $exp_permission_level = $result_exp_permission->fetch_assoc();
                // Check for null, nrow == 0, or missing 'Access' column
            if (!array_key_exists('Access', $exp_permission_level)) {
                // Missing 'Access' column
                throw new RunTimeException("`Access` column missing");
            } elseif ($exp_permission_level['Access'] === null) {
                // 'Access' is null => invalid Experiment_ID
                throw new RunTimeException("Invalid Experiment_ID: " . $entity_ID);
            }
                // Return the permission level
            return (int)$exp_permission_level['Access']; // Need to use (int) to convert from string to integer
        } else {
            throw new RunTimeException("Permission query failed: " . $stmt_exp_permission->error); // Error executing query
        }


    } elseif ($entity_type === 'project') {
        // TODO: Check permission for a project
            // Create query
        $sql_proj_permission = 
            "SELECT MAX(CASE -- Highest permission => access level
                -- Scriba admin => no access for privacy reasons
                -- Company admin => edit (Company member -> no access)
                WHEN Company_Member.Role = 'admin'    THEN 2
                -- Lab group admin => edit
                WHEN Lab_Group_Member.Role = 'admin'  THEN 2
                -- Lab group member => read
                WHEN Lab_Group_Member.Role = 'member' THEN 1
                -- Project
                WHEN Project_Member.Role = 'owner'    THEN 3
                WHEN Project_Member.Role = 'edit'     THEN 2
                WHEN Project_Member.Role = 'read'     THEN 1
                -- None of the above => no access
                ELSE 0
                END) AS `Access`
            FROM Project
            -- Add tables for bridging towards member tables
            LEFT JOIN Lab_Group
                ON Lab_Group.Lab_Group_ID = Project.Lab_Group_ID
            -- Add member tables to get roles and filter by user_ID
            LEFT JOIN Company_Member
                ON Company_Member.Company_ID = Lab_Group.Company_ID
                AND Company_Member.User_ID = ?
            LEFT JOIN Lab_Group_Member
                ON Lab_Group_Member.Lab_Group_ID = Lab_Group.Lab_Group_ID
                AND Lab_Group_Member.User_ID = ?
            LEFT JOIN Project_Member
                ON Project_Member.Project_ID = Project.Project_ID
                AND Project_Member.User_ID = ?
            -- Filter for the specific project
            WHERE Project.Project_ID = ?
            ";
            // Prepare query
        $stmt_proj_permission = $conn->prepare($sql_proj_permission);
            // Bind parameters
        $stmt_proj_permission->bind_param("ssss", $user_ID, $user_ID, $user_ID, $entity_ID);
            // Execute query
        if ($stmt_proj_permission->execute()) {
                // Get the result set from the executed query
            $result_proj_permission = $stmt_proj_permission->get_result(); // get_result() returns a mysqli_result object
            $proj_permission_level = $result_proj_permission->fetch_assoc();
                // Check for null, nrow == 0, or missing 'Access' column
            if (!array_key_exists('Access', $proj_permission_level)) {
                // Missing 'Access' column
                throw new RunTimeException("`Access` column missing");
            } elseif ($proj_permission_level['Access'] === null) {
                // 'Access' is null => invalid Project_ID
                throw new RunTimeException("Invalid Project_ID: " . $entity_ID);
            }
                // Return the permission level
            return (int)$proj_permission_level['Access']; // Need to use (int) to convert from string to integer
        } else {
            throw new RunTimeException("Permission query failed: " . $stmt_proj_permission->error); // Error executing query
        }


    } elseif ($entity_type === 'lab') {
        // TODO: Check permission for a lab
        return 0; // Placeholder return value


    } elseif ($entity_type === 'company') {
        // TODO: Check permission for a company
        return 0; // Placeholder return value


    } elseif ($entity_type === 'scriba') {
        // TODO: Check permission for Scriba
        return 0; // Placeholder return value


    } else {
        // Throw warning for invalid entity type
        trigger_error("Invalid entity type: " . $entity_type, E_USER_WARNING);
        return 0; // Invalid entity type => no access
    }
}
?>