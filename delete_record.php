<?php
session_start();
include('connect.php');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check login
if (!isset($_SESSION['userid'])) {
    echo "<script>alert('Please log in first.'); window.location='login.php';</script>";
    exit();
}

// Sanitize and get parameters
$table = isset($_GET['table']) ? $_GET['table'] : '';
$column = isset($_GET['column']) ? $_GET['column'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$return = isset($_GET['return']) ? $_GET['return'] : $table . '_list.php';

// Validate inputs
if (empty($table) || empty($column) || empty($id)) {
    echo "<script>alert('Missing table, column, or ID.'); window.history.back();</script>";
    exit();
}

// Whitelist allowed tables and columns (security)
$allowed = [
    'admin' => 'admin_id',
    'customer' => 'cust_email',
    'driver' => 'driver_id',
    'truck' => 'truck_plate',
    'cust_order' => 'Order_ID',
    'shipment' => 'Ship_ID'
];

if (!array_key_exists($table, $allowed) || $allowed[$table] != $column) {
    echo "<script>alert('Unauthorized delete attempt.'); window.history.back();</script>";
    exit();
}

// Perform deletion
$sql = "DELETE FROM `$table` WHERE `$column` = ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "s", $id);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Record deleted successfully.'); window.location='$return';</script>";
} else {
    echo "<script>alert('Deletion failed: " . mysqli_error($connect) . "'); window.location='$return';</script>";
}
?>
