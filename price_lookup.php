<?php
session_start();
include 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || ($_SESSION['status'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['found' => false, 'error' => 'Forbidden']);
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

$location = trim($_GET['location'] ?? '');
$location = preg_replace('/\s+/', ' ', $location);
if ($location === '') {
    echo json_encode(['found' => false]);
    exit();
}

$locationEsc = mysqli_real_escape_string($connect, $location);
$sql = "SELECT basis, unit_price 
        FROM price_list 
        WHERE LOWER(TRIM(location_name)) = LOWER(TRIM('{$locationEsc}'))
        ORDER BY id DESC
        LIMIT 1";
$res = mysqli_query($connect, $sql);

if ($res && $row = mysqli_fetch_assoc($res)) {
    echo json_encode([
        'found' => true,
        'basis' => strtoupper((string)$row['basis']),
        'unit_price' => (float)$row['unit_price']
    ]);
    exit();
}

echo json_encode(['found' => false]);
?>
