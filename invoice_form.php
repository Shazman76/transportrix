<?php
session_start();
include 'connect.php';
include 'invoice_lib.php';

if (!isset($_SESSION['userid']) || $_SESSION['status'] !== 'admin') {
    header('Location: login.php');
    exit();
}

@mysqli_query($connect, "ALTER TABLE invoice ADD COLUMN IF NOT EXISTS payment_term varchar(80) NOT NULL DEFAULT '30 DAYS' AFTER invoice_no");

$defaults = invoice_company_defaults();
$error = '';
$posted = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($posted && isset($_POST['save_invoice'])) {
    $invoice_date = $_POST['invoice_date'] ?? '';
    $invoice_no = trim($_POST['invoice_no'] ?? '');
    $client_name = trim($_POST['client_name'] ?? '');
    $client_address = trim($_POST['client_address'] ?? '');
    $client_tel = trim($_POST['client_tel'] ?? '');
    $client_email = trim($_POST['client_email'] ?? ''); 
    $payment_term = trim($_POST['payment_term'] ?? '30 DAYS');
    $bank_instructions = trim($_POST['bank_instructions'] ?? '');
    $cheque_instructions = trim($_POST['cheque_instructions'] ?? '');
    $manager_title = trim($_POST['manager_title'] ?? 'MANAGER');
    $manager_name = trim($_POST['manager_name'] ?? '');

    $line_dates = $_POST['line_service_date'] ?? [];
    $line_lorry_sel = $_POST['line_lorry_select'] ?? [];
    $line_lorry_custom = $_POST['line_lorry_custom'] ?? [];
    $line_desc = $_POST['line_description'] ?? [];
    $line_basis = $_POST['line_basis'] ?? [];
    $line_ton_qty = $_POST['line_ton_qty'] ?? [];
    $line_price = $_POST['line_price_per_ton'] ?? [];
    $line_mt = $_POST['line_mt'] ?? [];
    $line_do = $_POST['line_do'] ?? [];
    $line_total = $_POST['line_total'] ?? [];

    if ($invoice_no === '') {
        $invoice_no = invoice_next_number($connect);
    }

    if ($client_name === '' || $client_address === '') {
        $error = 'Client name and address are required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $invoice_date)) {
        $error = 'Please choose a valid invoice date.';
    } else {
        $lines = [];
        $n = max(count($line_dates), count($line_desc), count($line_total));
        for ($i = 0; $i < $n; $i++) {
            $d = trim($line_dates[$i] ?? '');
            $desc = trim($line_desc[$i] ?? '');
            $totRaw = trim($line_total[$i] ?? '');
            if ($desc === '' && $totRaw === '') continue;

            $basis = strtoupper(trim($line_basis[$i] ?? 'LOAD'));
            if ($basis !== 'TON' && $basis !== 'LOAD') {
                $basis = 'LOAD';
            }
            $pp = trim($line_price[$i] ?? '');
            $priceVal = ($pp !== '') ? round((float) str_replace([',', ' '], '', $pp), 2) : null;
            $tonQtyRaw = trim($line_ton_qty[$i] ?? '');
            $tonQtyVal = ($tonQtyRaw !== '') ? round((float) str_replace([',', ' '], '', $tonQtyRaw), 3) : 0.0;
            
            $lorrySel = trim($line_lorry_sel[$i] ?? '');
            $lorryNo = ($lorrySel === '__OTHER__') ? trim($line_lorry_custom[$i] ?? '') : $lorrySel;

            if ($basis === 'TON') {
                if ($priceVal === null || $tonQtyVal <= 0) {
                    $error = 'For TON basis, enter both price/ton and ton quantity.';
                    break;
                }
                $tot = round($priceVal * $tonQtyVal, 2);
                $mtValue = rtrim(rtrim(number_format($tonQtyVal, 3, '.', ''), '0'), '.') . ' TON';
            } else {
                $tot = round((float) str_replace([',', ' '], '', $totRaw), 2);
                if ($tot <= 0) {
                    $error = 'For LOAD basis, enter total RM.';
                    break;
                }
                $mtValue = '1 LOAD';
            }

            if ($desc === '') {
                $error = 'Description is required for each invoice row.';
                break;
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                $error = 'Please enter a valid service date on each row.';
                break;
            }
            if ($lorrySel === '__OTHER__' && $lorryNo === '') {
                $error = 'Please enter custom lorry no when Other is selected.';
                break;
            }

            $lines[] = [
                'service_date' => $d,
                'lorry_no' => $lorryNo,
                'description' => $desc,
                'price_per_ton' => $priceVal,
                'mt' => $mtValue,
                'do_no' => trim($line_do[$i] ?? ''),
                'line_total' => $tot,
            ];
        }
        if ($error === '' && count($lines) === 0) $error = 'Add at least one line item.';
    }

    if ($error === '') {
        $dup = mysqli_query($connect, "SELECT invoice_id FROM invoice WHERE invoice_no = '" . invoice_escape($connect, $invoice_no) . "' LIMIT 1");
        if ($dup && mysqli_fetch_assoc($dup)) {
            $error = 'Invoice number already exists.';
        }
    }

    if ($error === '') {
        mysqli_begin_transaction($connect);
        $sqlInv = sprintf(
            "INSERT INTO invoice (invoice_no, payment_term, invoice_date, client_name, client_address, client_tel, client_email, bank_instructions, cheque_instructions, manager_title, manager_name, admin_id) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
            invoice_escape($connect, $invoice_no), invoice_escape($connect, $payment_term), invoice_escape($connect, $invoice_date), invoice_escape($connect, $client_name), invoice_escape($connect, $client_address), invoice_escape($connect, $client_tel), invoice_escape($connect, $client_email), invoice_escape($connect, $bank_instructions), invoice_escape($connect, $cheque_instructions), invoice_escape($connect, $manager_title), invoice_escape($connect, $manager_name), invoice_escape($connect, (string)$_SESSION['userid'])
        );

        if (mysqli_query($connect, $sqlInv)) {
            $invoice_id = (int)mysqli_insert_id($connect);
            $ok = true;
            foreach ($lines as $idx => $L) {
                $ppSql = $L['price_per_ton'] === null ? 'NULL' : "'" . invoice_escape($connect, (string)$L['price_per_ton']) . "'";
                $sqlLine = sprintf(
                    "INSERT INTO invoice_line (invoice_id, line_no, service_date, lorry_no, description, price_per_ton, mt, do_no, line_total) VALUES (%d,%d,'%s','%s','%s',%s,'%s','%s','%s')",
                    $invoice_id, $idx + 1, invoice_escape($connect, $L['service_date']), invoice_escape($connect, $L['lorry_no']), invoice_escape($connect, $L['description']), $ppSql, invoice_escape($connect, $L['mt']), invoice_escape($connect, $L['do_no']), invoice_escape($connect, (string)$L['line_total'])
                );
                if (!mysqli_query($connect, $sqlLine)) { $ok = false; break; }
            }
            if ($ok) {
                mysqli_commit($connect);
                header('Location: invoice_print.php?id=' . $invoice_id);
                exit();
            }
        }
        mysqli_rollback($connect);
        $error = 'Database error occurred.';
    }
}

$customersRes = @mysqli_query($connect, 'SELECT cust_email, cust_name, cust_contact, address FROM customer ORDER BY cust_name');
$truckPlates = [];
$trucksRes = @mysqli_query($connect, 'SELECT truck_plate FROM truck ORDER BY truck_plate ASC');
if ($trucksRes) { while ($t = mysqli_fetch_assoc($trucksRes)) { $truckPlates[] = $t['truck_plate']; } }

// Pull unique destinations from price_list
$destinationOptions = [];
$destRes = @mysqli_query($connect, "SELECT DISTINCT location_name FROM price_list WHERE location_name <> '' ORDER BY location_name ASC");
if ($destRes) { while ($d = mysqli_fetch_assoc($destRes)) { $destinationOptions[] = $d['location_name']; } }

$priceLookupRows = [];
$priceRes = @mysqli_query($connect, "SELECT location_name, basis, unit_price, id FROM price_list ORDER BY id ASC");
if ($priceRes) { while ($p = mysqli_fetch_assoc($priceRes)) { $priceLookupRows[] = $p; } }

$suggestedNo = invoice_next_number($connect);
$today = date('Y-m-d');
$invoiceLogoUrl = invoice_logo_relative_url();

include 'navBar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invois Baharu - MSF MAJU GLOBAL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f8f9fa; color: #000; }
        .wrap { padding: 30px; max-width: 1100px; margin: auto; }
        .card { background: #fff; padding: 40px; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .comp-header { border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .comp-header-inner { display: flex; align-items: center; justify-content: space-between; }
        .comp-logo { flex: 0 0 130px; }
        .comp-logo img { width: 100%; height: auto; display: block; object-fit: contain; }
        .comp-text { flex: 1; text-align: center; padding-right: 130px; }
        .comp-header h2 { margin: 0; font-size: 26px; font-weight: bold; }
        .comp-header p { margin: 2px 0; font-size: 14px; font-weight: bold; }
        .invoice-meta { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .client-box { width: 50%; border: 1.5px solid #000; padding: 15px; min-height: 140px; }
        .date-box { width: 35%; text-align: right; }
        label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
        input, textarea, select { border: 1px solid #ccc; padding: 8px; font-size: 14px; width: 100%; box-sizing: border-box; }
        table.lines { width: 100%; border-collapse: collapse; margin-top: 20px; border-top: 2px solid #000; border-bottom: 2px solid #000; }
        table.lines th { border-bottom: 1px solid #000; padding: 10px 5px; font-size: 12px; text-align: center; }
        table.lines td { padding: 0; border-bottom: 1px dotted #ccc; }
        table.lines input, table.lines select { border: none; background: transparent; text-align: center; padding: 10px 5px; }
        .row-idx { text-align: center; font-weight: bold; width: 40px; }
        .btn-action { width: 40px; text-align: center; background: #fafafa; }
        .total-strip { border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 10px; text-align: right; font-weight: bold; margin-top: 5px; font-size: 16px; }
        .footer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; }
        .btn { padding: 12px 25px; border: none; cursor: pointer; font-weight: bold; border-radius: 4px; }
        .btn-save { background: #000; color: #fff; }
        .btn-add { background: #eee; color: #000; margin-top: 15px; }
        .btn-back { background: #6c757d; color: #fff; margin-right: 8px; text-decoration: none; display: inline-block; }
        .row-add-tools { margin-top: 15px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .row-add-tools input { width: 110px; }
        .err { background: #fee; border-left: 5px solid #c00; padding: 15px; margin-bottom: 20px; color: #c00; font-weight: bold; }
    </style>
</head>
<body>

<div class="wrap">
    <div class="card">
        <div class="comp-header">
            <div class="comp-header-inner">
                <div class="comp-logo">
                    <?php if ($invoiceLogoUrl): ?>
                        <img src="<?php echo htmlspecialchars($invoiceLogoUrl); ?>" alt="Logo">
                    <?php endif; ?>
                </div>
                <div class="comp-text">
                    <h2>MSF MAJU GLOBAL <span style="font-size: 16px; font-weight: normal;">( CA0364558-A )</span></h2>
                    <p>NO 373 LORONG 10 TAMAN DESA DAMAI,</p>
                    <p>28700 BENTONG, PAHANG DARUL MAKMUR.</p>
                    <p>TEL: 016-627 4287 / 016-376 8526</p>
                </div>
            </div>
        </div>

        <?php if ($error): ?><div class="err"><?php echo $error; ?></div><?php endif; ?>

        <form method="post" id="invForm">
            <input type="hidden" name="save_invoice" value="1">

            <div class="invoice-meta">
                <div class="client-box">
                    <label>INVOICE TO:</label>
                    <select id="cust_pick" style="margin-bottom:10px; border-color:#000;">
                        <option value="">-- PILIH PELANGGAN (OPSIONAL) --</option>
                        <?php mysqli_data_seek($customersRes, 0); while ($c = mysqli_fetch_assoc($customersRes)): ?>
                            <option value="<?php echo htmlspecialchars($c['cust_email']); ?>" 
                                    data-name="<?php echo htmlspecialchars($c['cust_name']); ?>" 
                                    data-address="<?php echo htmlspecialchars($c['address']); ?>" 
                                    data-tel="<?php echo htmlspecialchars($c['cust_contact']); ?>"
                                    data-email="<?php echo htmlspecialchars($c['cust_email']); ?>">
                                <?php echo htmlspecialchars($c['cust_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <input type="text" name="client_name" id="client_name" placeholder="NAMA SYARIKAT" style="font-weight:bold; border:none;" required>
                    <textarea name="client_address" id="client_address" placeholder="ALAMAT" style="border:none; height:60px;" required></textarea>
                    <input type="text" name="client_tel" id="client_tel" placeholder="TEL NO" style="border:none;">
                    <input type="email" name="client_email" id="client_email" placeholder="EMAIL" style="border:none; margin-top:5px;">
                </div>

                <div class="date-box">
                    <div style="margin-bottom: 15px;">
                        <label>DATE</label>
                        <input type="date" name="invoice_date" value="<?php echo $today; ?>" style="width: 180px; text-align: right;">
                    </div>
                    <div>
                        <label>INVOICE NO</label>
                        <input type="text" name="invoice_no" value="<?php echo $suggestedNo; ?>" style="width: 180px; text-align: right; font-weight: bold;">
                    </div>
                    <div style="margin-top: 15px;">
                        <label>TERM</label>
                        <input type="text" name="payment_term" value="<?php echo htmlspecialchars($_POST['payment_term'] ?? '30 DAYS'); ?>" style="width: 180px; text-align: right; font-weight: bold;">
                    </div>
                </div>
            </div>

            <table class="lines">
                <thead>
                    <tr>
                        <th style="width:40px;">NO</th>
                        <th style="width:110px;">DATE</th>
                        <th style="width:120px;">LORRY NO</th>
                        <th>DESCRIPTION</th>
                        <th style="width:85px;">BASIS</th>
                        <th style="width:85px;">TON QTY</th>
                        <th style="width:100px;">PRICE/TON</th>
                        <th style="width:95px;">MT</th>
                        <th style="width:100px;">D.O NO</th>
                        <th style="width:120px;">TOTAL RM</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody id="linesBody"></tbody>
            </table>

            <div class="total-strip">
                JUMLAH: RM <span id="grandTotalDisplay">0.00</span>
            </div>

            <div class="row-add-tools">
                <button type="button" class="btn btn-add" id="addRow"><i class="fa fa-plus"></i> TAMBAH 1 BARIS</button>
                <input type="number" id="rowCountInput" min="1" step="1" value="1" placeholder="Bilangan baris">
                <button type="button" class="btn btn-add" id="addRowsByCount"><i class="fa fa-list"></i> TAMBAH IKUT JUMLAH</button>
            </div>

            <div class="footer-grid">
                <div>
                    <label>BANKING INFO (REMARK 1)</label>
                    <textarea name="bank_instructions" rows="3" style="margin-bottom:10px;"><?php echo htmlspecialchars($defaults['bank_instructions']); ?></textarea>
                    
                    <label>CHEQUE INSTRUCTIONS (REMARK 2)</label>
                    <textarea name="cheque_instructions" rows="3"><?php echo htmlspecialchars($defaults['cheque_instructions']); ?></textarea>
                </div>
                <div style="text-align: right;">
                    <label>AUTHORISED SIGNATORY</label>
                    <input type="text" name="manager_name" value="<?php echo htmlspecialchars($defaults['manager_name']); ?>" style="text-align: right; margin-bottom: 5px; font-weight: bold;">
                    <input type="text" name="manager_title" value="<?php echo htmlspecialchars($defaults['manager_title']); ?>" style="text-align: right;">
                    <br><br>
                    <a href="invoice_list.php" class="btn btn-back"><i class="fa fa-arrow-left"></i> BACK</a>
                    <button type="submit" class="btn btn-save"><i class="fa fa-save"></i> SIMPAN & CETAK INVOIS</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var body = document.getElementById('linesBody');
    var today = <?php echo json_encode($today); ?>;
    var truckPlates = <?php echo json_encode($truckPlates); ?>;
    var destinationOptions = <?php echo json_encode($destinationOptions); ?>;
    var priceLookupRows = <?php echo json_encode($priceLookupRows); ?>;
    var priceLookupMap = {};

    function normalizeKey(v) {
        return String(v || '').trim().replace(/\s+/g, ' ').toLowerCase();
    }

    priceLookupRows.forEach(function (r) {
        var key = normalizeKey(r.location_name || '');
        if (!key) return;
        priceLookupMap[key] = {
            basis: String(r.basis || '').toUpperCase(),
            unit_price: parseFloat(r.unit_price || 0)
        };
    });

    function renumberRows() {
        var rows = body.querySelectorAll('tr');
        var grandTotal = 0;
        rows.forEach((row, i) => {
            row.querySelector('.row-idx').textContent = i + 1;
            var val = parseFloat(row.querySelector('.line-total-input').value) || 0;
            grandTotal += val;
        });
        document.getElementById('grandTotalDisplay').textContent = grandTotal.toFixed(2);
    }

    function destinationOptionsHtml(selectedValue) {
        var html = '<option value="">-- Destination --</option>';
        destinationOptions.forEach(function (dest) {
            var sel = (selectedValue && selectedValue === dest) ? 'selected' : '';
            html += `<option value="${dest}" ${sel}>${dest}</option>`;
        });
        return html;
    }

    function refreshDestinationSelects(selectedFallback) {
        var selects = body.querySelectorAll('.destination-sel');
        selects.forEach(function (sel) {
            var keep = sel.value || selectedFallback || '';
            sel.innerHTML = destinationOptionsHtml(keep);
        });
    }

    function syncDestinationsByCustomer(customerEmail) {
        // Keep all price list destinations visible
        refreshDestinationSelects();
    }

    function applyPricingByDestination(tr, destinationName) {
        if (!destinationName) return;
        destinationName = String(destinationName).trim().replace(/\s+/g, ' ');
        var normalized = normalizeKey(destinationName);
        var localMatch = priceLookupMap[normalized] || null;

        function applyPricing(data) {
            if (!data || !data.found) return;
            var basisSel = tr.querySelector('.basis-sel');
            var priceTonInput = tr.querySelector('.price-ton-input');
            var totalInput = tr.querySelector('.line-total-input');
            var normalizedBasis = String(data.basis || '').toUpperCase();
            var unitPrice = parseFloat(data.unit_price || 0);

            // Clear old data first
            priceTonInput.value = '';
            totalInput.value = '';

            if (normalizedBasis === 'TON') {
                basisSel.value = 'TON';
                priceTonInput.value = unitPrice > 0 ? unitPrice.toFixed(2) : '';
            } else if (normalizedBasis === 'LOAD') {
                basisSel.value = 'LOAD';
                totalInput.value = unitPrice > 0 ? unitPrice.toFixed(2) : '';
            }
            tr.querySelector('.desc-input').value = destinationName;
            
            // Manual trigger to update fields (disabled/readonly states)
            var event = new Event('change');
            basisSel.dispatchEvent(event);
        }

        if (localMatch) {
            applyPricing({
                found: true,
                basis: localMatch.basis,
                unit_price: localMatch.unit_price
            });
            return;
        }

        fetch('price_lookup.php?location=' + encodeURIComponent(destinationName))
            .then(function (resp) { return resp.json(); })
            .then(function (data) { applyPricing(data); })
            .catch(function () {});
    }

    function addRow(d = {}) {
        var tr = document.createElement('tr');
        var lorryOptions = `<option value="">-- Lori --</option>`;
        truckPlates.forEach(p => {
            var sel = (d.lorry_sel == p) ? 'selected' : '';
            lorryOptions += `<option value="${p}" ${sel}>${p}</option>`;
        });

        tr.innerHTML = `
            <td class="row-idx"></td>
            <td><input type="date" name="line_service_date[]" value="${d.date || today}"></td>
            <td>
                <select name="line_lorry_select[]" class="lorry-sel">
                    ${lorryOptions}
                    <option value="__OTHER__" ${d.lorry_custom ? 'selected' : ''}>Lain-lain</option>
                </select>
                <input type="text" name="line_lorry_custom[]" class="lorry-custom" placeholder="No Plate" 
                       style="display:${d.lorry_custom ? 'block' : 'none'}; border-top:1px solid #eee;" value="${d.lorry_custom || ''}">
            </td>
            <td>
                <select class="destination-sel" style="border-top:none; border-left:none; border-right:none; border-bottom:1px solid #eee; text-align:left;">
                    ${destinationOptionsHtml(d.destination || d.desc || '')}
                </select>
                <input type="text" name="line_description[]" class="desc-input" style="text-align:left;" value="${d.desc || ''}" required>
            </td>
            <td>
                <select name="line_basis[]" class="basis-sel">
                    <option value="LOAD" ${(d.basis || 'LOAD') === 'LOAD' ? 'selected' : ''}>LOAD</option>
                    <option value="TON" ${(d.basis || '') === 'TON' ? 'selected' : ''}>TON</option>
                </select>
            </td>
            <td><input type="number" step="0.001" min="0" name="line_ton_qty[]" class="ton-qty-input" value="${d.ton_qty || ''}" placeholder="TON"></td>
            <td><input type="number" step="0.01" min="0" name="line_price_per_ton[]" class="price-ton-input" value="${d.price || ''}" placeholder="RM"></td>
            <td><input type="text" name="line_mt[]" class="line-mt-input" value="${d.mt || '1 LOAD'}" readonly></td>
            <td><input type="text" name="line_do[]" value="${d.do || ''}"></td>
            <td><input type="number" step="0.01" min="0" name="line_total[]" class="line-total-input" value="${d.total || ''}" required></td>
            <td class="btn-action"><button type="button" class="rm-row" style="background:none; border:none; cursor:pointer; color:red;">&times;</button></td>
        `;

        body.appendChild(tr);

        tr.querySelector('.lorry-sel').addEventListener('change', function() {
            tr.querySelector('.lorry-custom').style.display = (this.value === '__OTHER__') ? 'block' : 'none';
        });
        tr.querySelector('.destination-sel').addEventListener('change', function() {
            if (this.value) {
                tr.querySelector('.desc-input').value = this.value;
                applyPricingByDestination(tr, this.value);
            }
        });

        function updateRowCalc() {
            var basis = tr.querySelector('.basis-sel').value;
            var tonQtyInput = tr.querySelector('.ton-qty-input');
            var priceTonInput = tr.querySelector('.price-ton-input');
            var totalInput = tr.querySelector('.line-total-input');
            var mtInput = tr.querySelector('.line-mt-input');

            if (basis === 'TON') {
                tonQtyInput.disabled = false;
                tonQtyInput.required = true;
                priceTonInput.disabled = false;
                priceTonInput.required = true;
                totalInput.readOnly = true;
                
                var tonQty = parseFloat(tonQtyInput.value) || 0;
                var priceTon = parseFloat(priceTonInput.value) || 0;
                var computed = tonQty * priceTon;
                totalInput.value = computed > 0 ? computed.toFixed(2) : '';
                mtInput.value = tonQty > 0 ? (tonQty + ' TON') : '';
            } else {
                // CLEAR DATA FOR LOAD BASIS
                tonQtyInput.value = '';
                tonQtyInput.disabled = true;
                tonQtyInput.required = false;
                
                priceTonInput.value = '';
                priceTonInput.disabled = true;
                priceTonInput.required = false;
                
                totalInput.readOnly = false;
                mtInput.value = '1 LOAD';
            }
            renumberRows();
        }

        tr.querySelector('.basis-sel').addEventListener('change', updateRowCalc);
        tr.querySelector('.ton-qty-input').addEventListener('input', updateRowCalc);
        tr.querySelector('.price-ton-input').addEventListener('input', updateRowCalc);
        tr.querySelector('.line-total-input').addEventListener('input', renumberRows);
        tr.querySelector('.rm-row').addEventListener('click', function() {
            if(body.querySelectorAll('tr').length > 1) { tr.remove(); renumberRows(); }
        });

        updateRowCalc();
        renumberRows();
    }

    document.getElementById('addRow').addEventListener('click', () => addRow());
    document.getElementById('addRowsByCount').addEventListener('click', function () {
        var countInput = document.getElementById('rowCountInput');
        var count = parseInt((countInput && countInput.value) || '1', 10);
        if (!Number.isFinite(count) || count < 1) count = 1;
        if (count > 100) count = 100;
        for (var j = 0; j < count; j++) addRow();
    });

    <?php if ($posted && isset($_POST['line_description'])): ?>
        <?php foreach($_POST['line_description'] as $i => $desc): ?>
            addRow({
                date: '<?php echo $_POST['line_service_date'][$i]; ?>',
                lorry_sel: '<?php echo $_POST['line_lorry_select'][$i]; ?>',
                lorry_custom: '<?php echo $_POST['line_lorry_custom'][$i]; ?>',
                desc: '<?php echo addslashes($desc); ?>',
                basis: '<?php echo $_POST['line_basis'][$i] ?? 'LOAD'; ?>',
                ton_qty: '<?php echo $_POST['line_ton_qty'][$i] ?? ''; ?>',
                price: '<?php echo $_POST['line_price_per_ton'][$i]; ?>',
                mt: '<?php echo $_POST['line_mt'][$i]; ?>',
                do: '<?php echo $_POST['line_do'][$i]; ?>',
                total: '<?php echo $_POST['line_total'][$i]; ?>'
            });
        <?php endforeach; ?>
    <?php else: ?>
        addRow({mt: '1 LOAD'}); addRow({mt: '1 LOAD'});
    <?php endif; ?>

    var pick = document.getElementById('cust_pick');
    if (pick) {
        pick.addEventListener('change', function () {
            var o = this.options[this.selectedIndex];
            if (!o.value) {
                document.getElementById('client_name').value = '';
                document.getElementById('client_address').value = '';
                document.getElementById('client_tel').value = '';
                document.getElementById('client_email').value = '';
                return;
            };
            document.getElementById('client_name').value = o.getAttribute('data-name');
            document.getElementById('client_address').value = o.getAttribute('data-address');
            document.getElementById('client_tel').value = o.getAttribute('data-tel');
            document.getElementById('client_email').value = o.getAttribute('data-email');
        });
    }
})();
</script>
</body>
</html>