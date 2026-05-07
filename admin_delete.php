<?php
include('connect.php');

if (isset($_GET['admin_id'])) {
    $admin_id = $_GET['admin_id'];

    $sql = "DELETE FROM admin WHERE admin_id = '$admin_id'";
    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo "<script>alert('Admin deleted successfully!');
              window.location='admin_list.php';</script>";
    } else {
        echo "<script>alert('Failed to delete admin: " . mysqli_error($connect) . "');
              window.location='admin_list.php';</script>";
    }
} else {
    echo "<script>alert('No Admin ID provided.');
          window.location='admin_list.php';</script>";
}
?>
