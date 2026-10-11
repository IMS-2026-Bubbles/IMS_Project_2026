<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
>
<link rel="stylesheet" href="assets/style.css?v=6">
<?php
// Retrieve the text content for a specific experiment section.
// Expected variables:
// $conn, $experiment_id, $experiment_section
//
// Supported sections: plan, log, result
// Returns the decrypted content in $experiment_section_text.

// Default value
$experiment_section_text = '';

// Only allow valid experiment section names.
$allowed_sections = ['plan', 'log', 'result'];

if (
    !isset($experiment_section) ||
    !in_array($experiment_section, $allowed_sections, true)
) {
    $error_message = 'Invalid experiment section.';

    if (isset($messages) && is_array($messages)) {
        $messages[] = $error_message;
    } else {
        echo htmlspecialchars(
            $error_message,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    return;
}

// Make sure the experiment ID is valid.
$experiment_id = filter_var(
    $experiment_id ?? null,
    FILTER_VALIDATE_INT
);

if (!$experiment_id || $experiment_id < 1) {
    $error_message = 'Invalid experiment ID.';

    if (isset($messages) && is_array($messages)) {
        $messages[] = $error_message;
    } else {
        echo htmlspecialchars(
            $error_message,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    return;
}

// Build the column name using the validated section.
$text_column = $experiment_section . '_text';

$sql_experiment_section_text =
    "SELECT `$text_column`
     FROM experiments
     WHERE experiment_id = ?";

$stmt_experiment_section_text =
    $conn->prepare($sql_experiment_section_text);

if (!$stmt_experiment_section_text) {
    $error_message = 'Could not prepare the experiment section query.';

    if (isset($messages) && is_array($messages)) {
        $messages[] = $error_message;
    } else {
        echo htmlspecialchars(
            $error_message,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    return;
}

// Bind the experiment ID.
$stmt_experiment_section_text->bind_param(
    'i',
    $experiment_id
);

// Execute the query.
if (!$stmt_experiment_section_text->execute()) {
    $error_message = 'Could not retrieve the experiment section text.';

    if (isset($messages) && is_array($messages)) {
        $messages[] = $error_message;
    } else {
        echo htmlspecialchars(
            $error_message,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    $stmt_experiment_section_text->close();
    return;
}

// Fetch the stored text.
$result_experiment_section_text =
    $stmt_experiment_section_text->get_result();

$row_experiment_section_text =
    $result_experiment_section_text->fetch_assoc();

$stmt_experiment_section_text->close();

if ($row_experiment_section_text === null) {
    $error_message = 'Experiment not found.';

    if (isset($messages) && is_array($messages)) {
        $messages[] = $error_message;
    } else {
        echo htmlspecialchars(
            $error_message,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    return;
}

$stored_section_text =
    $row_experiment_section_text[$text_column] ?? '';

// Load the existing encryption key and decryption function.
require_once __DIR__ . '/encryption_key.php';
require_once __DIR__ . '/encryption.php';

// Decrypt the text. Empty database fields remain empty.
if ($stored_section_text === '') {
    $experiment_section_text = '';
} else {
    $decrypted_text = decrypt_text(
        $stored_section_text,
        $encryption_key
    );

    $experiment_section_text =
        is_string($decrypted_text) ? $decrypted_text : '';
}
?>