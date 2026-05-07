<?php
session_start();
include('connect.php');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

// Fetch list of truck plates
$truckList = [];
$sql = "SELECT truck_plate FROM truck";
$result = mysqli_query($connect, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $truckList[] = $row['truck_plate'];
    }
}

// If a truck_plate is selected, fetch its data
$selectedTruck = null;
if (isset($_POST['select_truck'])) {
    $selectedPlate = $_POST['truck_plate'];
    $sql = "SELECT * FROM truck WHERE truck_plate = '$selectedPlate'";
    $result = mysqli_query($connect, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $selectedTruck = mysqli_fetch_assoc($result);
    }
}

// Handle update form submission
if (isset($_POST['update_truck'])) {
    $truck_plate = $_POST["truck_plate"];
    $truck_model = $_POST["truck_model"];
    $load_weight = $_POST["load_weight"];

    $sql = "UPDATE truck SET 
            truck_model = '$truck_model',
            load_weight = '$load_weight'
            WHERE truck_plate = '$truck_plate'";

    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo "<script>alert('Truck updated successfully!');
              window.location='truck_list.php';</script>";
    } else {
        echo "<script>alert('Failed to update truck: " . mysqli_error($connect) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Truck</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f6fa; }
        .wrap { max-width: 760px; margin: 40px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 24px; margin-bottom: 14px; }
        h3 { margin: 0 0 18px 0; color: #2c3e50; }
        .grid { display: grid; grid-template-columns: 1fr 2fr auto; gap: 12px; align-items: center; }
        .grid-update { display: grid; grid-template-columns: 1fr 2fr; gap: 12px; align-items: center; }
        label { font-weight: 600; color: #34495e; }
        input, select { padding: 10px; border: 1px solid #d9dfe6; border-radius: 6px; width: 100%; box-sizing: border-box; }
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
            <h3><i class="fa fa-truck"></i> Update Truck</h3>
            <form method="post" action="">
                <div class="grid">
                    <label>Select Truck Plate</label>
                    <select name="truck_plate" required>
                        <option value="">-- Select Truck Plate --</option>
                        <?php foreach ($truckList as $plate): ?>
                            <option value="<?= $plate ?>" <?= isset($selectedTruck) && $selectedTruck['truck_plate'] == $plate ? 'selected' : '' ?>>
                                <?= $plate ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" type="submit" name="select_truck"><i class="fa fa-search"></i> Select</button>
                </div>
            </form>
        </div>

        <?php if ($selectedTruck): ?>
        <div class="card">
            <form action="truck_update.php" method="post">
                <div class="grid-update">
                    <label>Truck Plate</label>
                    <input type="text" name="truck_plate" value="<?= $selectedTruck['truck_plate'] ?>" readonly>

                    <label>Truck Model</label>
                    <input type="text" name="truck_model" value="<?= $selectedTruck['truck_model'] ?>" required>

                    <label>Load Weight (kg)</label>
                    <input type="number" name="load_weight" value="<?= $selectedTruck['load_weight'] ?>" required>
                </div>
                <div class="actions">
                    <a href="truck_list.php" class="btn btn-muted"><i class="fa fa-arrow-left"></i> Back</a>
                    <button class="btn btn-primary" type="submit" name="update_truck"><i class="fa fa-save"></i> Update Truck</button>
                    <button class="btn btn-light" type="reset">Reset</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
