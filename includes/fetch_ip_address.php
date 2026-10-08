<?php
// Get and validate the ip address

// Fetches the IP address of the client making the request, considering both direct and proxy scenarios. 
// Validates the IP address format and returns the first valid IP address found. 
// 
// Expects $_SERVER['REMOTE_ADDR'] and $_SERVER['HTTP_X_FORWARDED_FOR'] to be set.
// Returns $ip_address (string|null) and $ip_address_proxy (string|null) variables.

    // Direct
$ip_address = $_SERVER['REMOTE_ADDR'] ?? NULL;
$ip_address = filter_var($ip_address, FILTER_VALIDATE_IP) ? $ip_address : NULL; // Validate IP address
    // Proxy (how to use this?)
$ip_address_proxy = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? NULL;
$ip_address_proxy = $ip_address_proxy ? explode(',', $ip_address_proxy)[0] : NULL; // Get the first IP in the list if multiple
$ip_address_proxy = filter_var($ip_address_proxy, FILTER_VALIDATE_IP) ? $ip_address_proxy : NULL; // Validate IP address

?>