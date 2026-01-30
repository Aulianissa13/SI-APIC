<?php
session_start();

// 1. SMART LOCATOR DATABASE
$paths = [
    '../../config/database.php', 
    '../config/database.php', 
    'config/database.php'
];
$koneksi = null;
foreach ($paths as $path) {
    if (file_exists($path)) { include $path; break; }
}
if (!$koneksi) { die("Error: Database tidak ditemukan."); }

// Cek sesi login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --- TANGKAP DATA ---
    $id_user       = $_POST['id_user'];
    $id_jenis      = $_POST['id_jenis'];
    $tgl_mulai     = $_POST['tgl_mulai'];
    $tgl_selesai   = $_POST['tgl_selesai'];
    $lama_hari     = (int) $_POST['lama_hari']; 
    $alasan        = $_POST['alasan'];
    $alamat_cuti   = $_POST['alamat_cuti'];
    
    // Logic Atasan
    $id_atasan = 0;
    if (isset($_POST['id_atasan']) && !empty($_POST['id_atasan'])) {
        $id_atasan = $_POST['id_atasan'];
    } elseif (isset($_POST['id_pejabat']) && !empty($_POST['id_pejabat'])) {
        $id_atasan = $_POST['id_pejabat'];
    }

    $no_surat      = $_POST['no_surat'];
    $masa_kerja    = $_POST['masa_kerja'];
    $tgl_pengajuan = date('Y-m-d'); 
    $status        = 'diajukan'; 

    // ==========================================================
    // 1. CEK BENTROK
    // ==========================================================
    $cek_bentrok = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti 
                   WHERE id_user = '$id_user' 
                   AND status != 'Ditolak' 
                   AND (
                       (tgl_mulai <= '$tgl_selesai' AND tgl_selesai >= '$tgl_mulai')
                   )");

    if (mysqli_num_rows($cek_bentrok) > 0) {
        // --- JIKA GAGAL (BENTROK) ---
        
        // 1. Simpan Pesan Error
        $_SESSION['swal'] = [
            'icon'  => 'error',
            'title' => 'Tanggal Bentrok!',
            'text'  => 'Anda sudah mengajukan cuti di tanggal ini.'
        ];

        // 2. KEMBALIKAN KE FORM ASAL (Agar background tetap form)
        // $_SERVER['HTTP_REFERER'] otomatis kembali ke halaman sebelumnya
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit(); 
    }

    // ==========================================================
    // 2. UPDATE KUOTA & SIMPAN
    // ==========================================================

    // ... (Logic hitung kuota sama seperti sebelumnya) ...
    $q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
    $user   = mysqli_fetch_array($q_user);

    $q_jenis = mysqli_query($koneksi, "SELECT * FROM jenis_cuti WHERE id_jenis='$id_jenis'");
    $jenis   = mysqli_fetch_array($q_jenis);
    $nama_jenis = strtolower($jenis['nama_jenis']);

    $sisa_n_awal  = $user['sisa_cuti_n'];
    $sisa_n1_awal = $user['sisa_cuti_n1'];
    $dipotong_n1  = 0;
    $dipotong_n   = 0;

    if (strpos($nama_jenis, 'tahunan') !== false) {
        if ($lama_hari <= $sisa_n1_awal) {
            $dipotong_n1 = $lama_hari;
        } else {
            $dipotong_n1 = $sisa_n1_awal; 
            $dipotong_n  = $lama_hari - $sisa_n1_awal; 
        }
        $sisa_n1_baru = $sisa_n1_awal - $dipotong_n1;
        $sisa_n_baru  = $sisa_n_awal - $dipotong_n;
        if ($sisa_n_baru < 0) $sisa_n_baru = 0;
        
        mysqli_query($koneksi, "UPDATE users SET sisa_cuti_n='$sisa_n_baru', sisa_cuti_n1='$sisa_n1_baru' WHERE id_user='$id_user'");
    } 
    else if (strpos($nama_jenis, 'sakit') !== false) {
        $sisa_sakit_baru = $user['kuota_cuti_sakit'] - $lama_hari;
        if ($sisa_sakit_baru < 0) $sisa_sakit_baru = 0;
        mysqli_query($koneksi, "UPDATE users SET kuota_cuti_sakit='$sisa_sakit_baru' WHERE id_user='$id_user'");
    }

    $query_insert = "INSERT INTO pengajuan_cuti 
        (nomor_surat, id_user, id_jenis, tgl_mulai, tgl_selesai, lama_hari, 
         alasan, alamat_cuti, status, tgl_pengajuan, id_atasan, masa_kerja,
         sisa_cuti_n, sisa_cuti_n1, dipotong_n, dipotong_n1) 
        VALUES 
        ('$no_surat', '$id_user', '$id_jenis', '$tgl_mulai', '$tgl_selesai', '$lama_hari', 
         '$alasan', '$alamat_cuti', '$status', '$tgl_pengajuan', '$id_atasan', '$masa_kerja',
         '$sisa_n_awal', '$sisa_n1_awal', '$dipotong_n', '$dipotong_n1')";

    if (mysqli_query($koneksi, $query_insert)) {
        // --- JIKA SUKSES ---
        $_SESSION['swal'] = [
            'icon'  => 'success',
            'title' => 'Berhasil!',
            'text'  => 'Pengajuan cuti berhasil dikirim.'
        ];
        // Pilih mau dilempar kemana:
        // Opsi A: Ke Halaman Riwayat Cuti (Standar aplikasi umumnya)
        header("Location: ../../index.php?page=riwayat_cuti");
        
        // Opsi B: Tetap di Form (Kalau mau input lagi)
        // header("Location: " . $_SERVER['HTTP_REFERER']); 
    } else {
        // --- JIKA ERROR DATABASE ---
        $_SESSION['swal'] = [
            'icon'  => 'error',
            'title' => 'Gagal!',
            'text'  => 'Database Error: ' . mysqli_error($koneksi)
        ];
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }
    exit();
}
?>