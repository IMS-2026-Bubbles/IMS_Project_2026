<?php
// Fetch user affiliations

// Fetch the user's affiliations from the database.
// Get if they are a scriba admin and list of companies + labs they belong to.
// Expects $conn and $profile_id to be set before including this file.
// Returns an associative array $user_affiliations with keys:
// - 'profile_id' => integer
// - 'is_scriba_admin' => boolean
// - 'companies' => ID
// - 'labs' => ID

$sql_affiliations = 
    "SELECT profile_id, is_scriba_admin, company_id, lab_id
    FROM profiles
    LEFT JOIN company_members
        ON profiles.profile_id = company_members.profile_id
    LEFT JOIN lab_members
        ON profiles.profile_id = lab_members.profile_id
    WHERE profiles.profile_id = ?
    GROUP BY profiles.profile_id, company_members.company_id";
$stmt_affiliations = $conn->prepare($sql_affiliations);
$stmt_affiliations->bind_param("i", $profile_id);
$stmt_affiliations->execute();
$result_affiliations = $stmt_affiliations->get_result();

$user_affiliations = $result_affiliations->fetch_all(MYSQLI_ASSOC); // return all rows
?>