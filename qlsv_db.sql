-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2026 at 05:02 PM
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
-- Table structure for table `diem`
--

CREATE TABLE `diem` (
  `ma_sv` varchar(20) NOT NULL,
  `ma_mh` varchar(10) NOT NULL,
  `diem_giua_ky` decimal(4,1) NOT NULL,
  `diem_cuoi_ky` decimal(4,1) NOT NULL,
  `diem_tong_ket` decimal(4,1) NOT NULL,
  `han_khoa_diem` date NOT NULL,
  `trang_thai` varchar(30) NOT NULL DEFAULT 'Chưa khóa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `diem`
--

INSERT INTO `diem` (`ma_sv`, `ma_mh`, `diem_giua_ky`, `diem_cuoi_ky`, `diem_tong_ket`, `han_khoa_diem`, `trang_thai`) VALUES
('SV000001', 'INT101', 8.0, 7.0, 7.3, '2026-06-30', 'Đã khóa'),
('SV000001', 'MAT101', 6.0, 5.0, 5.3, '2026-12-31', 'Chưa khóa'),
('SV000002', 'INT101', 9.0, 8.0, 8.3, '2026-06-30', 'Đã khóa'),
('SV000003', 'INT101', 5.0, 6.0, 5.7, '2026-06-30', 'Đã khóa'),
('SV000004', 'INT101', 7.0, 9.0, 8.4, '2026-06-30', 'Đã khóa'),
('SV000005', 'MAT101', 4.0, 6.0, 5.4, '2026-12-31', 'Chưa khóa');

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
-- Indexes for table `diem`
--
ALTER TABLE `diem`
  ADD PRIMARY KEY (`ma_sv`,`ma_mh`);

--
-- Indexes for table `sinhvien`
--
ALTER TABLE `sinhvien`
  ADD PRIMARY KEY (`ma_sv`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `diem`
--
ALTER TABLE `diem`
  ADD CONSTRAINT `fk_diem_sinhvien` FOREIGN KEY (`ma_sv`) REFERENCES `sinhvien` (`ma_sv`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
