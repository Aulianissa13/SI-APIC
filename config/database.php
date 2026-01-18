<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_siapic";
$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Gagal terhubung ke database: " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Jakarta');
?>