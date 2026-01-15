<?php
session_start();
include 'config/database.php';

// Cek Login
if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}

// 1. Load Header
include 'layout/header.php';

// 2. Load Sidebar
include 'layout/sidebar.php';

// 3. Load Topbar
include 'layout/topbar.php';

// 4. Load Konten Halaman (Dinamis)
if(isset($_GET['page'])){
    $page = $_GET['page'];

    // Routing Admin
    if($page == "dashboard_admin" && $_SESSION['role'] == 'admin') {
        include 'pages/admin/dashboard.php';
    } 
    else if($page == "validasi_cuti" && $_SESSION['role'] == 'admin') {
        include 'pages/admin/validasi_cuti.php';
    }
    // Routing User / Area Pribadi
    else if($page == "dashboard_user") {
        include 'pages/user/dashboard.php';
    }
    else if($page == "form_cuti") {
        include 'pages/user/form_cuti.php';
    }
    else if($page == "riwayat_cuti") {
        include 'pages/user/riwayat_cuti.php';
    }
    // Default jika halaman tidak ditemukan
    else {
        echo "<h4>Halaman tidak ditemukan!</h4>";
    }
} else {
    // Jika tidak ada parameter 'page', arahkan ke dashboard sesuai role
    if($_SESSION['role'] == 'admin'){
        include 'pages/admin/dashboard.php';
    } else {
        include 'pages/user/dashboard.php';
    }
}

// 5. Load Footer
include 'layout/footer.php';
?>