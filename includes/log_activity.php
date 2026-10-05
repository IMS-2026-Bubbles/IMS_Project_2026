<?php
// Log activity helper

// Log activity function to log user actions in the database.
// Creates an insert statement to the activity_log table with the provided parameters.

function log_activity(
    $conn, 
    int $profile_id, 
    string $entity_type, 
    string $entity_id, 
    string $activity_type, 
    string $detail) {
        // Check input parameters for validity
        $valid_entity_types = ['experiment', 'project', 'lab', 'company', 'profile'];
        if (!in_array($entity_type, $valid_entity_types)) {
            throw new InvalidArgumentException("Invalid entity type: $entity_type");
        }

        $valid_activity_types = [
            'create', 'update', 'delete', 
            'change_role', 'change_permission',
            'add_member', 'remove_member',
            'add_lab', 'remove_lab', 
            'add_company', 'remove_company']
        if (!in_array($activity_type, $valid_activity_types)) {
            throw new InvalidArgumentException("Invalid activity type: $activity_type");
        }


        // SQL statement to insert the log entry
        $sql_log_activity = 
            "INSERT INTO activity_log (profile_id, entity_type, entity_id, activity_type, detail) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt_log_activity = $conn->prepare($sql_log_activity);
        $stmt_log_activity->bind_param("isss", $profile_id, $entity_type, $entity_id, $activity_type, $detail);
        if (!$stmt_log_activity->execute()) {
            throw new RuntimeException("Failed to log activity: " . $stmt_log_activity->error);
        }
        $stmt_log_activity->close();

        return true; // Return true on successful logging
    }

?>