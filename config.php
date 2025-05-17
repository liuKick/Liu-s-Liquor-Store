<?php
session_start();

// InfinityFree MySQL Configuration (USE THESE EXACT VALUES)
$host = 'sql303.infinityfree.com';    // MySQL hostname from your screenshot
$user = 'if0_39003988';              // MySQL username from your screenshot
$pass = '123';       // The password YOU set when creating the DB
$db   = 'if0_39003988_liquorstore';  // Format: username_+dbname (you create this)

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>