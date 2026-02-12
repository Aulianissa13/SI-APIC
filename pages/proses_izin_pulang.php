<?php
// pages/proses_izin_pulang.php

// 1. Mulai Session & Koneksi
session_start();
include '../config/database.php';
/** @var mysqli $koneksi */

// 2. Cek apakah tombol simpan ditekan
if (isset($_POST['simpan_izin_pulang'])) {

    // Ambil data dari form dan amankan string (Sanitasi)
    $id_user    = mysqli_real_escape_string($koneksi, $_POST['id_user_pemohon']);
    $id_atasan  = mysqli_real_escape_string($koneksi, $_POST['id_atasan']);
    $tgl_izin   = mysqli_real_escape_string($koneksi, $_POST['tgl_izin']);
    $jam_pulang = mysqli_real_escape_string($koneksi, $_POST['jam_pulang']);
    $keperluan  = mysqli_real_escape_string($koneksi, $_POST['keperluan']);
    
    // Set status default
    // 0 = Menunggu Persetujuan
    // 1 = Disetujui
    // 2 = Ditolak
    // Jika Admin yang input, mungkin mau langsung disetujui? (Opsional, di bawah saya set default 0 semua)
    $status = 0; 

    // Validasi sederhana: Pastikan ID Atasan tidak kosong
    if (empty($id_atasan)) {
        echo "<script>alert('Harap pilih atasan valid dari daftar!'); window.history.back();</script>";
        exit;
    }

    // 3. Query Insert Data
    // Asumsi nama tabel: izin_pulang
    // Kolom: id_izin_pulang (Auto Increment), id_user, id_atasan, tgl_izin, jam_pulang, keperluan, status, tgl_input
    $query = "INSERT INTO izin_pulang (id_user, id_atasan, tgl_izin, jam_pulang, keperluan, status, tgl_input) 
              VALUES ('$id_user', '$id_atasan', '$tgl_izin', '$jam_pulang', '$keperluan', '$status', NOW())";

    if (mysqli_query($koneksi, $query)) {
        
        // --- LOGIKA PEMISAH ADMIN DAN USER ---
        
        // Cek level user dari session login Anda
        // Sesuaikan 'level' dan 'admin' dengan database Anda
        if (isset($_SESSION['level']) && $_SESSION['level'] == 'admin') {
            
            // A. JIKA ADMIN: Redirect ke halaman Rekap/Admin
            echo "<script>
                alert('Data Izin Pulang Awal berhasil ditambahkan (Mode Admin).');
                document.location.href = '../index.php?page=izin_pulang'; 
            </script>";
            
        } else {
            
            // B. JIKA USER BIASA: Redirect kembali ke form User
            echo "<script>
                alert('Permohonan Izin Pulang Awal berhasil dikirim! Menunggu persetujuan atasan.');
                document.location.href = '../index.php?page=izin_pulang'; 
            </script>";
        }

    } else {
        // Jika Gagal Query
        echo "<script>
            alert('Gagal menyimpan data: " . mysqli_error($koneksi) . "');
            window.history.back();
        </script>";
    }

} else {
    // Jika ada yang coba akses file ini tanpa klik tombol simpan
    header("Location: ../index.php");
}
?>