<?php
session_start();
include '../../config/database.php';

// Cek paramter
if(!isset($_GET['act']) || !isset($_GET['id'])){
    header("Location: ../../index.php?page=validasi_cuti");
    exit;
}

$act = $_GET['act']; // 'terima' atau 'tolak'
$id_pengajuan = $_GET['id'];

if ($act == 'terima') {
    // --- LOGIKA TERIMA ---
    // Ubah status jadi 'Disetujui'
    $query = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET status='Disetujui' WHERE id_pengajuan='$id_pengajuan'");

    if ($query) {
        $_SESSION['alert'] = ['type' => 'success', 'text' => 'Pengajuan berhasil DISETUJUI.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Gagal menyetujui: ' . mysqli_error($koneksi)];
    }

} elseif ($act == 'tolak') {
    // --- LOGIKA TOLAK (REFUND KUOTA) ---
    
    // 1. Ambil data pengajuan dulu (siapa user-nya, berapa lama cutinya, jenis cutinya)
    $cek_data = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_pengajuan='$id_pengajuan'");
    $data_cuti = mysqli_fetch_array($cek_data);

    // Cek jika data ada
    if($data_cuti) {
        $id_user   = $data_cuti['id_user'];
        $id_jenis  = $data_cuti['id_jenis'];
        $lama_hari = $data_cuti['lama_hari'];

        // 2. Kembalikan Kuota ke User (Refund)
        $update_user = false;

        if ($id_jenis == '1') { // Cuti Tahunan
            // Default refund ke sisa_cuti_n (Tahun Berjalan)
            $update_user = mysqli_query($koneksi, "UPDATE users SET sisa_cuti_n = sisa_cuti_n + $lama_hari WHERE id_user='$id_user'");
        } elseif ($id_jenis == '2') { // Cuti Sakit
            // Refund ke kuota_cuti_sakit
            $update_user = mysqli_query($koneksi, "UPDATE users SET kuota_cuti_sakit = kuota_cuti_sakit + $lama_hari WHERE id_user='$id_user'");
        } else {
            // Jenis lain (tidak potong kuota), set true aja
            $update_user = true;
        }

        // 3. Jika Refund Berhasil, Ubah Status jadi 'Ditolak'
        if ($update_user) {
            $query_status = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET status='Ditolak' WHERE id_pengajuan='$id_pengajuan'");
            $_SESSION['alert'] = ['type' => 'warning', 'text' => 'Pengajuan DITOLAK. Kuota cuti pegawai telah dikembalikan.'];
        } else {
            $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Gagal refund kuota: ' . mysqli_error($koneksi)];
        }
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Data pengajuan tidak ditemukan.'];
    }
}

// Redirect kembali ke halaman Validasi
header("Location: ../../index.php?page=validasi_cuti");
exit;
?>