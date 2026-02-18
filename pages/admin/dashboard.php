<?php
/** @var mysqli $koneksi */

// Pastikan koneksi database sudah ada sebelumnya

$get_pegawai = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='user' OR role='admin'");
$d_pegawai = mysqli_fetch_assoc($get_pegawai);

$get_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE status='Diajukan'");
$d_pending = mysqli_fetch_assoc($get_pending);

$get_acc = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE status='Disetujui'");
$d_acc = mysqli_fetch_assoc($get_acc);

$get_tolak = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE status='Ditolak'");
$d_tolak = mysqli_fetch_assoc($get_tolak);

$query_latest = "SELECT c.*, u.nama_lengkap, u.jabatan, c.created_at
                 FROM pengajuan_cuti c
                 JOIN users u ON c.id_user = u.id_user
                 ORDER BY c.created_at DESC LIMIT 5";
$sql_latest = mysqli_query($koneksi, $query_latest);

$query_libur = mysqli_query($koneksi, "SELECT * FROM libur_nasional");
$libur_data = [];
while($row = mysqli_fetch_assoc($query_libur)) {
    $libur_data[] = [
        'start' => $row['tanggal'],
        'title' => $row['keterangan'],
        'jenis' => strtolower($row['jenis_libur']),
        'allDay' => true
    ];
}
?>

<style>
    :root{
        --pn-green:#004d00;
        --pn-gold:#F9A825;
        --soft-bg:#f8f9fc;
    }

    body{ font-family:'Poppins',sans-serif; background:var(--soft-bg); }

    /* --- HERO CARD MENJADI CAROUSEL --- */
    .hero-card{
        background:linear-gradient(135deg,var(--pn-green) 0%, #004d00 100%);
        color:#fff; border-radius:20px; 
        padding: 0; 
        position:relative; overflow:hidden;
        box-shadow:0 10px 20px rgba(0,104,55,.2);
        min-height:150px;
    }
    .hero-card::after{content:'';position:absolute;top:-50px;right:-50px;width:200px;height:200px;background:rgba(255,255,255,.1);border-radius:50%; z-index: 1;}
    .hero-card::before{content:'';position:absolute;bottom:-30px;right:80px;width:100px;height:100px;background:rgba(249,168,37,.2);border-radius:50%; z-index: 1;}

    /* Konten di dalam slide */
    .hero-content {
        padding: 1.5rem 3.5rem; /* Padding kiri-kanan lebih besar agar tidak kena panah */
        position: relative;
        z-index: 2;
        height: 150px; /* Menjaga tinggi konsisten */
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Kustomisasi Panah Carousel */
    .hero-card .carousel-control-prev,
    .hero-card .carousel-control-next {
        width: 5%;
        z-index: 10;
        opacity: 0.5;
    }
    .hero-card .carousel-control-prev:hover,
    .hero-card .carousel-control-next:hover {
        opacity: 1;
    }
    
    .section-spacing{ margin-bottom:15px !important; }

    .stat-card{
        border:none;border-radius:15px;
        transition:transform .2s, box-shadow .2s;
        background:#fff;height:140px;overflow:hidden;
        position:relative;display:flex;align-items:center;
        box-shadow:0 4px 12px rgba(0,0,0,.05);
    }
    .stat-card:hover{ transform:translateY(-5px); box-shadow:0 8px 15px rgba(0,0,0,.05); }
    .stat-number{ font-size:3.5rem;font-weight:800;line-height:1;margin-top:15px;text-align:center; }
    .stat-icon-bg{ position:absolute;right:12px;bottom:10px;font-size:2.5rem;opacity:.12;transform:rotate(-15deg); }

    .border-left-brand{ border-left:5px solid var(--pn-green) !important; }
    .border-left-warning{ border-left:5px solid var(--pn-gold) !important; }
    .border-left-info{ border-left:5px solid #36b9cc !important; }
    .border-left-danger{ border-left:5px solid #e74a3b !important; }

    .card-modern{
        border:none;border-radius:0px;
        box-shadow:0 4px 6px rgba(0,0,0,.02);
        display:flex;flex-direction:column;
        overflow:hidden;
        background:#fff;
    }

    .card-header-modern{
        background:var(--pn-green)!important;
        color:#fff!important;
        border-bottom:none!important;
        padding:.75rem 1.25rem;
    }
    .card-header-modern h5{
        font-size:.9rem;margin:0;color:#fff!important;
    }

    .outline-pn{ border: 1px solid var(--pn-green) !important; }

    /* --- UKURAN KALENDER --- */
    .calendar-card-fix{ height:calc(150px + 140px + 20px) !important; }
    #calendar{ border:none !important; font-size:.6rem; max-height:200px; }

    .fc-theme-standard td, .fc-theme-standard th, .fc-scrollgrid{ border:none !important; }
    .fc-daygrid-day-frame{ display:flex;align-items:center;justify-content:center;min-height:20px!important;position:relative; }
    .fc .fc-daygrid-day-number{
        width:30px;height:30px;display:flex!important;align-items:center;justify-content:center;
        border-radius:50%;text-decoration:none!important;color:#333;z-index:2;
    }
    .fc-day-today{ background:transparent!important; }
    .fc-day-today .fc-daygrid-day-number{ background:var(--pn-green)!important;color:#fff!important; }
    .holiday-nasional .fc-daygrid-day-number{ background:#d9534f!important;color:#fff!important; }
    .holiday-cuti-bersama .fc-daygrid-day-number{ background:var(--pn-gold)!important;color:#fff!important; }
    .fc-event, .fc-daygrid-event{ display:none!important; }
    .fc .fc-toolbar-title{ font-size:1rem!important;font-weight:bold;color:var(--pn-green); }
    .fc .fc-button-primary{ background:var(--pn-green)!important;border:none!important;padding:3px 6px!important; }

    .libur-item{ font-size:.6rem;padding:4px 6px;margin-bottom:3px;border-radius:6px;display:flex;align-items:center; }
    .dot{ width:6px;height:6px;border-radius:50%;margin-right:6px;flex-shrink:0; }

    .permohonan-card{ height:320px; }

    .permohonan-card .card-body{
        padding:10px;
        flex:1;
        display:flex;
        overflow:hidden;
        background:transparent;
    }

    .permohonan-table-box{
        flex:1;
        display:flex;
        height:100%;
        background:#fff;
        border-radius:0px;
        border:1px solid #edf0f6;
        overflow:hidden;
    }

    .table-permohonan{
        width:100%;
        height:100%;
        margin:0;
        table-layout:fixed;
        border-collapse:collapse;
    }

    .table-permohonan thead tr{ height:42px; }
    .table-permohonan thead th{
        background:var(--pn-green);
        color:#fff;
        padding:8px 8px !important;
        font-size:.75rem;
        border:1px solid rgba(255,255,255,.15) !important;
        white-space:nowrap;
        vertical-align:middle;
    }

    .table-permohonan tbody{ height: calc(100% - 42px); }
    .table-permohonan tbody tr{ height: calc(100% / 5); }
    .table-permohonan tbody td{
        padding:6px 8px !important;
        line-height:1.1;
        font-size:.72rem;
        border:1px solid #e6e6e6 !important;
        vertical-align:middle !important;
    }

    .table-permohonan .nama{ font-weight:700; font-size:.74rem; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .table-permohonan .jabatan{ font-size:.68rem; color:#8a8f9c; margin:2px 0 0 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .table-permohonan .waktu{ font-size:.70rem; color:#6b7280; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

    .badge-mini{ font-size:.62rem !important; padding:3px 8px !important; border-radius:999px !important; white-space:nowrap; display:inline-block; max-width:100%; overflow:hidden; text-overflow:ellipsis; border:0; }
    .badge-link{ text-decoration:none !important; cursor:pointer; display:inline-block; }
    .badge-link:hover{ filter: brightness(0.95); transform: translateY(-1px); }
    
    .outline-info   { box-shadow: 0 0 0 1px #36b9cc, 0 4px 12px rgba(0,0,0,.05) !important; }
    .outline-warning{ box-shadow: 0 0 0 1px var(--pn-gold), 0 4px 12px rgba(0,0,0,.05) !important; }
    .outline-brand  { box-shadow: 0 0 0 1px var(--pn-green), 0 4px 12px rgba(0,0,0,.05) !important; }
    .outline-danger { box-shadow: 0 0 0 1px #e74a3b, 0 4px 12px rgba(0,0,0,.05) !important; }
    
    .outline-info:hover   { box-shadow: 0 0 0 2px #36b9cc, 0 8px 15px rgba(0,0,0,.05) !important; }
    .outline-warning:hover{ box-shadow: 0 0 0 2px var(--pn-gold), 0 8px 15px rgba(0,0,0,.05) !important; }
    .outline-brand:hover  { box-shadow: 0 0 0 2px var(--pn-green), 0 8px 15px rgba(0,0,0,.05) !important; }
    .outline-danger:hover { box-shadow: 0 0 0 2px #e74a3b, 0 8px 15px rgba(0,0,0,.05) !important; }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="row section-spacing">
                <div class="col-12">
                    
                    <div id="heroCarousel" class="carousel slide hero-card shadow" data-ride="carousel" data-interval="5000">
                        
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="hero-content">
                                    <div>
                                        <h1 class="font-weight-bold mb-2" style="font-size: 2rem;">Halo, Administrator!</h1>
                                        <p class="mb-0 text-white-50 h5">Selamat datang di Panel Kontrol SI-APIC Pengadilan Negeri Yogyakarta.</p>
                                    </div>
                                    <div class="d-none d-md-block"><i class="fas fa-chart-line fa-4x text-white-50"></i></div>
                                </div>
                            </div>

                            <div class="carousel-item">
                                <div class="hero-content">
                                    <div>
                                        <h1 class="font-weight-bold mb-2" style="font-size: 2rem;">Status Pengajuan</h1>
                                        <p class="mb-0 text-white-50 h5">Saat ini terdapat <strong><?php echo $d_pending['total']; ?></strong> permohonan cuti yang menunggu persetujuan Anda.</p>
                                    </div>
                                    <div class="d-none d-md-block"><i class="fas fa-file-signature fa-4x text-white-50"></i></div>
                                </div>
                            </div>

                            <div class="carousel-item">
                                <div class="hero-content">
                                    <div>
                                        <h1 class="font-weight-bold mb-2" style="font-size: 2rem;">Sistem Terintegrasi</h1>
                                        <p class="mb-0 text-white-50 h5">Kelola data cuti pegawai dengan cepat, akurat, dan transparan melalui SI-APIC.</p>
                                    </div>
                                    <div class="d-none d-md-block"><i class="fas fa-network-wired fa-4x text-white-50"></i></div>
                                </div>
                            </div>
                        </div>

                        <a class="carousel-control-prev" href="#heroCarousel" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#heroCarousel" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>

                    </div>
                    </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3 col-6 mb-4">
                    <div class="card stat-card border-left-info shadow-sm outline-info">
                        <div class="card-body">
                            <div class="stat-main-label text-info text-uppercase font-weight-bold" style="font-size: 0.8rem; line-height: 1.2; text-align: center;">Total<br>Pegawai</div>
                            <div class="stat-number text-gray-800"><?php echo $d_pegawai['total']; ?></div>
                            <i class="fas fa-users stat-icon-bg text-info"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="card stat-card border-left-warning shadow-sm outline-warning">
                        <div class="card-body">
                            <div class="stat-main-label text-warning text-uppercase font-weight-bold" style="font-size: 0.8rem; line-height: 1.2; text-align: center;">Menunggu Persetujuan</div>
                            <div class="stat-number text-gray-800"><?php echo $d_pending['total']; ?></div>
                            <i class="fas fa-hourglass-half stat-icon-bg text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="card stat-card border-left-brand shadow-sm outline-brand">
                        <div class="card-body">
                            <div class="stat-main-label text-success text-uppercase font-weight-bold" style="font-size: 0.8rem; line-height: 1.2; text-align: center;">Cuti<br>Disetujui</div>
                            <div class="stat-number text-gray-800"><?php echo $d_acc['total']; ?></div>
                            <i class="fas fa-check-circle stat-icon-bg text-success"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="card stat-card border-left-danger shadow-sm outline-danger">
                        <div class="card-body">
                            <div class="stat-main-label text-danger text-uppercase font-weight-bold" style="font-size: 0.8rem; line-height: 1.2; text-align: center;">Cuti<br>Ditolak</div>
                            <div class="stat-number text-gray-800"><?php echo $d_tolak['total']; ?></div>
                            <i class="fas fa-times-circle stat-icon-bg text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card card-modern calendar-card-fix shadow-sm outline-pn">
                <div class="card-body p-3">
                    <div id="calendar"></div>
                    <div id="libur-list-container" class="mt-3" style="max-height: 60px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: -35px;">
        <div class="col-lg-6 mb-4">
            <div class="card card-modern shadow-sm outline-pn" style="height: 320px; border-top: 4px solid var(--pn-green);">
                <div class="card-header-modern py-2">
                    <h5 class="m-0 font-weight-bold" style="font-size: 0.9rem;">Grafik Status Cuti</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 250px;">
                    <canvas id="myAreaChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card card-modern shadow-sm permohonan-card outline-pn">
                <div class="card-header-modern py-2">
                    <h5 class="m-0 font-weight-bold" style="font-size: 0.9rem;">Permohonan Terbaru</h5>
                </div>

                <div class="card-body">
                    <div class="permohonan-table-box">
                        <table class="table table-hover table-permohonan">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40%;">Nama Pegawai</th>
                                    <th class="text-center" style="width: 35%;">Waktu Pengajuan</th>
                                    <th class="text-center" style="width: 25%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_array($sql_latest)) {
                                    $badge = ($row['status'] == 'Diajukan') ? "warning" : (($row['status'] == 'Disetujui') ? "success" : "danger");
                                    $waktu = date('d/m/Y H:i', strtotime($row['created_at']));
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <div class="nama"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                        <div class="jabatan"><?= htmlspecialchars($row['jabatan']) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <div class="waktu"><?= htmlspecialchars($waktu) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <a href="index.php?page=validasi_cuti" 
                                           class="badge badge-pill badge-<?= $badge ?> badge-mini badge-link">
                                            <?= htmlspecialchars($row['status']) ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

<script>
const dataLibur = <?php echo json_encode($libur_data); ?>;

new Chart(document.getElementById("myAreaChart"), {
    type: 'doughnut',
    data: {
        labels: ["Disetujui", "Menunggu", "Ditolak"],
        datasets: [{
            data: [<?= (int)$d_acc['total'] ?>, <?= (int)$d_pending['total'] ?>, <?= (int)$d_tolak['total'] ?>],
            backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
        }],
    },
    options: {
        maintainAspectRatio: false,
        cutoutPercentage: 75,
        legend: { position: 'bottom', labels: { boxWidth: 12, fontSize: 10 } }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: { left: 'prev', center: 'title', right: 'next' },
        events: dataLibur,
        datesSet: function(info) {
            const listContainer = document.getElementById('libur-list-container');
            listContainer.innerHTML = '';

            const currentMonth = info.view.currentStart.getMonth();
            const currentYearInView = info.view.currentStart.getFullYear();

            let filtered = dataLibur.filter(item => {
                const itemDate = new Date(item.start);
                return itemDate.getFullYear() === currentYearInView && itemDate.getMonth() === currentMonth;
            }).sort((a,b) => new Date(a.start) - new Date(b.start));

            if(filtered.length === 0) {
                listContainer.innerHTML = '<div class="text-center text-muted small py-2">Tidak ada libur</div>';
            } else {
                filtered.forEach(item => {
                    const isNasional = item.jenis.includes('nasional');
                    const color = isNasional ? '#d9534f' : '#856404';
                    const bg = isNasional ? '#ffcccb' : '#fff3cd';
                    const itemDate = new Date(item.start);
                    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    const month = monthNames[itemDate.getMonth()];
                    const day = itemDate.getDate();
                    listContainer.innerHTML += `<div class="libur-item" style="background:${bg}; color:${color}"><div class="dot" style="background:${color}"></div><strong>${day} ${month}</strong>&nbsp; ${item.title}</div>`;
                });
            }

            document.querySelectorAll('.fc-daygrid-day').forEach(dayEl => {
                const dateStr = dayEl.getAttribute('data-date');
                if (dateStr) {
                    const date = new Date(dateStr);
                    const holiday = dataLibur.find(item => {
                        const itemDate = new Date(item.start);
                        return itemDate.getFullYear() === date.getFullYear() && itemDate.getMonth() === date.getMonth() && itemDate.getDate() === date.getDate();
                    });
                    if (holiday) {
                        if (holiday.jenis.includes('nasional')) dayEl.classList.add('holiday-nasional');
                        else dayEl.classList.add('holiday-cuti-bersama');
                    }
                }
            });
        }
    });
    calendar.render();
});
</script>