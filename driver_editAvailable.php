<?php
session_start();
include('connect.php');
include('navBarDiver.php');

// Get driver ID from session
$driver_id = $_SESSION['userid'];

// Fetch current driver details
$sql = "SELECT * FROM driver WHERE driver_id = '$driver_id'";
$result = mysqli_query($connect, $sql);
$driver = mysqli_fetch_assoc($result);

// Handle form submission to update status
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];

    $update_sql = "UPDATE driver SET current_status = '$new_status' WHERE driver_id = '$driver_id'";
    $update_result = mysqli_query($connect, $update_sql);

    if ($update_result) {
        echo "<script>alert('Availability updated successfully'); window.location='driver_editAvailable.php';</script>";
    } else {
        echo "<script>alert('Failed to update availability');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Driver Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f2f2f2;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }

        .profile-container {
            max-width: 500px;
            margin: 60px auto;
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.25);
            border: 2px solid #DE0003;
        }

        .profile-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #DE0003;
            font-weight: 700;
        }

        .profile-table {
            width: 100%;
            border-collapse: collapse;
        }

        .profile-table td {
            padding: 12px 8px;
            vertical-align: top;
            font-size: 16px;
            color: #333;
        }

        .profile-table td:first-child {
            font-weight: bold;
            color: #555;
            width: 40%;
        }

        select {
            width: 100%;
            padding: 10px;
            font-size: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        button {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: #DE0003;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #a70000;
        }

        @media (max-width: 600px) {
            .profile-container {
                margin: 30px 15px;
                padding: 25px;
            }

            .profile-table td {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>

<div class="profile-container">
    <h2>Driver Profile</h2>
    <form method="post">
        <table class="profile-table">
            <tr>
                <td>Driver ID:</td>
                <td><?php echo htmlspecialchars($driver['driver_id']); ?></td>
            </tr>
            <tr>
                <td>Name:</td>
                <td><?php echo htmlspecialchars($driver['driver_name']); ?></td>
            </tr>
            <tr>
                <td>Truck Plate:</td>
                <td><?php echo htmlspecialchars($driver['truck_plate']); ?></td>
            </tr>
            <tr>
                <td>Contact:</td>
                <td><?php echo htmlspecialchars($driver['contact_driver']); ?></td>
            </tr>
            <tr>
                <td>Availability:</td>
                <td>
                    <select name="status" required>
                        <option value="Available" <?php if ($driver['current_status'] == 'Available') echo 'selected'; ?>>Available</option>
                        <option value="Not Available" <?php if ($driver['current_status'] == 'Not Available') echo 'selected'; ?>>Not Available</option>
                        <option value="On Duty" <?php if ($driver['current_status'] == 'On Duty') echo 'selected'; ?>>On Duty</option>
                    </select>
                </td>
            </tr>
        </table>

        <button type="submit" name="update_status">Update Availability</button>
    </form>
</div>

</body>
</html>
