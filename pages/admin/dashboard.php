<?php
// --- 1. LOGIKA PHP PENGAMBILAN DATA (REAL TIME) ---

// Hitung Total Pegawai (User)
$get_pegawai = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='user'");
$d_pegawai = mysqli_fetch_assoc($get_pegawai);

// Hitung Cuti Menunggu (Pending) - DARI TABEL pengajuan_cuti
$get_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE status='Diajukan'");
$d_pending = mysqli_fetch_assoc($get_pending);

// Hitung Cuti Disetujui - DARI TABEL pengajuan_cuti
$get_acc = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE status='Disetujui'");
$d_acc = mysqli_fetch_assoc($get_acc);

// Hitung Cuti Ditolak - DARI TABEL pengajuan_cuti
$get_tolak = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE status='Ditolak'");
$d_tolak = mysqli_fetch_assoc($get_tolak);

// Ambil 5 Pengajuan Terakhir (Untuk Tabel Mini)
// Query diganti ke tabel pengajuan_cuti
$query_latest = "SELECT c.*, u.nama_lengkap, u.nip 
                 FROM pengajuan_cuti c 
                 JOIN users u ON c.id_user = u.id_user 
                 ORDER BY c.tgl_pengajuan DESC LIMIT 5";
$sql_latest = mysqli_query($koneksi, $query_latest);
?>

<style>
    :root {
        --pn-green: #006837;
        --pn-gold: #F9A825;
        --soft-bg: #f8f9fc;
        --text-dark: #2c3e50;
    }
    
    body { font-family: 'Poppins', sans-serif; background-color: var(--soft-bg); }

    /* Hero Card (Welcome) */
    .hero-card {
        background: linear-gradient(135deg, var(--pn-green) 0%, #004d29 100%);
        color: white;
        border-radius: 20px;
        padding: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0, 104, 55, 0.2);
    }
    .hero-card::after {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .hero-card::before {
        content: '';
        position: absolute;
        bottom: -30px; right: 80px;
        width: 100px; height: 100px;
        background: rgba(249, 168, 37, 0.2); /* Gold transparent */
        border-radius: 50%;
    }

    /* Stat Cards */
    .stat-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.2s, box-shadow 0.2s;
        background: #fff;
        height: 100%;
        overflow: hidden;
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.05);
    }
    .stat-icon-bg {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 5rem;
        opacity: 0.1;
        transform: rotate(-15deg);
    }
    .border-left-brand { border-left: 5px solid var(--pn-green) !important; }
    .border-left-warning { border-left: 5px solid var(--pn-gold) !important; }
    .border-left-info { border-left: 5px solid #36b9cc !important; }
    .border-left-danger { border-left: 5px solid #e74a3b !important; }

    /* Table & Components */
    .card-modern {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .card-header-modern {
        background-color: white;
        border-bottom: 1px solid #f0f0f0;
        padding: 1.5rem;
        border-radius: 15px 15px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .badge-pill-modern {
        padding: 0.5em 1em;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.75rem;
    }
    
    /* Table Styling */
    .table-hover tbody tr:hover { background-color: #f8f9fa; }
    .table th { border-top: none; font-size: 0.85rem; text-transform: uppercase; color: #888; font-weight: 600; }
    .table td { vertical-align: middle; font-size: 0.95rem; color: #444; }
</style>

<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="hero-card d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="font-weight-bold mb-1">Halo, Administrator!</h2>
                    <p class="mb-0 text-white-50">Selamat datang di Panel Kontrol SI-APIC Pengadilan Negeri Yogyakarta.</p>
                </div>
                <div class="d-none d-md-block">
                    <i class="fas fa-chart-line fa-3x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card shadow-sm border-left-info py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Pegawai</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800"><?php echo $d_pegawai['total']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <i class="fas fa-users stat-icon-bg text-info"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card shadow-sm border-left-warning py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Menunggu Tindakan</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800"><?php echo $d_pending['total']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <i class="fas fa-hourglass-half stat-icon-bg text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card shadow-sm border-left-brand py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Cuti Disetujui</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800"><?php echo $d_acc['total']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <i class="fas fa-check-circle stat-icon-bg text-success"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card shadow-sm border-left-danger py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Cuti Ditolak</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800"><?php echo $d_tolak['total']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <i class="fas fa-times-circle stat-icon-bg text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-xl-8 col-lg-7">
            <div class="card card-modern shadow-sm mb-4">
                <div class="card-header-modern">
                    <h6 class="m-0 font-weight-bold" style="color: var(--pn-green);">
                        <i class="fas fa-chart-bar mr-2"></i>Statistik Pengajuan Cuti (Bulan Ini)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="myAreaChart" style="height: 300px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card card-modern shadow-sm mb-4">
                <div class="card-header-modern">
                    <h6 class="m-0 font-weight-bold text-gray-800">
                        <i class="fas fa-bell mr-2 text-warning"></i>Permohonan Terbaru
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="pl-4">Nama / NIP</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if(mysqli_num_rows($sql_latest) > 0) {
                                    while($row = mysqli_fetch_array($sql_latest)) { 
                                        // Tentukan warna badge
                                        $badge_color = "secondary";
                                        if($row['status'] == 'Menunggu') $badge_color = "warning";
                                        if($row['status'] == 'Disetujui') $badge_color = "success";
                                        if($row['status'] == 'Ditolak') $badge_color = "danger";
                                ?>
                                <tr>
                                    <td class="pl-4">
                                        <div class="font-weight-bold text-dark"><?php echo $row['nama_lengkap']; ?></div>
                                        <div class="small text-muted"><?php echo $row['nip']; ?></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-pill-modern badge-<?php echo $badge_color; ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } else {
                                    echo "<tr><td colspan='2' class='text-center py-4 text-muted'>Belum ada pengajuan.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Data Statistik untuk Grafik
var ctx = document.getElementById("myAreaChart");
var myLineChart = new Chart(ctx, {
  type: 'doughnut', 
  data: {
    labels: ["Disetujui", "Menunggu", "Ditolak"],
    datasets: [{
      data: [
          <?php echo $d_acc['total']; ?>, 
          <?php echo $d_pending['total']; ?>, 
          <?php echo $d_tolak['total']; ?>
      ],
      backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
      hoverBackgroundColor: ['#17a673', '#dda20a', '#be2617'],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  },
  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
    },
    legend: {
      display: true,
      position: 'bottom'
    },
    cutoutPercentage: 70,
  },
});
</script>