<?php
session_start();
include '../config/database.php';

if(isset($_POST['simpan_izin'])) {
    
    // 1. TENTUKAN PEMOHON
    // Jika Admin yang input & memilih pegawai lain
    if(isset($_POST['id_user_pemohon']) && !empty($_POST['id_user_pemohon'])) {
        $id_pemohon = $_POST['id_user_pemohon']; 
    } else {
        // Jika User yang input sendiri
        $id_pemohon = $_SESSION['id_user']; 
    }

    // 2. AMBIL DATA FORM
    $id_atasan   = $_POST['id_atasan']; // ID Atasan yang dipilih
    $tgl_izin    = $_POST['tgl_izin'];
    $jam_keluar  = $_POST['jam_keluar'];
    $jam_kembali = $_POST['jam_kembali'];
    $keperluan   = mysqli_real_escape_string($koneksi, $_POST['keperluan']);

    // 3. SIMPAN KE DATABASE
    $query = "INSERT INTO izin_keluar (id_user, id_atasan, tgl_izin, jam_keluar, jam_kembali, keperluan, status) 
              VALUES ('$id_pemohon', '$id_atasan', '$tgl_izin', '$jam_keluar', '$jam_kembali', '$keperluan', 'Disetujui')";

    if(mysqli_query($koneksi, $query)) {
        $_SESSION['swal'] = [
            'icon' => 'success',
            'title' => 'Berhasil Disimpan',
            'text' => 'Data izin berhasil disimpan. Silakan cetak melalui tabel riwayat.'
        ];
    } else {
        $_SESSION['swal'] = [
            'icon' => 'error',
            'title' => 'Gagal',
            'text' => 'Error: ' . mysqli_error($koneksi)
        ];
    }

    // 4. KEMBALIKAN KE HALAMAN ASAL
    header("Location: ../index.php?page=izin_keluar");
    exit();
}
?>