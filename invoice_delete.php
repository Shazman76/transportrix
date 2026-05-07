<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['userid']) || $_SESSION['status'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: invoice_list.php');
    exit();
}

mysqli_query($connect, "DELETE FROM invoice WHERE invoice_id = {$id} LIMIT 1");
header('Location: invoice_list.php');
exit();
