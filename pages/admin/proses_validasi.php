<?php
// --- FILE: pages/admin/proses_validasi.php ---

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 1. SMART LOCATOR DATABASE
$kemungkinan_path = ['../../config/database.php', '../config/database.php', 'config/database.php', '../database.php'];
$db_found = false;
foreach ($kemungkinan_path as $path) {
    if (file_exists($path)) { include_once $path; $db_found = true; break; }
}
if (!$db_found) { die("Error: File config/database.php tidak ditemukan."); }

// 2. LOGIC PROSES
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    
    $id_pengajuan = intval($_GET['id']); 
    $aksi         = $_GET['aksi']; 

    // Ambil data
    $query_cek = "SELECT pengajuan_cuti.*, jenis_cuti.nama_jenis 
                  FROM pengajuan_cuti 
                  JOIN jenis_cuti ON pengajuan_cuti.id_jenis = jenis_cuti.id_jenis 
                  WHERE id_pengajuan='$id_pengajuan'";
    
    $cek_data = mysqli_query($koneksi, $query_cek);
    
    if(mysqli_num_rows($cek_data) > 0) {
        $data       = mysqli_fetch_array($cek_data);
        $id_user    = $data['id_user'];
        $status_now = $data['status'];
        
        $dipotong_n  = intval($data['dipotong_n']);
        $dipotong_n1 = intval($data['dipotong_n1']);
        $lama        = intval($data['lama_hari']);

        // Cek Double Process
        if($status_now == 'Disetujui' || $status_now == 'Ditolak') {
            $_SESSION['swal'] = [
                'icon'  => 'warning',
                'title' => 'Sudah Diproses',
                'text'  => 'Data ini sudah diverifikasi sebelumnya.'
            ];
            header("Location: ../../index.php?page=validasi_cuti");
            exit();
        }

        // --- SKENARIO 1: SETUJU ---
        if ($aksi == 'setuju') {
            $up_status = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET status='Disetujui' WHERE id_pengajuan='$id_pengajuan'");
            
            if($up_status) {
                $_SESSION['swal'] = [
                    'icon'  => 'success',
                    'title' => 'Disetujui!',
                    'text'  => 'Pengajuan cuti berhasil disetujui.'
                ];
            } else {
                $_SESSION['swal'] = [
                    'icon'  => 'error',
                    'title' => 'Gagal',
                    'text'  => 'Terjadi kesalahan database.'
                ];
            }

        // --- SKENARIO 2: TOLAK (REFUND) ---
        } elseif ($aksi == 'tolak') {
            
            $up_tolak = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET status='Ditolak' WHERE id_pengajuan='$id_pengajuan'");
            
            $pesan_tambahan = "";

            if (stripos($data['nama_jenis'], 'Tahunan') !== false) {
                mysqli_query($koneksi, "UPDATE users SET sisa_cuti_n = sisa_cuti_n + $dipotong_n, sisa_cuti_n1 = sisa_cuti_n1 + $dipotong_n1 WHERE id_user='$id_user'");
                $pesan_tambahan = "Kuota Cuti Tahunan dikembalikan.";
                
            } elseif (stripos($data['nama_jenis'], 'Sakit') !== false) { 
                mysqli_query($koneksi, "UPDATE users SET kuota_cuti_sakit = kuota_cuti_sakit + $lama WHERE id_user='$id_user'");
                $pesan_tambahan = "Kuota Cuti Sakit dikembalikan.";
            }

            $_SESSION['swal'] = [
                'icon'  => 'info', // Pakai icon info atau warning untuk penolakan
                'title' => 'Ditolak',
                'text'  => 'Pengajuan ditolak. ' . $pesan_tambahan
            ];
        }
    }
    // Redirect kembali ke halaman utama
    header("Location: ../../index.php?page=validasi_cuti");
    exit(); 
}
?>