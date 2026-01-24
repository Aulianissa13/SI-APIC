<?php
session_start();
include '../../config/database.php';

$id_pengajuan  = $_GET['id'];
$id_user_login = $_SESSION['id_user']; // Validasi keamanan: hanya user pemilik yg bisa hapus

// 1. Ambil data dulu (untuk cek status & kuota yg harus dikembalikan)
// Pastikan query SELECT mengambil semua kolom termasuk dipotong_n dan dipotong_n1
$cek = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_pengajuan='$id_pengajuan' AND id_user='$id_user_login'");
$data = mysqli_fetch_array($cek);

if ($data) {
    // Validasi: Hanya status 'Diajukan' atau 'Menunggu' yang boleh dihapus
    $status = strtolower($data['status']);
    
    if ($status == 'diajukan' || $status == 'menunggu') {
        
        // --- PROSES PENGEMBALIAN KUOTA (REFUND) YANG BENAR ---
        $id_jenis = $data['id_jenis'];
        $lama     = $data['lama_hari'];
        
        // Ambil data user sekarang untuk diupdate
        $q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user_login'");
        $u      = mysqli_fetch_array($q_user);
        
        // Ambil riwayat potongan (Berapa yang diambil dari N-1, berapa dari N)
        // Menggunakan (int) agar jika datanya null/kosong dianggap 0
        $kembali_n1 = (int) $data['dipotong_n1']; 
        $kembali_n  = (int) $data['dipotong_n'];

        if ($id_jenis == '1') { // Cuti Tahunan
            
            // Hitung saldo baru (Kembalikan ke pos masing-masing)
            $n_baru  = $u['sisa_cuti_n'] + $kembali_n;
            $n1_baru = $u['sisa_cuti_n1'] + $kembali_n1;
            
            // Update User (Update kedua kolom: sisa_cuti_n DAN sisa_cuti_n1)
            $query_restore = "UPDATE users SET 
                              sisa_cuti_n = '$n_baru', 
                              sisa_cuti_n1 = '$n1_baru' 
                              WHERE id_user='$id_user_login'";
            
            mysqli_query($koneksi, $query_restore);

        } else if ($id_jenis == '2') { // Cuti Sakit
            // Kalau sakit, kembalikan ke kuota sakit saja
            $sakit_baru = $u['kuota_cuti_sakit'] + $lama;
            mysqli_query($koneksi, "UPDATE users SET kuota_cuti_sakit='$sakit_baru' WHERE id_user='$id_user_login'");
        }

        // Hapus data pengajuan setelah saldo dikembalikan
        $hapus = mysqli_query($koneksi, "DELETE FROM pengajuan_cuti WHERE id_pengajuan='$id_pengajuan'");

        if ($hapus) {
            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Dibatalkan',
                'text' => 'Pengajuan cuti berhasil dihapus dan kuota dikembalikan ke asalnya.'
            ];
        } else {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Gagal',
                'text' => 'Terjadi kesalahan database saat menghapus.'
            ];
        }

    } else {
        $_SESSION['alert'] = [
            'icon' => 'warning',
            'title' => 'Ditolak',
            'text' => 'Data yang sudah diproses tidak dapat dihapus.'
        ];
    }
} else {
    $_SESSION['alert'] = [
        'icon' => 'error',
        'title' => 'Error',
        'text' => 'Data tidak ditemukan.'
    ];
}

// Balik ke halaman riwayat
header("Location: ../../index.php?page=riwayat_cuti");
exit;
?>