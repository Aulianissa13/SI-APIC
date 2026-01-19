<?php
session_start();
include '../../config/database.php';

$id_pengajuan = $_GET['id'];
$id_user_login = $_SESSION['id_user']; // Validasi keamanan: hanya user pemilik yg bisa hapus

// 1. Ambil data dulu (untuk cek status & kuota yg harus dikembalikan)
$cek = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_pengajuan='$id_pengajuan' AND id_user='$id_user_login'");
$data = mysqli_fetch_array($cek);

if ($data) {
    // Validasi: Hanya status 'Diajukan' atau 'Menunggu' yang boleh dihapus
    $status = strtolower($data['status']);
    
    if ($status == 'diajukan' || $status == 'menunggu') {
        
        // --- PROSES PENGEMBALIAN KUOTA (REFUND) ---
        // Karena saat "Proses Cuti" kuota sudah dipotong, maka saat dihapus kuota harus balik.
        $id_jenis = $data['id_jenis'];
        $lama     = $data['lama_hari'];
        
        // Ambil data user sekarang
        $q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user_login'");
        $u      = mysqli_fetch_array($q_user);
        
        $n_baru   = $u['sisa_cuti_n'];
        $n1_baru  = $u['sisa_cuti_n1'];
        $sakit_baru = $u['kuota_cuti_sakit'];
        
        if ($id_jenis == '1') { // Cuti Tahunan
            // Logika Reverse FIFO agak rumit, sederhananya kita kembalikan ke N dulu
            // Atau sesuaikan dengan logika pemotongan Anda. 
            // Disini saya asumsikan dikembalikan ke Tahun N (Tahun Ini) biar aman/simple
            $n_baru = $u['sisa_cuti_n'] + $lama; 
            
            // Update User
            mysqli_query($koneksi, "UPDATE users SET sisa_cuti_n='$n_baru' WHERE id_user='$id_user_login'");

        } else if ($id_jenis == '2') { // Cuti Sakit
            $sakit_baru = $u['kuota_cuti_sakit'] + $lama;
            mysqli_query($koneksi, "UPDATE users SET kuota_cuti_sakit='$sakit_baru' WHERE id_user='$id_user_login'");
        }

        // --- HAPUS DATA ---
        $hapus = mysqli_query($koneksi, "DELETE FROM pengajuan_cuti WHERE id_pengajuan='$id_pengajuan'");

        if ($hapus) {
            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Dibatalkan',
                'text' => 'Pengajuan cuti berhasil dihapus dan kuota dikembalikan.'
            ];
        } else {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Gagal',
                'text' => 'Terjadi kesalahan database.'
            ];
        }

    } else {
        // Jika status sudah disetujui/ditolak, tidak boleh hapus
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