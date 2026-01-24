<?php
session_start();
include '../../config/database.php'; // Sesuaikan path koneksi Anda

// Cek sesi login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. Tangkap Data dari Form
    $id_user       = $_POST['id_user'];
    $id_jenis      = $_POST['id_jenis'];
    $tgl_mulai     = $_POST['tgl_mulai'];
    $tgl_selesai   = $_POST['tgl_selesai'];
    $lama_hari     = (int) $_POST['lama_hari']; // Pastikan jadi angka integer
    $alasan        = $_POST['alasan'];
    $alamat_cuti   = $_POST['alamat_cuti'];
    $id_atasan     = $_POST['id_atasan']; // Pastikan ini menangkap id_pejabat jika formnya menggunakan nama 'id_atasan'
    $no_surat      = $_POST['no_surat'];
    $masa_kerja    = $_POST['masa_kerja'];
    $tgl_pengajuan = date('Y-m-d');
    $status        = 'diajukan'; // Status awal

    // 2. Ambil Data User Terbaru (Untuk cek stok cuti sebelum dipotong)
    $q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
    $user   = mysqli_fetch_array($q_user);

    // Ambil nama jenis cuti untuk logika pemotongan
    $q_jenis = mysqli_query($koneksi, "SELECT * FROM jenis_cuti WHERE id_jenis='$id_jenis'");
    $jenis   = mysqli_fetch_array($q_jenis);
    $nama_jenis = strtolower($jenis['nama_jenis']);

    // Siapkan variabel awal
    $sisa_n_awal  = $user['sisa_cuti_n'];
    $sisa_n1_awal = $user['sisa_cuti_n1'];
    
    // Variabel untuk menyimpan rincian potongan (PENTING untuk Restore/Batal nanti)
    $dipotong_n1 = 0;
    $dipotong_n  = 0;

    // ==========================================================
    // LOGIKA UTAMA: PERHITUNGAN MATEMATIKA (UPDATE)
    // ==========================================================
    
    // KASUS A: CUTI TAHUNAN (Gunakan Logika FIFO & Simpan Rincian)
    if (strpos($nama_jenis, 'tahunan') !== false) {
        
        if ($lama_hari <= $sisa_n1_awal) {
            // Skenario 1: Cukup diambil dari N-1
            $dipotong_n1 = $lama_hari;
            $dipotong_n  = 0;
        } else {
            // Skenario 2: N-1 habis, sisanya ambil dari N
            $dipotong_n1 = $sisa_n1_awal; // Habiskan N-1
            $dipotong_n  = $lama_hari - $sisa_n1_awal; // Sisanya dari N
        }

        // Hitung sisa baru berdasarkan rincian di atas
        $sisa_n1_baru = $sisa_n1_awal - $dipotong_n1;
        $sisa_n_baru  = $sisa_n_awal - $dipotong_n;

        // Validasi agar tidak minus (Safety net)
        if ($sisa_n_baru < 0) $sisa_n_baru = 0;

        // Query Update Stok Tahunan User
        // Langsung potong saat diajukan (sesuai logika kode asli Anda)
        $update_user = "UPDATE users SET 
                        sisa_cuti_n = '$sisa_n_baru', 
                        sisa_cuti_n1 = '$sisa_n1_baru' 
                        WHERE id_user = '$id_user'";
        mysqli_query($koneksi, $update_user);
    } 
    
    // KASUS B: CUTI SAKIT
    else if (strpos($nama_jenis, 'sakit') !== false) {
        $sisa_sakit_baru = $user['kuota_cuti_sakit'] - $lama_hari;
        if ($sisa_sakit_baru < 0) $sisa_sakit_baru = 0;

        // Query Update Stok Sakit User
        $update_user = "UPDATE users SET kuota_cuti_sakit = '$sisa_sakit_baru' WHERE id_user = '$id_user'";
        mysqli_query($koneksi, $update_user);
    }

    // ==========================================================
    // INSERT DATA KE TABEL PENGAJUAN (UPDATE)
    // ==========================================================
    
    // Kita simpan:
    // 1. Snapshot saldo AWAL (sisa_cuti_n, sisa_cuti_n1) -> untuk cetak surat
    // 2. Rincian POTONGAN (dipotong_n, dipotong_n1) -> untuk logika batal/restore nanti
    
    $query_insert = "INSERT INTO pengajuan_cuti 
        (nomor_surat, id_user, id_jenis, tgl_mulai, tgl_selesai, lama_hari, 
         alasan, alamat_cuti, status, tgl_pengajuan, id_atasan, masa_kerja,
         sisa_cuti_n, sisa_cuti_n1, dipotong_n, dipotong_n1) 
        VALUES 
        ('$no_surat', '$id_user', '$id_jenis', '$tgl_mulai', '$tgl_selesai', '$lama_hari', 
         '$alasan', '$alamat_cuti', '$status', '$tgl_pengajuan', '$id_atasan', '$masa_kerja',
         '$sisa_n_awal', '$sisa_n1_awal', '$dipotong_n', '$dipotong_n1')";

    if (mysqli_query($koneksi, $query_insert)) {
        // Berhasil
        $_SESSION['alert'] = [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Pengajuan cuti Anda telah dikirim.'
        ];
    } else {
        // Gagal
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'Gagal!',
            'text' => 'Terjadi kesalahan sistem: ' . mysqli_error($koneksi)
        ];
    }

    // Kembali ke index
    header("Location: ../../index.php");
    exit();
}
?>