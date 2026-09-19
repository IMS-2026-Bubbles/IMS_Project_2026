<?php
// Check user permission level

// Check what permission levels the user (or guest) has.
// This is a helper function to check if the user has the required permission level 
// for a specific action or page.
// Return 'owner', 'editor', 'viewer', or 'none' for projects & experiments.
// Return 'admin' or 'none' for Scriba, company, or lab permissions.

function check_user_permission(string $user_ID, string $entity_type, string $entity_ID): string {
    // Setup database connection
    include "../Database_related/db.php";

    if ($entity_type === 'experiment') {
        // TODO: Check permission for an experiment
        return 'none'; // Placeholder return value
    } elseif ($entity_type === 'project') {
        // TODO: Check permission for a project
        return 'none'; // Placeholder return value
    } elseif ($entity_type === 'lab') {
        // TODO: Check permission for a lab
        return 'none'; // Placeholder return value
    } elseif ($entity_type === 'company') {
        // TODO: Check permission for a company
        return 'none'; // Placeholder return value
    } elseif ($entity_type === 'scriba') {
        // TODO: Check permission for Scriba
        return 'none'; // Placeholder return value
    } else {
        return 'invalid entity type'; // Invalid entity type
    }
}
?>