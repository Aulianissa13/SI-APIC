<?php
session_start();
include 'config/database.php';

// Cek Login
if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}

// =============================================================
// LOGIKA PAGE (DIPINDAH KE ATAS)
// =============================================================
// Kita ambil variabel 'page' di sini supaya SIDEBAR bisa membacanya
// untuk keperluan fitur "Active State" (Highlight Menu)
if(isset($_GET['page'])){
    $page = $_GET['page'];
} else {
    // Default page jika tidak ada parameter
    if($_SESSION['role'] == 'admin'){
        $page = 'dashboard_admin';
    } else {
        $page = 'dashboard_user';
    }
}

// 1. Load Header
include 'layout/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* 1. Global Font */
    body, #wrapper, h1, h2, h3, h4, h5, h6, p, span, a, div, table, input, button, select, textarea {
        font-family: 'Poppins', sans-serif !important;
    }
    
    /* 2. Background Content Lebih Bersih */
    #content-wrapper {
        background-color: #F2F5F3 !important; 
    }

    /* 3. MODIFIKASI TAMPILAN KARTU (CARD) - AGAR TIDAK KOTAK */
    .card {
        border-radius: 20px !important; /* Sudut melengkung besar */
        border: none !important; /* Hapus garis pinggir kasar */
        box-shadow: 0 5px 20px rgba(0,0,0,0.05) !important; /* Bayangan halus */
        overflow: hidden; /* Agar isi tidak keluar dari lengkungan */
        margin-bottom: 25px; /* Jarak antar kartu */
    }

    .card-header {
        background-color: #fff !important;
        border-bottom: 1px solid #f0f0f0 !important;
        padding-top: 20px;
        padding-bottom: 20px;
    }

    /* 4. MODIFIKASI TOMBOL & INPUT - AGAR TIDAK KOTAK */
    .btn {
        border-radius: 12px !important; /* Tombol melengkung */
        padding: 10px 20px;
        font-weight: 500;
        box-shadow: none !important; /* Hilangkan shadow kasar bawaan */
    }
    
    .form-control {
        border-radius: 12px !important; /* Input text melengkung */
        height: 45px; /* Sedikit lebih tinggi */
        padding: 10px 15px;
    }

    /* 5. Sidebar Active State (Hijau-Emas) */
    .sidebar .nav-item.active .nav-link {
        font-weight: 600 !important;
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.1);
        border-left: 5px solid #F9A825;
        padding-left: 1rem;
        border-radius: 0 10px 10px 0; /* Lengkungan di sisi kanan menu */
    }
    .sidebar .nav-item.active .nav-link i {
        color: #F9A825 !important;
    }
    .sidebar .nav-item .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 0 10px 10px 0;
    }
</style>

<?php
// 2. Load Sidebar
include 'layout/sidebar.php';

// 3. Load Topbar
include 'layout/topbar.php';

// 4. Load Konten Halaman (Dinamis)
// Menggunakan variabel $page yang sudah didefinisikan di atas

// --- AREA ADMIN ---
if($page == "dashboard_admin" && $_SESSION['role'] == 'admin') {
    include 'pages/admin/dashboard.php';
} 
else if($page == "validasi_cuti" && $_SESSION['role'] == 'admin') {
    include 'pages/admin/validasi_cuti.php';
}
else if($page == "data_pegawai" && $_SESSION['role'] == 'admin') {
    include 'pages/admin/data_pegawai.php';
}
// --- [TAMBAHAN BARU] ROUTING FORM & PROSES PEGAWAI ---
else if($page == "form_pegawai" && $_SESSION['role'] == 'admin') {
    include 'pages/admin/form_pegawai.php';
}
else if($page == "proses_pegawai" && $_SESSION['role'] == 'admin') {
    include 'pages/admin/proses_pegawai.php';
}
// --- [END TAMBAHAN] ---

else if($page == "laporan_cuti" && $_SESSION['role'] == 'admin') {
    // Asumsi file nanti akan dibuat di pages/admin/laporan_cuti.php
    include 'pages/admin/laporan_cuti.php';
}

// --- AREA USER ---
else if($page == "dashboard_user") {
    include 'pages/user/dashboard.php';
}
else if($page == "form_cuti") {
    include 'pages/user/form_cuti.php';
}
else if($page == "riwayat_cuti") {
    include 'pages/user/riwayat_cuti.php';
}

// --- AREA UMUM (Ganti Password & Profile) ---
else if($page == "ganti_password") {
    // Sesuaikan path ini dengan lokasi file Anda
    include 'pages/ganti_password.php'; 
}
else {
    echo "
    <div class='container-fluid mt-5 text-center'>
        <i class='fas fa-search fa-3x text-gray-300 mb-3'></i>
        <h3>Halaman tidak ditemukan!</h3>
        <p>Halaman yang Anda cari (<b>$page</b>) tidak tersedia atau Anda tidak memiliki akses.</p>
        <a href='index.php' class='btn btn-primary'>Kembali ke Dashboard</a>
    </div>";
}

// 5. Load Footer
include 'layout/footer.php';
?>