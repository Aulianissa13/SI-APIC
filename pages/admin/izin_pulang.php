<?php
/** @var mysqli $koneksi */

$bulanIndo = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
    'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
    'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
];
$hariIni = date('d') . ' ' . $bulanIndo[date('F')] . ' ' . date('Y');

$batas   = 6;
$halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$halaman = ($halaman < 1) ? 1 : $halaman;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

$keyword = "";
$where_clause = "";

if (isset($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['cari']);
    $where_clause = " AND (
        u.nama_lengkap LIKE '%$keyword%' OR
        a.nama_lengkap LIKE '%$keyword%' OR
        i.tgl_izin LIKE '%$keyword%' OR
        i.jam_pulang LIKE '%$keyword%' OR
        i.keperluan LIKE '%$keyword%'
    )";
}

$query_count_str = "
    SELECT COUNT(i.id_izin_pulang) AS jumlah
    FROM izin_pulang i
    JOIN users u ON i.id_user = u.id_user
    LEFT JOIN users a ON i.id_atasan = a.id_user
    WHERE 1=1 $where_clause
";
$query_count = mysqli_query($koneksi, $query_count_str);
$data_count  = mysqli_fetch_assoc($query_count);
$jumlah_data = (int)($data_count['jumlah'] ?? 0);
$total_halaman = ($jumlah_data > 0) ? (int)ceil($jumlah_data / $batas) : 1;

$query_utama = "
    SELECT i.*, u.nama_lengkap AS pemohon, a.nama_lengkap AS atasan
    FROM izin_pulang i
    JOIN users u ON i.id_user = u.id_user
    LEFT JOIN users a ON i.id_atasan = a.id_user
    WHERE 1=1 $where_clause
    ORDER BY i.id_izin_pulang DESC
    LIMIT $halaman_awal, $batas
";
$q_riwayat = mysqli_query($koneksi, $query_utama);
$no = $halaman_awal + 1;
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    :root {
        --pn-green: #004d00;
        --pn-dark-green: #003300;
        --pn-gold: #F9A825;
        --pn-gold-dark: #F9A825;
        --border-color: #d1d3e2;
    }

    body { font-family: 'Poppins', sans-serif !important; background-color: #f4f6f9; }

    .flatpickr-calendar { border: none !important; box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important; }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange,
    .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange,
    .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus,
    .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus,
    .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover,
    .flatpickr-day.endRange:hover {
        background: var(--pn-green) !important;
        border-color: var(--pn-green) !important;
    }
    .flatpickr-months .flatpickr-month { background: var(--pn-green) !important; color: #fff !important; fill: #fff !important; padding: 10px 0; }
    .flatpickr-current-month .flatpickr-monthDropdown-months .flatpickr-monthDropdown-month { background-color: var(--pn-green) !important; }
    span.flatpickr-weekday { background: var(--pn-green) !important; color: #fff !important; }

    .card-header-pn{
        background-color: var(--pn-green);
        color: white;
        border-bottom: 4px solid var(--pn-gold);
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 10px 10px 0 0;
        gap: 15px;
    }

    .card-header-pn.card-header-compact{
        padding: 10px 16px;
    }

    .page-header-title {
        border-left: 5px solid var(--pn-gold);
        padding-left: 15px;
        color: var(--pn-green);
        font-weight: 700;
        font-size: 1.6rem;
    }

    .form-label-pn { font-size: 0.85rem; font-weight: 600; color: var(--pn-green); margin-bottom: 0.5rem; display: block; }
    .input-group-clean{
        display: flex; align-items: center;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background-color: #fff;
        transition: all 0.3s ease;
        overflow: hidden;
        height: 48px; width: 100%;
        position: relative;
    }
    .input-group-clean:focus-within{
        border-color: var(--pn-green);
        box-shadow: 0 0 0 4px rgba(0, 77, 0, 0.1);
        transform: translateY(-1px);
    }
    .input-icon-clean{
        width: 45px; height: 100%;
        display: flex; align-items: center; justify-content: center;
        color: var(--pn-green);
        background-color: #f8f9fc;
        border-right: 1px solid #eaecf4;
        font-size: 1rem;
        flex-shrink: 0;
    }

    /* === FIX: INPUT JANGAN CENTER, RATA KIRI === */
    .form-control-clean{
        flex: 1; border: none; height: 100%; width: 100%;
        padding: 0 15px;
        font-size: 0.95rem;
        color: #495057;
        background-color: #fff !important;
        outline: none;
        text-align: left !important; /* <--- ini */
    }
    .form-control-clean::placeholder{
        text-align: left !important; /* <--- placeholder rata kiri */
    }
    .form-control-clean:focus{ box-shadow: none; }

    /* pastiin khusus jam juga rata kiri (flatpickr kadang inject style) */
    input.flatpickr-time.form-control-clean{
        text-align: left !important;
    }
    input.flatpickr-date, input.flatpickr-time { cursor: pointer; background-color: #fff !important; }

    .input-group-clean.textarea-group { height: auto !important; align-items: stretch; }
    .input-group-clean.textarea-group .input-icon-clean { height: auto; min-height: 80px; padding-top: 0; }
    textarea.form-control-clean { padding-top: 15px; padding-bottom: 15px; line-height: 1.5; }

    .btn-pn-solid{
        background: linear-gradient(45deg, var(--pn-green), var(--pn-dark-green));
        color: white; border: none; font-weight: 600; padding: 12px 20px;
        border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.15); transition: 0.3s;
    }
    .btn-pn-solid:hover{ transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); color: var(--pn-gold); }

    .card-clean { border: none; border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); overflow: hidden; }
    .section-divider { display: flex; align-items: center; margin: 30px 0 20px 0; }
    .section-divider span { background-color: var(--pn-green); color: white; padding: 6px 15px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border-bottom: 2px solid var(--pn-gold-dark); }
    .section-divider hr { flex-grow: 1; border-top: 2px solid #e3e6f0; margin-left: 15px; }

    .table-pn-head { background-color: var(--pn-green); color: #fff; font-weight: 600; border-top: none; }
    .table-pn-head th{
        border-bottom: 3px solid var(--pn-gold) !important;
        vertical-align: middle !important;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        text-align: center;
    }
    .table tbody td{ font-size: 0.85rem !important; vertical-align: middle; }
    .table-hover tbody tr:hover{ background-color: rgba(0, 77, 0, 0.03) !important; }

    .btn-circle-action { width: 35px; height: 35px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; cursor: pointer; margin: 0 2px; }
    .btn-print { background-color: #e3f2fd; color: #0d47a1; }
    .btn-print:hover { background-color: #bbdefb; transform: scale(1.1); }

    .search-wrapper { position: relative; width: 100%; max-width: 320px; }
    .card-header-pn.card-header-compact .search-wrapper { max-width: 300px; }

    .search-input-inside{
        width: 100%;
        height: 30px;
        padding: 4px 34px 4px 14px !important;
        border-radius: 20px !important;
        border: none;
        background-color: #fff;
        transition: all 0.3s ease;
        font-size: 0.85rem;
        outline: none;
        box-sizing: border-box;
    }

    .card-header-pn.card-header-compact .search-input-inside{
        height: 28px;
        padding: 3px 32px 3px 14px !important;
        font-size: 0.82rem;
    }

    .search-input-inside:focus { box-shadow: 0 0 0 0.2rem rgba(0, 77, 0, 0.18); }
    .search-icon-inside { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #999; pointer-events: none; }

    .pagination { margin-top: 10px; }
    .pagination .page-item .page-link{
        padding: .4rem .9rem !important;
        margin-left: 6px !important;
        border-radius: 10px !important;
        border: 1px solid #e5e7eb !important;
        background: #fff !important;
        color: var(--pn-green) !important;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s ease;
    }
    .pagination .page-item.active .page-link{
        background: var(--pn-green) !important;
        color: #fff !important;
        border: 1px solid var(--pn-green) !important;
    }
    .pagination .page-item .page-link:hover{
        background: #f0fdf4 !important;
        border-color: var(--pn-green) !important;
        transform: translateY(-2px);
    }
    .pagination .page-item.disabled .page-link{
        color: #9ca3af !important;
        background: #f9fafb !important;
        border-color: #e5e7eb !important;
        transform: none;
    }
</style>

<div class="container-fluid mb-5 mt-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="page-header-title mb-2 mb-sm-0">Izin Pulang Awal</h1>
        <div class="bg-white py-2 px-3 rounded shadow-sm" style="border-left: 4px solid var(--pn-gold);">
            <span class="small font-weight-bold" style="color: var(--pn-green);">
                <i class="far fa-calendar-alt mr-2"></i><?php echo $hariIni; ?>
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card card-clean shadow-sm h-100">
                <div class="card-header-pn">
                    <div class="font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Buat Izin Baru</div>
                </div>
                <div class="card-body p-4">
                    <form action="pages/proses_izin_pulang.php" method="POST" autocomplete="off">

                        <div class="section-divider mt-0">
                            <span>A. Data Pegawai</span><hr>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Nama Pegawai <span class="text-danger">*</span></label>
                            <div class="input-group-clean">
                                <div class="input-icon-clean"><i class="fas fa-search"></i></div>
                                <input class="form-control-clean" list="list_pegawai" id="input_pegawai" placeholder="Cari nama pegawai..." required>
                            </div>
                            <datalist id="list_pegawai">
                                <?php
                                $q_all = mysqli_query($koneksi, "SELECT * FROM users ORDER BY nama_lengkap ASC");
                                while($p = mysqli_fetch_array($q_all)){
                                    echo "<option data-id='".$p['id_user']."' value='".$p['nama_lengkap']."'>";
                                }
                                ?>
                            </datalist>
                            <input type="hidden" name="id_user_pemohon" id="id_user_hidden">
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Atasan Langsung <span class="text-danger">*</span></label>
                            <div class="input-group-clean">
                                <div class="input-icon-clean"><i class="fas fa-user-tie"></i></div>
                                <input class="form-control-clean" list="list_atasan" id="input_atasan" placeholder="Cari atasan..." required>
                            </div>
                            <datalist id="list_atasan">
                                <?php
                                $q_atasan = mysqli_query($koneksi, "SELECT * FROM users WHERE is_atasan='1' ORDER BY nama_lengkap ASC");
                                while($at = mysqli_fetch_array($q_atasan)){
                                    echo "<option data-id='".$at['id_user']."' value='".$at['nama_lengkap']." (".$at['jabatan'].")'>";
                                }
                                ?>
                            </datalist>
                            <input type="hidden" name="id_atasan" id="id_atasan_hidden">
                        </div>

                        <div class="section-divider mt-4">
                            <span>B. Detail Izin</span><hr>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Tanggal Pulang <span class="text-danger">*</span></label>
                            <div class="input-group-clean">
                                <div class="input-icon-clean"><i class="far fa-calendar-alt"></i></div>
                                <input type="text" name="tgl_izin" class="form-control-clean flatpickr-date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Jam Pulang <span class="text-danger">*</span></label>
                            <div class="input-group-clean">
                                <div class="input-icon-clean"><i class="far fa-clock"></i></div>
                                <input type="text" name="jam_pulang" class="form-control-clean flatpickr-time" required placeholder="Contoh: 14:00">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-pn">Keperluan <span class="text-danger">*</span></label>
                            <div class="input-group-clean textarea-group">
                                <div class="input-icon-clean"><i class="fas fa-align-left"></i></div>
                                <textarea name="keperluan" class="form-control-clean" rows="2" placeholder="Alasan..." required></textarea>
                            </div>
                        </div>

                        <button type="submit" name="simpan_izin_pulang" class="btn btn-pn-solid btn-block py-2">
                            <i class="fas fa-save mr-2"></i> Simpan Data
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-clean shadow-sm mb-4">
                <div class="card-header-pn card-header-compact">
                    <div class="font-weight-bold">
                        <i class="fas fa-history mr-2"></i>Riwayat Izin Pulang Awal
                    </div>

                    <div class="search-wrapper">
                        <input type="text" id="keyword" class="search-input-inside"
                               placeholder="Cari pegawai / tanggal / keperluan..."
                               value="<?php echo htmlspecialchars($keyword); ?>"
                               autocomplete="off">
                        <i class="fas fa-search search-icon-inside"></i>
                    </div>
                </div>

                <div class="card-body">
                    <div id="area_tabel_admin">

                        <?php if(mysqli_num_rows($q_riwayat) == 0) { ?>
                            <div class="alert alert-light text-center border shadow-sm" style="border-radius: 12px;">
                                <h6 class="text-muted m-3"><i class="fas fa-info-circle mr-2"></i>Data tidak ditemukan.</h6>
                            </div>
                        <?php } else { ?>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead class="table-pn-head">
                                    <tr class="text-center">
                                        <th width="5%">No</th>
                                        <th>Pegawai</th>
                                        <th>Waktu & Keperluan</th>
                                        <th>Atasan Langsung</th>
                                        <th width="10%">Cetak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_array($q_riwayat)): ?>
                                    <tr>
                                        <td class="text-center font-weight-bold align-middle"><?= $no++; ?></td>

                                        <td class="align-middle">
                                            <div class="font-weight-bold text-dark"><?= $row['pemohon']; ?></div>
                                            <!-- HAPUS atasan di bawah nama pemohon (sesuai request) -->
                                        </td>

<td class="align-middle text-center">

    <div class="font-weight-bold text-primary" style="font-size: 0.9rem;">
        <?= date('d/m/Y', strtotime($row['tgl_izin'])); ?>
    </div>

    <div class="font-weight-bold text-dark" style="font-size: 0.86rem; margin-top: 2px;">
        <?= date('H:i', strtotime($row['jam_pulang'])); ?> WIB
    </div>

    <div class="text-muted font-italic small mt-1" style="line-height: 1.25;">
        "<?= $row['keperluan']; ?>"
    </div>

</td>

                                        <td class="align-middle">
                                            <?= $row['atasan'] ? $row['atasan'] : '<span class="text-muted">-</span>'; ?>
                                        </td>

                                        <td class="text-center align-middle">
                                            <a href="pages/cetak_izin_pulang.php?id=<?= $row['id_izin_pulang']; ?>" target="_blank"
                                               class="btn-circle-action btn-print shadow-sm" title="Cetak Surat">
                                                <i class="fas fa-print fa-sm"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                            <div class="text-muted small mb-2 mb-md-0">
                                Halaman <?php echo $halaman; ?> dari <?php echo $total_halaman; ?>. total: <?php echo $jumlah_data; ?> data
                            </div>

                            <nav>
                                <ul class="pagination mb-0">
                                    <li class="page-item <?php if($halaman <= 1) echo 'disabled'; ?>">
                                        <a class="page-link" href="<?php if($halaman > 1){ echo "?page=izin_pulang&hal=".($halaman-1)."&cari=".urlencode($keyword); } ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>

                                    <?php for($x = 1; $x <= $total_halaman; $x++): ?>
                                        <li class="page-item <?php if($halaman == $x) echo 'active'; ?>">
                                            <a class="page-link" href="?page=izin_pulang&hal=<?php echo $x; ?>&cari=<?php echo urlencode($keyword); ?>">
                                                <?php echo $x; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php if($halaman >= $total_halaman) echo 'disabled'; ?>">
                                        <a class="page-link" href="<?php if($halaman < $total_halaman){ echo "?page=izin_pulang&hal=".($halaman+1)."&cari=".urlencode($keyword); } ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>

                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<script>
    function setupAutocomplete(inputId, listId, hiddenId) {
        const inputEl = document.getElementById(inputId);
        const hiddenEl = document.getElementById(hiddenId);

        if(inputEl) {
            inputEl.addEventListener('input', function() {
                var inputVal = this.value;
                var listOptions = document.querySelectorAll('#' + listId + ' option');
                hiddenEl.value = "";
                for (var i = 0; i < listOptions.length; i++) {
                    if (listOptions[i].value === inputVal) {
                        hiddenEl.value = listOptions[i].getAttribute('data-id');
                        break;
                    }
                }
            });

            inputEl.addEventListener('change', function() {
                if(hiddenEl.value === "") {
                    var inputVal = this.value;
                    var listOptions = document.querySelectorAll('#' + listId + ' option');
                    for (var i = 0; i < listOptions.length; i++) {
                        if (listOptions[i].value === inputVal) {
                            hiddenEl.value = listOptions[i].getAttribute('data-id');
                            break;
                        }
                    }
                }
            });
        }
    }
    setupAutocomplete('input_pegawai', 'list_pegawai', 'id_user_hidden');
    setupAutocomplete('input_atasan', 'list_atasan', 'id_atasan_hidden');

    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            locale: "id",
            allowInput: false,
            disableMobile: "true"
        });

        flatpickr(".flatpickr-time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minTime: "08:00",
            maxTime: "17:00",
            allowInput: false
        });
    });

    $(document).ready(function() {
        $('#keyword').on('keyup', function() {
            var keyword = $(this).val();
            $('#area_tabel_admin').load(
                'index.php?page=izin_pulang&cari=' + encodeURIComponent(keyword) + ' #area_tabel_admin'
            );
        });
    });
</script>
