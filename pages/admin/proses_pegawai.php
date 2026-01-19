<?php
session_start();
include '../../config/database.php';

$act = $_POST['act'] ?? $_GET['act'];

// 1. TAMBAH PEGAWAI
if ($act == 'tambah') {
    $nama     = $_POST['nama_lengkap'];
    $role     = $_POST['role'];
    $telp     = $_POST['no_telepon'];
    
    // GANTI USERNAME -> NIP
    $nip      = $_POST['nip'];
    $pass     = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    
    $cuti_n   = $_POST['sisa_cuti_n'];
    $cuti_n1  = $_POST['sisa_cuti_n1'];
    $cuti_skt = $_POST['kuota_cuti_sakit'];

    // Cek NIP kembar
    $cek = mysqli_query($koneksi, "SELECT nip FROM users WHERE nip='$nip'");
    if(mysqli_num_rows($cek) > 0){
        $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Gagal! NIP sudah terdaftar.'];
        header("Location: ../../index.php?page=form_pegawai");
        exit;
    }

    // Query INSERT pakai NIP
    $query = "INSERT INTO users (nama_lengkap, role, no_telepon, nip, password, sisa_cuti_n, sisa_cuti_n1, kuota_cuti_sakit) 
              VALUES ('$nama', '$role', '$telp', '$nip', '$pass', '$cuti_n', '$cuti_n1', '$cuti_skt')";

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['alert'] = ['type' => 'success', 'text' => 'Pegawai berhasil ditambahkan.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Error: ' . mysqli_error($koneksi)];
    }
    header("Location: ../../index.php?page=data_pegawai");

// 2. EDIT PEGAWAI
} elseif ($act == 'edit') {
    $id       = $_POST['id_user'];
    $nama     = $_POST['nama_lengkap'];
    $role     = $_POST['role'];
    $telp     = $_POST['no_telepon'];
    
    // GANTI USERNAME -> NIP
    $nip      = $_POST['nip'];
    
    $cuti_n   = $_POST['sisa_cuti_n'];
    $cuti_n1  = $_POST['sisa_cuti_n1'];
    $cuti_skt = $_POST['kuota_cuti_sakit'];

    // Query Update pakai NIP
    $query = "UPDATE users SET 
              nama_lengkap='$nama', role='$role', no_telepon='$telp', nip='$nip',
              sisa_cuti_n='$cuti_n', sisa_cuti_n1='$cuti_n1', kuota_cuti_sakit='$cuti_skt'";

    if (!empty($_POST['password'])) {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query .= ", password='$pass'";
    }

    $query .= " WHERE id_user='$id'";

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['alert'] = ['type' => 'success', 'text' => 'Data pegawai berhasil diperbarui.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Error: ' . mysqli_error($koneksi)];
    }
    header("Location: ../../index.php?page=data_pegawai");

// 3. HAPUS PEGAWAI
} elseif ($act == 'hapus') {
    $id = $_GET['id'];
    
    if($id == $_SESSION['id_user']){
        $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Anda tidak bisa menghapus akun yang sedang login!'];
        header("Location: ../../index.php?page=data_pegawai");
        exit;
    }

    mysqli_query($koneksi, "DELETE FROM pengajuan_cuti WHERE id_user='$id'");
    
    if (mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$id'")) {
        $_SESSION['alert'] = ['type' => 'success', 'text' => 'Pegawai berhasil dihapus.'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'text' => 'Gagal hapus: ' . mysqli_error($koneksi)];
    }
    header("Location: ../../index.php?page=data_pegawai");
}
?>