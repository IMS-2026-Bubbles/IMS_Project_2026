<?php
// Encryption and decryption functions for the Scriba application

// Include in actions/save_experiment_section.php and includes/fetch_experiment_section_text.php
// to encrypt and decrypt the text content of experiment sections (plan, log, result) 
// before saving to or after fetching from the database.

// key variable
$key = base64_decode(require "includes/encryption_key.php");

// Encryption function



// Decryption function



?>