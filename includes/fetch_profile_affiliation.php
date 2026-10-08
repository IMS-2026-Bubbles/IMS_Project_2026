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

$sql_is_scriba_admin =
    'SELECT profile_id, is_scriba_admin
    FROM profiles
    WHERE profiles.profile_id = ?';
$stmt_is_scriba_admin = $conn->prepare($sql_is_scriba_admin);
$stmt_is_scriba_admin->bind_param('i', $profile_id);
if ($stmt_is_scriba_admin->execute()) {
    $result_is_scriba_admin = $stmt_is_scriba_admin->get_result();
    $user_is_scriba_admin = $result_is_scriba_admin->fetch_assoc();  // Should be 1 row (1 or 0)
};

$sql_companies =
    'SELECT company_members.profile_id, company_members.company_id
    FROM company_members
    WHERE company_members.profile_id = ?';
$stmt_companies = $conn->prepare($sql_companies);
$stmt_companies->bind_param('i', $profile_id);
if ($stmt_companies->execute()) {
    $result_companies = $stmt_companies->get_result();
    $user_companies = $result_companies->fetch_all(MYSQLI_ASSOC);  // return all rows
}

$sql_labs =
    'SELECT lab_members.profile_id, lab_members.lab_id, labs.company_id
    FROM lab_members
    LEFT JOIN labs ON lab_members.lab_id = labs.lab_id
    WHERE lab_members.profile_id = ?';
$stmt_labs = $conn->prepare($sql_labs);
$stmt_labs->bind_param('i', $profile_id);
if ($stmt_labs->execute()) {
    $result_labs = $stmt_labs->get_result();
    $user_labs = $result_labs->fetch_all(MYSQLI_ASSOC);  // return all rows
}

// Check that the profile_id from queries matches the input profile_id
if ($user_is_scriba_admin['profile_id'] !== $profile_id) {
    error_log("Error: profile_id mismatch in is_scriba_admin query. Expected $profile_id, got " . $user_is_scriba_admin[0]['profile_id']);
}
if ($user_companies[0]['profile_id'] !== $profile_id) {
    error_log("Error: profile_id mismatch in companies query. Expected $profile_id, got " . $user_companies[0]['profile_id']);
}
if ($user_labs[0]['profile_id'] !== $profile_id) {
    error_log("Error: profile_id mismatch in labs query. Expected $profile_id, got " . $user_labs[0]['profile_id']);
}

// Collect results into an array of associative arrays
$user_affiliations = [];
$user_affiliations['profile_id'] = $profile_id;  // Should be 1 row (the profile_id passed in)
$user_affiliations['is_scriba_admin'] = $user_is_scriba_admin['is_scriba_admin'];  // Should be one row and one value (1 or 0)
$user_affiliations['companies'] = array_column($user_companies, 'company_id');  // Should be 0 or more rows
$user_affiliations['labs'] = array_column($user_labs, 'lab_id', 'company_id');  // Should be 0 or more rows (new keys are company_id, values are lab_id)
?>