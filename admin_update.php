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

// Get existing admin data
if (isset($_GET['admin_id'])) {
    $admin_id = $_GET['admin_id'];
    $query = "SELECT * FROM admin WHERE admin_id = '$admin_id'";
    $result = mysqli_query($connect, $query);
    $admin = mysqli_fetch_assoc($result);

    if (!$admin) {
        echo "<script>alert('Admin not found'); window.location='admin_list.php';</script>";
        exit();
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_POST["admin_id"];
    $admin_name = $_POST["admin_name"];
    $admin_contact = $_POST["admin_contact"];
    $admin_pass = $_POST["admin_pass"];

    $update_sql = "UPDATE admin SET 
        admin_name = '$admin_name',
        admin_contact = '$admin_contact',
        admin_pass = '$admin_pass'
        WHERE admin_id = '$admin_id'";

    if (mysqli_query($connect, $update_sql)) {
        echo "<script>alert('Admin updated successfully!');
              window.location='admin_list.php';</script>";
    } else {
        echo "<script>alert('Update failed: " . mysqli_error($connect) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f6fa; }
        .wrap { max-width: 720px; margin: 40px auto; padding: 0 16px; }
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
            <h3><i class="fa fa-user-pen"></i> Update Admin</h3>
            <form action="admin_update.php?admin_id=<?php echo $admin['admin_id']; ?>" method="post">
                <div class="grid">
                    <label>Admin ID</label>
                    <input type="text" name="admin_id" value="<?php echo $admin['admin_id']; ?>" readonly>

                    <label>Admin Name</label>
                    <input type="text" name="admin_name" value="<?php echo $admin['admin_name']; ?>" required>

                    <label>Admin Contact</label>
                    <input type="text" name="admin_contact" value="<?php echo $admin['admin_contact']; ?>" required>

                    <label>Admin Password</label>
                    <input type="text" name="admin_pass" value="<?php echo $admin['admin_pass']; ?>" required>
                </div>
                <div class="actions">
                    <a href="admin_list.php" class="btn btn-muted"><i class="fa fa-arrow-left"></i> Back</a>
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Update Admin</button>
                    <button class="btn btn-light" type="reset">Reset</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
