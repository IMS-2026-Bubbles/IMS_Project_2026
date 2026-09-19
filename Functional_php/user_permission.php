<?php
// Check user permission level

// Check what permission levels the user (or guest) has.
// This is a helper function to check if the user has the required permission level 
// for a specific action or page.
// Return 'owner', 'editor', 'viewer', or 'none' for projects & experiments.
// Return 'admin' or 'none' for Scriba, company, or lab permissions.

function check_user_permission(string $user_ID, string $entity_type, string $entity_ID): string {
    if ($entity_type === 'experiment') {
        // Check permission for an experiment
    } elseif ($entity_type === 'project') {
        // Check permission for a project
    } elseif ($entity_type === 'lab') {
        // Check permission for a lab
    } elseif ($entity_type === 'company') {
        // Check permission for a company
    } elseif ($entity_type === 'scriba') {
        // Check permission for Scriba
    } else {
        return 'none'; // Invalid entity type
    }
}
?>