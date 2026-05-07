<?php
session_start();
include('connect.php');

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$month = isset($_GET['month']) ? trim($_GET['month']) : date('m');
$monthNum = (int)$month;
if ($monthNum < 1 || $monthNum > 12) {
    $monthNum = (int)date('m');
}
$month = str_pad((string)$monthNum, 2, '0', STR_PAD_LEFT);
$year = date('Y');

$company = null;
$trips = [];
$tripCount = 0;
$totalPrice = 0.00;
$pricePerTrip = 0.00; // fallback when no trip pricing exists yet in DB
$error = '';

if ($email === '') {
    $error = 'Missing customer email.';
} else {
    $emailEsc = mysqli_real_escape_string($connect, $email);
    $monthEsc = mysqli_real_escape_string($connect, $month);
    $yearEsc = mysqli_real_escape_string($connect, (string)$year);

    $companySql = "SELECT cust_name, address, cust_contact, cust_email 
                   FROM customer 
                   WHERE cust_email = '{$emailEsc}' 
                   LIMIT 1";
    $companyRes = mysqli_query($connect, $companySql);
    if ($companyRes) {
        $company = mysqli_fetch_assoc($companyRes);
    }

    $sql = "SELECT o.Order_Date, o.Destination, o.Order_address, s.truck_plate, s.Ship_ID
            FROM cust_order o
            INNER JOIN shipment s ON o.Ship_ID = s.Ship_ID
            WHERE o.Cust_Email = '{$emailEsc}'
              AND MONTH(o.Order_Date) = '{$monthEsc}'
              AND YEAR(o.Order_Date) = '{$yearEsc}'
              AND o.Order_Status = 'Processing'
            ORDER BY o.Order_Date ASC, o.Ship_ID ASC";
    $res = mysqli_query($connect, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $trips[] = $row;
        }
    }

    $tripCount = count($trips);
    $totalPrice = $tripCount * $pricePerTrip;
}

include('navBar.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Monthly Invoice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f6fa; }
        .container { padding: 30px; }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 25px;
        }
        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 20px;
        }
        .company-box h2 { margin: 0 0 6px 0; color: #2c3e50; }
        .company-box p { margin: 2px 0; color: #555; }
        .meta-box { text-align: right; color: #34495e; font-weight: 700; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: #fff;
        }
        th, td {
            border-bottom: 1px solid #e9ecef;
            padding: 10px 12px;
            text-align: left;
        }
        th {
            background: #f9fafc;
            text-transform: uppercase;
            font-size: 12px;
            color: #2c3e50;
        }
        .right { text-align: right; }
        .destination { font-weight: 700; }
        .total-box {
            margin-top: 18px;
            display: flex;
            justify-content: flex-end;
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }
        .actions { margin-top: 22px; display: flex; gap: 10px; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            border-radius: 6px;
            padding: 10px 14px;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            font-weight: 700;
        }
        .btn-success { background: #27ae60; }
        .btn-dark { background: #2c3e50; }
        .note {
            margin-top: 12px;
            font-size: 12px;
            color: #7f8c8d;
        }
        .err {
            background: #fee;
            color: #c0392b;
            border-left: 4px solid #c0392b;
            padding: 12px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <?php if ($error !== ''): ?>
            <div class="err"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="top-row">
            <div class="company-box">
                <h2><?php echo htmlspecialchars($company['cust_name'] ?? $email); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($company['address'] ?? '-')); ?></p>
                <p>Email: <?php echo htmlspecialchars($company['cust_email'] ?? $email); ?></p>
                <p>Contact: <?php echo htmlspecialchars($company['cust_contact'] ?? '-'); ?></p>
            </div>
            <div class="meta-box">
                <div>MONTH: <?php echo date('F', mktime(0, 0, 0, $monthNum, 1)); ?></div>
                <div>YEAR: <?php echo htmlspecialchars($year); ?></div>
                <div>PENDING TRIPS: <?php echo $tripCount; ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Date</th>
                    <th>Truck Plate</th>
                    <th>From</th>
                    <th>Destination</th>
                    <th class="right">Trip Price (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tripCount === 0): ?>
                    <tr>
                        <td colspan="6">No pending trips found for this company in the selected month.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($trips as $idx => $trip): ?>
                        <tr>
                            <td><?php echo $idx + 1; ?></td>
                            <td><?php echo htmlspecialchars($trip['Order_Date']); ?></td>
                            <td><?php echo htmlspecialchars($trip['truck_plate'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($trip['Order_address'] ?: '-'); ?></td>
                            <td class="destination"><?php echo htmlspecialchars($trip['Destination']); ?></td>
                            <td class="right"><?php echo number_format($pricePerTrip, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="total-box">
            TOTAL PRICE: RM <?php echo number_format($totalPrice, 2); ?>
        </div>
        <div class="note">
            Pricing is currently calculated as Pending Trips x Price Per Trip (default RM 0.00). 
            Add a per-trip/company rate field later to enable automatic monetary totals.
        </div>

        <div class="actions">
            <a href="invoice_list.php" class="btn btn-dark"><i class="fa fa-arrow-left"></i> Back</a>
            <button type="button" class="btn btn-success" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
</div>
</body>
</html>
