-- Invoice module for Transportrix (run once in phpMyAdmin or mysql CLI)
-- Creates invoice header + line items matching MSF-style billing.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `invoice` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(40) NOT NULL,
  `payment_term` varchar(80) NOT NULL DEFAULT '30 DAYS',
  `invoice_date` date NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_address` text NOT NULL,
  `client_tel` varchar(80) NOT NULL DEFAULT '',
  `client_email` varchar(120) NOT NULL DEFAULT '',
  `bank_instructions` text DEFAULT NULL,
  `cheque_instructions` varchar(500) DEFAULT NULL,
  `manager_title` varchar(80) NOT NULL DEFAULT 'MANAGER',
  `manager_name` varchar(120) NOT NULL DEFAULT '',
  `admin_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `invoice_date` (`invoice_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `invoice_line` (
  `line_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `line_no` int(11) NOT NULL,
  `service_date` date NOT NULL,
  `lorry_no` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(600) NOT NULL,
  `price_per_ton` decimal(14,2) DEFAULT NULL,
  `mt` varchar(80) NOT NULL DEFAULT '',
  `do_no` varchar(80) NOT NULL DEFAULT '',
  `line_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`line_id`),
  KEY `fk_invoice_line` (`invoice_id`),
  CONSTRAINT `fk_invoice_line` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`invoice_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
