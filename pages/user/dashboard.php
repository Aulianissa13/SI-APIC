<?php
/** @var mysqli $koneksi */

$id_user = $_SESSION['id_user'];
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");

$total_tahunan = 0;
$sisa_sakit    = 0;
$nama_lengkap  = $_SESSION['nama_lengkap'];

if(mysqli_num_rows($query_user) > 0){
    $user = mysqli_fetch_assoc($query_user);
    $nama_lengkap = $user['nama_lengkap'];

    $n  = isset($user['sisa_cuti_n'])  ? $user['sisa_cuti_n']  : 0;
    $n1 = isset($user['sisa_cuti_n1']) ? $user['sisa_cuti_n1'] : 0;
    $n2 = isset($user['sisa_cuti_n2']) ? $user['sisa_cuti_n2'] : 0;

    $total_tahunan = $n + $n1 + $n2;
    $sisa_sakit = isset($user['kuota_cuti_sakit']) ? $user['kuota_cuti_sakit'] : 0;
}

$thn_skrg = date('Y');
$thn_min1 = $thn_skrg - 1;
$thn_min2 = $thn_skrg - 2;

$query_menunggu = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_user='$id_user' AND status='diajukan'");
$jml_menunggu = mysqli_num_rows($query_menunggu);

$query_setuju = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_user='$id_user' AND status='Disetujui' AND YEAR(tgl_mulai)='$thn_skrg'");
$jml_setuju = mysqli_num_rows($query_setuju);

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

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-8 col-lg-7">

            <!-- HERO CAROUSEL (DIPENDEKIN BIAR GA KETINGGIAN) -->
            <div id="heroCarousel" class="carousel slide shadow mb-3" data-ride="carousel" style="border-radius: 18px; overflow: hidden;">
                <ol class="carousel-indicators">
                    <li data-target="#heroCarousel" data-slide-to="0" class="active"></li>
                    <li data-target="#heroCarousel" data-slide-to="1"></li>
                    <li data-target="#heroCarousel" data-slide-to="2"></li>
                </ol>

                <div class="carousel-inner">
                    <div class="carousel-item active hero-user hero-compact" style="background: linear-gradient(90deg, #004d00 0%, #006837 100%);">
                        <div class="card-body p-3 hero-body-compact">
                            <div class="row w-100 align-items-center m-0 text-white">
                                <div class="col-md-8">
                                    <h2 class="font-weight-bold mb-1 hero-title">
                                        Hai, <?php echo $nama_lengkap; ?>! 👋
                                    </h2>
                                    <p class="mb-2 hero-desc">
                                        Selamat datang di SI-APIC. Jaga kesehatan agar tetap produktif melayani masyarakat.
                                    </p>
                                    <a href="index.php?page=form_cuti" class="btn btn-light font-weight-bold shadow-sm hero-btn">
                                        <i class="fas fa-plus mr-1"></i> Ajukan Cuti Baru
                                    </a>
                                </div>
                                <div class="col-md-4 d-none d-md-block text-right">
                                    <i class="fas fa-calendar-alt hero-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="carousel-item hero-user hero-compact" style="background: linear-gradient(90deg, #1b5e20 0%, #2e7d32 100%);">
                        <div class="card-body p-3 hero-body-compact">
                            <div class="row w-100 align-items-center m-0 text-white">
                                <div class="col-md-8">
                                    <h2 class="font-weight-bold mb-1 hero-title">Cek Kuota Cuti</h2>
                                    <p class="mb-2 hero-desc">
                                        Sisa Cuti Tahunan Anda: <strong><?php echo $total_tahunan; ?> Hari</strong>.
                                        Pastikan memeriksa riwayat pengajuan Anda secara berkala.
                                    </p>
                                    <a href="index.php?page=riwayat_cuti" class="btn btn-outline-light font-weight-bold shadow-sm hero-btn hero-btn-outline">
                                        <i class="fas fa-history mr-1"></i> Riwayat Cuti
                                    </a>
                                </div>
                                <div class="col-md-4 d-none d-md-block text-right">
                                    <i class="fas fa-chart-pie hero-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="carousel-item hero-user hero-compact" style="background: linear-gradient(90deg, #00695c 0%, #00897b 100%);">
                        <div class="card-body p-3 hero-body-compact">
                            <div class="row w-100 align-items-center m-0 text-white">
                                <div class="col-md-8">
                                    <h2 class="font-weight-bold mb-1 hero-title">Stay Healthy!</h2>
                                    <p class="mb-0 hero-desc">
                                        "Kesehatan adalah investasi terbaik untuk masa depan." Jangan lupa istirahat yang cukup.
                                    </p>
                                </div>
                                <div class="col-md-4 d-none d-md-block text-right">
                                    <i class="fas fa-heartbeat hero-icon"></i>
                                </div>
                            </div>
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

            <!-- STAT CARDS -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card py-1 border-left-success shadow-sm h-100 outline-brand" style="border-left: 5px solid #006837 !important;">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sisa Cuti<br>Tahunan</div>
                            <div class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $total_tahunan; ?></div>
                            <div class="mt-2 text-xs font-weight-bold text-muted" style="background: #f8f9fc; padding: 2px 4px; border-radius: 5px; display: inline-block;">
                                <?php echo substr($thn_skrg, 2); ?>:<b><?php echo $n; ?></b> |
                                <?php echo substr($thn_min1, 2); ?>:<b><?php echo $n1; ?></b> |
                                <?php echo substr($thn_min2, 2); ?>:<b><?php echo $n2; ?></b>
                            </div>
                            <i class="fas fa-clipboard-check stat-icon-bg text-success"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card py-1 border-left-success shadow-sm h-100 outline-info" style="border-left: 5px solid #36b9cc !important;">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Sisa Cuti<br>Sakit</div>
                            <div class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $sisa_sakit; ?></div>
                            <div class="mt-1 text-xs text-muted">Sisa saat ini</div>
                            <i class="fas fa-first-aid stat-icon-bg text-info"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card py-1 border-left-success shadow-sm h-100 outline-warning" style="border-left: 5px solid #F9A825 !important;">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sedang<br>Diproses</div>
                            <div class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $jml_menunggu; ?></div>
                            <div class="mt-1 text-xs text-muted">Menunggu approval</div>
                            <i class="fas fa-hourglass-half stat-icon-bg text-warning"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card py-1 border-left-primary shadow-sm h-100 outline-primary" style="border-left: 5px solid #4e73df !important;">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Cuti<br>Disetujui</div>
                            <div class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $jml_setuju; ?></div>
                            <div class="mt-1 text-xs text-muted">Kali pengambilan</div>
                            <i class="fas fa-check-circle stat-icon-bg text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CALENDAR -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card card-modern calendar-card-fix shadow-sm outline-pn">
                <div class="card-body p-3">
                    <div id="calendar"></div>
                    <div id="libur-list-container" class="mt-2" style="max-height: 60px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- KETENTUAN -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm outline-pn">
                <div class="card-header py-3 bg-white d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary" style="color: #004d00 !important;">
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
</div>

<style>
    :root {
        --pn-green: #004d00;
        --pn-gold: #F9A825;
        --soft-bg: #f8f9fc;
    }

    body { font-family: 'Poppins', sans-serif; background-color: var(--soft-bg); }
    .card-modern { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; flex-direction: column; }

    /* CALENDAR */
    #calendar { border: none !important; font-size: 0.9rem; max-height: 260px; }
    .fc-theme-standard td, .fc-theme-standard th, .fc-scrollgrid { border: none !important; }
    .fc-daygrid-day-frame { display: flex; align-items: center; justify-content: center; min-height: 15px !important; position: relative; }

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

    .libur-item { font-size: 0.7rem; padding: 5px 8px; margin-bottom: 4px; border-radius: 6px; display: flex; align-items: center; }
    .dot { width: 7px; height: 7px; border-radius: 50%; margin-right: 8px; flex-shrink: 0; }

    .calendar-card-fix { height: 360px !important; }

    .outline-pn{ border: 1px solid var(--pn-green) !important; }

    .outline-info   { box-shadow: 0 0 0 1px #36b9cc, 0 4px 12px rgba(0,0,0,.05) !important; }
    .outline-warning{ box-shadow: 0 0 0 1px var(--pn-gold), 0 4px 12px rgba(0,0,0,.05) !important; }
    .outline-brand  { box-shadow: 0 0 0 1px var(--pn-green), 0 4px 12px rgba(0,0,0,.05) !important; }
    .outline-primary{ box-shadow: 0 0 0 1px #4e73df, 0 4px 12px rgba(0,0,0,.05) !important; }

    .outline-info:hover   { box-shadow: 0 0 0 2px #36b9cc, 0 8px 15px rgba(0,0,0,.05) !important; }
    .outline-warning:hover{ box-shadow: 0 0 0 2px var(--pn-gold), 0 8px 15px rgba(0,0,0,.05) !important; }
    .outline-brand:hover  { box-shadow: 0 0 0 2px var(--pn-green), 0 8px 15px rgba(0,0,0,.05) !important; }
    .outline-primary:hover{ box-shadow: 0 0 0 2px #4e73df, 0 8px 15px rgba(0,0,0,.05) !important; }
</style>

<style>
  /* STAT CARD */
  .stat-card.py-1 {
    transition: all 0.25s ease;
    cursor: default;
    border-radius: 12px;
    overflow: hidden;
  }

  .stat-card.py-1 .card-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    position: relative;
    padding: 8px 5px !important;
  }

  .stat-card.py-1 .h2.mb-0 {
    font-size: 2.5rem !important;
    font-weight: 800 !important;
    line-height: 1 !important;
    margin: 5px 0 !important;
  }

  .stat-card.py-1 .text-xs {
    font-size: 0.9rem !important;
    font-weight: 700 !important;
    margin-bottom: 0px;
    line-height: 1.1;
  }

  .stat-card.py-1 .text-muted {
    font-size: 0.85rem !important;
    margin-top: 6px;
  }

  .stat-card.py-1 .text-xs.font-weight-bold.text-muted {
    font-size: 0.8rem !important;
    padding: 1px 4px;
    margin-top: 8px;
  }

  .stat-card.py-1 .stat-icon-bg {
    position: absolute;
    right: 10px;
    bottom: 5px;
    font-size: 1.8rem;
    opacity: 0.15;
    pointer-events: none;
  }

  .stat-card.py-1:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 10px rgba(0,0,0,0.1) !important;
  }

  @media (max-width: 768px) {
    .stat-card.py-1 { margin-bottom: 10px; }
  }

  /* HERO (DIPENDEKIN) */
  .hero-user {
    position: relative;
    overflow: hidden;
    border: none;
  }

  .hero-user::after{
    content:'';
    position:absolute;
    top:-60px;
    right:-60px;
    width:190px;
    height:190px;
    background:rgba(255,255,255,.10);
    border-radius:50%;
    z-index:0;
  }

  .hero-user::before{
    content:'';
    position:absolute;
    bottom:-40px;
    right:70px;
    width:110px;
    height:110px;
    background:rgba(249,168,37,.22);
    border-radius:50%;
    z-index:0;
  }

  .hero-user .card-body,
  .hero-user .card-body *{
    position: relative;
    z-index: 1;
  }

  /* INI KUNCI: TINGGI HERO DITURUNIN */
  .hero-compact .hero-body-compact{
    min-height: 180px;           /* sebelumnya 220px */
    display: flex;
    align-items: center;
  }

  .hero-compact .hero-title{
    font-size: 1.25rem;          /* sebelumnya 1.5rem */
    line-height: 1.15;
  }

  .hero-compact .hero-desc{
    font-size: 0.88rem;
    opacity: .92;
    line-height: 1.25;
  }

  .hero-compact .hero-btn{
    border-radius: 28px;
    color: #006837 !important;
    font-size: 0.78rem;
    padding: 7px 12px;
  }

  .hero-compact .hero-btn.hero-btn-outline{
    color: #fff !important;
  }

  .hero-compact .hero-icon{
    font-size: 5.1rem;           /* sebelumnya 6rem */
    opacity: 0.20;
  }

  /* indikator lebih rapet biar ga makan tempat */
  #heroCarousel .carousel-indicators{
    margin-bottom: 6px;
  }
  #heroCarousel .carousel-indicators li{
    width: 18px;
    height: 3px;
    border-radius: 5px;
  }
</style>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

<script>
const dataLibur = <?php echo json_encode($libur_data); ?>;

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

                    listContainer.innerHTML += `
                        <div class="libur-item" style="background:${bg}; color:${color}">
                            <div class="dot" style="background:${color}"></div>
                            <strong>${itemDate.getDate()} ${monthNames[itemDate.getMonth()]}</strong>&nbsp; ${item.title}
                        </div>
                    `;
                });
            }

            document.querySelectorAll('.fc-daygrid-day').forEach(dayEl => {
                const dateStr = dayEl.getAttribute('data-date');
                if (dateStr) {
                    const date = new Date(dateStr);
                    const holiday = dataLibur.find(item => {
                        const itemDate = new Date(item.start);
                        return itemDate.getFullYear() === date.getFullYear()
                            && itemDate.getMonth() === date.getMonth()
                            && itemDate.getDate() === date.getDate();
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
