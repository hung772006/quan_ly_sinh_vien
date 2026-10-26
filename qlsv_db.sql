-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2026 at 09:05 AM
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
-- Database: `qlsv_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `sinhvien`
--

CREATE TABLE `sinhvien` (
  `ma_sv` varchar(20) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `ngay_sinh` date NOT NULL,
  `so_dien_thoai` varchar(15) NOT NULL,
  `lop` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sinhvien`
--

INSERT INTO `sinhvien` (`ma_sv`, `ho_ten`, `ngay_sinh`, `so_dien_thoai`, `lop`, `email`) VALUES
('SV000001', 'Nguyễn Văn An', '2006-12-06', '0912345678', 'K58A1', 'an.nguyen@gmail.com'),
('SV000002', 'Trần Thị Bình', '2005-09-03', '0923456789', 'K58A2', 'binh.tran@gmail.com'),
('SV000003', 'Lê Văn Cường', '2006-11-13', '0934567890', 'K58A1', 'cuong.le@gmail.com'),
('SV000004', 'Phạm Thị Dung', '2006-02-21', '0945678901', 'K58A3', 'dung.pham@gmail.com'),
('SV000005', 'Hoàng Văn Đức', '2006-05-22', '0956789012', 'K58A2', 'duc.hoang@gmail.com'),
('SV000006', 'Vũ Thị Hà', '2005-08-14', '0967890123', 'K58A1', 'ha.vu@gmail.com'),
('SV000007', 'Đặng Văn Hùng', '2006-07-28', '0978901234', 'K58A3', 'hung.dang@gmail.com'),
('SV000008', 'Bùi Thị Lan', '2006-04-18', '0989012345', 'K58A2', 'lan.bui@gmail.com'),
('SV000009', 'Đỗ Văn Minh', '2006-02-12', '0990123456', 'K58A1', 'minh.do@gmail.com'),
('SV000010', 'Ngô Thị Ngọc', '2006-05-28', '0901234567', 'K58A3', 'ngoc.ngo@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sinhvien`
--
ALTER TABLE `sinhvien`
  ADD PRIMARY KEY (`ma_sv`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
