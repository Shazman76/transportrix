<?php
session_start();
include 'connect.php';
include 'invoice_lib.php';

if (!isset($_SESSION['userid']) || $_SESSION['status'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$res = @mysqli_query($connect, 'SELECT invoice_id, invoice_no, invoice_date, client_name, created_at FROM invoice ORDER BY invoice_date DESC, invoice_id DESC');
$dbError = $res ? null : mysqli_error($connect);
$invoicesByClient = [];
$clientOrder = [];
$clientCounts = [];

if (!$dbError && $res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $clientName = trim((string)($row['client_name'] ?? '')) !== '' ? (string)$row['client_name'] : 'Unknown';
        if (!isset($invoicesByClient[$clientName])) {
            $invoicesByClient[$clientName] = [];
            $clientOrder[] = $clientName;
        }
        $invoicesByClient[$clientName][] = $row;
    }

    foreach ($clientOrder as $cn) {
        $clientCounts[$cn] = count($invoicesByClient[$cn]);
    }
}

include 'navBar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoices</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; background: #f4f6fa; }
        .container { padding: 30px; }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 20px;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .card h2 { margin: 0; font-size: 20px; font-weight: 600; color: #2c3e50; }
        .btn-add {
            background: #27ae60;
            color: #fff;
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-add:hover { background: #219150; }
        .btn-back {
            background: #6c757d;
            color: #fff;
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .orders-table { width: 100%; border-collapse: collapse; }
        .orders-table th, .orders-table td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #e5e5e5;
        }
        .orders-table th {
            background: #f9fafc;
            font-size: 13px;
            font-weight: 600;
            color: #34495e;
            text-transform: uppercase;
        }
        .orders-table tr:hover { background: #f4f9ff; }
        .btn {
            border: none;
            border-radius: 6px;
            padding: 6px 10px;
            margin: 2px;
            cursor: pointer;
            font-size: 14px;
            color: #fff;
            text-decoration: none;
            display: inline-block;
        }
        .btn-print { background: #3498db; }
        .btn-del { background: #e74c3c; }
        .accordion-item { border: 1px solid #e5e5e5; border-radius: 10px; margin-bottom: 12px; overflow: hidden; }
        .accordion-button { background: #fff; font-weight: 600; border-radius: 10px; }
        .accordion-button:not(.collapsed) { color: #2c3e50; background: #f9fafc; }
        .accordion-body { padding: 0; }
        .chevron-icon {
            margin-left: auto;
            transition: transform .2s ease;
            color: #34495e;
        }
        .accordion-button:not(.collapsed) .chevron-icon { transform: rotate(180deg); }
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .alert code { background: rgba(0,0,0,0.06); padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2><i class="fa fa-file-invoice"></i> Invoices (MSF style)</h2>
                <div>
                    <a href="Menu.php" class="btn-back"><i class="fa fa-arrow-left"></i> Back</a>
                    <a href="invoice_form.php" class="btn-add"><i class="fa fa-plus"></i> New invoice</a>
                </div>
            </div>

            <?php if ($dbError): ?>
                <div class="alert">
                    <strong>Database:</strong> invoice tables are missing or the query failed.
                    Import <code>DATABASE/invoice_tables.sql</code> in phpMyAdmin, then refresh this page.
                    <br><small><?php echo htmlspecialchars($dbError); ?></small>
                </div>
            <?php else: ?>
                <?php if (count($clientOrder) === 0): ?>
                    <div class="alert" style="background:#f8f9fa; border-color:#e5e5e5; color:#555;">
                        No invoices found.
                    </div>
                <?php else: ?>
                    <div class="accordion" id="invoiceAccordion">
                        <?php foreach ($clientOrder as $clientName): ?>
                            <?php
                                $hash = md5($clientName);
                                $headingId = 'heading_' . $hash;
                                $collapseId = 'collapse_' . $hash;
                                $invoices = $invoicesByClient[$clientName];
                                $cnt = $clientCounts[$clientName] ?? count($invoices);
                            ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="<?php echo htmlspecialchars($headingId); ?>">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#<?php echo htmlspecialchars($collapseId); ?>"
                                            aria-expanded="false"
                                            aria-controls="<?php echo htmlspecialchars($collapseId); ?>">
                                        <i class="fas fa-folder me-2"></i>
                                        <span><?php echo htmlspecialchars($clientName); ?></span>
                                        <span class="badge bg-secondary ms-3"><?php echo (int)$cnt; ?> invoices</span>
                                        <i class="fas fa-chevron-down chevron-icon" aria-hidden="true"></i>
                                    </button>
                                </h2>
                                <div id="<?php echo htmlspecialchars($collapseId); ?>" class="accordion-collapse collapse"
                                     aria-labelledby="<?php echo htmlspecialchars($headingId); ?>" data-bs-parent="#invoiceAccordion">
                                    <div class="accordion-body">
                                        <table class="orders-table">
                                            <thead>
                                                <tr>
                                                    <th>Invoice no.</th>
                                                    <th>Date</th>
                                                    <th>Created</th>
                                                    <th style="text-align:center;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($invoices as $row): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                                                        <td><?php echo htmlspecialchars(invoice_format_dmY($row['invoice_date'])); ?></td>
                                                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                                        <td style="text-align:center;">
                                                            <a class="btn btn-print" href="invoice_print.php?id=<?php echo (int) $row['invoice_id']; ?>" target="_blank" title="Print / PDF">
                                                                <i class="fa fa-print"></i>
                                                            </a>
                                                            <a class="btn btn-del" href="invoice_delete.php?id=<?php echo (int) $row['invoice_id']; ?>" onclick="return confirm('Delete this invoice?');" title="Delete">
                                                                <i class="fa fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
