<?php
// config/db.php
// Simple MySQLi connection for the quiz_system database

// Database credentials - adjust as needed for your environment
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'quiz_system';

// Create a new mysqli instance and connect
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check for a connection error and handle it
if ($mysqli->connect_errno) {
    // In production don't echo credentials or detailed errors
    error_log('Database connection failed: ' . $mysqli->connect_error);
    die('Database connection failed. Check logs for details.');
}

// You can now use $mysqli for queries (prepare, query, etc.)
?>
