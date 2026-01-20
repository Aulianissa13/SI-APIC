<?php
// 1. AMBIL DATA USER & SISA CUTI
$id_user = $_SESSION['id_user'];
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");

// Variabel Default
$total_tahunan = 0;
$sisa_sakit    = 0;
$nama_lengkap  = $_SESSION['nama_lengkap']; 

if(mysqli_num_rows($query_user) > 0){
    $user = mysqli_fetch_assoc($query_user);
    $nama_lengkap = $user['nama_lengkap']; 

    // MENGHITUNG TOTAL CUTI TAHUNAN (N + N-1 + N-2)
    $n  = isset($user['sisa_cuti_n'])  ? $user['sisa_cuti_n']  : 0;
    $n1 = isset($user['sisa_cuti_n1']) ? $user['sisa_cuti_n1'] : 0;
    $n2 = isset($user['sisa_cuti_n2']) ? $user['sisa_cuti_n2'] : 0;
    
    $total_tahunan = $n + $n1 + $n2;

    // MENGAMBIL SISA CUTI SAKIT
    $sisa_sakit = isset($user['kuota_cuti_sakit']) ? $user['kuota_cuti_sakit'] : 0;
}

// 2. LOGIKA TAHUN
$thn_skrg = date('Y');      
$thn_min1 = $thn_skrg - 1;  
$thn_min2 = $thn_skrg - 2;  

// 3. HITUNG STATUS PENGAJUAN (BAGIAN INI YANG DIPERBAIKI)

// A. Sedang Diproses 
// PERBAIKAN: Mengubah filter status menjadi 'diajukan'
$query_menunggu = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_user='$id_user' AND status='diajukan'");
$jml_menunggu = mysqli_num_rows($query_menunggu);

// B. Total Disetujui Tahun Ini
// CATATAN: Pastikan di database statusnya 'Disetujui' atau 'disetujui'. 
// Jika nanti angka ini tidak muncul, cek apakah di database tulisannya huruf kecil semua.
$query_setuju = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_user='$id_user' AND status='Disetujui' AND YEAR(tgl_mulai)='$thn_skrg'");
$jml_setuju = mysqli_num_rows($query_setuju);
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card text-white shadow" style="background: linear-gradient(90deg, #006837 0%, #43a047 100%); border-radius: 20px; border:none;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="font-weight-bold mb-1">Hai, <?php echo $nama_lengkap; ?>! 👋</h2>
                        <p class="mb-3" style="opacity: 0.9;">
                            Selamat datang di Dashboard Pegawai.<br>
                            Jaga kesehatan agar tetap produktif melayani masyarakat.
                        </p>
                        <a href="index.php?page=form_cuti" class="btn btn-light text-success font-weight-bold shadow-sm" style="border-radius: 30px;">
                            <i class="fas fa-plus mr-1"></i> Ajukan Cuti Baru
                        </a>
                    </div>
                    <div class="col-md-4 d-none d-md-block text-right">
                        <i class="fas fa-calendar-alt" style="font-size: 6rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 py-2 border-left-success shadow-sm" style="border-left: 5px solid #006837 !important;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Sisa Tahunan (Total)</div>
                        <div class="h1 mb-0 font-weight-bold text-gray-800">
                            <?php echo $total_tahunan; ?><span style="font-size:1rem"> Hari</span>
                        </div>
                        <div class="mt-2 text-xs font-weight-bold text-muted" style="background: #f8f9fc; padding: 5px; border-radius: 5px;">
                            <i class="fas fa-history mr-1"></i> Rincian: <br>
                            <?php echo $thn_skrg; ?>: <b><?php echo $n; ?></b> | 
                            <?php echo $thn_min1; ?>: <b><?php echo $n1; ?></b> | 
                            <?php echo $thn_min2; ?>: <b><?php echo $n2; ?></b>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 py-2 border-left-info shadow-sm" style="border-left: 5px solid #36b9cc !important;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Sisa Cuti Sakit</div>
                        <div class="h1 mb-0 font-weight-bold text-gray-800">
                            <?php echo $sisa_sakit; ?><span style="font-size:1rem"> Hari</span>
                        </div>
                        <div class="mt-2 text-xs text-muted">
                            Sisa saat ini
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-heartbeat fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 py-2 border-left-warning shadow-sm" style="border-left: 5px solid #F9A825 !important;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Sedang Diproses</div>
                        <div class="h1 mb-0 font-weight-bold text-gray-800">
                            <?php echo $jml_menunggu; ?>
                        </div>
                        <div class="mt-2 text-xs text-muted">Permohonan menunggu</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 py-2 border-left-primary shadow-sm" style="border-left: 5px solid #4e73df !important;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Disetujui (<?php echo $thn_skrg; ?>)</div>
                        <div class="h1 mb-0 font-weight-bold text-gray-800">
                            <?php echo $jml_setuju; ?>
                        </div>
                        <div class="mt-2 text-xs text-muted">Kali pengambilan cuti</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-double fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4 shadow-sm">
            <div class="card-header py-3 bg-white d-flex align-items-center">
                <h6 class="m-0 font-weight-bold text-primary" style="color: #006837 !important;">
                    <i class="fas fa-info-circle mr-2"></i> Ketentuan
                </h6>
            </div>
            <div class="card-body text-gray-700">
                <ul class="mb-0 pl-3">
                    <li class="mb-2">Sisa Cuti Tahunan yang ditampilkan adalah akumulasi dari <b>Tahun <?php echo $thn_skrg; ?></b>, <b>Tahun <?php echo $thn_min1; ?></b>, dan <b>Tahun <?php echo $thn_min2; ?></b>.</li>
                    <li class="mb-2">Pastikan <b>Sisa Cuti</b> mencukupi sebelum mengajukan permohonan.</li>
                    <li>Status pengajuan dapat dipantau di menu <b>Riwayat & Status</b>.</li>
                </ul>
            </div>
        </div>
    </div>
</div>