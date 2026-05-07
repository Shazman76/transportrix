<?php
include("connect.php");

if (isset($_GET['driver_id'])) {
    $driver_id = $_GET['driver_id'];

    // Step 1: Delete all orders assigned to this admin
    $query1 = "DELETE FROM shipment WHERE driver_id = ?";
    $stmt1 = $connect->prepare($query1);
    $stmt1->bind_param("s", $driver_id);
    $stmt1->execute();

    // Step 2: Now delete the admin
    $query2 = "DELETE FROM driver WHERE driver_id = ?";
    $stmt2 = $connect->prepare($query2);
    $stmt2->bind_param("s", $driver_id);

    if ($stmt2->execute()) {
        // Redirect to customer list after deletion
        header("Location: driver_list.php");
        exit(); // Important: stop script after redirect
    } else {
        echo "Failed to delete customer: " . $stmt2->error;
    }

    $stmt1->close();
    $stmt2->close();
    $connect->close();
} else {
    echo "No Driver ID provided.";
}
