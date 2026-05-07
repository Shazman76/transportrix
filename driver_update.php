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

// Get driver ID from URL
if (isset($_GET['driver_id'])) {
    $driver_id = $_GET['driver_id'];

    // Get current data
    $sql = "SELECT * FROM driver WHERE driver_id = '$driver_id'";
    $result = mysqli_query($connect, $sql);
    $driver = mysqli_fetch_assoc($result);

    if (!$driver) {
        echo "<script>alert('Driver not found.'); window.location='driver_list.php';</script>";
        exit();
    }
}

// Handle update submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $driver_id = $_POST["driver_id"];
    $driver_name = $_POST["driver_name"];
    $contact_driver = $_POST["contact_driver"];
    $driver_pass = $_POST["driver_pass"];
    $truck_plate = $_POST["truck_plate"];
    $current_status = $_POST["current_status"]; // optional if you want to allow status update

    // Optional: ensure truck exists
    $insert_truck = "INSERT IGNORE INTO truck (truck_plate) VALUES ('$truck_plate')";
    mysqli_query($connect, $insert_truck);

    $update = "UPDATE driver SET 
        driver_name = '$driver_name',
        contact_driver = '$contact_driver',
        driver_pass = '$driver_pass',
        truck_plate = '$truck_plate',
        current_status = '$current_status'
        WHERE driver_id = '$driver_id'";

    if (mysqli_query($connect, $update)) {
        echo "<script>alert('Driver updated successfully!');
              window.location='driver_list.php';</script>";
    } else {
        echo "<script>alert('Failed to update driver: " . mysqli_error($connect) . "');
              window.location='driver_update.php?driver_id=$driver_id';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Driver</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f6fa; }
        .wrap { max-width: 760px; margin: 40px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 24px; }
        h3 { margin: 0 0 18px 0; color: #2c3e50; }
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 12px; align-items: center; }
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
            <h3><i class="fa fa-id-card"></i> Update Driver</h3>
            <form action="driver_update.php" method="post">
                <input type="hidden" name="driver_id" value="<?php echo $driver['driver_id']; ?>">
                <div class="grid">
                    <label>Driver Name</label>
                    <input type="text" name="driver_name" value="<?php echo $driver['driver_name']; ?>" required>

                    <label>Driver Contact</label>
                    <input type="text" name="contact_driver" value="<?php echo $driver['contact_driver']; ?>" required>

                    <label>Driver Password</label>
                    <input type="password" name="driver_pass" value="<?php echo $driver['driver_pass']; ?>" required>

                    <label>Truck Plate</label>
                    <select name="truck_plate" required>
                        <option value="">-- Select Truck Plate --</option>
                        <?php
                        $trucks = mysqli_query($connect, "SELECT truck_plate FROM truck");
                        while ($t = mysqli_fetch_assoc($trucks)) {
                            $selected = ($shipment['truck_plate'] == $t['truck_plate']) ? 'selected' : '';
                            echo "<option value='" . $t['truck_plate'] . "' $selected>" . $t['truck_plate'] . "</option>";
                        }
                        ?>
                    </select>

                    <label>Status</label>
                    <select name="current_status">
                        <option value="Offline" <?php if($driver['current_status'] == "Offline") echo "selected"; ?>>Offline</option>
                        <option value="Available" <?php if($driver['current_status'] == "Available") echo "selected"; ?>>Available</option>
                        <option value="Busy" <?php if($driver['current_status'] == "Busy") echo "selected"; ?>>Busy</option>
                    </select>
                </div>
                <div class="actions">
                    <a href="driver_list.php" class="btn btn-muted"><i class="fa fa-arrow-left"></i> Back</a>
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Update Driver</button>
                    <button class="btn btn-light" type="reset">Reset</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
