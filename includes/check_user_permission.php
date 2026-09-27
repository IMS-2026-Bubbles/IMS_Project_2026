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
// - Company
//     - 'admin' (2) => Add users to company; create and manage labs, projects, and users within the company
//     - 'member' (1) => View labs within the company (not projects or users?)
// - Lab
//     - 'admin' (2) => Add users from company to lab; manage projects
//     - 'member' (1) => View projects + users within the lab
// - None (0) => No access
// 
// Permission levels for Scriba admin pages:
// - Scriba
//     - 'admin' (1) => Full access to manage Scriba (0 = no access)
// 
// Return the permission level as an integer. (split into different functions?)

function check_user_permission(mysqli $conn, string $profile_id, string $entity_type, string|NULL $entity_id): int {
    // Check if $profile_id isset/exists
    $profile_id = $profile_id ?? null;
    if ($profile_id === null) {
        // Profile ID is null => no access
        return 0;
    }
        // Create query
    $sql_check_profile = 
    "SELECT profile_id FROM profiles WHERE profile_id = ?";
        // Prepare query
    $stmt_check_profile = $conn->prepare($sql_check_profile);
        // Bind parameters
    $stmt_check_profile->bind_param("s", $profile_id);
        // Execute query
    if ($stmt_check_profile->execute()) {
        // Get the result set from the executed query
        $result_check_profile = $stmt_check_profile->get_result();
        // Check if the profile_id exists
        if ($result_check_profile->num_rows === 0) {
            return 0; // Profile does not exist => no access
        } else {
            // Profile exists, continue to check permissions
        }
    } else {
        throw new RunTimeException("Profile check query failed: " . $stmt_check_profile->error);
    }

    // Check the permission level for the given entity type and ID
    if ($entity_type === 'experiment') {
        // Check permission for an experiment
            // Create query
        $sql_exp_permission = 
            "SELECT MAX(CASE -- Highest permission => access level
                -- Scriba admin => no access for privacy reasons
                -- Company admin => edit (Company member -> no access)
                WHEN company_members.role = 'admin'    THEN 2
                -- Lab group admin => edit
                WHEN lab_members.role = 'admin'  THEN 2
                -- Lab group member => read
                WHEN lab_members.role = 'member' THEN 1
                -- Project
                WHEN project_members.role = 'owner'    THEN 3
                WHEN project_members.role = 'edit'     THEN 2
                WHEN project_members.role = 'read'     THEN 1
                -- Experiment owner is project owner
                WHEN experiment_members.role = 'edit'  THEN 2
                WHEN experiment_members.role = 'read'  THEN 1
                -- None of the above => no access
                ELSE 0
                END) AS `access` -- New column named Access to hold the highest permission level
            FROM experiments 
            -- Add tables for bridging towards member tables
            JOIN projects
                ON projects.project_id = experiments.project_id
            LEFT JOIN labs 
                ON labs.lab_id = projects.lab_id
            -- Add member tables to get roles and filter by user_ID
            LEFT JOIN company_members 
                ON company_members.company_id = labs.company_id
                AND company_members.profile_id = ?
            LEFT JOIN lab_members
                ON lab_members.lab_id = labs.lab_id
                AND lab_members.profile_id = ?
            LEFT JOIN project_members
                ON project_members.project_id = projects.project_id
                AND project_members.profile_id = ?
            LEFT JOIN experiment_members
                ON experiment_members.experiment_id = experiments.experiment_id
                AND experiment_members.profile_id = ?
            -- Filter for the specific experiment
            WHERE experiments.experiment_id = ?";
            // Prepare query
        $stmt_exp_permission = $conn->prepare($sql_exp_permission);
            // Bind parameters
        $stmt_exp_permission->bind_param("sssss", $profile_id, $profile_id, $profile_id, $profile_id, $entity_id);
            // Execute query
        if ($stmt_exp_permission->execute()) {
                // Get the result set from the executed query
            $result_exp_permission = $stmt_exp_permission->get_result(); // get_result() returns a mysqli_result object
                // Fetch the permission level from the result set
            $exp_permission_level = $result_exp_permission->fetch_assoc();
                // Check for null, nrow == 0, or missing 'access' column
            if (!array_key_exists('access', $exp_permission_level)) {
                // Missing 'access' column
                throw new RunTimeException("`access` column missing");
            } elseif ($exp_permission_level['access'] === null) {
                // 'access' is null => invalid Experiment_ID
                throw new RunTimeException("Invalid Experiment_ID: " . $entity_id);
            }
                // Return the permission level
            return (int)$exp_permission_level['access']; // Need to use (int) to convert from string to integer
        } else {
            throw new RunTimeException("Permission query failed: " . $stmt_exp_permission->error); // Error executing query
        }


    } elseif ($entity_type === 'project') {
        // Check permission for a project
            // Create query
        $sql_proj_permission = 
            "SELECT MAX(CASE -- Highest permission => access level
                -- Scriba admin => no access for privacy reasons
                -- Company admin => edit (Company member -> no access)
                WHEN company_members.role = 'admin'    THEN 2
                -- Lab group admin => edit
                WHEN lab_members.role = 'admin'  THEN 2
                -- Lab group member => read
                WHEN lab_members.role = 'member' THEN 1
                -- Project
                WHEN project_members.role = 'owner'    THEN 3
                WHEN project_members.role = 'edit'     THEN 2
                WHEN project_members.role = 'read'     THEN 1
                -- None of the above => no access
                ELSE 0
                END) AS `access`
            FROM projects
            -- Add tables for bridging towards member tables
            LEFT JOIN labs
                ON labs.lab_id = projects.lab_id
            -- Add member tables to get roles and filter by user_ID
            LEFT JOIN company_members
                ON company_members.company_id = labs.company_id
                AND company_members.profile_id = ?
            LEFT JOIN lab_members
                ON lab_members.lab_id = labs.lab_id
                AND lab_members.profile_id = ?
            LEFT JOIN project_members
                ON project_members.project_id = projects.project_id
                AND project_members.profile_id = ?
            -- Filter for the specific project
            WHERE projects.project_id = ?
            ";
            // Prepare query
        $stmt_proj_permission = $conn->prepare($sql_proj_permission);
            // Bind parameters
        $stmt_proj_permission->bind_param("ssss", $profile_id, $profile_id, $profile_id, $entity_id);
            // Execute query
        if ($stmt_proj_permission->execute()) {
                // Get the result set from the executed query
            $result_proj_permission = $stmt_proj_permission->get_result(); // get_result() returns a mysqli_result object
                // Fetch the permission level from the result set
            $proj_permission_level = $result_proj_permission->fetch_assoc();
                // Check for null, nrow == 0, or missing 'access' column
            if (!array_key_exists('access', $proj_permission_level)) {
                // Missing 'access' column
                throw new RunTimeException("`access` column missing");
            } elseif ($proj_permission_level['access'] === null) {
                // 'access' is null => invalid Project_ID
                throw new RunTimeException("Invalid Project_ID: " . $entity_id);
            }
                // Return the permission level
            return (int)$proj_permission_level['access']; // Need to use (int) to convert from string to integer
        } else {
            throw new RunTimeException("Permission query failed: " . $stmt_proj_permission->error); // Error executing query
        }


    } elseif ($entity_type === 'lab') {
        // Check permission for a lab
            // Create query
        $sql_lab_permission = 
        "SELECT MAX(CASE -- Highest permission => access level
            -- Scriba admin => no access for privacy reasons
            -- Company admin => edit (Company member -> no access)
            WHEN company_members.role = 'admin'    THEN 2
            -- Lab group admin => edit
            WHEN lab_members.role = 'admin'  THEN 2
            -- Lab group member => read
            WHEN lab_members.role = 'member' THEN 1
            -- None of the above => no access
            ELSE 0
            END) AS `access`
        FROM labs
        -- Add tables for bridging towards member tables
        LEFT JOIN companies
            ON companies.company_id = labs.company_id
        -- Add member tables to get roles and filter by user_ID
        LEFT JOIN company_members
            ON company_members.company_id = companies.company_id
            AND company_members.profile_id = ?
        LEFT JOIN lab_members
            ON lab_members.lab_id = labs.lab_id
            AND lab_members.profile_id = ?
        -- Filter for the specific lab
        WHERE labs.lab_id = ?
        ";
            // Prepare query
        $stmt_lab_permission = $conn->prepare($sql_lab_permission);
            // Bind parameters
        $stmt_lab_permission->bind_param("sss", $profile_id, $profile_id, $entity_id);
            // Execute query
        if ($stmt_lab_permission->execute()) {
                // Get the result set from the executed query
            $result_lab_permission = $stmt_lab_permission->get_result(); // get_result() returns a mysqli_result object
                // Fetch the permission level from the result set
            $lab_permission_level = $result_lab_permission->fetch_assoc();
                // Check for null, nrow == 0, or missing 'access' column
            if (!array_key_exists('access', $lab_permission_level)) {
                // Missing 'access' column
                throw new RunTimeException("`access` column missing");
            } elseif ($lab_permission_level['access'] === null) {
                // 'access' is null => invalid Lab_Group_ID
                throw new RunTimeException("Invalid Lab_Group_ID: " . $entity_id);
            }
                // Return the permission level
            return (int)$lab_permission_level['access']; // Need to use (int) to convert from string to integer
        } else {
            throw new RunTimeException("Permission query failed: " . $stmt_lab_permission->error); // Error executing query
        }


    } elseif ($entity_type === 'company') {
        // Check permission for a company
            // Create query
        $sql_company_permission = 
        "SELECT MAX(CASE -- Highest permission => access level
            -- Scriba admin => no access for privacy reasons
            -- Company admin => edit (Company member -> no access)
            WHEN company_members.role = 'admin'    THEN 2
            -- Company member => read
            WHEN company_members.role = 'member'   THEN 1
            -- None of the above => no access
            ELSE 0
            END) AS `access`
        FROM companies
        -- Add member tables to get roles and filter by user_ID
        LEFT JOIN company_members
            ON company_members.company_id = companies.company_id
            AND company_members.profile_id = ?
        -- Filter for the specific company
        WHERE companies.company_id = ?
        ";
            // Prepare query
        $stmt_company_permission = $conn->prepare($sql_company_permission);
            // Bind parameters
        $stmt_company_permission->bind_param("ss", $profile_id, $entity_id);
            // Execute query
        if ($stmt_company_permission->execute()) {
                // Get the result set from the executed query
            $result_company_permission = $stmt_company_permission->get_result(); // get_result() returns a mysqli_result object
                // Fetch the permission level from the result set
            $company_permission_level = $result_company_permission->fetch_assoc();
                // Check for null, nrow == 0, or missing 'access' column
            if (!array_key_exists('access', $company_permission_level)) {
                // Missing 'access' column
                throw new RunTimeException("`access` column missing");
            } elseif ($company_permission_level['access'] === null) {
                // 'access' is null => invalid Company_ID
                throw new RunTimeException("Invalid Company_ID: " . $entity_id);
            }
                // Return the permission level
            return (int)$company_permission_level['access']; // Need to use (int) to convert from string to integer
        } else {
            throw new RunTimeException("Permission query failed: " . $stmt_company_permission->error); // Error executing query
        }


    } elseif ($entity_type === 'scriba') {
        // Check permission for Scriba
            // Create query
        $sql_scriba_permission =
        "SELECT is_scriba_admin AS `access` -- 1 if user is Scriba admin, 0 otherwise
        FROM profiles
        WHERE profile_id = ?
        ";
            // Prepare query
        $stmt_scriba_permission = $conn->prepare($sql_scriba_permission);
            // Bind parameters
        $stmt_scriba_permission->bind_param("s", $profile_id);
            // Execute query
        if ($stmt_scriba_permission->execute()) {
                // Get the result set from the executed query
            $result_scriba_permission = $stmt_scriba_permission->get_result(); // get_result() returns a mysqli_result object
                // Fetch the permission level from the result set
            $scriba_permission_level = $result_scriba_permission->fetch_assoc();
                // Check for null, nrow == 0, or missing 'access' column
            if (!array_key_exists('access', $scriba_permission_level)) {
                // Missing 'access' column
                throw new RunTimeException("`access` column missing");
            } elseif ($scriba_permission_level['access'] === null) {
                // 'access' is null => invalid User_ID
                throw new RunTimeException("Invalid User_ID: " . $profile_id);
            }
                // Return the permission level
            return (int)$scriba_permission_level['access']; // Need to use (int) to convert from string to integer
        } else {
            throw new RunTimeException("Permission query failed: " . $stmt_scriba_permission->error); // Error executing query
        }


    } else {
        // Throw warning for invalid entity type
        trigger_error("Invalid entity type: " . $entity_type, E_USER_WARNING);
        return 0; // Invalid entity type => no access
    }
}
?>