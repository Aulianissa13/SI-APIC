<?php 
session_start();
include 'config/database.php';

// Menangkap data yang dikirim dari form
$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = $_POST['password'];

// 1. Cek apakah Username ada di database?
// PENTING: Jangan cek password di query SQL (WHERE), cukup username saja
$login = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
$cek = mysqli_num_rows($login);

if($cek > 0){
    // Ambil data user
    $data = mysqli_fetch_assoc($login);
    $password_db = $data['password'];

    // 2. Cek Password (SAPU JAGAT: Support MD5, Hash Baru, dan Text Biasa)
    
    // Cek Hash Modern (Ganti Password Baru)
    $verify_hash = password_verify($password, $password_db);
    
    // Cek MD5 (Password Lama)
    $verify_md5 = (md5($password) == $password_db);
    
    // Cek Plain Text (Data Dummy Awal)
    $verify_plain = ($password == $password_db);

    // Jika SALAH SATU benar, maka Login Sukses
    if($verify_hash || $verify_md5 || $verify_plain) {
        
        // Buat Session
        $_SESSION['username'] = $username;
        $_SESSION['status']   = "login";
        $_SESSION['role']     = $data['role'];
        $_SESSION['id_user']  = $data['id_user'];
        $_SESSION['nama']     = $data['nama_lengkap']; // Tambahan biar enak dipanggil

        // Redirect ke dashboard
        header("location:index.php");
    
    } else {
        // Password Salah
        header("location:login.php?pesan=gagal");
    }

} else {
    // Username Tidak Ditemukan
    header("location:login.php?pesan=gagal");
}
?>