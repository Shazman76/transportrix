-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2025 at 09:15 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `transportrix`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` varchar(50) NOT NULL,
  `admin_name` varchar(50) NOT NULL,
  `admin_contact` varchar(50) NOT NULL,
  `admin_pass` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_contact`, `admin_pass`) VALUES
('001', 'Muhammad ', '01132527298', '123');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `cust_email` varchar(50) NOT NULL,
  `cust_name` varchar(50) NOT NULL,
  `cust_contact` varchar(50) NOT NULL,
  `address` varchar(100) NOT NULL,
  `cust_pass` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`cust_email`, `cust_name`, `cust_contact`, `address`, `cust_pass`) VALUES
('2023483498@gmail.com', 'shazman Fariz ', '0166274171', 'taman desa damai', '123'),
('ahmadzulkifli@gmail.com', 'Ahmad ', '601234567', 'No. 12, Jalan Ampang, Kuala Lumpur', '123'),
('ahmadzulkifli@gmsail', 'man', '01233456789', 'bangi', '123');

-- --------------------------------------------------------

--
-- Table structure for table `cust_order`
--

CREATE TABLE `cust_order` (
  `Order_ID` varchar(50) NOT NULL,
  `Order_name` varchar(50) NOT NULL,
  `Shipment_Date` date NOT NULL,
  `Order_Date` date NOT NULL,
  `Order_address` varchar(100) NOT NULL,
  `Destination` varchar(100) NOT NULL,
  `Order_Status` varchar(30) NOT NULL,
  `Ship_ID` varchar(15) NOT NULL,
  `admin_id` varchar(50) DEFAULT NULL,
  `Cust_Email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cust_order`
--

INSERT INTO `cust_order` (`Order_ID`, `Order_name`, `Shipment_Date`, `Order_Date`, `Order_address`, `Destination`, `Order_Status`, `Ship_ID`, `admin_id`, `Cust_Email`) VALUES
('OD0001', 'paper roll', '2025-06-29', '2025-06-29', 'bangi', 'johor', 'Processing', 'SHIP0009', '001', '2023483498@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `driver`
--

CREATE TABLE `driver` (
  `driver_id` varchar(50) NOT NULL,
  `driver_name` varchar(50) NOT NULL,
  `truck_plate` varchar(20) DEFAULT NULL,
  `contact_driver` varchar(50) NOT NULL,
  `current_status` varchar(50) NOT NULL,
  `driver_pass` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`driver_id`, `driver_name`, `truck_plate`, `contact_driver`, `current_status`, `driver_pass`) VALUES
('DR0002', 'man', 'MNB3456', '012347583', 'Offline', '123'),
('DRV001', 'Ali Hassan', NULL, '60113456789', 'Available', '123');

-- --------------------------------------------------------

--
-- Table structure for table `shipment`
--

CREATE TABLE `shipment` (
  `Ship_ID` varchar(15) NOT NULL,
  `Shipment_Stat` varchar(20) NOT NULL,
  `Delivery_Date` date NOT NULL,
  `driver_id` varchar(50) DEFAULT NULL,
  `truck_plate` varchar(20) DEFAULT NULL,
  `admin_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipment`
--

INSERT INTO `shipment` (`Ship_ID`, `Shipment_Stat`, `Delivery_Date`, `driver_id`, `truck_plate`, `admin_id`) VALUES
('SHIP0004', 'In Transit', '2025-07-12', 'DRV001', 'MNB3456', '001'),
('SHIP0009', 'Pending', '2025-07-05', 'DR0002', 'VCB205', '001');

-- --------------------------------------------------------

--
-- Table structure for table `truck`
--

CREATE TABLE `truck` (
  `truck_plate` varchar(50) NOT NULL,
  `truck_model` varchar(50) NOT NULL,
  `load_weight` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `truck`
--

INSERT INTO `truck` (`truck_plate`, `truck_model`, `load_weight`) VALUES
('JGB1234', 'Hino 300', '5000kg'),
('MNB3450', 'volvo', '55000'),
('MNB3456', 'Fuso Canter', '6000kg'),
('PRK6789', 'DAF XF', '14000kg'),
('VCB205', 'myvi', '1000'),
('WPK1234', 'Isuzu Giga', '7000kg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`cust_email`);

--
-- Indexes for table `cust_order`
--
ALTER TABLE `cust_order`
  ADD PRIMARY KEY (`Order_ID`) USING BTREE,
  ADD UNIQUE KEY `Ship_ID` (`Ship_ID`),
  ADD KEY `fk_custEmail_customer` (`Cust_Email`);

--
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`driver_id`),
  ADD KEY `fk_truckPlate_driver` (`truck_plate`);

--
-- Indexes for table `shipment`
--
ALTER TABLE `shipment`
  ADD PRIMARY KEY (`Ship_ID`),
  ADD UNIQUE KEY `Driver_ID` (`driver_id`,`truck_plate`),
  ADD KEY `fk_admin_shipment` (`admin_id`),
  ADD KEY `fk_truckplate_shipment` (`truck_plate`);

--
-- Indexes for table `truck`
--
ALTER TABLE `truck`
  ADD PRIMARY KEY (`truck_plate`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cust_order`
--
ALTER TABLE `cust_order`
  ADD CONSTRAINT `fk_custEmail_customer` FOREIGN KEY (`Cust_Email`) REFERENCES `customer` (`cust_email`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_shipID_order` FOREIGN KEY (`Ship_ID`) REFERENCES `shipment` (`Ship_ID`);

--
-- Constraints for table `driver`
--
ALTER TABLE `driver`
  ADD CONSTRAINT `fk_truckPlate_driver` FOREIGN KEY (`truck_plate`) REFERENCES `truck` (`truck_plate`) ON DELETE SET NULL;

--
-- Constraints for table `shipment`
--
ALTER TABLE `shipment`
  ADD CONSTRAINT `fk_admin_shipment` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`),
  ADD CONSTRAINT `fk_driverID_shipment` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`),
  ADD CONSTRAINT `fk_truckplate_shipment` FOREIGN KEY (`truck_plate`) REFERENCES `truck` (`truck_plate`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
