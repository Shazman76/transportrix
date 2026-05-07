<?php
// Database configuration
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    // Local XAMPP settings
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "transportrix";
} else {
    // InfinityFree settings (Replace these with your actual InfinityFree details)
    $host = "sqlXXX.infinityfree.com"; // Change this
    $user = "if0_XXXXXXX";            // Change this
    $pass = "YOUR_PASSWORD";          // Change this
    $db   = "if0_XXXXXXX_transportrix"; // Change this
}

$connect = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
?>