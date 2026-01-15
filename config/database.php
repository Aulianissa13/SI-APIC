<?php
// File: config/database.php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_siapic";

// Melakukan koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Gagal terhubung ke database: " . mysqli_connect_error());
}

// Set Timezone agar sesuai WIB
date_default_timezone_set('Asia/Jakarta');
?>