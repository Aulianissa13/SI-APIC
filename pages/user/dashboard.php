<?php
// =========================================================
// 1. KONEKSI & AMBIL DATA USER
// =========================================================

// Cek lokasi database (Smart Locator)
if (!isset($koneksi)) {
    $kemungkinan_path = ['../../config/database.php', '../config/database.php', 'config/database.php'];
    foreach ($kemungkinan_path as $path) {
        if (file_exists($path)) { include_once $path; break; }
    }
}

$id_user = $_SESSION['id_user'];

// A. Ambil Data Sisa Cuti User
$q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
$user   = mysqli_fetch_array($q_user);

// B. Hitung Pengajuan yang STATUSNYA MENUNGGU (Pending)
$q_pending   = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_user='$id_user' AND (status='Menunggu' OR status='Diajukan')");
$jml_pending = mysqli_num_rows($q_pending);

// C. Hitung Total Hari Cuti yang SUDAH DISETUJUI (History)
$q_acc       = mysqli_query($koneksi, "SELECT SUM(lama_hari) as total_ambil FROM pengajuan_cuti WHERE id_user='$id_user' AND status='Disetujui'");
$d_acc       = mysqli_fetch_array($q_acc);
$total_ambil = $d_acc['total_ambil'] == NULL ? 0 : $d_acc['total_ambil'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Pegawai</h1>
    <a href="index.php?page=form_cuti" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Ajukan Cuti Baru
    </a>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Selamat Datang</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Halo, <?php echo $user['nama_lengkap']; ?>!
                        </div>
                        <p class="mb-0 mt-2 text-muted small">
                            Selamat bekerja. Pastikan selalu cek sisa kuota cuti Anda sebelum mengajukan permohonan.
                        </p>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-circle fa-4x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Sisa Cuti Tahunan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $user['sisa_cuti_n']; ?> Hari
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Sisa Kuota Sakit</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $user['kuota_cuti_sakit']; ?> Hari
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-briefcase-medical fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Menunggu Validasi</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $jml_pending; ?> Pengajuan
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                            Total Cuti Diambil</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $total_ambil; ?> Hari
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-history fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Penting</h6>
            </div>
            <div class="card-body">
                <p>Halo <b><?php echo $user['nama_lengkap']; ?></b>, ini adalah sistem informasi cuti kepegawaian.</p>
                <ul>
                    <li>Cuti Tahunan Anda akan berkurang otomatis jika pengajuan disetujui.</li>
                    <li>Cuti Sakit memiliki kuota tersendiri.</li>
                    <li>Jika Anda salah mengajukan, segera hubungi Admin sebelum divalidasi, atau Admin akan menolaknya agar kuota Anda kembali.</li>
                </ul>
                <a href="index.php?page=riwayat_cuti" class="btn btn-primary btn-icon-split btn-sm">
                    <span class="icon text-white-50">
                        <i class="fas fa-flag"></i>
                    </span>
                    <span class="text">Lihat Riwayat Saya</span>
                </a>
            </div>
        </div>
    </div>
</div>