<?php
// Encryption and decryption functions for the Scriba application

// Include in actions/save_experiment_section.php and includes/fetch_experiment_section_text.php
// to encrypt and decrypt the text content of experiment sections (plan, log, result) 
// before saving to or after fetching from the database.

// key variable
$encryption_key = base64_decode(require "encryption_key.php");

// Encryption function
function encrypt_text(
    string $plaintext,
    string $encryption_key
) {
    // If string is empty, return empty string without encrypting
    if (empty($plaintext)) {
        return "";
    }

    // Generate a fresh random initialization vector (IV) for each encryption
    $iv = openssl_random_pseudo_bytes(12);
        
    // Encrypt text
    $ciphertext = openssl_encrypt(
        $plaintext,
        'aes-256-gcm', // encryption algorithm
        $encryption_key,
        OPENSSL_RAW_DATA, // output raw data
        $iv,
        $tag // authentication tag, will be filled by openssl_encrypt 
        // and serves as proof that the ciphertext has not been tampered with
    );

    // Check for FALSE (openssl_encrypt returns FALSE on failure)
    if ($ciphertext === false) {
        throw new Exception('Encryption failed: ' . openssl_error_string());
    }

    // Bundle everything together for storage in database: IV + tag + ciphertext
    $encrypted_text = base64_encode($iv . $tag . $ciphertext);
    return $encrypted_text;
};


// Decryption function
function decrypt_text(
    string $encrypted_text,
    string $encryption_key
) {
    // If string is empty, return empty string without decrypting
    if (empty($encrypted_text)) {
        return "";
    }

    // Decode the base64 encoded string
    $decoded = base64_decode($encrypted_text);

    // Extract the IV, tag, and ciphertext (IV is 12 bytes, tag is 16 bytes for AES-256-GCM)
    $iv = substr($decoded, 0, 12); // first 12 bytes for IV
    $tag = substr($decoded, 12, 16); // next 16 bytes for tag
    $ciphertext = substr($decoded, 28); // remaining bytes for ciphertext

    // Decrypt text
    $plaintext = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm', // encryption algorithm
        $encryption_key,
        OPENSSL_RAW_DATA, // output raw data
        $iv,
        $tag // authentication tag
    );

    // Check for FALSE (openssl_decrypt returns FALSE on failure)
    if ($plaintext === false) {
        // throw new Exception('Decryption failed: ' . openssl_error_string());
        return "Decryption failed."; // return a string indicating decryption failure instead of throwing an exception
    }

    return $plaintext;
};

?>