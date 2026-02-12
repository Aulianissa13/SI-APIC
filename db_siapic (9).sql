-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 06:27 AM
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
-- Table structure for table `izin_keluar`
--

CREATE TABLE `izin_keluar` (
  `id_izin` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_atasan` int(11) DEFAULT NULL,
  `tgl_izin` date NOT NULL,
  `jam_keluar` time NOT NULL,
  `jam_kembali` time NOT NULL,
  `keperluan` text NOT NULL,
  `status` enum('Diajukan','Disetujui','Ditolak') DEFAULT 'Disetujui',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `izin_keluar`
--

INSERT INTO `izin_keluar` (`id_izin`, `id_user`, `id_atasan`, `tgl_izin`, `jam_keluar`, `jam_kembali`, `keperluan`, `status`, `created_at`) VALUES
(1, 3, 295, '2026-02-09', '09:23:00', '13:24:00', 'Ke Kampus', 'Disetujui', '2026-02-09 20:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `izin_pulang`
--

CREATE TABLE `izin_pulang` (
  `id_izin_pulang` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_atasan` int(11) NOT NULL,
  `tgl_izin` date NOT NULL,
  `jam_pulang` time NOT NULL,
  `keperluan` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT 0 COMMENT '0=Pending, 1=ACC, 2=Tolak',
  `tgl_input` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `izin_pulang`
--

INSERT INTO `izin_pulang` (`id_izin_pulang`, `id_user`, `id_atasan`, `tgl_izin`, `jam_pulang`, `keperluan`, `status`, `tgl_input`) VALUES
(1, 3, 295, '2026-02-12', '15:00:00', 'Acara', 1, '2026-02-12 11:13:36'),
(2, 3, 295, '2026-02-12', '15:00:00', 'Acara', 0, '2026-02-12 11:14:57');

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
  `tgl_pengajuan` date DEFAULT NULL,
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
  `id_pejabat` int(11) DEFAULT 0,
  `ttd_pejabat` enum('ketua','wakil','plh') DEFAULT 'ketua'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengajuan_cuti`
--

INSERT INTO `pengajuan_cuti` (`id_pengajuan`, `id_user`, `jenis_cuti`, `id_atasan`, `id_jenis`, `nomor_surat`, `tgl_pengajuan`, `tgl_mulai`, `tgl_selesai`, `lama_hari`, `alasan`, `no_telepon`, `masa_kerja`, `alamat_cuti`, `status`, `catatan_admin`, `created_at`, `sisa_cuti_n`, `sisa_cuti_n1`, `dipotong_n1`, `dipotong_n`, `id_pejabat`, `ttd_pejabat`) VALUES
(66, 3, NULL, NULL, 1, '001/KPN/W13.U1/KP.05.3/I/2026', '2026-01-28', '2026-01-12', '2026-01-19', 5, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-28 04:17:22', 0, 0, 4, 1, 0, 'ketua'),
(68, 3, NULL, 0, 4, '002/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-01-01', '2026-02-13', 30, 'Melahirkan', NULL, '', 'Magelang', 'Ditolak', NULL, '2026-01-29 03:07:30', 12, 4, 0, 0, 0, 'ketua'),
(70, 3, NULL, 0, 1, '003/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-02-02', '2026-02-03', 2, 'Acara Keluarga', NULL, '', 'Magelang', 'Disetujui', NULL, '2026-01-29 03:47:23', 12, 4, 2, 0, 0, 'ketua'),
(71, 3, NULL, NULL, 1, '004/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-02-02', '2026-02-03', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 03:51:40', 0, 0, 2, 0, 0, 'ketua'),
(72, 3, NULL, 233, 1, '005/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-02-02', '2026-02-03', 2, 'Acara Keluarga', NULL, '', 'Magelang\r\n', 'Ditolak', NULL, '2026-01-29 03:58:29', 12, 2, 2, 0, 0, 'ketua'),
(73, 3, NULL, 233, 1, '006/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-02-02', '2026-02-03', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 04:12:19', 0, 0, 0, 2, 0, 'ketua'),
(74, 3, NULL, 233, 1, '007/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-01-05', '2026-01-06', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 04:13:52', 0, 0, 0, 2, 0, 'ketua'),
(75, 3, NULL, 233, 1, '008/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-01-07', '2026-01-08', 2, 'Acara', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 04:19:35', 0, 0, 2, 0, 0, 'ketua'),
(76, 3, NULL, 233, 1, '009/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-01-15', '2026-01-19', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 06:34:02', 0, 0, 0, 2, 0, 'ketua'),
(77, 3, NULL, 233, 1, '010/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-01-19', '2026-01-20', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 06:38:09', 0, 0, 0, 2, 0, 'ketua'),
(84, 346, NULL, 233, 1, '011/KPN/W13.U1/KP.05.3/I/2026', '2026-01-31', '2026-01-30', '2026-02-03', 2, 'Bimbingan Kampus', NULL, '', 'Magelang', 'Ditolak', NULL, '2026-01-31 09:57:25', 12, 0, 0, 2, 0, 'ketua'),
(85, 346, NULL, 233, 1, '012/KPN/W13.U1/KP.05.3/I/2026', '2026-01-31', '2026-01-30', '2026-02-03', 2, 'Acara Kampus', NULL, '', 'Magelang', 'Disetujui', NULL, '2026-01-31 09:59:06', 12, 0, 0, 2, 0, 'ketua'),
(86, 346, NULL, 233, 1, '013/KPN/W13.U1/KP.05.3/I/2026', '2026-01-31', '2026-02-04', '2026-02-05', 2, 'Menikah', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-31 15:28:23', 0, 0, 0, 2, 0, 'ketua'),
(87, 346, NULL, 233, 1, '014/KPN/W13.U1/KP.05.3/I/2026', '2026-01-31', '2026-02-06', '2026-02-09', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-31 15:33:27', 0, 0, 0, 2, 0, 'ketua'),
(88, 346, NULL, 233, 1, '015/KPN/W13.U1/KP.05.3/I/2026', '2026-01-31', '2026-01-02', '2026-01-05', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-31 15:38:00', 0, 0, 0, 2, 0, 'ketua'),
(89, 3, NULL, 295, 1, '016/KPN/W13.U1/KP.05.3/II/2026', '2026-02-01', '2026-03-05', '2026-03-06', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-02-01 06:38:31', 0, 0, 0, 2, 0, 'ketua'),
(90, 344, NULL, 295, 1, '017/KPN/W13.U1/KP.05.3/II/2026', '2026-02-01', '2026-02-03', '2026-02-04', 2, 'Acara Keluarga', NULL, NULL, 'Jogja', 'Disetujui', NULL, '2026-02-01 06:47:37', 0, 0, 2, 0, 0, 'ketua'),
(91, 344, NULL, 295, 2, '018/KPN/W13.U1/KP.05.3/II/2026', '2026-02-01', '2026-02-05', '2026-02-05', 1, 'Sakit', NULL, NULL, 'Jogja', 'Disetujui', NULL, '2026-02-01 07:09:00', 0, 0, 0, 0, 0, 'ketua'),
(92, 344, NULL, 295, 3, '019/KPN/W13.U1/KP.05.3/II/2026', '2026-02-01', '2026-02-06', '2026-02-09', 2, 'Cuti Besar', NULL, NULL, 'Jogja', 'Disetujui', NULL, '2026-02-01 07:10:18', 0, 0, 0, 0, 0, 'ketua'),
(93, 344, NULL, 295, 5, '020/KPN/W13.U1/KP.05.3/II/2026', '2026-02-01', '2026-02-10', '2026-02-10', 1, 'Menemani Presiden', NULL, NULL, 'Jogja', 'Disetujui', NULL, '2026-02-01 07:11:28', 0, 0, 0, 0, 0, 'ketua'),
(94, 344, NULL, 295, 1, '021/KPN/W13.U1/KP.05.3/II/2026', '2026-02-01', '2026-03-02', '2026-03-06', 5, 'Acara ', NULL, NULL, 'Jogja', 'Disetujui', NULL, '2026-02-01 07:37:29', 0, 0, 4, 1, 0, 'ketua'),
(95, 344, NULL, 233, 1, '022/KPN/W13.U1/KP.05.3/II/2026', '2026-02-01', '2026-02-17', '2026-02-18', 1, 'Acara Keluarga', NULL, NULL, 'Jogja', 'Disetujui', NULL, '2026-02-01 14:33:17', 0, 0, 0, 1, 0, 'ketua'),
(96, 3, NULL, 233, 1, '023/KPN/W13.U1/KP.05.3/II/2026', '2026-02-02', '2026-02-05', '2026-02-06', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-02-02 01:27:52', 0, 0, 0, 2, 0, 'ketua'),
(97, 3, NULL, 295, 1, '024/KPN/W13.U1/KP.05.3/II/2026', '2026-02-02', '2026-02-09', '2026-02-10', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-02-02 02:04:17', 0, 0, 2, 0, 0, 'ketua'),
(98, 3, NULL, 294, 2, '025/KPN/W13.U1/KP.05.3/II/2026', '2026-02-03', '2025-12-03', '2025-12-04', 2, 'Sakit', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-02-03 07:55:46', 0, 0, 0, 0, 0, 'wakil'),
(99, 3, NULL, 293, 1, '026/KPN/W13.U1/KP.05.3/II/2026', '2026-02-03', '2025-12-15', '2025-12-16', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-02-03 08:02:41', 0, 0, 2, 0, 0, 'plh'),
(100, 3, NULL, 295, 1, '027/KPN/W13.U1/KP.05.3/II/2026', '2026-02-04', '2025-11-03', '2025-11-05', 3, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-02-04 00:59:16', 0, 0, 2, 1, 0, 'ketua'),
(101, 3, NULL, 254, 3, '028/KPN/W13.U1/KP.05.3/II/2026', '2026-02-04', '2026-02-23', '2026-02-24', 2, 'Cuti Besar', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-02-04 01:04:04', 0, 0, 0, 0, 0, 'plh'),
(106, 3, NULL, 293, 4, '029/KPN/W13.U1/KP.05.3/II/2026', '2026-02-04', '2026-09-01', '2026-10-25', 39, 'Melahirkan', NULL, '', 'Magelang', 'Ditolak', NULL, '2026-02-04 03:22:25', 11, 0, 0, 0, 0, 'ketua'),
(107, 3, NULL, 256, 5, '030/KPN/W13.U1/KP.05.3/II/2026', '2026-02-04', '2026-11-17', '2026-11-19', 3, 'Alasan Penting', NULL, '', 'Magelang', 'Disetujui', NULL, '2026-02-04 03:26:30', 11, 0, 0, 0, 0, 'wakil'),
(108, 298, NULL, 295, 1, '201/KPN/W13.U1/KP.05.3/II/2026', '2026-02-12', '2026-02-13', '2026-02-18', 2, 'Acara Keluarga', NULL, NULL, 'Yogyakarta', 'Disetujui', NULL, '2026-02-12 01:20:20', 0, 0, 2, 0, 0, 'wakil'),
(109, 299, NULL, 295, 1, '202/KPN/W13.U1/KP.05.3/II/2026', '2026-02-12', '2026-02-09', '2026-02-12', 4, 'Acara keluarga', NULL, NULL, 'Wonosobo', 'Disetujui', NULL, '2026-02-12 01:43:43', 0, 0, 4, 0, 0, 'wakil'),
(110, 3, NULL, 295, 5, '203/KPN/W13.U1/KP.05.3/II/2026', '2026-02-12', '2026-04-06', '2026-04-10', 5, 'hal penting', NULL, '2 Tahun 3 Bulan', 'yogyakarta', 'Diajukan', NULL, '2026-02-12 02:08:15', 11, 0, 0, 0, 0, 'ketua'),
(111, 233, NULL, 233, 1, '204/KPN/W13.U1/KP.05.3/II/2026', '2026-02-12', '2026-02-09', '2026-02-18', 6, 'Acara Keluarga', NULL, NULL, 'Jakarta', 'Disetujui', NULL, '2026-02-12 02:19:57', 0, 0, 6, 0, 0, 'ketua');

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
(1, 'Pengadilan Negeri Yogyakarta', '', 'SYAFRIZAL, S.H.', '196804141996031002', 'MELINDA ARITONANG, S.H.', '197809112001122002');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `masa_kerja` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `kategori_laporan` enum('HAKIM KARIR DAN AD HOC','PANITERA DAN PANMUD','SEKRETARIS DAN KASUBBAG','PANITERA PENGGANTI','JURUSITA','JURUSITA PENGGANTI','STAF') NOT NULL,
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
  `id_atasan` int(11) DEFAULT NULL,
  `is_atasan` enum('0','1') DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nip`, `masa_kerja`, `password`, `nama_lengkap`, `jabatan`, `kategori_laporan`, `pangkat`, `unit_kerja`, `no_telepon`, `role`, `is_atasan_langsung`, `sisa_cuti_n`, `sisa_cuti_n1`, `sisa_cuti_n2`, `kuota_cuti_sakit`, `created_at`, `status_akun`, `id_atasan`, `is_atasan`) VALUES
(1, 'admin', NULL, '$2y$10$Hz8R7a0J4bGIuHB4Ygu.LeYjaZG2aQRO5JqfQDX3dw2Iqp3nYMDcS', 'Administrator Ortala', 'Kepala Sub Bagian', 'STAF', '', 'Pengadilan Negeri Yogyakarta', NULL, 'admin', 0, 12, 6, 0, 14, '2026-01-15 02:30:50', 'aktif', 0, '0'),
(3, '124230050', NULL, '$2y$10$8BG/A/1OTFtCEuZmtKqyY.pi/nnKQiLwli56itJxFB6y/HjpZvT42', 'Nissa Aulia', 'Panitera', 'STAF', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '0897878657657', 'user', 0, 11, 0, 0, 12, '2026-01-17 15:38:04', 'aktif', 0, '0'),
(233, '196804141996031002', '26 Tahun', 'e10adc3949ba59abbe56e057f20f883e', 'SYAFRIZAL, S.H.', 'Ketua', 'HAKIM KARIR DAN AD HOC', 'Pembina Utama Madya (IV/d)', 'Pengadilan Negeri Yogyakarta', '81264701704', 'user', 1, 12, 0, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '1'),
(234, '197809112001122002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'MELINDA ARITONANG, S.H.', 'Wakil Ketua', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81397887256', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '1'),
(235, '196905311996031001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SUNARYANTO, SH.,MH', 'Hakim Utama Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Utama Madya (IV/d)', 'Pengadilan Negeri Yogyakarta', '81395831369', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(236, '197501272000032003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NI LUH SUKMARINI, SH., MH', 'Hakim Madya Utama', 'HAKIM KARIR DAN AD HOC', 'Pembina Utama Muda (IV/c)', 'Pengadilan Negeri Yogyakarta', '81285095065', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(237, '197204271993031003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'KARSENA, S.H.,M.H.', 'Hakim Madya Utama', 'HAKIM KARIR DAN AD HOC', 'Pembina Utama Muda (IV/c)', 'Pengadilan Negeri Yogyakarta', '8125059669', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(238, '196908101990031006', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'JAMUJI, SH.,MH', 'Hakim Madya Utama', 'HAKIM KARIR DAN AD HOC', 'Pembina Utama Muda (IV/c)', 'Pengadilan Negeri Yogyakarta', '81359570067', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(239, '197510282000122002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'FITRI RAMADHAN, SH', 'Hakim Madya Utama', 'HAKIM KARIR DAN AD HOC', 'Pembina Utama Muda (IV/c)', 'Pengadilan Negeri Yogyakarta', '81328346942', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(240, '197701312001121002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SURYA LAKSEMANA, SH', 'Hakim Madya Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81373283366', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(241, '197609172001122003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SETYANINGSIH, SH', 'Hakim Madya Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81223451508', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(242, '197801122002121002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'GABRIEL SIALLAGAN, SH.,MH', 'Hakim Madya Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '85245673555', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(243, '197505162002122001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'PATYARINI MEININGSIH R, SH.,M.Hum', 'Hakim Madya Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81328017333', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(244, '197610092002121004', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'MUHAMMAD ISMAIL HAMID, SH.,MH', 'Hakim Madya Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81227140722', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(245, '197709242002122003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SRI SULASTUTI, SH', 'Hakim Madya Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81326875277', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(246, '197912282002122001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ERNI KUSUMAWATI, SH., MH', 'Hakim Madya Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81326875277', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(247, '197703192002122003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SRI WIJAYANTI TANJUNG, SH', 'Hakim Madya Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '87708771701', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(248, '197701192002121004', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'REZA TYRAMA, SH', 'Hakim Madya Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81339456870', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(249, '197807152003121002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'PURNOMO WIBOWO, SH.,MH', 'Hakim Madya Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '85216443344', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(250, '197901272003121001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'DJOKO WIRYONO BUDHI S, SH', 'Hakim Madya Muda', 'HAKIM KARIR DAN AD HOC', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '85702054136', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(251, '197303151992032001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'JOHANA CAROLINA LEKBILA, S.IP.,SH', 'Panitera', 'PANITERA DAN PANMUD', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '82144567924', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '1'),
(252, '197807082006042001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'DIAN UMAWATI,SH., MH', 'Panitera Muda Khusus PHI', 'PANITERA DAN PANMUD', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '81226333474', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '1'),
(253, '197207092006042002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'VIRONIKA SRI YULIATI, S.Sos.,SH.,MH', 'Panitera Muda Pidana', 'PANITERA DAN PANMUD', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '81328599889', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '1'),
(254, '198010092008051002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ANDANG CATUR PRASETYA, SH., MH', 'Panitera Muda Hukum', 'PANITERA DAN PANMUD', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '82227322732', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '1'),
(255, '198204152011011005', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'DARU BUANA SEJATI, SH', 'Panitera Muda Perdata', 'PANITERA DAN PANMUD', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81227579570', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '1'),
(256, '196805141990032005', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'HENY SURYANI, SH', 'Panitera Muda Khusus Tipikor', 'PANITERA DAN PANMUD', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '82135842639', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '1'),
(257, '197801272002122003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'THESIANA MAYA FITRIA A, SH.,MH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '81353053399', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(258, '198310262008012008', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'OCTAVIA MARIANA WIJAYANTI, SH.,MH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '87839938069', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(259, '196710201993032005', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'RR.DINAWATI, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81336178675', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(260, '196907141994032005', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'Rr. SRI WINASTUTI,SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81361183214', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(261, '196606021999032003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ANNA HENY W,SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81903961537', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(262, '196911151992032004', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'MARIA LUSIATI,SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '85742376049', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(263, '196908051992031004', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'KUWAT WAHYU MURDANA,SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81804320567', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(264, '197001191992032002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'YANI WIDIYANTI, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '82134317035', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(265, '197006101992032002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SRI SUWANTI, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81393568989', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(266, '197509052001122001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NURI MAHAR KESTRI,SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81215204192', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(267, '196604091990032003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NUNUNG DIAH RST, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81233687016', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(268, '197111102006041001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ANTONIUS ANDI SUSANTO, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8158376447', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(269, '196911161993031002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'YUDI SUHENDRO, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81347990600', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(270, '196605181988031001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SURYONO NUGROHO,SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '85866360946', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(271, '197907092009042004', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'RULLIANA YUDAWATI, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8122940090', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(272, '197606152006042002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'YUDHA AYU TIMORNIYATI, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81348288688', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(273, '197706072000122002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'RR. WORO HAPSARI D,SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8159523402', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(274, '198508052009122005', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'RIKE SIMBALAGO, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '87839950005', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(275, '198309172011011008', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SEPTIAN ADI SASTRIA, S.H.', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '82223266484', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(276, '198207062011012009', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NAFISATUN ANA FITRIA UTAMI, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8175489514', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(277, '198711122006041001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'FRANGKY ANTONI P, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81341481180', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(278, '197111061993031002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'AGUS RIYANTO, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8562939683', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(279, '198212192006041002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'RIMBANG KRISDIANTO, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '81328708324', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(280, '198803252015032001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SHEILA POSITA, SH.,MH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '82138690608', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(281, '199006132009042001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'YUNITA NILA KRISNA, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '81310525304', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(282, '198409262009041004', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'BARATA MUHARAMIN, SH', 'Panitera Pengganti', 'PANITERA PENGGANTI', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '82242270197', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(283, '197306261994031003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'HERI PRASETYA, SH', 'Jurusita', 'JURUSITA', 'Penata Tk I (III/d)', 'Pengadilan Negeri Yogyakarta', '82134892673', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(284, '197508252006042003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'LUSI RACHMAYANI,SE.SH', 'Jurusita', 'JURUSITA', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8895705566', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(285, '198007072008051001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ARLYO PERDANA PUTRA,SH', 'Jurusita', 'JURUSITA', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '87779597666', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(286, '197305252006041004', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NANANG SUPRIYADI, SE.,SH.,M.Kn', 'Jurusita', 'JURUSITA', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81328017697', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(287, '197210041993031005', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SALASA AGUS EKOYADI, SH', 'Jurusita', 'JURUSITA', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '81804041072', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(288, '198001012008052002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NURMAYA REZEKY AR, SH', 'Jurusita', 'JURUSITA', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '81227948889', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(289, '198209222009042008', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'JEANNE PAMELA,S.Kom,MT', 'JSP / Staf Kepan Perdata', 'JURUSITA PENGGANTI', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '85365416982', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(290, '197001171990032001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'WARSIYATI', 'JSP / Staf Kepan Pidana', 'JURUSITA PENGGANTI', 'Penata Muda Tk.I (III/b)', 'Pengadilan Negeri Yogyakarta', '85867508294', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(291, '197601011995101001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'DOMINGOS DOUTEL', 'JSP / Staf Sub Bag Umum dan Keu', 'JURUSITA PENGGANTI', 'Penata Muda Tk.I (III/b)', 'Pengadilan Negeri Yogyakarta', '81227962272', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(292, '196812211990031002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'MOHAMAD SAID IDUL FITRI', 'JSP / Staf Kepan Perdata', 'JURUSITA PENGGANTI', 'Penata Muda Tk.I (III/b)', 'Pengadilan Negeri Yogyakarta', '87738087730', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(293, '197308161994031001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'TASIMAN, SH.,MH', 'Sekretaris', 'SEKRETARIS DAN KASUBBAG', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81328424336', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '1'),
(294, '198404102009042016', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'YENNY VIKKY EFFENDY,ST.SH.M.Eng', 'Kepala Sub Bagian PTIP', 'SEKRETARIS DAN KASUBBAG', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '87838370023', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '1'),
(295, '198103302006041004', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'EVENDI NUGROHO,ST', 'Kepala Sub Bagian Kepegawaian Ortala', 'SEKRETARIS DAN KASUBBAG', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '85711685685', 'admin', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '1'),
(296, '198607242011011005', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'KUNCORO SETYA R,SE.,MM', 'Analis APBN', 'STAF', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '87878321018', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(297, '199102032019031005', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NUGRAHA ABDILLAH, S.Kom', 'Pranata Komp.Ahli Pertama', 'STAF', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '87880101733', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(298, '199509072020121004', '5 Tahun', 'e10adc3949ba59abbe56e057f20f883e', 'MUHAMMAD NUR FIRDAUS S, A.Md', 'Arsiparis Terampil', 'STAF', 'Pengatur Tk.I (II/d)', 'Pengadilan Negeri Yogyakarta', '85875803132', 'user', 0, 12, 4, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(299, '198208182010121002', '17 Tahun 9 Bulan', 'e10adc3949ba59abbe56e057f20f883e', 'HARIS HERMAWAN EFFENDI, SS.,MM', 'Penata Layanan Operasional', 'STAF', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '85772126935', 'admin', 0, 12, 2, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(300, '198510182015031001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ARDI WICAKSONO, ST', 'Penata Layanan Operasional', 'STAF', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '82141081211', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(301, '199510162020122006', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'OKTA EMILIA LARASATI, SH', 'Analis Perkara Peradilan', 'STAF', 'Penata Muda Tk.I (III/b)', 'Pengadilan Negeri Yogyakarta', '81904224409', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(302, '199511142020122005', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NADYA PRIMAASHA BRAHMANA, SH', 'Analis Perkara Peradilan', 'STAF', 'Penata Muda Tk.I (III/b)', 'Pengadilan Negeri Yogyakarta', '82283958516', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(303, '198911262015032003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NOVITA DIASTUTI, S.Kom', 'Penata Layanan Operasional', 'STAF', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '81325151283', 'admin', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(304, '199711132020121003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'DWI NOVIANDARU, S. Tr. Kom', 'Penata Layanan Operasional', 'STAF', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '85702066899', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(305, '199603252020122003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NADYA MAULANI MELYANA, A.Md. A.P', 'Pengelola Perkara', 'STAF', 'Pengatur Tk.I (II/d)', 'Pengadilan Negeri Yogyakarta', '88806292633', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(306, '199911232024052001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'DINA TRI LESTARI, SH', 'Analis Perkara Peradilan', 'STAF', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '81297433256', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(307, '200010122024052002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'PUTRI AZZAHRA, SH', 'Analis Perkara Peradilan', 'STAF', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '85322596883', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(308, '200007192024051002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'RYAN ADE SAPUTRO, SH', 'Analis Perkara Peradilan', 'STAF', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '85951287035', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(309, '197212161993031001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'MOH. RUSDIANTO', 'Teknisi Sarana dan Prasarana-Umum', 'STAF', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '81329432020', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(310, '197806192014081004', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NINDYA YOSDALU PUTRA', 'Pengadministrasi Perkantoran', 'STAF', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '81904032504', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(311, '199904292022032021', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'INDAH MELINDA, A.Md.A.B.', 'Pengelola Perkara', 'STAF', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '85817300752', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(312, '199412122022032011', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'TESA MONICA BR GULTOM, A.Md', 'Pengelola Data dan Informasi', 'STAF', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '81262368209', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(313, '199112192022032006', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SUSI SUSANTI SINAGA, A.Md', 'Pengelola Data dan Informasi', 'STAF', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '81396211674', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(314, '200108022025062018', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'JESSICA IRENE NADEAK, SH', 'Analis Perkara Peradilan', 'STAF', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '895613412475', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(315, '199310022025062003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'FRANCISCA WESTRI INDASARI, SH', 'Analis Perkara Peradilan', 'STAF', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '81332627344', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(316, '199703042025062014', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ANIS HANIFAH, A.Md.Kom', 'Pengelola Data dan Informasi', 'STAF', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '83840989004', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(317, '1471110904700001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'HERI PURNOMO, S.Si.,SH.,MH', 'HAKIM AD HOC PHI', 'HAKIM KARIR DAN AD HOC', '', 'Pengadilan Negeri Yogyakarta', '81365421908', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(318, '3471085612690001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SITI UMI AKHIROKH, SH.,MH', 'HAKIM AD HOC PHI', 'HAKIM KARIR DAN AD HOC', '', 'Pengadilan Negeri Yogyakarta', '81328388544', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(319, '3216072404760003', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'AJI, S.H.', 'HAKIM AD HOC PHI', 'HAKIM KARIR DAN AD HOC', '', 'Pengadilan Negeri Yogyakarta', '8176364078', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(320, '7106096210740001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'MAYA RIESKE J RUMAMBI, SH.,MH', 'HAKIM AD HOC PHI', 'HAKIM KARIR DAN AD HOC', '', 'Pengadilan Negeri Yogyakarta', '8989960298', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(321, '3578080611690005', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SOEBEKTI, S.H.', 'HAKIM AD HOC TIPIKOR', 'HAKIM KARIR DAN AD HOC', '', 'Pengadilan Negeri Yogyakarta', '81217812187', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(322, '1271071003660004', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ELIAS HAMONANGAN, SE.,SH.,MH', 'HAKIM AD HOC TIPIKOR', 'HAKIM KARIR DAN AD HOC', '', 'Pengadilan Negeri Yogyakarta', '85250038183', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(323, '3401070601700001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'WARSONO, SH.,MH', 'HAKIM AD HOC TIPIKOR', 'HAKIM KARIR DAN AD HOC', '', 'Pengadilan Negeri Yogyakarta', '87829027477', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(324, '3322195308730001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ATUN BUDI ASTUTI, SH', 'HAKIM AD HOC TIPIKOR', 'HAKIM KARIR DAN AD HOC', '', 'Pengadilan Negeri Yogyakarta', '82257588439', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(325, '3217022704690001', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'KUSMAT TIRTA SASMITA, SH', 'HAKIM AD HOC TIPIKOR', 'HAKIM KARIR DAN AD HOC', '', 'Pengadilan Negeri Yogyakarta', '859115520810', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(326, '3313092802720002', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'YULIUS EKA SETIAWAN, SH.,MH', 'HAKIM AD HOC TIPIKOR', 'HAKIM KARIR DAN AD HOC', '', 'Pengadilan Negeri Yogyakarta', '8122582023', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(327, '198107302025212017', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'DIAH SUKORINI,SH', 'Penata Layanan Operasional- Umum Keu', 'STAF', 'IX', 'Pengadilan Negeri Yogyakarta', '89648738981', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(328, '198708282025211042', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'FAHMI HIDAYAT, SH', 'Penata Layanan Operasional- PTIP', 'STAF', 'IX', 'Pengadilan Negeri Yogyakarta', '82231227733', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(329, '198412272025211029', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'DONNY SURIPTO', 'Operator Layanan Operasional-Perdata', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '81391575570', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(330, '197312172025211016', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'TUNJUNG SULAKSANA P', 'Operator Layanan Operasional-Tipikor', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '895415121366', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(331, '198701202025211030', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'KEMAS INDARTO', 'Operator Layanan Operasional-Pidana', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '87782043871', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(332, '197308302025211014', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'WIRID WINOTO', 'Pengadministrasi Perkantoran-Umum Keu', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '87738213999', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(333, '198508312025211035', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ARIF PRIHENDARTO', 'Pengadministrasi Perkantoran-PTIP', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '88233204286', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(334, '196906272025212006', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'BARIYAH', 'Operator Layanan Operasional-Umum Keu', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '85105011453', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(335, '197305292025211010', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'BUDI PRASETYO', 'Operator Layanan Operasional-Hukum', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '8813849841', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(336, '197511092025211023', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ANDIK SULISTYO', 'Pengadministrasi Perkantoran- Kepeg Ortala', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '817278128', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(337, '197608072025211028', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'DENY DWI SUSILO', 'Operator Layanan Operasional-HAM', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '81227872811', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(338, '198902142025211034', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'PEBRIANTO', 'Operator Layanan Operasional-PTIP', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '81328658736', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(339, '198107172025211034', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'BAMBANG NUGROHO A MARTANTYO', 'Operator Layanan Operasional-PHI', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '82140187130', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(340, '198307232025211038', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'DWI RIYANTO', 'Operator Layanan Operasional-Umum Keu', 'STAF', 'V', 'Pengadilan Negeri Yogyakarta', '882006091009', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(341, '198109292025211037', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'SUDARMADI', 'Pengelola Umum Operasional-Umum Keu', 'STAF', 'I', 'Pengadilan Negeri Yogyakarta', '85642404369', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(342, '197801252025211018', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NGADIYO', 'Pengelola Umum Operasional-Kepeg Ortala', 'STAF', 'I', 'Pengadilan Negeri Yogyakarta', '81226081887', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(343, '198412312025211083', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'EDI SISWANTO', 'Pengelola Umum Operasional-Hukum', 'STAF', 'I', 'Pengadilan Negeri Yogyakarta', '85643573684', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(344, '123455', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'ANGGA PERDANA PUTRA, SH', 'Penata Layanan Operasional- Pidana', 'STAF', 'Paruh Waktu', 'Pengadilan Negeri Yogyakarta', '85729199282', 'user', 0, 10, 0, 0, 13, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(345, '123456', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NOVIA IKE DEVITA, S.Kom.', 'PRAMUBHAKTI', 'STAF', '', 'Pengadilan Negeri Yogyakarta', '', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL, '0'),
(346, '1234 5678 90', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'Budi Magang', 'Magang', 'STAF', '-', 'Pengadilan Negeri Yogyakarta', NULL, 'admin', 0, 12, 6, 0, 14, '2026-01-31 09:55:12', 'aktif', 0, '0'),
(347, '131004 131004', NULL, 'e10adc3949ba59abbe56e057f20f883e', 'NANA NINI NUNU', 'Panitera', 'STAF', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', NULL, 'user', 0, 12, 4, 0, 14, '2026-02-01 13:50:33', 'aktif', 0, '0');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `izin_keluar`
--
ALTER TABLE `izin_keluar`
  ADD PRIMARY KEY (`id_izin`);

--
-- Indexes for table `izin_pulang`
--
ALTER TABLE `izin_pulang`
  ADD PRIMARY KEY (`id_izin_pulang`);

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
-- AUTO_INCREMENT for table `izin_keluar`
--
ALTER TABLE `izin_keluar`
  MODIFY `id_izin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `izin_pulang`
--
ALTER TABLE `izin_pulang`
  MODIFY `id_izin_pulang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jenis_cuti`
--
ALTER TABLE `jenis_cuti`
  MODIFY `id_jenis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `libur_nasional`
--
ALTER TABLE `libur_nasional`
  MODIFY `id_libur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  MODIFY `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `tbl_setting_instansi`
--
ALTER TABLE `tbl_setting_instansi`
  MODIFY `id_setting` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=348;

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
