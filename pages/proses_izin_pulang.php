<?php
// pages/proses_izin_pulang.php
session_start();
include '../config/database.php';
/** @var mysqli $koneksi */

if (isset($_POST['simpan_izin_pulang'])) {

    // 1) TENTUKAN PEMOHON
    if (isset($_POST['id_user_pemohon']) && !empty($_POST['id_user_pemohon'])) {
        $id_pemohon = $_POST['id_user_pemohon'];
    } else {
        $id_pemohon = $_SESSION['id_user'];
    }

    // 2) AMBIL DATA FORM
    $id_atasan  = $_POST['id_atasan'] ?? '';
    $tgl_izin   = $_POST['tgl_izin'] ?? '';
    $jam_pulang = $_POST['jam_pulang'] ?? '';
    $keperluan  = mysqli_real_escape_string($koneksi, $_POST['keperluan'] ?? '');

    // 3) VALIDASI
    if (empty($id_atasan) || empty($tgl_izin) || empty($jam_pulang) || empty($keperluan)) {
        $_SESSION['swal'] = [
            'icon'  => 'warning',
            'title' => 'Data belum lengkap',
            'text'  => 'Pastikan semua field sudah diisi dan atasan dipilih dari daftar.'
        ];
        header("Location: ../index.php?page=izin_pulang");
        exit();
    }

    // 4) INSERT (TANPA STATUS)
    $query = "INSERT INTO izin_pulang (id_user, id_atasan, tgl_izin, jam_pulang, keperluan)
              VALUES ('$id_pemohon', '$id_atasan', '$tgl_izin', '$jam_pulang', '$keperluan')";

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['swal'] = [
            'icon'  => 'success',
            'title' => 'Berhasil Disimpan',
            'text'  => 'Data izin berhasil disimpan. Silakan cetak melalui tabel riwayat.'
        ];
    } else {
        $_SESSION['swal'] = [
            'icon'  => 'error',
            'title' => 'Gagal',
            'text'  => 'Error: ' . mysqli_error($koneksi)
        ];
    }

    header("Location: ../index.php?page=izin_pulang");
    exit();
}

header("Location: ../index.php?page=izin_pulang");
exit();
?>
