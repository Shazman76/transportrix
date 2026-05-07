<?php
include("connect.php");

if (isset($_GET['admin_id'])) {
    $admin_id = $_GET['admin_id'];

    // Step 1: Delete all orders assigned to this admin
    $query1 = "DELETE FROM cust_order WHERE admin_id = ?";
    $stmt1 = $connect->prepare($query1);
    $stmt1->bind_param("s", $admin_id);
    $stmt1->execute();

    // Step 2: Now delete the admin
    $query2 = "DELETE FROM admin WHERE admin_id = ?";
    $stmt2 = $connect->prepare($query2);
    $stmt2->bind_param("s", $admin_id);

    if ($stmt2->execute()) {
        echo "Admin deleted successfully.";
    } else {
        echo "Failed to delete admin: " . $stmt2->error;
    }

    $stmt1->close();
    $stmt2->close();
    $connect->close();
} else {
    echo "No admin ID provided.";
}
