<?php
// Mengaktifkan session php
session_start();

// Menghubungkan dengan koneksi
include 'config/database.php';

// Menangkap data yang dikirim dari form
$nip = $_POST['nip'];
$password = md5($_POST['password']); // Password di-hash MD5 sesuai database

// Menyeleksi data user dengan nip dan password yang sesuai
$data = mysqli_query($koneksi, "SELECT * FROM users WHERE nip='$nip' AND password='$password'");

// Menghitung jumlah data yang ditemukan
$cek = mysqli_num_rows($data);

if($cek > 0){
    // Ambil datanya
    $row = mysqli_fetch_assoc($data);

    // Set SESSION (Menyimpan data user sementara di browser)
    $_SESSION['status'] = "login";
    $_SESSION['id_user'] = $row['id_user'];
    $_SESSION['nip'] = $row['nip'];
    $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
    $_SESSION['role'] = $row['role']; // Penting untuk membedakan Admin/User
    $_SESSION['unit_kerja'] = $row['unit_kerja'];

    // Alihkan ke halaman index (Dashboard)
    header("location:index.php");
} else {
    // Jika gagal, alihkan kembali ke login dengan pesan error
    header("location:login.php?pesan=gagal");
}
?>