<?php
session_start();
include('connect.php');

if (!isset($_SESSION['userid']) || $_SESSION['status'] !== 'admin') {
    header("Location: login.php");
    exit();
}

function safeCount($connect, $table) {
    $res = @mysqli_query($connect, "SELECT COUNT(*) AS total FROM {$table}");
    if (!$res) return 0;
    $row = mysqli_fetch_assoc($res);
    return (int)($row['total'] ?? 0);
}

$totalInvoices = safeCount($connect, 'invoice');
$totalCustomers = safeCount($connect, 'customer');
$totalDrivers = safeCount($connect, 'driver');
$totalTrucks = safeCount($connect, 'truck');
$totalPriceRules = safeCount($connect, 'price_list');

$recentInvoices = [];
$invRes = @mysqli_query($connect, "SELECT invoice_no, invoice_date, client_name, created_at FROM invoice ORDER BY invoice_id DESC LIMIT 10");
if ($invRes) {
    while ($r = mysqli_fetch_assoc($invRes)) {
        $recentInvoices[] = $r;
    }
}

include("navBar.php");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { background: #f8f9fc; font-family: Arial, Helvetica, sans-serif; margin: 0; }
    .content { padding: 10px 0; }
    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin: 16px 0;
    }
    .card {
        background: #fff;
        padding: 18px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        text-align: center;
    }
    .card h2 { margin: 8px 0; font-size: 28px; color: #2c3e50; }
    .card h3 { margin: 0; font-size: 15px; color: #666; font-weight: 600; }
    .quick-actions, .table-wrap {
        background: #fff;
        padding: 18px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 16px;
    }
    .quick-actions h2, .table-wrap h2 { margin: 0 0 12px 0; font-size: 20px; color: #2c3e50; }
    .quick-grid { display: flex; flex-wrap: wrap; gap: 10px; }
    .quick-btn {
        display: inline-flex; align-items: center; gap: 8px;
        background: #27ae60; color: #fff; text-decoration: none;
        padding: 10px 14px; border-radius: 6px; font-weight: 600;
    }
    .quick-btn.alt { background: #2980b9; }
    .quick-btn.dark { background: #2c3e50; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #e5e7eb; padding: 9px; text-align: left; }
    th { background: #f4f6fa; }
</style>
</head>
<body>

<div class="content">
    <h1>Welcome Admin</h1>

    <div class="cards">
        <div class="card" style="border-top: 5px solid #3498db;">
            <i class="fa fa-file-invoice fa-lg"></i>
            <h2><?php echo $totalInvoices; ?></h2>
            <h3>Total Invoices</h3>
        </div>
        <div class="card" style="border-top: 5px solid #9b59b6;">
            <i class="fa fa-tags fa-lg"></i>
            <h2><?php echo $totalPriceRules; ?></h2>
            <h3>Price Rules</h3>
        </div>
        <div class="card" style="border-top: 5px solid #2ecc71;">
            <i class="fa fa-users fa-lg"></i>
            <h2><?php echo $totalCustomers; ?></h2>
            <h3>Customers</h3>
        </div>
        <div class="card" style="border-top: 5px solid #e67e22;">
            <i class="fa fa-id-card fa-lg"></i>
            <h2><?php echo $totalDrivers; ?></h2>
            <h3>Drivers</h3>
        </div>
        <div class="card" style="border-top: 5px solid #16a085;">
            <i class="fa fa-truck fa-lg"></i>
            <h2><?php echo $totalTrucks; ?></h2>
            <h3>Trucks</h3>
        </div>
    </div>

    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="quick-grid">
            <a class="quick-btn" href="invoice_form.php"><i class="fa fa-plus"></i> New Invoice</a>
            <a class="quick-btn alt" href="invoice_list.php"><i class="fa fa-folder-open"></i> View Invoices</a>
            <a class="quick-btn dark" href="manage_prices.php"><i class="fa fa-tags"></i> Manage Price Lookup</a>
        </div>
    </div>

    <div class="table-wrap">
        <h2>Recent Invoices</h2>
        <table>
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recentInvoices) === 0): ?>
                    <tr><td colspan="4">No invoices found yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentInvoices as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['invoice_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
