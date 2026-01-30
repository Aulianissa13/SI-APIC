-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2026 at 02:50 PM
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
  `id_pejabat` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengajuan_cuti`
--

INSERT INTO `pengajuan_cuti` (`id_pengajuan`, `id_user`, `jenis_cuti`, `id_atasan`, `id_jenis`, `nomor_surat`, `tgl_pengajuan`, `tgl_mulai`, `tgl_selesai`, `lama_hari`, `alasan`, `no_telepon`, `masa_kerja`, `alamat_cuti`, `status`, `catatan_admin`, `created_at`, `sisa_cuti_n`, `sisa_cuti_n1`, `dipotong_n1`, `dipotong_n`, `id_pejabat`) VALUES
(66, 3, NULL, NULL, 1, '001/KPN/W13.U1/KP.05.3/I/2026', '2026-01-28', '2026-01-12', '2026-01-19', 5, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-28 04:17:22', 0, 0, 4, 1, 0),
(68, 3, NULL, 0, 4, '002/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-01-01', '2026-02-13', 30, 'Melahirkan', NULL, '', 'Magelang', 'Ditolak', NULL, '2026-01-29 03:07:30', 12, 4, 0, 0, 0),
(70, 3, NULL, 0, 1, '003/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-02-02', '2026-02-03', 2, 'Acara Keluarga', NULL, '', 'Magelang', 'Disetujui', NULL, '2026-01-29 03:47:23', 12, 4, 2, 0, 0),
(71, 3, NULL, NULL, 1, '004/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-02-02', '2026-02-03', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 03:51:40', 0, 0, 2, 0, 0),
(72, 3, NULL, 233, 1, '005/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-02-02', '2026-02-03', 2, 'Acara Keluarga', NULL, '', 'Magelang\r\n', 'Ditolak', NULL, '2026-01-29 03:58:29', 12, 2, 2, 0, 0),
(73, 3, NULL, 233, 1, '006/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-02-02', '2026-02-03', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 04:12:19', 0, 0, 0, 2, 0),
(74, 3, NULL, 233, 1, '007/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-01-05', '2026-01-06', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 04:13:52', 0, 0, 0, 2, 0),
(75, 3, NULL, 233, 1, '008/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-01-07', '2026-01-08', 2, 'Acara', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 04:19:35', 0, 0, 2, 0, 0),
(76, 3, NULL, 233, 1, '009/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-01-15', '2026-01-19', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 06:34:02', 0, 0, 0, 2, 0),
(77, 3, NULL, 233, 1, '010/KPN/W13.U1/KP.05.3/I/2026', '2026-01-29', '2026-01-19', '2026-01-20', 2, 'Acara Keluarga', NULL, NULL, 'Magelang', 'Disetujui', NULL, '2026-01-29 06:38:09', 0, 0, 0, 2, 0);

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
(3, '124230050', '$2y$10$8BG/A/1OTFtCEuZmtKqyY.pi/nnKQiLwli56itJxFB6y/HjpZvT42', 'Nissa Aulia', 'Panitera', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '0897878657657', 'user', 0, 4, 0, 0, 14, '2026-01-17 15:38:04', 'aktif', 0),
(233, '196804141996031002', 'e10adc3949ba59abbe56e057f20f883e', 'SYAFRIZAL, S.H.', 'Ketua', 'Pembina Utama Madya (IV/d)', 'Pengadilan Negeri Yogyakarta', '81264701704', 'user', 1, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(234, '197809112001122002', 'e10adc3949ba59abbe56e057f20f883e', 'MELINDA ARITONANG, S.H.', 'Wakil Ketua', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81397887256', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(235, '196905311996031001', 'e10adc3949ba59abbe56e057f20f883e', 'SUNARYANTO, SH.,MH', 'Hakim Utama Muda', 'Pembina Utama Madya (IV/d)', 'Pengadilan Negeri Yogyakarta', '81395831369', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(236, '197501272000032003', 'e10adc3949ba59abbe56e057f20f883e', 'NI LUH SUKMARINI, SH., MH', 'Hakim Madya Utama', 'Pembina Utama Muda (IV/c)', 'Pengadilan Negeri Yogyakarta', '81285095065', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(237, '197204271993031003', 'e10adc3949ba59abbe56e057f20f883e', 'KARSENA, S.H.,M.H.', 'Hakim Madya Utama', 'Pembina Utama Muda (IV/c)', 'Pengadilan Negeri Yogyakarta', '8125059669', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(238, '196908101990031006', 'e10adc3949ba59abbe56e057f20f883e', 'JAMUJI, SH.,MH', 'Hakim Madya Utama', 'Pembina Utama Muda (IV/c)', 'Pengadilan Negeri Yogyakarta', '81359570067', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(239, '197510282000122002', 'e10adc3949ba59abbe56e057f20f883e', 'FITRI RAMADHAN, SH', 'Hakim Madya Utama', 'Pembina Utama Muda (IV/c)', 'Pengadilan Negeri Yogyakarta', '81328346942', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(240, '197701312001121002', 'e10adc3949ba59abbe56e057f20f883e', 'SURYA LAKSEMANA, SH', 'Hakim Madya Muda', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81373283366', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(241, '197609172001122003', 'e10adc3949ba59abbe56e057f20f883e', 'SETYANINGSIH, SH', 'Hakim Madya Muda', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81223451508', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(242, '197801122002121002', 'e10adc3949ba59abbe56e057f20f883e', 'GABRIEL SIALLAGAN, SH.,MH', 'Hakim Madya Muda', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '85245673555', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(243, '197505162002122001', 'e10adc3949ba59abbe56e057f20f883e', 'PATYARINI MEININGSIH R, SH.,M.Hum', 'Hakim Madya Muda', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81328017333', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(244, '197610092002121004', 'e10adc3949ba59abbe56e057f20f883e', 'MUHAMMAD ISMAIL HAMID, SH.,MH', 'Hakim Madya Muda', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81227140722', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(245, '197709242002122003', 'e10adc3949ba59abbe56e057f20f883e', 'SRI SULASTUTI, SH', 'Hakim Madya Muda', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81326875277', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(246, '197912282002122001', 'e10adc3949ba59abbe56e057f20f883e', 'ERNI KUSUMAWATI, SH., MH', 'Hakim Madya Muda', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81326875277', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(247, '197703192002122003', 'e10adc3949ba59abbe56e057f20f883e', 'SRI WIJAYANTI TANJUNG, SH', 'Hakim Madya Muda', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '87708771701', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(248, '197701192002121004', 'e10adc3949ba59abbe56e057f20f883e', 'REZA TYRAMA, SH', 'Hakim Madya Muda', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81339456870', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(249, '197807152003121002', 'e10adc3949ba59abbe56e057f20f883e', 'PURNOMO WIBOWO, SH.,MH', 'Hakim Madya Muda', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '85216443344', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(250, '197901272003121001', 'e10adc3949ba59abbe56e057f20f883e', 'DJOKO WIRYONO BUDHI S, SH', 'Hakim Madya Muda', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '85702054136', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(251, '197303151992032001', 'e10adc3949ba59abbe56e057f20f883e', 'ANA CAROLINA LEKBILA, S.IP.,SH', 'Panitera', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '82144567924', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(252, '197807082006042001', 'e10adc3949ba59abbe56e057f20f883e', 'DIAN UMAWATI,SH., MH', 'Panitera Muda Khusus PHI', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '81226333474', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(253, '197207092006042002', 'e10adc3949ba59abbe56e057f20f883e', 'VIRONIKA SRI YULIATI, S.Sos.,SH.,MH', 'Panitera Muda Pidana', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '81328599889', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(254, '198010092008051002', 'e10adc3949ba59abbe56e057f20f883e', 'ANDANG CATUR PRASETYA, SH., MH', 'Panitera Muda Hukum', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '82227322732', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(255, '198204152011011005', 'e10adc3949ba59abbe56e057f20f883e', 'DARU BUANA SEJATI, SH', 'Panitera Muda Perdata', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81227579570', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(256, '196805141990032005', 'e10adc3949ba59abbe56e057f20f883e', 'HENY SURYANI, SH', 'Panitera Muda Khusus Tipikor', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '82135842639', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(257, '197801272002122003', 'e10adc3949ba59abbe56e057f20f883e', 'THESIANA MAYA FITRIA A, SH.,MH', 'Panitera Pengganti', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '81353053399', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(258, '198310262008012008', 'e10adc3949ba59abbe56e057f20f883e', 'OCTAVIA MARIANA WIJAYANTI, SH.,MH', 'Panitera Pengganti', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '87839938069', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(259, '196710201993032005', 'e10adc3949ba59abbe56e057f20f883e', 'RR.DINAWATI, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81336178675', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(260, '196907141994032005', 'e10adc3949ba59abbe56e057f20f883e', 'Rr. SRI WINASTUTI,SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81361183214', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(261, '196606021999032003', 'e10adc3949ba59abbe56e057f20f883e', 'ANNA HENY W,SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81903961537', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(262, '196911151992032004', 'e10adc3949ba59abbe56e057f20f883e', 'MARIA LUSIATI,SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '85742376049', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(263, '196908051992031004', 'e10adc3949ba59abbe56e057f20f883e', 'KUWAT WAHYU MURDANA,SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81804320567', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(264, '197001191992032002', 'e10adc3949ba59abbe56e057f20f883e', 'YANI WIDIYANTI, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '82134317035', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(265, '197006101992032002', 'e10adc3949ba59abbe56e057f20f883e', 'SRI SUWANTI, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81393568989', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(266, '197509052001122001', 'e10adc3949ba59abbe56e057f20f883e', 'NURI MAHAR KESTRI,SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81215204192', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(267, '196604091990032003', 'e10adc3949ba59abbe56e057f20f883e', 'NUNUNG DIAH RST, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81233687016', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(268, '197111102006041001', 'e10adc3949ba59abbe56e057f20f883e', 'ANTONIUS ANDI SUSANTO, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8158376447', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(269, '196911161993031002', 'e10adc3949ba59abbe56e057f20f883e', 'YUDI SUHENDRO, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81347990600', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(270, '196605181988031001', 'e10adc3949ba59abbe56e057f20f883e', 'SURYONO NUGROHO,SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '85866360946', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(271, '197907092009042004', 'e10adc3949ba59abbe56e057f20f883e', 'RULLIANA YUDAWATI, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8122940090', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(272, '197606152006042002', 'e10adc3949ba59abbe56e057f20f883e', 'YUDHA AYU TIMORNIYATI, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81348288688', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(273, '197706072000122002', 'e10adc3949ba59abbe56e057f20f883e', 'RR. WORO HAPSARI D,SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8159523402', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(274, '198508052009122005', 'e10adc3949ba59abbe56e057f20f883e', 'RIKE SIMBALAGO, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '87839950005', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(275, '198309172011011008', 'e10adc3949ba59abbe56e057f20f883e', 'SEPTIAN ADI SASTRIA, S.H.', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '82223266484', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(276, '198207062011012009', 'e10adc3949ba59abbe56e057f20f883e', 'NAFISATUN ANA FITRIA UTAMI, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8175489514', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(277, '198711122006041001', 'e10adc3949ba59abbe56e057f20f883e', 'FRANGKY ANTONI P, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81341481180', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(278, '197111061993031002', 'e10adc3949ba59abbe56e057f20f883e', 'AGUS RIYANTO, SH', 'Panitera Pengganti', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8562939683', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(279, '198212192006041002', 'e10adc3949ba59abbe56e057f20f883e', 'RIMBANG KRISDIANTO, SH', 'Panitera Pengganti', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '81328708324', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(280, '198803252015032001', 'e10adc3949ba59abbe56e057f20f883e', 'SHEILA POSITA, SH.,MH', 'Panitera Pengganti', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '82138690608', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(281, '199006132009042001', 'e10adc3949ba59abbe56e057f20f883e', 'YUNITA NILA KRISNA, SH', 'Panitera Pengganti', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '81310525304', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(282, '198409262009041004', 'e10adc3949ba59abbe56e057f20f883e', 'BARATA MUHARAMIN, SH', 'Panitera Pengganti', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '82242270197', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(283, '197306261994031003', 'e10adc3949ba59abbe56e057f20f883e', 'HERI PRASETYA, SH', 'Jurusita', 'Penata Tk I (III/d)', 'Pengadilan Negeri Yogyakarta', '82134892673', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(284, '197508252006042003', 'e10adc3949ba59abbe56e057f20f883e', 'LUSI RACHMAYANI,SE.SH', 'Jurusita', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '8895705566', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(285, '198007072008051001', 'e10adc3949ba59abbe56e057f20f883e', 'ARLYO PERDANA PUTRA,SH', 'Jurusita', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '87779597666', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(286, '197305252006041004', 'e10adc3949ba59abbe56e057f20f883e', 'NANANG SUPRIYADI, SE.,SH.,M.Kn', 'Jurusita', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '81328017697', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(287, '197210041993031005', 'e10adc3949ba59abbe56e057f20f883e', 'SALASA AGUS EKOYADI, SH', 'Jurusita', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '81804041072', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(288, '198001012008052002', 'e10adc3949ba59abbe56e057f20f883e', 'NURMAYA REZEKY AR, SH', 'Jurusita', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '81227948889', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(289, '198209222009042008', 'e10adc3949ba59abbe56e057f20f883e', 'JEANNE PAMELA,S.Kom,MT', 'JSP / Staf Kepan Perdata', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '85365416982', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(290, '197001171990032001', 'e10adc3949ba59abbe56e057f20f883e', 'WARSIYATI', 'JSP / Staf Kepan Pidana', 'Penata Muda Tk.I (III/b)', 'Pengadilan Negeri Yogyakarta', '85867508294', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(291, '197601011995101001', 'e10adc3949ba59abbe56e057f20f883e', 'DOMINGOS DOUTEL', 'JSP / Staf Sub Bag Umum dan Keu', 'Penata Muda Tk.I (III/b)', 'Pengadilan Negeri Yogyakarta', '81227962272', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(292, '196812211990031002', 'e10adc3949ba59abbe56e057f20f883e', 'MOHAMAD SAID IDUL FITRI', 'JSP / Staf Kepan Perdata', 'Penata Muda Tk.I (III/b)', 'Pengadilan Negeri Yogyakarta', '87738087730', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(293, '197308161994031001', 'e10adc3949ba59abbe56e057f20f883e', 'TASIMAN, SH.,MH', 'Sekretaris', 'Pembina Tk.I (IV/b)', 'Pengadilan Negeri Yogyakarta', '81328424336', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(294, '198404102009042016', 'e10adc3949ba59abbe56e057f20f883e', 'YENNY VIKKY EFFENDY,ST.SH.M.Eng', 'Ka.Sub Bab PTIP', 'Pembina (IV/a)', 'Pengadilan Negeri Yogyakarta', '87838370023', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(295, '198103302006041004', 'e10adc3949ba59abbe56e057f20f883e', 'EVENDI NUGROHO,ST', 'Ka.Sub Bag.Kepeg. Ortalak', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '85711685685', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(296, '198607242011011005', 'e10adc3949ba59abbe56e057f20f883e', 'KUNCORO SETYA R,SE.,MM', 'Analis APBN', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '87878321018', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(297, '199102032019031005', 'e10adc3949ba59abbe56e057f20f883e', 'NUGRAHA ABDILLAH, S.Kom', 'Pranata Komp.Ahli Pertama', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '87880101733', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(298, '199509072020121004', 'e10adc3949ba59abbe56e057f20f883e', 'MUHAMMAD NUR FIRDAUS S, A.Md', 'Arsiparis Terampil', 'Pengatur Tk.I (II/d)', 'Pengadilan Negeri Yogyakarta', '85875803132', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(299, '198208182010121002', 'e10adc3949ba59abbe56e057f20f883e', 'HARIS HERMAWAN EFFENDI, SS.,MM', 'Penata Layanan Operasional', 'Penata Tk.I (III/d)', 'Pengadilan Negeri Yogyakarta', '85772126935', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(300, '198510182015031001', 'e10adc3949ba59abbe56e057f20f883e', 'ARDI WICAKSONO, ST', 'Penata Layanan Operasional', 'Penata (III/c)', 'Pengadilan Negeri Yogyakarta', '82141081211', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(301, '199510162020122006', 'e10adc3949ba59abbe56e057f20f883e', 'OKTA EMILIA LARASATI, SH', 'Analis Perkara Peradilan', 'Penata Muda Tk.I (III/b)', 'Pengadilan Negeri Yogyakarta', '81904224409', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(302, '199511142020122005', 'e10adc3949ba59abbe56e057f20f883e', 'NADYA PRIMAASHA BRAHMANA, SH', 'Analis Perkara Peradilan', 'Penata Muda Tk.I (III/b)', 'Pengadilan Negeri Yogyakarta', '82283958516', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(303, '198911262015032003', 'e10adc3949ba59abbe56e057f20f883e', 'NOVITA DIASTUTI, S.Kom', 'Penata Layanan Operasional', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '81325151283', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(304, '199711132020121003', 'e10adc3949ba59abbe56e057f20f883e', 'DWI NOVIANDARU, S. Tr. Kom', 'Penata Layanan Operasional', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '85702066899', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(305, '199603252020122003', 'e10adc3949ba59abbe56e057f20f883e', 'NADYA MAULANI MELYANA, A.Md. A.P', 'Pengelola Perkara', 'Pengatur Tk.I (II/d)', 'Pengadilan Negeri Yogyakarta', '88806292633', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(306, '199911232024052001', 'e10adc3949ba59abbe56e057f20f883e', 'DINA TRI LESTARI, SH', 'Analis Perkara Peradilan', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '81297433256', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(307, '200010122024052002', 'e10adc3949ba59abbe56e057f20f883e', 'PUTRI AZZAHRA, SH', 'Analis Perkara Peradilan', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '85322596883', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(308, '200007192024051002', 'e10adc3949ba59abbe56e057f20f883e', 'RYAN ADE SAPUTRO, SH', 'Analis Perkara Peradilan', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '85951287035', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(309, '197212161993031001', 'e10adc3949ba59abbe56e057f20f883e', 'MOH. RUSDIANTO', 'Teknisi Sarana dan Prasarana-Umum', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '81329432020', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(310, '197806192014081004', 'e10adc3949ba59abbe56e057f20f883e', 'NINDYA YOSDALU PUTRA', 'Pengadministrasi Perkantoran', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '81904032504', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(311, '199904292022032021', 'e10adc3949ba59abbe56e057f20f883e', 'INDAH MELINDA, A.Md.A.B.', 'Pengelola Perkara', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '85817300752', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(312, '199412122022032011', 'e10adc3949ba59abbe56e057f20f883e', 'TESA MONICA BR GULTOM, A.Md', 'Pengelola Data dan Informasi', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '81262368209', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(313, '199112192022032006', 'e10adc3949ba59abbe56e057f20f883e', 'SUSI SUSANTI SINAGA, A.Md', 'Pengelola Data dan Informasi', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '81396211674', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(314, '200108022025062018', 'e10adc3949ba59abbe56e057f20f883e', 'JESSICA IRENE NADEAK, SH', 'Analis Perkara Peradilan', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '895613412475', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(315, '199310022025062003', 'e10adc3949ba59abbe56e057f20f883e', 'FRANCISCA WESTRI INDASARI, SH', 'Analis Perkara Peradilan', 'Penata Muda (III/a)', 'Pengadilan Negeri Yogyakarta', '81332627344', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(316, '199703042025062014', 'e10adc3949ba59abbe56e057f20f883e', 'ANIS HANIFAH, A.Md.Kom', 'Pengelola Data dan Informasi', 'Pengatur (II/c)', 'Pengadilan Negeri Yogyakarta', '83840989004', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(317, '1471110904700001', 'e10adc3949ba59abbe56e057f20f883e', 'HERI PURNOMO, S.Si.,SH.,MH', 'HAKIM AD HOC PHI', '', 'Pengadilan Negeri Yogyakarta', '81365421908', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(318, '3471085612690001', 'e10adc3949ba59abbe56e057f20f883e', 'SITI UMI AKHIROKH, SH.,MH', 'HAKIM AD HOC PHI', '', 'Pengadilan Negeri Yogyakarta', '81328388544', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(319, '3216072404760003', 'e10adc3949ba59abbe56e057f20f883e', 'AJI, S.H.', 'HAKIM AD HOC PHI', '', 'Pengadilan Negeri Yogyakarta', '8176364078', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(320, '7106096210740001', 'e10adc3949ba59abbe56e057f20f883e', 'MAYA RIESKE J RUMAMBI, SH.,MH', 'HAKIM AD HOC PHI', '', 'Pengadilan Negeri Yogyakarta', '8989960298', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(321, '3578080611690005', 'e10adc3949ba59abbe56e057f20f883e', 'SOEBEKTI, S.H.', 'HAKIM AD HOC TIPIKOR', '', 'Pengadilan Negeri Yogyakarta', '81217812187', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(322, '1271071003660004', 'e10adc3949ba59abbe56e057f20f883e', 'ELIAS HAMONANGAN, SE.,SH.,MH', 'HAKIM AD HOC TIPIKOR', '', 'Pengadilan Negeri Yogyakarta', '85250038183', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(323, '3401070601700001', 'e10adc3949ba59abbe56e057f20f883e', 'WARSONO, SH.,MH', 'HAKIM AD HOC TIPIKOR', '', 'Pengadilan Negeri Yogyakarta', '87829027477', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(324, '3322195308730001', 'e10adc3949ba59abbe56e057f20f883e', 'ATUN BUDI ASTUTI, SH', 'HAKIM AD HOC TIPIKOR', '', 'Pengadilan Negeri Yogyakarta', '82257588439', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(325, '3217022704690001', 'e10adc3949ba59abbe56e057f20f883e', 'KUSMAT TIRTA SASMITA, SH', 'HAKIM AD HOC TIPIKOR', '', 'Pengadilan Negeri Yogyakarta', '859115520810', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(326, '3313092802720002', 'e10adc3949ba59abbe56e057f20f883e', 'YULIUS EKA SETIAWAN, SH.,MH', 'HAKIM AD HOC TIPIKOR', '', 'Pengadilan Negeri Yogyakarta', '8122582023', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(327, '198107302025212017', 'e10adc3949ba59abbe56e057f20f883e', 'DIAH SUKORINI,SH', 'Penata Layanan Operasional- Umum Keu', 'IX', 'Pengadilan Negeri Yogyakarta', '89648738981', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(328, '198708282025211042', 'e10adc3949ba59abbe56e057f20f883e', 'FAHMI HIDAYAT, SH', 'Penata Layanan Operasional- PTIP', 'IX', 'Pengadilan Negeri Yogyakarta', '82231227733', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(329, '198412272025211029', 'e10adc3949ba59abbe56e057f20f883e', 'DONNY SURIPTO', 'Operator Layanan Operasional-Perdata', 'V', 'Pengadilan Negeri Yogyakarta', '81391575570', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(330, '197312172025211016', 'e10adc3949ba59abbe56e057f20f883e', 'TUNJUNG SULAKSANA P', 'Operator Layanan Operasional-Tipikor', 'V', 'Pengadilan Negeri Yogyakarta', '895415121366', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(331, '198701202025211030', 'e10adc3949ba59abbe56e057f20f883e', 'KEMAS INDARTO', 'Operator Layanan Operasional-Pidana', 'V', 'Pengadilan Negeri Yogyakarta', '87782043871', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(332, '197308302025211014', 'e10adc3949ba59abbe56e057f20f883e', 'WIRID WINOTO', 'Pengadministrasi Perkantoran-Umum Keu', 'V', 'Pengadilan Negeri Yogyakarta', '87738213999', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(333, '198508312025211035', 'e10adc3949ba59abbe56e057f20f883e', 'ARIF PRIHENDARTO', 'Pengadministrasi Perkantoran-PTIP', 'V', 'Pengadilan Negeri Yogyakarta', '88233204286', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(334, '196906272025212006', 'e10adc3949ba59abbe56e057f20f883e', 'BARIYAH', 'Operator Layanan Operasional-Umum Keu', 'V', 'Pengadilan Negeri Yogyakarta', '85105011453', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(335, '197305292025211010', 'e10adc3949ba59abbe56e057f20f883e', 'BUDI PRASETYO', 'Operator Layanan Operasional-Hukum', 'V', 'Pengadilan Negeri Yogyakarta', '8813849841', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(336, '197511092025211023', 'e10adc3949ba59abbe56e057f20f883e', 'ANDIK SULISTYO', 'Pengadministrasi Perkantoran- Kepeg Ortala', 'V', 'Pengadilan Negeri Yogyakarta', '817278128', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(337, '197608072025211028', 'e10adc3949ba59abbe56e057f20f883e', 'DENY DWI SUSILO', 'Operator Layanan Operasional-HAM', 'V', 'Pengadilan Negeri Yogyakarta', '81227872811', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(338, '198902142025211034', 'e10adc3949ba59abbe56e057f20f883e', 'PEBRIANTO', 'Operator Layanan Operasional-PTIP', 'V', 'Pengadilan Negeri Yogyakarta', '81328658736', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(339, '198107172025211034', 'e10adc3949ba59abbe56e057f20f883e', 'BAMBANG NUGROHO A MARTANTYO', 'Operator Layanan Operasional-PHI', 'V', 'Pengadilan Negeri Yogyakarta', '82140187130', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(340, '198307232025211038', 'e10adc3949ba59abbe56e057f20f883e', 'DWI RIYANTO', 'Operator Layanan Operasional-Umum Keu', 'V', 'Pengadilan Negeri Yogyakarta', '882006091009', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(341, '198109292025211037', 'e10adc3949ba59abbe56e057f20f883e', 'SUDARMADI', 'Pengelola Umum Operasional-Umum Keu', 'I', 'Pengadilan Negeri Yogyakarta', '85642404369', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(342, '197801252025211018', 'e10adc3949ba59abbe56e057f20f883e', 'NGADIYO', 'Pengelola Umum Operasional-Kepeg Ortala', 'I', 'Pengadilan Negeri Yogyakarta', '81226081887', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(343, '198412312025211083', 'e10adc3949ba59abbe56e057f20f883e', 'EDI SISWANTO', 'Pengelola Umum Operasional-Hukum', 'I', 'Pengadilan Negeri Yogyakarta', '85643573684', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(344, '123455', 'e10adc3949ba59abbe56e057f20f883e', 'ANGGA PERDANA PUTRA, SH', 'Penata Layanan Operasional- Pidana', 'Paruh Waktu', 'Pengadilan Negeri Yogyakarta', '85729199282', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL),
(345, '123456', 'e10adc3949ba59abbe56e057f20f883e', 'NOVIA IKE DEVITA, S.Kom.', 'PRAMUBHAKTI', '', 'Pengadilan Negeri Yogyakarta', '', 'user', 0, 12, 6, 0, 14, '2026-01-29 02:58:38', 'aktif', NULL);

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
  MODIFY `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `tbl_setting_instansi`
--
ALTER TABLE `tbl_setting_instansi`
  MODIFY `id_setting` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=346;

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
