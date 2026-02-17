<?php
/** @var mysqli $koneksi */

// --- 1. SETUP LOGIKA TANGGAL (RANGE) ---
$tgl_awal_pilih  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir_pilih = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// --- 2. LOGIKA PAGINATION ---
// Ambil parameter menu utama (agar tidak hilang saat pindah halaman)
$page_url = isset($_GET['page']) ? $_GET['page'] : ''; 

$limit = 10; // Jumlah data per halaman

// Ganti nama variabel pagination dari 'page' ke 'hal' untuk menghindari konflik
$halaman_aktif = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$halaman_aktif = ($halaman_aktif < 1) ? 1 : $halaman_aktif;
$start = ($halaman_aktif - 1) * $limit;

// A. Hitung Total Data (Tanpa Limit)
$query_count = "SELECT COUNT(*) as total 
                FROM pengajuan_cuti p
                WHERE p.status = 'Disetujui'
                  AND (p.tgl_mulai BETWEEN '$tgl_awal_pilih' AND '$tgl_akhir_pilih')";
$result_count = mysqli_query($koneksi, $query_count);
$row_count    = mysqli_fetch_assoc($result_count);
$total_data   = $row_count['total'];
$total_pages  = ceil($total_data / $limit);

// B. Query Data Utama (Dengan Limit)
$query_range = "SELECT p.*, u.nama_lengkap, u.nip, j.nama_jenis
                FROM pengajuan_cuti p
                JOIN users u ON p.id_user = u.id_user
                JOIN jenis_cuti j ON p.id_jenis = j.id_jenis
                WHERE p.status = 'Disetujui'
                  AND (p.tgl_mulai BETWEEN '$tgl_awal_pilih' AND '$tgl_akhir_pilih')
                ORDER BY p.tgl_mulai ASC, p.created_at ASC
                LIMIT $start, $limit";

$result_range = mysqli_query($koneksi, $query_range);
$jumlah_data_tampil = mysqli_num_rows($result_range); // Data di halaman ini saja

// --- 3. LOGIKA BULANAN (UNTUK CARD ATAS) ---
$nama_bulan = [
  '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
  '04' => 'April',   '05' => 'Mei',      '06' => 'Juni',
  '07' => 'Juli',    '08' => 'Agustus',  '09' => 'September',
  '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { --pn-green: #004d00; --pn-gold: #F9A825; --text-dark: #2c3e50; }
    body { font-family: 'Poppins', sans-serif !important; background-color: #f4f6f9; }
    
    .page-header-title { border-left: 5px solid var(--pn-gold); padding-left: 15px; color: var(--pn-green) !important; font-weight: 700; font-size: 1.6rem; }
    .card-pn-custom { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); background: #fff; overflow: hidden; margin-bottom: 30px; }
    .card-header-green { background-color: #1b5e20; color: white; padding: 15px 25px; border-bottom: 4px solid var(--pn-gold); display: flex; justify-content: space-between; align-items: center; }
    
    /* Tombol Tampilkan (Outline) */
    .btn-pn-outline {
        background-color: transparent; color: var(--pn-green); border: 2px solid var(--pn-green); font-weight: 600; border-radius: 8px; padding: 8px 15px; height: 44px; width: 100%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;
    }
    .btn-pn-outline:hover { background-color: var(--pn-green); color: white; }

    /* Tombol Export (Solid) */
    .btn-pn-solid {
        background-color: var(--pn-green); color: white; border: 2px solid var(--pn-green); font-weight: 600; border-radius: 8px; padding: 8px 15px; height: 44px; width: 100%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;
    }
    .btn-pn-solid:hover { background-color: #003800; border-color: #003800; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 77, 0, 0.3); color: var(--pn-gold); }

    .form-control-pn { border-radius: 8px; height: 44px; border: 1px solid #ced4da; font-size: 0.95rem; }
    .form-control-pn:focus { border-color: var(--pn-green); box-shadow: 0 0 0 0.2rem rgba(0, 77, 0, 0.25); }
    label { font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }

    /* Table Styles */
    .thead-pn { background-color: var(--pn-green); color: white; }
    .thead-pn th { padding: 12px 15px; border-bottom: 3px solid var(--pn-gold) !important; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; vertical-align: middle !important; border-top: none; white-space: nowrap; }
    .table-custom tbody tr:hover { background-color: #f1f8e9; }
    .table-custom td { padding: 12px 15px; vertical-align: middle !important; border-bottom: 1px solid #eee; font-size: 0.9rem; color: #444; }
    .badge-jenis-cuti { background-color: #e9f5e9; color: var(--pn-green); border: 1px solid #c3e6cb; padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
    
    .divider-soft { height: 1px; background: #e9ecef; margin: 20px 0; }

    /* --- PAGINATION CUSTOM STYLE (REQUESTED) --- */
    .pagination { margin-top: 10px; justify-content: end; } /* justify-content: end agar di kanan */
    .pagination .page-item .page-link {
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
    .pagination .page-item.active .page-link {
        background: var(--pn-green) !important;
        color: #fff !important;
        border: 1px solid var(--pn-green) !important;
    }
    .pagination .page-item .page-link:hover {
        background: #f0fdf4 !important;
        border-color: var(--pn-green) !important;
        transform: translateY(-2px);
    }
    .pagination .page-item.disabled .page-link {
        color: #9ca3af !important;
        background: #f9fafb !important;
        border-color: #e5e7eb !important;
        transform: none;
        cursor: not-allowed;
    }
</style>

<div class="container-fluid mb-5">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <h1 class="h3 mb-0 page-header-title">Laporan Rekapitulasi Cuti</h1>
    </div>

    <div class="card card-pn-custom">
        <div class="card-header-green">
            <div><i class="fas fa-file-alt mr-2"></i><span class="font-weight-bold">Export Laporan Bulanan</span></div>
        </div>
        <div class="card-body p-4">
            <form id="formExportBulanan" action="pages/admin/proses_export_excel.php" method="GET" target="_blank">
                <input type="hidden" name="tipe_laporan" value="bulanan">
                <div class="form-row align-items-end">
                    <div class="col-md-3 mb-3">
                        <label>Bulan</label>
                        <select name="bulan" id="bulan" class="form-control form-control-pn">
                            <?php foreach ($nama_bulan as $kode => $nama) {
                                $sel = ($kode == date('m')) ? 'selected' : ''; echo "<option value='$kode' $sel>$nama</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>Tahun</label>
                        <select name="tahun" id="tahun" class="form-control form-control-pn">
                            <?php for ($i = 2026; $i <= 2030; $i++) {
                                $sel = ($i == date('Y')) ? 'selected' : ''; echo "<option value='$i' $sel>$i</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Jenis Laporan</label>
                        <select name="id_jenis" id="id_jenis" class="form-control form-control-pn">
                            <option value="1">Rekap Cuti Tahunan</option>
                            <option value="2">Rekap Cuti Sakit</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button type="submit" class="btn-pn-solid"><i class="fas fa-file-excel mr-2"></i>Export</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-pn-custom">
        <div class="card-header-green">
            <div><i class="fas fa-calendar-week mr-2"></i><span class="font-weight-bold">Export Cuti Per Periode</span></div>
        </div>
        <div class="card-body p-4">
            
            <form method="GET" action="">
                <input type="hidden" name="page" value="<?= $page_url ?>">
                
                <input type="hidden" name="hal" value="1">

                <div class="form-row align-items-end">
                    <div class="col-md-4 mb-3">
                        <label>Dari Tanggal</label>
                        <input type="date" name="tgl_awal" id="filter_tgl_awal" class="form-control form-control-pn" value="<?= $tgl_awal_pilih ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="tgl_akhir" id="filter_tgl_akhir" class="form-control form-control-pn" value="<?= $tgl_akhir_pilih ?>" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn-pn-outline">
                            <i class="fas fa-search mr-2"></i>Tampilkan
                        </button>
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="button" onclick="downloadExcelRange()" class="btn-pn-solid">
                            <i class="fas fa-file-excel mr-2"></i>Export
                        </button>
                    </div>
                </div>
            </form>

            <div class="divider-soft"></div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div style="color:var(--pn-green); font-weight:bold;">
                    <i class="fas fa-list-alt mr-2"></i>
                    Hasil Filter: <?= date('d M Y', strtotime($tgl_awal_pilih)) ?> s/d <?= date('d M Y', strtotime($tgl_akhir_pilih)) ?>
                </div>
                <span class="badge badge-success px-3 py-2">Total: <?= $total_data ?> Data</span>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead class="thead-pn">
                        <tr class="text-center">
                            <th width="5%">No</th>
                            <th>Pegawai</th> 
                            <th width="15%">Jenis Cuti</th>
                            <th width="15%">Tanggal Mulai</th>
                            <th width="15%">Tanggal Selesai</th>
                            <th width="10%">Lama</th>
                            <th width="15%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Sesuaikan nomor urut dengan halaman
                        $no = $start + 1;
                        if ($jumlah_data_tampil > 0) {
                            while ($row = mysqli_fetch_assoc($result_range)) {
                                // Hitung hari jika lama_hari kosong/0
                                $total_hari = isset($row['lama_hari']) ? (int)$row['lama_hari'] : 0;
                                if ($total_hari <= 0) {
                                    $tgl1 = new DateTime($row['tgl_mulai']);
                                    $tgl2 = new DateTime($row['tgl_selesai']);
                                    $total_hari = $tgl2->diff($tgl1)->days + 1;
                                }
                        ?>
                        <tr class="align-middle">
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td>
                                <div class="font-weight-bold text-dark"><?php echo $row['nama_lengkap']; ?></div>
                                <div class="small text-muted">NIP: <?php echo $row['nip']; ?></div>
                            </td>
                            <td class="text-center"><span class="badge-jenis-cuti"><?php echo $row['nama_jenis']; ?></span></td>
                            <td class="text-center"><?php echo date('d-m-Y', strtotime($row['tgl_mulai'])); ?></td>
                            <td class="text-center"><?php echo date('d-m-Y', strtotime($row['tgl_selesai'])); ?></td>
                            <td class="text-center font-weight-bold text-pn"><?php echo $total_hari; ?> Hari</td>
                            <td class="text-center">
                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <img src="https://img.icons8.com/ios/100/cccccc/empty-box.png" width="60" class="mb-3 opacity-50"/><br>
                                    Tidak ada data cuti disetujui pada periode ini.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination">
                    
                    <li class="page-item <?php echo ($halaman_aktif <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo ($halaman_aktif > 1) ? "?page=$page_url&hal=".($halaman_aktif - 1)."&tgl_awal=$tgl_awal_pilih&tgl_akhir=$tgl_akhir_pilih" : '#'; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <?php 
                    $start_number = ($halaman_aktif > 2) ? $halaman_aktif - 2 : 1;
                    $end_number   = ($halaman_aktif < ($total_pages - 2)) ? $halaman_aktif + 2 : $total_pages;
                    
                    if($start_number > 1) { 
                        echo '<li class="page-item"><a class="page-link" href="?page='.$page_url.'&hal=1&tgl_awal='.$tgl_awal_pilih.'&tgl_akhir='.$tgl_akhir_pilih.'">1</a></li>';
                        if($start_number > 2) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; }
                    }

                    for ($i = $start_number; $i <= $end_number; $i++): 
                    ?>
                        <li class="page-item <?php echo ($halaman_aktif == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page_url; ?>&hal=<?php echo $i; ?>&tgl_awal=<?php echo $tgl_awal_pilih; ?>&tgl_akhir=<?php echo $tgl_akhir_pilih; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; 

                    if($end_number < $total_pages) {
                        if($end_number < $total_pages - 1) { echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; }
                        echo '<li class="page-item"><a class="page-link" href="?page='.$page_url.'&hal='.$total_pages.'&tgl_awal='.$tgl_awal_pilih.'&tgl_akhir='.$tgl_akhir_pilih.'">'.$total_pages.'</a></li>';
                    }
                    ?>

                    <li class="page-item <?php echo ($halaman_aktif >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo ($halaman_aktif < $total_pages) ? "?page=$page_url&hal=".($halaman_aktif + 1)."&tgl_awal=$tgl_awal_pilih&tgl_akhir=$tgl_akhir_pilih" : '#'; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>

                </ul>
            </nav>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<script>
    // 1. Script untuk Form Export Bulanan (Card Atas)
    document.getElementById('formExportBulanan').addEventListener('submit', function(e){
        // ... (Script sweetalert sama seperti sebelumnya) ...
    });

    // 2. Fungsi Khusus Download Excel Range (Card Bawah)
    function downloadExcelRange() {
        const tglAwal = document.getElementById('filter_tgl_awal').value;
        const tglAkhir = document.getElementById('filter_tgl_akhir').value;

        if(tglAwal > tglAkhir){
            Swal.fire({icon: 'error', title: 'Error', text: 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.'});
            return;
        }

        Swal.fire({
            title: 'Download Excel?',
            text: 'Data cuti periode terpilih akan diunduh.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#004d00',
            confirmButtonText: 'Ya, Unduh',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect ke file proses export dengan parameter tanggal
                window.open(`pages/admin/proses_export_excel.php?tipe_laporan=harian&tgl_awal=${tglAwal}&tgl_akhir=${tglAkhir}`, '_blank');
            }
        });
    }
</script>