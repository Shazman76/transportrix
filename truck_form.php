<?php
session_start();
include('connect.php');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if customer is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if (isset($_POST['truck_plate'])) {
    $truck_plate = $_POST["truck_plate"];
    $truck_model = $_POST["truck_model"];
    $load_weight = $_POST["load_weight"];

    $sql = "INSERT INTO truck (truck_plate, truck_model, load_weight)
            VALUES ('$truck_plate', '$truck_model', '$load_weight')";

    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo "<script>alert('New truck added successfully!');
              window.location='truck_list.php';</script>";
    } else {
        echo "<script>alert('Failed to add truck: " . mysqli_error($connect) . "');
              window.location='truck_form.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add New Truck</title>
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
            <h3><i class="fa fa-truck"></i> Add New Truck</h3>
            <form action="truck_form.php" method="post">
                <div class="grid">
                    <label>Truck Plate</label>
                    <input type="text" name="truck_plate" required>

                    <label>Truck Model</label>
                    <input type="text" name="truck_model" required>

                    <label>Load Weight (kg)</label>
                    <input type="number" name="load_weight" required>
                </div>

                <div class="actions">
                    <a href="truck_list.php" class="btn btn-muted"><i class="fa fa-arrow-left"></i> Back</a>
                    <button class="btn btn-primary" type="submit"><i class="fa fa-plus"></i> Add Truck</button>
                    <button class="btn btn-light" type="reset">Reset</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
