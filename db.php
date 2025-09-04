<?php
$host = "localhost";
$dbname = "finalproject";
$username = "root";
$password = ""; // XAMPP default

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Return the connection for use in tests or other scripts
return $conn;
