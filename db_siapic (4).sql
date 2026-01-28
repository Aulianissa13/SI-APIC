-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 28, 2026 at 08:36 AM
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
  `jenis_libur` enum('nasional','cuti_bersama') NOT NULL DEFAULT 'nasional',
  `keterangan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `libur_nasional`
--

INSERT INTO `libur_nasional` (`id_libur`, `tanggal`, `jenis_libur`, `keterangan`) VALUES
(1, '2026-01-01', 'nasional', 'Tahun Baru 2026 Masehi'),
(2, '2026-01-16', 'nasional', 'Isra Mikraj Nabi Muhammad S.A.W.'),
(3, '2026-02-17', 'nasional', 'Tahun Baru Imlek 2577 Kongzili'),
(4, '2026-03-19', 'nasional', 'Hari Suci Nyepi (Tahun Baru Saka 1948)'),
(5, '2026-03-21', 'nasional', 'Idul Fitri 1447 Hijriah'),
(6, '2026-03-22', 'nasional', 'Idul Fitri 1447 Hijriah'),
(7, '2026-04-03', 'nasional', 'Wafat Yesus Kristus'),
(8, '2026-04-05', 'nasional', 'Kebangkitan Yesus Kristus (Paskah)'),
(9, '2026-05-01', 'nasional', 'Hari Buruh Internasional'),
(10, '2026-05-14', 'nasional', 'Kenaikan Yesus Kristus'),
(11, '2026-05-27', 'nasional', 'Idul Adha 1447 Hijriah'),
(12, '2026-05-31', 'nasional', 'Hari Raya Waisak 2570 BE'),
(13, '2026-06-01', 'nasional', 'Hari Lahir Pancasila'),
(14, '2026-06-16', 'nasional', '1 Muharam Tahun Baru Islam 1448 Hijriah'),
(15, '2026-08-17', 'nasional', 'Proklamasi Kemerdekaan'),
(16, '2026-08-25', 'nasional', 'Maulid Nabi Muhammad S.A.W.'),
(17, '2026-12-25', 'nasional', 'Kelahiran Yesus Kristus'),
(18, '2026-02-16', 'cuti_bersama', 'Cuti Bersama Tahun Baru Imlek 2577 Kongzili'),
(19, '2026-03-18', 'cuti_bersama', 'Cuti Bersama Hari Suci Nyepi (Tahun Baru Saka 1948)'),
(20, '2026-03-20', 'cuti_bersama', 'Cuti Bersama Idul Fitri 1447 Hijriah'),
(21, '2026-03-23', 'cuti_bersama', 'Cuti Bersama Idul Fitri 1447 Hijriah'),
(22, '2026-03-24', 'cuti_bersama', 'Cuti Bersama Idul Fitri 1447 Hijriah'),
(23, '2026-05-15', 'cuti_bersama', 'Cuti Bersama Kenaikan Yesus Kristus'),
(24, '2026-05-28', 'cuti_bersama', 'Cuti Bersama Idul Adha 1447 Hijriah');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_cuti`
--

CREATE TABLE `pengajuan_cuti` (
  `id_pengajuan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `jenis_cuti` varchar(50) DEFAULT NULL,
  `id_atasan` int(11) DEFAULT NULL,
  `id_jenis` int(11) NOT NULL,
  `nomor_surat` varchar(100) DEFAULT NULL,
  `tgl_pengajuan` date NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `lama_hari` int(11) NOT NULL,
  `alasan` text NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `masa_kerja` varchar(50) DEFAULT NULL,
  `alamat_cuti` text NOT NULL,
  `status` enum('Diajukan','Disetujui','Ditolak','Dibatalkan') DEFAULT 'Diajukan',
  `catatan_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sisa_cuti_n` int(11) DEFAULT 0,
  `sisa_cuti_n1` int(11) DEFAULT 0,
  `dipotong_n1` int(11) DEFAULT 0,
  `dipotong_n` int(11) DEFAULT 0,
  `id_pejabat` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengajuan_cuti`
--

INSERT INTO `pengajuan_cuti` (`id_pengajuan`, `id_user`, `jenis_cuti`, `id_atasan`, `id_jenis`, `nomor_surat`, `tgl_pengajuan`, `tgl_mulai`, `tgl_selesai`, `lama_hari`, `alasan`, `no_telepon`, `masa_kerja`, `alamat_cuti`, `status`, `catatan_admin`, `created_at`, `sisa_cuti_n`, `sisa_cuti_n1`, `dipotong_n1`, `dipotong_n`, `id_pejabat`) VALUES
(66, 3, NULL, NULL, 1, '001/KPN/W13.U1/KP.05.3/I/2026', '2026-01-28', '2026-01-12', '2026-01-19', 5, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-28 04:17:22', 0, 0, 4, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_setting_instansi`
--

CREATE TABLE `tbl_setting_instansi` (
  `id_setting` int(11) NOT NULL,
  `nama_instansi` varchar(100) DEFAULT 'Pengadilan Negeri Yogyakarta',
  `alamat_instansi` text DEFAULT NULL,
  `ketua_nama` varchar(100) DEFAULT NULL,
  `ketua_nip` varchar(50) DEFAULT NULL,
  `wakil_nama` varchar(100) DEFAULT NULL,
  `wakil_nip` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_setting_instansi`
--

INSERT INTO `tbl_setting_instansi` (`id_setting`, `nama_instansi`, `alamat_instansi`, `ketua_nama`, `ketua_nip`, `wakil_nama`, `wakil_nip`) VALUES
(1, 'Pengadilan Negeri Yogyakarta', '', 'SYAFRIZAL, S.H.', '19680414 199603 1 002', 'MELINDA ARITONANG, S.H.', '19780911 200112 2 002');

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
  `is_atasan_langsung` tinyint(1) DEFAULT 0 COMMENT '1=Bisa TTD, 0=Staf Biasa',
  `sisa_cuti_n` int(11) DEFAULT 12,
  `sisa_cuti_n1` int(11) DEFAULT 0,
  `sisa_cuti_n2` int(11) DEFAULT 0,
  `kuota_cuti_sakit` int(11) DEFAULT 14,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_akun` enum('aktif','nonaktif') DEFAULT 'aktif',
  `id_atasan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nip`, `password`, `nama_lengkap`, `jabatan`, `pangkat`, `unit_kerja`, `no_telepon`, `role`, `is_atasan_langsung`, `sisa_cuti_n`, `sisa_cuti_n1`, `sisa_cuti_n2`, `kuota_cuti_sakit`, `created_at`, `status_akun`, `id_atasan`) VALUES
(1, 'admin', '$2y$10$Hz8R7a0J4bGIuHB4Ygu.LeYjaZG2aQRO5JqfQDX3dw2Iqp3nYMDcS', 'Administrator Ortala', 'Kepala Sub Bagian', NULL, 'Pengadilan Negeri Yogyakarta', NULL, 'admin', 0, 12, 6, 0, 14, '2026-01-15 02:30:50', 'aktif', 0),
(2, '198001011', '202cb962ac59075b964b07152d234b70', 'Budi Santoso, S.H.', 'Hakim Pratama', '', 'Pengadilan Negeri Yogyakarta', '088888888888', 'user', 0, 3, 2, 0, 14, '2026-01-15 02:30:50', 'aktif', 5),
(3, '124230050', '$2y$10$8BG/A/1OTFtCEuZmtKqyY.pi/nnKQiLwli56itJxFB6y/HjpZvT42', 'Nissa Aulia', 'Panitera', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '0897878657657', 'user', 0, 12, 4, 0, 14, '2026-01-17 15:38:04', 'aktif', 0),
(4, '123', 'e10adc3949ba59abbe56e057f20f883e', 'Tsalatsa', 'Panitera', NULL, 'Pengadilan Negeri Yogyakarta', '0808080808', 'user', 0, 12, 6, 0, 14, '2026-01-21 01:43:11', 'aktif', 5),
(5, '19680414 199603 1 002', '123', 'SYAFRIZAL, S.H.', 'Ketua ', 'Pembina Utama Madya (IV/d)', 'Pengadilan Negeri Yogyakarta', '81264701704', 'user', 0, 12, 6, 0, 14, '2026-01-21 04:27:12', 'aktif', 0),
(6, '19780911 200112 2 002', 'e10adc3949ba59abbe56e057f20f883e', 'MELINDA ARITONANG, S.H.', 'Wakil Ketua ', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', NULL, 'user', 0, 12, 5, 0, 14, '2026-01-22 02:44:06', 'aktif', 0);

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
-- Indexes for table `tbl_setting_instansi`
--
ALTER TABLE `tbl_setting_instansi`
  ADD PRIMARY KEY (`id_setting`);

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
  MODIFY `id_libur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  MODIFY `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `tbl_setting_instansi`
--
ALTER TABLE `tbl_setting_instansi`
  MODIFY `id_setting` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
