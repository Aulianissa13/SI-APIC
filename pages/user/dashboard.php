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

// 3. HITUNG STATUS PENGAJUAN
$query_menunggu = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_user='$id_user' AND status='diajukan'");
$jml_menunggu = mysqli_num_rows($query_menunggu);

$query_setuju = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_user='$id_user' AND status='Disetujui' AND YEAR(tgl_mulai)='$thn_skrg'");
$jml_setuju = mysqli_num_rows($query_setuju);

// Ambil Data Hari Libur
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
// Filter hanya untuk tahun 2026
$libur_data = array_filter($libur_data, function($item) {
    $item_year = date('Y', strtotime($item['start']));
    return $item_year == 2026;
});
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card text-white shadow mb-3" style="background: linear-gradient(90deg, #006837 0%, #43a047 100%); border-radius: 20px; border:none;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="font-weight-bold mb-2" style="font-size: 1.5rem;">Hai, <?php echo $nama_lengkap; ?>! 👋</h2>
                            <p class="mb-3" style="opacity: 0.9;">Selamat datang di Dashboard Pegawai. Jaga kesehatan agar tetap produktif melayani masyarakat.</p>
                            <a href="index.php?page=form_cuti" class="btn btn-light font-weight-bold shadow-sm" style="border-radius: 30px; color: #006837 !important; font-size: 0.8rem;"><i class="fas fa-plus mr-1"></i> Ajukan Cuti Baru</a>
                        </div>
                        <div class="col-md-4 d-none d-md-block text-right">
                            <i class="fas fa-calendar-alt" style="font-size: 6rem; opacity: 0.2;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card py-2 border-left-success shadow-sm h-100" style="border-left: 5px solid #006837 !important;">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sisa Tahunan</div>
                            <div class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $total_tahunan; ?><span style="font-size:1rem"> H</span></div>
                            <div class="mt-2 text-xs font-weight-bold text-muted" style="background: #f8f9fc; padding: 4px; border-radius: 5px;">
                                <?php echo substr($thn_skrg, 2); ?>:<b><?php echo $n; ?></b> | <?php echo substr($thn_min1, 2); ?>:<b><?php echo $n1; ?></b> | <?php echo substr($thn_min2, 2); ?>:<b><?php echo $n2; ?></b>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card py-2 border-left-info shadow-sm h-100" style="border-left: 5px solid #36b9cc !important;">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Sisa Cuti Sakit</div>
                            <div class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $sisa_sakit; ?><span style="font-size:1rem"> H</span></div>
                            <div class="mt-2 text-xs text-muted">Sisa saat ini</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card py-2 border-left-warning shadow-sm h-100" style="border-left: 5px solid #F9A825 !important;">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sedang Diproses</div>
                            <div class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $jml_menunggu; ?></div>
                            <div class="mt-2 text-xs text-muted">Menunggu approval</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card py-2 border-left-primary shadow-sm h-100" style="border-left: 5px solid #4e73df !important;">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Disetujui</div>
                            <div class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $jml_setuju; ?></div>
                            <div class="mt-2 text-xs text-muted">Kali pengambilan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card card-modern calendar-card-fix shadow-sm">
                <div class="card-body p-3">
                    <div id="calendar"></div>
                    <div id="libur-list-container" class="mt-2" style="max-height: 60px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
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
</div>

<style>
    :root {
        --pn-green: #006837;
        --pn-gold: #F9A825;
        --soft-bg: #f8f9fc;
    }
    
    body { font-family: 'Poppins', sans-serif; background-color: var(--soft-bg); }
    .card-modern { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; flex-direction: column; }
    
    /* STYLE KALENDER ASLI KAMU */
    #calendar { border: none !important; font-size: 0.9rem; max-height: 250px; }
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

    /* Ukuran Kalender disesuaikan agar sejajar dengan Kotak Info */
    .calendar-card-fix { height: calc(232px + 140px + 15px) !important; }
</style>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

<script>
// SCRIPT KALENDER ASLI KAMU
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

            let filtered = [];
            if (currentYearInView === 2026) {
                filtered = dataLibur.filter(item => {
                    const itemDate = new Date(item.start);
                    return itemDate.getFullYear() === 2026 && itemDate.getMonth() === currentMonth;
                }).sort((a,b) => new Date(a.start) - new Date(b.start));
            }

            if(filtered.length === 0) {
                listContainer.innerHTML = '<div class="text-center text-muted small py-2">Tidak ada libur</div>';
            } else {
                filtered.forEach(item => {
                    const isNasional = item.jenis.includes('nasional');
                    const color = isNasional ? '#d9534f' : '#856404';
                    const bg = isNasional ? '#ffcccb' : '#fff3cd';
                    const itemDate = new Date(item.start);
                    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    listContainer.innerHTML += `<div class="libur-item" style="background:${bg}; color:${color}"><div class="dot" style="background:${color}"></div><strong>${itemDate.getDate()} ${monthNames[itemDate.getMonth()]}</strong>&nbsp; ${item.title}</div>`;
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
</script>n