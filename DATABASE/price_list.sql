-- Master Pricing & Destination table
-- Run once in phpMyAdmin or mysql CLI

CREATE TABLE IF NOT EXISTS `price_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_name` varchar(150) NOT NULL,
  `basis` enum('TON','LOAD') NOT NULL DEFAULT 'LOAD',
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_location_basis` (`location_name`, `basis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
