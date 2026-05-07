<?php
include('connect.php');
include('navBar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f4f6fa;
        }

        .container {
            padding: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 20px;
        }

        .card h2 {
            margin: 0 0 15px 0;
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .driver-table {
            width: 100%;
            border-collapse: collapse;
        }

        .driver-table th, .driver-table td {
            padding: 12px 14px;
            text-align: center;
            border-bottom: 1px solid #e5e5e5;
        }

        .driver-table th {
            background: #f9fafc;
            font-size: 14px;
            font-weight: 600;
            color: #34495e;
            text-transform: uppercase;
        }

        .driver-table tr:hover {
            background: #f4f9ff;
        }

        /* Status colors */
        .status.available {
            color: #27ae60;
            font-weight: bold;
        }
        .status.busy {
            color: #e74c3c;
            font-weight: bold;
        }
        .status.offline {
            color: gray;
            font-weight: bold;
        }

        /* Buttons */
        .btn {
            border: none;
            border-radius: 6px;
            padding: 6px 10px;
            margin: 2px;
            cursor: pointer;
            font-size: 14px;
            color: white;
        }
        .btn-primary { background: #3498db; }
        .btn-danger { background: #e74c3c; }
        .btn-success { background: #27ae60; }
        .btn i { font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2>
            Drivers
            <a href="driver_form.php">
                <button class="btn btn-success"><i class="fa fa-plus"></i> Add Driver</button>
            </a>
        </h2>

        <table class="driver-table">
            <tr>
                <th>Driver ID</th>
                <th>Driver Name</th>
                <th>Truck Plate</th>
                <th>Contact</th>
                <th>Password</th>
                <th>Status</th>
                <th colspan="2">Actions</th>
            </tr>

            <?php
            $sql = "SELECT * FROM driver";
            $result = mysqli_query($connect, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($driver = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$driver['driver_id']}</td>
                            <td>{$driver['driver_name']}</td>
                            <td>{$driver['truck_plate']}</td>
                            <td>{$driver['contact_driver']}</td>
                            <td>{$driver['driver_pass']}</td>
                            <td><span class='status {$driver['current_status']}'>{$driver['current_status']}</span></td>
                            <td>
                                <a href='driver_update.php?driver_id=" . urlencode($driver['driver_id']) . "'>
                                    <button class='btn btn-primary'><i class='fa fa-edit'></i></button>
                                </a>
                                <a href='driver_delete.php?driver_id=" . urlencode($driver['driver_id']) . "' onclick=\"return confirm('Are you sure you want to delete this driver?')\">
                                    <button class='btn btn-danger'><i class='fa fa-trash'></i></button>
                                </a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No drivers found.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>
