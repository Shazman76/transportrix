<?php
session_start();
include('connect.php');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Only allow logged-in admins
if (!isset($_SESSION['userid']) || $_SESSION['status'] != 'admin') {
    header("Location: login.php");
    exit();
}

$customer = null;

// Get existing customer data
if (isset($_GET['cust_email'])) {
    $cust_email = $_GET['cust_email'];
    $query = "SELECT * FROM customer WHERE cust_email = '$cust_email'";
    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $customer = mysqli_fetch_assoc($result);
    } else {
        echo "<script>alert('Customer not found'); window.location='customer_list.php';</script>";
        exit();
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cust_email = $_POST["cust_email"];
    $cust_name = $_POST["cust_name"];
    $cust_contact = $_POST["cust_contact"];
    $address = $_POST["address"];

    $update_sql = "UPDATE customer SET 
        cust_name = '$cust_name',
        cust_contact = '$cust_contact',
        address = '$address'
        WHERE cust_email = '$cust_email'";

    if (mysqli_query($connect, $update_sql)) {
        echo "<script>alert('Customer updated successfully!');
              window.location='customer_list.php';</script>";
        exit();
    } else {
        echo "<script>alert('Update failed: " . mysqli_error($connect) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Customer</title>
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
        .err { color: #c0392b; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h3><i class="fa fa-user-pen"></i> Update Customer</h3>
            <?php if ($customer): ?>
            <form action="customer_update.php?cust_email=<?php echo htmlspecialchars($customer['cust_email']); ?>" method="post">
                <div class="grid">
                    <label>Email</label>
                    <input type="text" name="cust_email" value="<?php echo htmlspecialchars($customer['cust_email']); ?>" readonly>

                    <label>Name</label>
                    <input type="text" name="cust_name" value="<?php echo htmlspecialchars($customer['cust_name']); ?>" required>

                    <label>Contact</label>
                    <input type="text" name="cust_contact" value="<?php echo htmlspecialchars($customer['cust_contact']); ?>" required>

                    <label>Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($customer['address']); ?>" required>
                </div>
                <div class="actions">
                    <a href="customer_list.php" class="btn btn-muted"><i class="fa fa-arrow-left"></i> Back</a>
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Update Customer</button>
                    <button class="btn btn-light" type="reset">Reset</button>
                </div>
            </form>
            <?php else: ?>
                <p class="err"><b>Error:</b> No customer selected for update.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
