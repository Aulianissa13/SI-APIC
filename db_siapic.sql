-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2026 at 02:46 AM
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
-- Database: `db_siapic`
--

-- --------------------------------------------------------

--
-- Table structure for table `jenis_cuti`
--

CREATE TABLE `jenis_cuti` (
  `id_jenis` int(11) NOT NULL,
  `nama_jenis` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jenis_cuti`
--

INSERT INTO `jenis_cuti` (`id_jenis`, `nama_jenis`) VALUES
(1, 'Cuti Tahunan'),
(2, 'Cuti Sakit'),
(3, 'Cuti Besar'),
(4, 'Cuti Melahirkan'),
(5, 'Cuti Alasan Penting');

-- --------------------------------------------------------

--
-- Table structure for table `libur_nasional`
--

CREATE TABLE `libur_nasional` (
  `id_libur` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `libur_nasional`
--

INSERT INTO `libur_nasional` (`id_libur`, `tanggal`, `keterangan`) VALUES
(1, '2026-08-17', 'HUT RI ke-79'),
(4, '2026-12-25', 'Hari Raya Natal');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_cuti`
--

CREATE TABLE `pengajuan_cuti` (
  `id_pengajuan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_jenis` int(11) NOT NULL,
  `nomor_surat` varchar(100) DEFAULT NULL,
  `tgl_pengajuan` date NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `lama_hari` int(11) NOT NULL,
  `alasan` text NOT NULL,
  `alamat_cuti` text NOT NULL,
  `status` enum('Diajukan','Disetujui','Ditolak','Dibatalkan') DEFAULT 'Diajukan',
  `catatan_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengajuan_cuti`
--

INSERT INTO `pengajuan_cuti` (`id_pengajuan`, `id_user`, `id_jenis`, `nomor_surat`, `tgl_pengajuan`, `tgl_mulai`, `tgl_selesai`, `lama_hari`, `alasan`, `alamat_cuti`, `status`, `catatan_admin`, `created_at`) VALUES
(1, 2, 1, NULL, '2026-01-15', '2026-01-15', '2026-01-19', 3, 'Pergi Keluar Kota', 'Magelang', 'Disetujui', NULL, '2026-01-15 04:03:00'),
(2, 2, 1, NULL, '2026-01-15', '2026-01-16', '2026-01-18', 1, 'Acara Keluarga', 'Yogyakarta', 'Disetujui', NULL, '2026-01-15 04:10:41'),
(3, 2, 1, '001/KPN/W13.U1/KP.05.3/I/2026', '2026-01-15', '2026-02-04', '2026-02-07', 3, 'Pergi Ziarah', 'Yogyakarta', 'Disetujui', NULL, '2026-01-15 04:18:14'),
(4, 3, 1, '002/KPN/W13.U1/KP.05.3/I/2026', '2026-01-17', '2026-01-19', '2026-01-26', 6, 'Acara Keluarga', 'London', 'Ditolak', NULL, '2026-01-17 15:39:20'),
(5, 3, 2, '003/KPN/W13.U1/KP.05.3/I/2026', '2026-01-17', '2026-01-22', '2026-01-26', 3, 'Sakit Tipes', 'Maguwo', 'Ditolak', NULL, '2026-01-17 15:44:46'),
(6, 3, 2, '004/KPN/W13.U1/KP.05.3/I/2026', '2026-01-17', '2026-01-26', '2026-01-28', 3, 'Izin Sakit', 'Jakarta', 'Ditolak', NULL, '2026-01-17 15:48:20'),
(7, 3, 2, '005/KPN/W13.U1/KP.05.3/I/2026', '2026-01-17', '2026-02-05', '2026-02-06', 2, 'Izin Sakit', 'Florida', 'Disetujui', NULL, '2026-01-17 15:51:21'),
(8, 3, 2, '006/KPN/W13.U1/KP.05.3/I/2026', '2026-01-17', '2026-01-26', '2026-01-30', 5, 'Izin Sakit', 'Kanada', 'Ditolak', NULL, '2026-01-17 16:02:51'),
(9, 3, 2, '007/KPN/W13.U1/KP.05.3/I/2026', '2026-01-17', '2026-01-21', '2026-01-21', 1, 'SAKIT', 'MAGUWO', 'Ditolak', NULL, '2026-01-17 16:21:04'),
(10, 3, 1, '008/KPN/W13.U1/KP.05.3/I/2026', '2026-01-17', '2026-01-21', '2026-01-23', 3, 'Keluarga', 'Batu', 'Ditolak', NULL, '2026-01-17 16:26:43'),
(11, 3, 2, '009/KPN/W13.U1/KP.05.3/I/2026', '2026-01-17', '2026-01-19', '2026-01-21', 3, 'SAKIT', 'BATU', 'Ditolak', NULL, '2026-01-17 16:34:36'),
(12, 3, 2, '010/KPN/W13.U1/KP.05.3/I/2026', '2026-01-18', '2026-01-21', '2026-01-24', 3, 'Berobat Kerumahsakit', 'Jakarta\r\n', 'Ditolak', NULL, '2026-01-18 06:04:08'),
(13, 3, 1, '011/KPN/W13.U1/KP.05.3/I/2026', '2026-01-18', '2026-01-29', '2026-02-03', 4, 'Acara keluarga', 'magelang', 'Disetujui', NULL, '2026-01-18 06:30:35'),
(14, 3, 1, '012/KPN/W13.U1/KP.05.3/I/2026', '2026-01-18', '2026-01-28', '2026-02-02', 4, 'Pergi Keluar Kota', 'Bandung', 'Disetujui', NULL, '2026-01-18 07:26:40'),
(21, 3, 1, '014/KPN/W13.U1/KP.05.3/I/2026', '2026-01-20', '2026-12-24', '2026-12-28', 3, 'ke luar ', 'new york', 'Disetujui', NULL, '2026-01-20 00:51:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `pangkat` varchar(50) DEFAULT NULL,
  `unit_kerja` varchar(100) DEFAULT 'Pengadilan Negeri Yogyakarta',
  `no_telepon` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL,
  `sisa_cuti_n` int(11) DEFAULT 12,
  `sisa_cuti_n1` int(11) DEFAULT 0,
  `sisa_cuti_n2` int(11) DEFAULT 0,
  `kuota_cuti_sakit` int(11) DEFAULT 14,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nip`, `password`, `nama_lengkap`, `jabatan`, `pangkat`, `unit_kerja`, `no_telepon`, `role`, `sisa_cuti_n`, `sisa_cuti_n1`, `sisa_cuti_n2`, `kuota_cuti_sakit`, `created_at`) VALUES
(1, 'admin', '$2y$10$Hz8R7a0J4bGIuHB4Ygu.LeYjaZG2aQRO5JqfQDX3dw2Iqp3nYMDcS', 'Administrator Ortala', 'Kepala Sub Bagian', NULL, 'Pengadilan Negeri Yogyakarta', NULL, 'admin', 12, 0, 0, 14, '2026-01-15 02:30:50'),
(2, '19800101', '202cb962ac59075b964b07152d234b70', 'Budi Santoso, S.H.', 'Hakim Pratama', NULL, 'Pengadilan Negeri Yogyakarta', '088888888888', 'user', -3, 0, 0, 14, '2026-01-15 02:30:50'),
(3, '124230050', '$2y$10$8BG/A/1OTFtCEuZmtKqyY.pi/nnKQiLwli56itJxFB6y/HjpZvT42', 'Nissa Aulia', NULL, NULL, 'Pengadilan Negeri Yogyakarta', '0897878657657', '', 4, 0, 0, 11, '2026-01-17 15:38:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jenis_cuti`
--
ALTER TABLE `jenis_cuti`
  ADD PRIMARY KEY (`id_jenis`);

--
-- Indexes for table `libur_nasional`
--
ALTER TABLE `libur_nasional`
  ADD PRIMARY KEY (`id_libur`);

--
-- Indexes for table `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  ADD PRIMARY KEY (`id_pengajuan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_jenis` (`id_jenis`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jenis_cuti`
--
ALTER TABLE `jenis_cuti`
  MODIFY `id_jenis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `libur_nasional`
--
ALTER TABLE `libur_nasional`
  MODIFY `id_libur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  MODIFY `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  ADD CONSTRAINT `pengajuan_cuti_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `pengajuan_cuti_ibfk_2` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_cuti` (`id_jenis`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
