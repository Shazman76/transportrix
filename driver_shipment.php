<?php
session_start();
include('connect.php');
include("navBarDiver.php");

// Get current logged-in driver info
$driver_id = $_SESSION['userid'];
$driver_name = $_SESSION['name'];

// ? Handle status update
if (isset($_POST['update_status'])) {
    $ship_id = $_POST['ship_id'];
    $new_status = $_POST['new_status'];
    $update_sql = "UPDATE shipment SET Shipment_Stat = '$new_status' WHERE Ship_ID = '$ship_id' AND driver_id = '$driver_id'";
    mysqli_query($connect, $update_sql);
}

// ? Query shipments for this driver
$sql = "SELECT 
            shipment.Ship_ID, 
            shipment.Shipment_Stat, 
            shipment.Delivery_Date, 
            shipment.truck_plate, 
            shipment.admin_id, 
            cust_order.Order_address, 
            cust_order.Destination 
        FROM shipment
        JOIN cust_order ON shipment.Ship_ID = cust_order.Ship_ID
        WHERE shipment.driver_id = '$driver_id'
        ORDER BY shipment.Delivery_Date DESC";
$result = mysqli_query($connect, $sql);
?>

<!-- ? Bootstrap / CSS style similar to your screenshot -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<div class="container mt-4">
    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <h4 class="mb-4">Shipments for Driver: <?php echo $driver_name; ?></h4>
            
            <table class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ship ID</th>
                        <th>Admin ID</th>
                        <th>Shipment Date</th>
                        <th>Pickup Address</th>
                        <th>Destination</th>
                        <th>Truck Plate</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_array($result)) {
                        // ? Status badge styling
                        $statusClass = "secondary";
                        if ($row['Shipment_Stat'] == "Pending") $statusClass = "warning";
                        if ($row['Shipment_Stat'] == "In Transit") $statusClass = "info";
                        if ($row['Shipment_Stat'] == "Completed") $statusClass = "success";
                        
                        echo "<tr>
                                <td>{$row['Ship_ID']}</td>
                                <td>{$row['admin_id']}</td>
                                <td>{$row['Delivery_Date']}</td>
                                <td>{$row['Order_address']}</td>
                                <td>{$row['Destination']}</td>
                                <td>{$row['truck_plate']}</td>
                                <td><span class='badge bg-$statusClass'>{$row['Shipment_Stat']}</span></td>
                                <td>
                                    <form method='POST' class='d-flex'>
                                        <input type='hidden' name='ship_id' value='{$row['Ship_ID']}'>
                                        <select name='new_status' class='form-select form-select-sm me-2' required>
                                            <option value='' disabled selected>Change</option>
                                            <option value='Pending'>Pending</option>
                                            <option value='In Transit'>In Transit</option>
                                            <option value='Completed'>Completed</option>
                                        </select>
                                        <button type='submit' name='update_status' class='btn btn-sm btn-primary'>Update</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center text-muted'>Tiada penghantaran untuk dipaparkan.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
