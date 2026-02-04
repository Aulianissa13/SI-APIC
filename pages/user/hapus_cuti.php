<?php
session_start();

// 1. SMART LOCATOR DATABASE (Agar $koneksi tidak error lagi)
$paths = [
    '../../config/database.php', 
    '../config/database.php', 
    'config/database.php'
];
$koneksi = null;
foreach ($paths as $path) {
    if (file_exists($path)) { include $path; break; }
}
if (!$koneksi) { die("Error Fatal: Database tidak ditemukan. Cek path file config."); }

$id_pengajuan  = $_GET['id'];
$id_user_login = $_SESSION['id_user']; 

$cek = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_pengajuan='$id_pengajuan' AND id_user='$id_user_login'");
$data = mysqli_fetch_array($cek);

if ($data) {
    $status = strtolower($data['status']);
    
    if ($status == 'diajukan' || $status == 'menunggu') {
        $id_jenis = $data['id_jenis'];
        $lama     = $data['lama_hari'];
        
        $q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user_login'");
        $u      = mysqli_fetch_array($q_user);
        
        $kembali_n1 = (int) $data['dipotong_n1']; 
        $kembali_n  = (int) $data['dipotong_n'];

        if ($id_jenis == '1') { 
            $n_baru  = $u['sisa_cuti_n'] + $kembali_n;
            $n1_baru = $u['sisa_cuti_n1'] + $kembali_n1;
            
            $query_restore = "UPDATE users SET 
                              sisa_cuti_n = '$n_baru', 
                              sisa_cuti_n1 = '$n1_baru' 
                              WHERE id_user='$id_user_login'";
            
            mysqli_query($koneksi, $query_restore);

        } else if ($id_jenis == '2') { 
            $sakit_baru = $u['kuota_cuti_sakit'] + $lama;
            mysqli_query($koneksi, "UPDATE users SET kuota_cuti_sakit='$sakit_baru' WHERE id_user='$id_user_login'");
        }

        $hapus = mysqli_query($koneksi, "DELETE FROM pengajuan_cuti WHERE id_pengajuan='$id_pengajuan'");

        if ($hapus) {
            $_SESSION['swal'] = [
                'icon' => 'success',
                'title' => 'Dibatalkan',
                'text' => 'Pengajuan cuti berhasil dihapus dan kuota dikembalikan.'
            ];
        } else {
            $_SESSION['swal'] = [
                'icon' => 'error',
                'title' => 'Gagal',
                'text' => 'Terjadi kesalahan database saat menghapus.'
            ];
        }

    } else {
        $_SESSION['swal'] = [
            'icon' => 'warning',
            'title' => 'Gagal Hapus',
            'text' => 'Data yang sudah diproses (Disetujui/Ditolak) tidak dapat dihapus.'
        ];
    }
} else {
    $_SESSION['swal'] = [
        'icon' => 'error',
        'title' => 'Error',
        'text' => 'Data tidak ditemukan atau bukan milik Anda.'
    ];
}

header("Location: ../../index.php?page=riwayat_cuti");
exit;
?>