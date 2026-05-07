<?php
include("connect.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (isset($_GET['truck_plate'])) {
    $truck_plate = trim($_GET['truck_plate']); // Clean input

    // Step 1: Unassign truck from drivers
    $stmt1 = $connect->prepare("UPDATE driver SET truck_plate = NULL WHERE truck_plate = ?");
    $stmt1->bind_param("s", $truck_plate);
    $stmt1->execute();
    $stmt1->close();

    // Step 2: Unassign truck from shipments
    $stmt2 = $connect->prepare("UPDATE shipment SET truck_plate = NULL WHERE truck_plate = ?");
    $stmt2->bind_param("s", $truck_plate);
    $stmt2->execute();
    $stmt2->close();

    // Step 3: Delete truck
    $stmt3 = $connect->prepare("DELETE FROM truck WHERE truck_plate = ?");
    $stmt3->bind_param("s", $truck_plate);

    if ($stmt3->execute()) {
        header("Location: truck_list.php?deleted=success");
        exit();
    } else {
        die("❌ Failed to delete truck: " . $stmt3->error);
    }

    $stmt3->close();
    $connect->close();
} else {
    echo "❌ No truck plate provided.";
}
