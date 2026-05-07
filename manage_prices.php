<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['userid']) || ($_SESSION['status'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

$createTableSql = "CREATE TABLE IF NOT EXISTS `price_list` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `location_name` varchar(150) NOT NULL,
    `basis` enum('TON','LOAD') NOT NULL DEFAULT 'LOAD',
    `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_location_basis` (`location_name`, `basis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
mysqli_query($connect, $createTableSql);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $location = trim($_POST['location_name'] ?? '');
        $location = preg_replace('/\s+/', ' ', $location);
        $basis = strtoupper(trim($_POST['basis'] ?? ''));
        $unitPrice = (float)($_POST['unit_price'] ?? 0);

        if ($location === '' || !in_array($basis, ['TON', 'LOAD'], true) || $unitPrice < 0) {
            $error = 'Please enter valid location, basis and price.';
        } else {
            $locationEsc = mysqli_real_escape_string($connect, $location);
            $basisEsc = mysqli_real_escape_string($connect, $basis);
            $unitPriceEsc = mysqli_real_escape_string($connect, number_format($unitPrice, 2, '.', ''));
            try {
                mysqli_query(
                    $connect,
                    "INSERT INTO price_list (location_name, basis, unit_price)
                     VALUES ('{$locationEsc}', '{$basisEsc}', '{$unitPriceEsc}')
                     ON DUPLICATE KEY UPDATE unit_price = VALUES(unit_price)"
                );
                header('Location: manage_prices.php');
                exit();
            } catch (mysqli_sql_exception $e) {
                $error = 'Unable to save price. Please try again.';
            }
        }
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $location = trim($_POST['location_name'] ?? '');
        $location = preg_replace('/\s+/', ' ', $location);
        $basis = strtoupper(trim($_POST['basis'] ?? ''));
        $unitPrice = (float)($_POST['unit_price'] ?? 0);

        if ($id <= 0 || $location === '' || !in_array($basis, ['TON', 'LOAD'], true) || $unitPrice < 0) {
            $error = 'Please enter valid data for update.';
        } else {
            $locationEsc = mysqli_real_escape_string($connect, $location);
            $basisEsc = mysqli_real_escape_string($connect, $basis);
            $unitPriceEsc = mysqli_real_escape_string($connect, number_format($unitPrice, 2, '.', ''));
            try {
                mysqli_query($connect, "UPDATE price_list SET location_name='{$locationEsc}', basis='{$basisEsc}', unit_price='{$unitPriceEsc}' WHERE id={$id}");
                header('Location: manage_prices.php');
                exit();
            } catch (mysqli_sql_exception $e) {
                $error = 'Update failed. Location+basis may already exist.';
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                mysqli_query($connect, "DELETE FROM price_list WHERE id={$id}");
            } catch (mysqli_sql_exception $e) {
                $error = 'Delete failed.';
            }
        }
        if ($error === '') {
            header('Location: manage_prices.php');
            exit();
        }
    }
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$rows = [];
$res = mysqli_query($connect, "SELECT * FROM price_list ORDER BY location_name ASC");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $rows[] = $r;
    }
}

include 'navBar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Master Pricing & Destination</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f6fa; }
        .wrap { padding: 30px; }
        .card { background: #fff; border-radius: 10px; padding: 22px; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
        h2 { margin: 0 0 18px 0; color: #2c3e50; }
        .err { background: #fee; color: #c0392b; border-left: 4px solid #c0392b; padding: 10px; margin-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; margin-bottom: 18px; }
        input, select { padding: 10px; border: 1px solid #d9dfe6; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e9ecef; text-align: left; }
        th { background: #f9fafc; text-transform: uppercase; font-size: 12px; }
        .btn { border: none; border-radius: 6px; padding: 8px 12px; cursor: pointer; color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-success { background: #27ae60; }
        .btn-primary { background: #3498db; }
        .btn-danger { background: #e74c3c; }
        .action-form { display: inline; }
        .inline-edit { display: grid; grid-template-columns: 2fr 1fr 1fr auto auto; gap: 8px; align-items: center; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h2>Master Pricing & Destination Manager</h2>
        <?php if ($error !== ''): ?><div class="err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="add">
            <input type="text" name="location_name" placeholder="Destination / Location Name" required>
            <select name="basis" required>
                <option value="LOAD">LOAD</option>
                <option value="TON">TON</option>
            </select>
            <input type="number" step="0.01" min="0" name="unit_price" placeholder="Unit Price" required>
            <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Add New Price</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Destination</th>
                    <th>Basis</th>
                    <th>Unit Price (RM)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rows) === 0): ?>
                    <tr><td colspan="4">No pricing records yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td colspan="4">
                                <?php if ($editId === (int)$row['id']): ?>
                                    <form method="post" class="inline-edit">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                        <input type="text" name="location_name" value="<?php echo htmlspecialchars($row['location_name']); ?>" required>
                                        <select name="basis" required>
                                            <option value="LOAD" <?php echo strtoupper($row['basis']) === 'LOAD' ? 'selected' : ''; ?>>LOAD</option>
                                            <option value="TON" <?php echo strtoupper($row['basis']) === 'TON' ? 'selected' : ''; ?>>TON</option>
                                        </select>
                                        <input type="number" step="0.01" min="0" name="unit_price" value="<?php echo htmlspecialchars($row['unit_price']); ?>" required>
                                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                                        <a class="btn btn-primary" href="manage_prices.php"><i class="fa fa-times"></i> Cancel</a>
                                    </form>
                                <?php else: ?>
                                    <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:10px;align-items:center;">
                                        <div><?php echo htmlspecialchars($row['location_name']); ?></div>
                                        <div><?php echo htmlspecialchars(strtoupper($row['basis'])); ?></div>
                                        <div><?php echo number_format((float)$row['unit_price'], 2); ?></div>
                                        <div>
                                            <a class="btn btn-primary" href="manage_prices.php?edit=<?php echo (int)$row['id']; ?>"><i class="fa fa-edit"></i> Edit</a>
                                            <form method="post" class="action-form" onsubmit="return confirm('Delete this pricing record?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
