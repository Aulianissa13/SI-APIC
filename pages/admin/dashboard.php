<?php
/** @var mysqli $koneksi */

$get_pegawai = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='user'");
$d_pegawai = mysqli_fetch_assoc($get_pegawai);

$get_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE status='Diajukan'");
$d_pending = mysqli_fetch_assoc($get_pending);

$get_acc = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE status='Disetujui'");
$d_acc = mysqli_fetch_assoc($get_acc);

$get_tolak = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE status='Ditolak'");
$d_tolak = mysqli_fetch_assoc($get_tolak);

$query_latest = "SELECT c.*, u.nama_lengkap, u.jabatan, c.created_at FROM pengajuan_cuti c JOIN users u ON c.id_user = u.id_user ORDER BY c.created_at DESC LIMIT 5";
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
    :root {
        --pn-green: #004d00;
        --pn-gold: #F9A825;
        --soft-bg: #f8f9fc;
        --text-dark: #2c3e50;
    }
    
    body { font-family: 'Poppins', sans-serif; background-color: var(--soft-bg); }

    .hero-card {
        background: linear-gradient(135deg, var(--pn-green) 0%, #004d00 100%);
        color: white; border-radius: 20px; padding: 1.5rem;
        position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0, 104, 55, 0.2);
    }
    .hero-card::after { content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; }
    .hero-card::before { content: ''; position: absolute; bottom: -30px; right: 80px; width: 100px; height: 100px; background: rgba(249, 168, 37, 0.2); border-radius: 50%; }

    .stat-card { border: none; border-radius: 15px; transition: transform 0.2s, box-shadow 0.2s; background: #fff; height: 100%; overflow: hidden; position: relative; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.05); }
    .stat-icon-bg { position: absolute; right: -10px; bottom: -10px; font-size: 5rem; opacity: 0.1; transform: rotate(-15deg); }
    .border-left-brand { border-left: 5px solid var(--pn-green) !important; }
    .border-left-warning { border-left: 5px solid var(--pn-gold) !important; }
    .border-left-info { border-left: 5px solid #36b9cc !important; }
    .border-left-danger { border-left: 5px solid #e74a3b !important; }

    .card-modern { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); height: 20px; display: flex; flex-direction: column; }
    .card-header-modern { background-color: white; border-bottom: 1px solid #f0f0f0; padding: 0.75rem 1.25rem; border-radius: 15px 15px 0 0; }
    .card-header-modern h5 { font-size: 0.9rem; margin-bottom: 0; }
    
    #calendar { border: none !important; font-size: 0.9rem; max-height: 200px; }
    .fc-theme-standard td, .fc-theme-standard th, .fc-scrollgrid { border: none !important; }
    .fc-daygrid-day-frame { display: flex; align-items: center; justify-content: center; min-height: 20px !important; position: relative; }
    
    .fc .fc-daygrid-day-number {
        width: 30px; height: 30px; display: flex !important; align-items: center; justify-content: center;
        border-radius: 50%; text-decoration: none !important; color: #333; z-index: 2;
    }

    .fc-day-today { background-color: transparent !important; }
    .fc-day-today .fc-daygrid-day-number { background-color: var(--pn-green) !important; color: white !important; }

    .holiday-nasional .fc-daygrid-day-number { background-color: #d9534f !important; color: white !important; }
    .holiday-cuti-bersama .fc-daygrid-day-number { background-color: var(--pn-gold) !important; color: white !important; }

    .fc-event, .fc-daygrid-event { display: none !important; }

    .fc .fc-toolbar-title { font-size: 1rem !important; font-weight: bold; color: var(--pn-green); }
    .fc .fc-button-primary { background-color: var(--pn-green) !important; border: none !important; padding: 3px 6px !important; }

    .libur-item { font-size: 0.6rem; padding: 4px 6px; margin-bottom: 3px; border-radius: 6px; display: flex; align-items: center; }
    .dot { width: 6px; height: 6px; border-radius: 50%; margin-right: 6px; flex-shrink: 0; }
</style>

<style>
    .hero-card { background: linear-gradient(135deg, var(--pn-green) 0%, #004d00 100%); color: white; border-radius: 20px; padding: 1.5rem; position: relative; overflow: hidden; min-height: 150px; }
    .section-spacing { margin-bottom: 15px !important; }
    .stat-card { border: none; border-radius: 15px; background: #fff; height: 140px; position: relative; overflow: hidden; display: flex; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .stat-number { font-size: 3.5rem; font-weight: 800; line-height: 1; margin-top: 15px; text-align: center; }
    .stat-icon-bg { position: absolute; right: 12px; bottom: 10px; font-size: 2.5rem; opacity: 0.12; }
    .calendar-card-fix { height: calc(150px + 140px + 20px) !important; }
    #calendar { font-size: 0.6rem; }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="row section-spacing">
                <div class="col-12">
                    <div class="hero-card d-flex align-items-center justify-content-between shadow">
                        <div><h1 class="font-weight-bold mb-2" style="font-size: 2rem;">Halo, Administrator!</h1><p class="mb-0 text-white-50 h5">Selamat datang di Panel Kontrol SI-APIC Pengadilan Negeri Yogyakarta.</p></div>
                        <div class="d-none d-md-block"><i class="fas fa-chart-line fa-4x text-white-50"></i></div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3 col-6 mb-4"><div class="card stat-card border-left-info shadow-sm"><div class="card-body"><div class="stat-main-label text-info text-uppercase font-weight-bold" style="font-size: 0.8rem; line-height: 1.2; text-align: center;">Total<br>Pegawai</div><div class="stat-number text-gray-800"><?php echo $d_pegawai['total']; ?></div><i class="fas fa-users stat-icon-bg text-info"></i></div></div></div>
                <div class="col-md-3 col-6 mb-4"><div class="card stat-card border-left-warning shadow-sm"><div class="card-body"><div class="stat-main-label text-warning text-uppercase font-weight-bold" style="font-size: 0.8rem; line-height: 1.2; text-align: center;">Menunggu Persetujuan</div><div class="stat-number text-gray-800"><?php echo $d_pending['total']; ?></div><i class="fas fa-hourglass-half stat-icon-bg text-warning"></i></div></div></div>
                <div class="col-md-3 col-6 mb-4"><div class="card stat-card border-left-brand shadow-sm"><div class="card-body"><div class="stat-main-label text-success text-uppercase font-weight-bold" style="font-size: 0.8rem; line-height: 1.2; text-align: center;">Cuti<br>Disetujui</div><div class="stat-number text-gray-800"><?php echo $d_acc['total']; ?></div><i class="fas fa-check-circle stat-icon-bg text-success"></i></div></div></div>
                <div class="col-md-3 col-6 mb-4"><div class="card stat-card border-left-danger shadow-sm"><div class="card-body"><div class="stat-main-label text-danger text-uppercase font-weight-bold" style="font-size: 0.8rem; line-height: 1.2; text-align: center;">Cuti<br>Ditolak</div><div class="stat-number text-gray-800"><?php echo $d_tolak['total']; ?></div><i class="fas fa-times-circle stat-icon-bg text-danger"></i></div></div></div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card card-modern calendar-card-fix shadow-sm"><div class="card-body p-3"><div id="calendar"></div><div id="libur-list-container" class="mt-3" style="max-height: 60px; overflow-y: auto;"></div></div></div>
        </div>
    </div>
    <div class="row" style="margin-top: -35px;">
        <div class="col-lg-6 mb-4">
            <div class="card card-modern shadow-sm" style="height: 320px;">
                <div class="card-header-modern py-2"><h5 class="m-0 font-weight-bold" style="color: #004d00; font-size: 0.9rem;">Grafik Status Cuti</h5></div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 250px;"><canvas id="myAreaChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card card-modern shadow-sm" style="height: 320px;">
                <div class="card-header-modern py-2"><h5 class="m-0 font-weight-bold" style="color: #004d00; font-size: 0.9rem;">Permohonan Terbaru</h5></div>
                <div class="card-body p-0" style="height: 100%;">
                    <table class="table table-hover mb-0" style="border-collapse: collapse; height: 100%;">
                        <thead style="background-color: #004d00; color: white;"><tr><th class="text-center py-1" style="width: 40%; border: 1px solid #ddd; font-size: 0.75rem;">Nama Pegawai</th><th class="text-center py-1" style="width: 35%; border: 1px solid #ddd; font-size: 0.75rem;">Waktu Pengajuan</th><th class="text-center pr-4 py-1" style="width: 25%; border: 1px solid #ddd; font-size: 0.75rem;">Status</th></tr></thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_array($sql_latest)) {
                                $badge = ($row['status'] == 'Diajukan') ? "warning" : (($row['status'] == 'Disetujui') ? "success" : "danger");
                                $waktu = date('d/m/Y H:i', strtotime($row['created_at']));
                            ?>
                            <tr><td class="text-center py-1" style="border: 1px solid #ddd;"><div class="font-weight-bold text-dark" style="font-size: 0.75rem;"><?= $row['nama_lengkap'] ?></div><div class="text-muted small" style="font-size: 0.7rem;"><?= $row['jabatan'] ?></div></td><td class="text-center py-1" style="border: 1px solid #ddd;"><div class="text-muted small" style="font-size: 0.7rem;"><?= $waktu ?></div></td><td class="text-center pr-4 py-1" style="border: 1px solid #ddd;"><span class="badge badge-pill badge-<?= $badge ?> p-1 px-2" style="font-size: 0.65rem;"><?= $row['status'] ?></span></td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
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
const currentYear = <?php echo date('Y'); ?>;

new Chart(document.getElementById("myAreaChart"), {
    type: 'doughnut',
    data: { labels: ["Disetujui", "Menunggu", "Ditolak"], datasets: [{ data: [<?= $d_acc['total'] ?>, <?= $d_pending['total'] ?>, <?= $d_tolak['total'] ?>], backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'], }], },
    options: { maintainAspectRatio: false, cutoutPercentage: 75, legend: { position: 'bottom', labels: { boxWidth: 12, fontSize: 10 } } }
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
                        if (holiday.jenis.includes('nasional')) {
                            dayEl.classList.add('holiday-nasional');
                        } else {
                            dayEl.classList.add('holiday-cuti-bersama');
                        }
                    }
                }
            });
        }
    });
    calendar.render();
});
</script>