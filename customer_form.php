<?php
session_start();
include('connect.php');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if (isset($_POST['cust_email'])) {
    $cust_email = $_POST["cust_email"];
    $cust_name = $_POST["cust_name"];
    $cust_contact = $_POST["cust_contact"];
    $address = $_POST["address"];
    // Customer login interface removed; keep a placeholder password.
    $cust_pass = "DISABLED";

    $sql = "INSERT INTO customer (cust_email, cust_name, cust_contact, address, cust_pass)
            VALUES ('$cust_email', '$cust_name', '$cust_contact', '$address', '$cust_pass')";

    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo "<script>alert('New customer added successfully!');
              window.location='customer_list.php';</script>";
    } else {
        echo "<script>alert('Failed to add customer: " . mysqli_error($connect) . "');
              window.location='customer_form.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add New Customer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f6fa; }
        .wrap { max-width: 760px; margin: 40px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 24px; }
        h3 { margin: 0 0 18px 0; color: #2c3e50; }
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 12px; align-items: center; }
        label { font-weight: 600; color: #34495e; }
        input { padding: 10px; border: 1px solid #d9dfe6; border-radius: 6px; width: 100%; box-sizing: border-box; }
        .actions { margin-top: 18px; display: flex; gap: 10px; }
        .btn { border: none; border-radius: 8px; padding: 10px 14px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: #27ae60; color: #fff; }
        .btn-muted { background: #6c757d; color: #fff; }
        .btn-light { background: #ecf0f1; color: #2c3e50; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h3><i class="fa fa-user-plus"></i> Add New Customer</h3>
            <form action="customer_form.php" method="post">
                <div class="grid">
                    <label>Email</label>
                    <input type="email" name="cust_email" required>

                    <label>Name</label>
                    <input type="text" name="cust_name" required>

                    <label>Contact</label>
                    <input type="text" name="cust_contact" required>

                    <label>Address</label>
                    <input type="text" name="address" required>
                </div>

                <div class="actions">
                    <a href="customer_list.php" class="btn btn-muted"><i class="fa fa-arrow-left"></i> Back</a>
                    <button class="btn btn-primary" type="submit"><i class="fa fa-plus"></i> Add Customer</button>
                    <button class="btn btn-light" type="reset">Reset</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
