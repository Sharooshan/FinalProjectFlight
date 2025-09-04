<?php
// Ensure session functions are available for the test environment
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include the database configuration
$_SERVER['DOCUMENT_ROOT'] = 'C:\xampp\htdocs\Intelliflight';
include 'C:/xampp/htdocs/Intelliflight/config/db.php';
