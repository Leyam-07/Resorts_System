<?php
// Prevent direct access to this file
if (!defined('APP_LOADED')) {
    http_response_code(403);
    die('Direct access not allowed.');
}

// Dynamically determine the base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
// Adjust script_name to point to the project root, not the public directory
$script_name = str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']);

// Define the base URL constant
define('BASE_URL', rtrim($protocol . $host . $script_name, '/'));