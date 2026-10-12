<?php
// Load .env file and set environment variables

// Custom function to read and load our .env file into environment variables.

function load_dotenv()
{
    // Path to the .env file
    $dotenv_path = __DIR__ . '/.env';
    // Check if the .env file exists
    if (!file_exists($dotenv_path)) {
        return false;  // .env file not found, return false
    }

    // Read lines (skip empty lines)
    $lines = file($dotenv_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Filter out comments and invalid lines (lines that don't contain '=' or start with '=')
    $lines = array_filter(
        $lines,
        function ($line) {
            return strpos(trim($line), '#') !== 0 &&  // Skip comments (lines starting with '#')
                !str_starts_with(trim($line), '=') &&  // Skip lines that begin with '=' (invalid format)
                str_contains(trim($line), '=');  // Only keep lines that contain at least one '='
        }
    );
    // Check if any valid lines were found
    if (empty($lines)) {
        return false;  // No valid lines found, return false
    }

    // Get current environment variables to avoid overwriting existing ones
    $current_env_vars = $_ENV;

    // Split (explode) each line into key-value pairs
    foreach ($lines as $line) {
        // Split the line into key and value
        // Use limit of 2 to split only by the first '=' (allows for '=' in the value)
        [$key, $value] = explode('=', $line, 2);
        // Set in $_ENV superglobal
        if (!isset($current_env_vars[trim($key)])) {
            $_ENV[trim($key)] = trim($value);
        }
    }

    // Return true on success
    return true;
}

?>