<?php
$host = "localhost";      // or your DB host
$user = "root";           // default user for XAMPP
$pass = "";               // default password is empty for XAMPP
$db   = "transportrix";   // your actual database name

$connect = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
?>