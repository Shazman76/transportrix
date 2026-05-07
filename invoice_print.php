<?php
session_start();
include 'connect.php';
include 'invoice_lib.php';

if (!isset($_SESSION['userid']) || $_SESSION['status'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    exit('Not found.');
}

$invRes = mysqli_query($connect, "SELECT * FROM invoice WHERE invoice_id = {$id} LIMIT 1");
$invoice = $invRes ? mysqli_fetch_assoc($invRes) : null;
if (!$invoice) {
    http_response_code(404);
    exit('Invoice not found.');
}

$linesRes = mysqli_query($connect, "SELECT * FROM invoice_line WHERE invoice_id = {$id} ORDER BY line_no ASC");
$lines = [];
if ($linesRes) {
    while ($r = mysqli_fetch_assoc($linesRes)) {
        $lines[] = $r;
    }
}

$grand = invoice_lines_total($lines);
$words = invoice_ringgit_in_words($grand);
$invoiceLogoUrl = invoice_logo_relative_url();
$termDisplay = !empty($invoice['payment_term']) ? (string)$invoice['payment_term'] : '30 DAYS';
$linePages = array_chunk($lines, 20);
if (count($linePages) === 0) {
    $linePages = [[]];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?php echo htmlspecialchars($invoice['invoice_no']); ?></title>
    <style>
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0;
            font-family: Arial, sans-serif;
            font-size: 11px; color: #000;
            background: #f0f0f0;
        }
        .sheet {
            max-width: 210mm; margin: 10px auto;
            padding: 15mm; background: #fff;
            min-height: 297mm;
        }
        
        .hdr {
            display: flex; align-items: center; border-bottom: 2px solid #000;
            padding-bottom: 12px; margin-bottom: 20px;
        }
        .hdr-logo { flex: 0 0 130px; }
        .hdr-logo img { height: auto; width: 130px; display: block; object-fit: contain; }
        .hdr-text { flex: 1; text-align: center; }
        .co-name { font-size: 20px; font-weight: bold; margin: 0; }
        .co-addr { margin: 2px 0; font-size: 11px; font-weight: bold; }

        .meta-container { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .to-box { width: 55%; border: 1.5px solid #000; padding: 10px; min-height: 90px; }
        .to-label { font-weight: bold; text-decoration: underline; margin-bottom: 3px; }
        .to-details { font-weight: bold; line-height: 1.4; font-size: 12px; }
        
        .inv-info { width: 35%; }
        .inv-info table { width: 100%; border-collapse: collapse; }
        .inv-info td { padding: 2px 0; font-weight: bold; font-size: 12px; }
        .inv-info .val { text-align: left; padding-left: 10px; }

        table.grid {
            width: 100%; border-collapse: collapse;
            border-top: 2px solid #000; border-bottom: 2px solid #000;
        }
        table.grid th {
            border-bottom: 1.5px solid #000; padding: 8px 4px;
            font-size: 10px; text-align: center; background-color: #f9f9f9;
        }
        table.grid td { 
            padding: 10px 4px; border-bottom: none;
            font-weight: bold; vertical-align: middle;
            word-wrap: break-word; overflow-wrap: break-word;
        }
        
        /* Optimized widths to ensure Destination fits */
        .c-no { width: 25px; text-align: center; }
        .c-date { width: 65px; text-align: center; }
        .c-lorry { width: 80px; text-align: center; }
        .c-desc { text-align: center; line-height: 1.2; } /* Center text & allow wrapped lines to breathe */
        .c-ppt { width: 70px; text-align: center; }
        .c-mt { width: 75px; text-align: center; white-space: nowrap; font-size: 10.5px; } 
        .c-do { width: 65px; text-align: center; }
        .c-tot { width: 90px; text-align: right; }

        .total-row {
            border-bottom: 2px solid #000; padding: 10px 5px; 
            display: flex; justify-content: space-between; align-items: center; font-weight: bold;
        }
        .words-container {
            font-style: italic; font-size: 10px; text-transform: uppercase;
            flex: 1; padding-right: 15px;
        }
        .total-value-container { display: flex; font-size: 14px; white-space: nowrap; }

        .footer-section { margin-top: 25px; }
        .remark-box { width: 100%; font-size: 10px; line-height: 1.4; margin-bottom: 30px; }
        .remark-box strong { text-decoration: underline; }
        
        .sign-box { width: 250px; text-align: left; }
        .sign-line { border-bottom: 1px dotted #000; margin: 50px 0 5px 0; width: 100%; }

        .toolbar { text-align: center; margin: 20px 0; }
        .toolbar button { 
            padding: 8px 20px; background: #333; color: #fff; 
            border: none; border-radius: 3px; cursor: pointer; font-weight: bold;
        }

        @media print {
            body { background: #fff; }
            .sheet { box-shadow: none; margin: 0; width: 100%; padding: 0; }
            .toolbar { display: none; }
            .invoice-page + .invoice-page { page-break-before: always; }
        }
    </style>
</head>
<body>

<div class="sheet">
    <?php $i = 1; $totalPages = count($linePages); foreach ($linePages as $pageIdx => $pageLines): ?>
        <div class="invoice-page">
            <div class="hdr">
                <?php if ($invoiceLogoUrl): ?>
                <div class="hdr-logo"><img src="<?php echo htmlspecialchars($invoiceLogoUrl); ?>" alt="Logo"></div>
                <?php endif; ?>
                <div class="hdr-text">
                    <div class="co-name">MSF MAJU GLOBAL</div>
                    <div class="co-name"><span style="font-size: 12px; font-weight: normal;">( CA0364558-A )</span></div>
                    <div class="co-addr">NO 373 LORONG 10 TAMAN DESA DAMAI,</div>
                    <div class="co-addr">28700 BENTONG, PAHANG DARUL MAKMUR.</div>
                    <div class="co-addr">TEL: 016-627 4287 / 016-376 8526</div>
                </div>
                <?php if ($invoiceLogoUrl): ?><div class="hdr-logo" style="visibility:hidden;width:130px;"></div><?php endif; ?>
            </div>

            <div class="meta-container">
                <div class="to-box">
                    <div class="to-label">INVOICE TO:</div>
                    <div class="to-details">
                        <?php echo htmlspecialchars($invoice['client_name']); ?><br>
                        <?php echo nl2br(htmlspecialchars($invoice['client_address'])); ?>
                        <?php if($invoice['client_tel']): ?><br>TEL: <?php echo htmlspecialchars($invoice['client_tel']); ?><?php endif; ?>
                        <?php if(!empty($invoice['client_email'])): ?><br>EMAIL: <?php echo htmlspecialchars($invoice['client_email']); ?><?php endif; ?>
                    </div>
                </div>
                <div class="inv-info">
                    <table>
                        <tr><td>DATE</td><td class="val">: <?php echo date('d/m/Y', strtotime($invoice['invoice_date'])); ?></td></tr>
                        <tr><td>INVOICE NO</td><td class="val">: <?php echo htmlspecialchars($invoice['invoice_no']); ?></td></tr>
                        <tr><td>TERM</td><td class="val">: <?php echo htmlspecialchars($termDisplay); ?></td></tr>
                    </table>
                </div>
            </div>

            <table class="grid">
                <thead>
                    <tr>
                        <th class="c-no">NO</th>
                        <th class="c-date">DATE</th>
                        <th class="c-lorry">LORRY NO</th>
                        <th class="c-desc">DESCRIPTION / DESTINATION</th>
                        <th class="c-ppt">PRICE/ TON</th>
                        <th class="c-mt">MT</th>
                        <th class="c-do">D.O NO</th>
                        <th class="c-tot">TOTAL RM</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pageLines as $L): ?>
                        <tr>
                            <td class="c-no"><?php echo $i++; ?></td>
                            <td class="c-date"><?php echo date('d/m/y', strtotime($L['service_date'])); ?></td>
                            <td class="c-lorry"><?php echo htmlspecialchars($L['lorry_no']); ?></td>
                            <td class="c-desc"><?php echo nl2br(htmlspecialchars($L['description'])); ?></td>
                            <td class="c-ppt">
                                <?php echo (!empty($L['price_per_ton']) && $L['price_per_ton'] > 0) ? number_format($L['price_per_ton'], 2) : ''; ?>
                            </td>
                            <td class="c-mt">
                                <?php echo !empty($L['mt']) ? htmlspecialchars($L['mt']) : ''; ?>
                            </td>
                            <td class="c-do"><?php echo htmlspecialchars($L['do_no']); ?></td>
                            <td class="c-tot"><?php echo number_format($L['line_total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($pageIdx === $totalPages - 1): ?>
                <div class="total-row">
                    <div class="words-container">RINGGIT MALAYSIA: <?php echo htmlspecialchars($words); ?></div>
                    <div class="total-value-container">
                        <div style="margin-right: 15px;">TOTAL :</div>
                        <div>RM <?php echo number_format($grand, 2); ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($pageIdx === $totalPages - 1): ?>
            <div class="footer-section">
                    <div class="remark-box">
                        <strong>REMARK:</strong><br>
                        1. <?php echo htmlspecialchars($invoice['bank_instructions']); ?><br>
                        2. <?php echo htmlspecialchars($invoice['cheque_instructions']); ?>
                    </div>

                <div class="sign-box">
                    <div style="font-weight: bold;">MSF MAJU GLOBAL</div>
                    <div class="sign-line"></div>
                    <div style="font-weight: bold; text-transform: uppercase;"><?php echo htmlspecialchars($invoice['manager_name']); ?></div>
                    <div style="font-size: 9px;"><?php echo htmlspecialchars($invoice['manager_title']); ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="toolbar">
    <button type="button" onclick="window.history.back()">BACK</button>
    <button type="button" onclick="window.print()">PRINT / SAVE PDF</button>
</div>

</body>
</html>