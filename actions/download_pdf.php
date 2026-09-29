<?php
// Download PDF file

// Lets a user download a PDF file from the server. Expects a GET parameter 'file' with the base filename.
// Whitelist permitted pdf files to prevent directory traversal attacks.
// See Alex V reply at https://stackoverflow.com/a/8122372/17852580 for reference.
// Returns: the file itself as a download (with Content-Disposition headers),
// or exits with an error message if the file is not in the whitelist or doesn't exist.

// Arrive from:
    // register_user.php (click on "Download Terms of Service" link)
// Action:
    // Download the specified PDF file from the server
// Redirect to:
    // register_user.php (if file is not in whitelist or doesn't exist)


    
// Doesn't need session, login check, or database connection

$file = $_GET['file'];
$allowed_files = [
    'terms_of_service' => 'docs/terms_of_service.pdf'
    // Add more allowed files here
];
if (!array_key_exists($file, $allowed_files)) {
    exit('Invalid file specified.');
} elseif ($file_exists($allowed_files[$file])) {
    // Set headers (need to learn more about how headers actually work!)
    header("Content-Decription: File Transfer");
    header("Content-Type: aplication/octet-stream");
    header("Content-Type: aplication/force-download");
    header("Content-Disposition: attachment; filename=" . urlencode(basename($allowed_files[$file])));
    // header("Content-Transfer-Encoding: binary) // Not sure why this is still here?
    header("Expires: 0");
    header("Cache-Control: must-revaludate, post-check=0, pre-check=0");
    header("Pragma: public");
    header("Content-Length: " . filesize($allowed_files[$file]));
    ob_clean();
    flush();
    readfile($allowed_files[$file]);
    exit;
}
?>