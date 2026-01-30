<?php
// =========================================================
// 1. CONFIG PAGINATION & SEARCH
// =========================================================

$batas   = 10; 
$halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// Setup Pencarian
$keyword = "";
$where_clause = ""; 

if (isset($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['cari']);
    // Cari berdasarkan Nama, Tanggal, atau Status
    $where_clause = " AND (users.nama_lengkap LIKE '%$keyword%' OR pengajuan_cuti.tgl_pengajuan LIKE '%$keyword%' OR pengajuan_cuti.status LIKE '%$keyword%')";
}

// Hitung Total Data (Untuk Pagination)
$query_count_str = "SELECT count(id_pengajuan) as jumlah 
                    FROM pengajuan_cuti 
                    JOIN users ON pengajuan_cuti.id_user = users.id_user 
                    WHERE 1=1 $where_clause";
$query_count = mysqli_query($koneksi, $query_count_str);
$data_count  = mysqli_fetch_assoc($query_count);
$jumlah_data = $data_count['jumlah'];
$total_halaman = ceil($jumlah_data / $batas);

// Query Data Utama
$query_utama = "SELECT pengajuan_cuti.*, 
                       users.nama_lengkap, users.nip, 
                       users.sisa_cuti_n, users.sisa_cuti_n1, users.kuota_cuti_sakit, 
                       jenis_cuti.nama_jenis 
                FROM pengajuan_cuti 
                JOIN users ON pengajuan_cuti.id_user = users.id_user 
                JOIN jenis_cuti ON pengajuan_cuti.id_jenis = jenis_cuti.id_jenis 
                WHERE 1=1 $where_clause
                ORDER BY pengajuan_cuti.tgl_pengajuan DESC, pengajuan_cuti.id_pengajuan DESC 
                LIMIT $halaman_awal, $batas";

$query = mysqli_query($koneksi, $query_utama);
$nomor = $halaman_awal + 1;
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* STYLE UTAMA HALAMAN */
    :root { --pn-green: #004d00; --pn-gold: #FFD700; }
    .card-pn { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); overflow: hidden; }
    .card-header-pn { background: linear-gradient(135deg, var(--pn-green) 0%, #006400 100%); color: white; border-bottom: 4px solid var(--pn-gold); padding: 15px 20px; }
    .badge-status { border-radius: 10px; padding: 8px 12px; font-weight: 600; }
    .thead-pn { background-color: var(--pn-green); color: white; }
    .page-title-pn { font-weight: 700; border-left: 5px solid var(--pn-gold); padding-left: 15px; color: var(--pn-green) !important; }
    .page-item.active .page-link { background-color: var(--pn-green); border-color: var(--pn-green); color: white; }
    .page-link { color: var(--pn-green); }

    /* STYLE SEARCH BAR COSTUM */
    .search-wrapper {
        position: relative;
        width: 100%;
        max-width: 300px;
    }
    
    .search-input-inside {
        width: 100%;
        padding-right: 40px !important;
        padding-left: 15px !important;
        border-radius: 50px !important;
        border: 1px solid #ddd;
        background-color: #f8f9fc;
        transition: all 0.3s ease;
        height: 38px;
    }

    .search-input-inside:focus {
        background-color: #fff;
        border-color: #006837;
        box-shadow: 0 0 0 0.2rem rgba(0, 104, 55, 0.25);
        outline: none;
    }

    .search-icon-inside {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
        pointer-events: none;
    }
</style>

<div class="container-fluid mb-5">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <h1 class="h3 mb-0 text-gray-800 page-title-pn">Validasi Permohonan Cuti</h1>
    </div>

    <div class="card card-pn mb-4">
        <div class="card-header-pn d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="mb-2 mb-md-0">
                <i class="fas fa-check-double mr-2"></i>
                <h6 class="d-inline m-0 font-weight-bold text-white">Daftar Pengajuan Masuk</h6>
            </div>
            
            <div class="search-wrapper">
                <input type="text" id="keyword" class="search-input-inside" 
                       placeholder="Cari Nama / Tanggal..." 
                       value="<?php echo $keyword; ?>" 
                       autocomplete="off">
                <i class="fas fa-search search-icon-inside"></i>
            </div>

        </div>

        <div class="card-body">
            
            <div id="area_tabel">
                <?php if(mysqli_num_rows($query) == 0) { ?>
                    <div class="alert alert-info text-center" style="border-radius: 15px; border-left: 5px solid #36b9cc;">
                        <i class="fas fa-info-circle mr-2"></i>Data tidak ditemukan.
                    </div>
                <?php } else { ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-pn">
                            <tr class="text-center">
                                <th width="5%" style="vertical-align: middle;">No</th>
                                <th style="vertical-align: middle;">Pegawai</th>
                                <th style="vertical-align: middle;">Jenis Cuti</th>
                                <th style="vertical-align: middle;">Detail Pengajuan</th>
                                
                                <th width="5%" style="vertical-align: middle;">Sisa<br>Tahun Ini</th>
                                <th width="5%" style="vertical-align: middle;">Sisa<br>Tahun Lalu</th>
                                <th width="5%" style="vertical-align: middle;">Sisa<br>Sakit</th>
                                
                                <th style="vertical-align: middle;">Status</th>
                                <th width="15%" style="vertical-align: middle;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            while($row = mysqli_fetch_array($query)){ 
                                $raw_status = strtolower(trim($row['status'])); 
                                $bg_row = "";

                                if ($raw_status == 'disetujui') {
                                    $badge = '<span class="badge badge-success badge-status"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>';
                                } elseif ($raw_status == 'ditolak') {
                                    $badge = '<span class="badge badge-danger badge-status"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>';
                                    $bg_row = "style='background-color: #ffecec;'"; 
                                } else {
                                    $badge = '<span class="badge badge-warning badge-status"><i class="fas fa-clock mr-1"></i>Menunggu</span>';
                                    $bg_row = "style='background-color: #fff3cd;'"; 
                                }
                            ?>
                            <tr <?php echo $bg_row; ?>>
                                <td class="text-center font-weight-bold" style="vertical-align: middle;"><?php echo $nomor++; ?></td>
                                <td style="vertical-align: middle;">
                                    <div class="font-weight-bold text-dark" style="font-size: 1.05rem;"><?php echo $row['nama_lengkap']; ?></div>
                                    <small class="text-muted"><i class="fas fa-id-badge mr-1"></i>NIP: <?php echo $row['nip']; ?></small>
                                </td>
                                <td style="vertical-align: middle;"><?php echo $row['nama_jenis']; ?></td>
                                <td style="vertical-align: middle;">
                                    <div class="font-weight-bold text-primary"><?php echo $row['lama_hari']; ?> Hari</div>
                                    <small class="text-dark">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        <?php echo date('d M', strtotime($row['tgl_mulai'])); ?> s/d 
                                        <?php echo date('d M Y', strtotime($row['tgl_selesai'])); ?>
                                    </small>
                                    <br>
                                    <small class="text-muted" style="font-size: 0.8rem;">
                                        <i>Diajukan: <?php echo date('d M Y', strtotime($row['tgl_pengajuan'])); ?></i>
                                    </small>
                                </td>
                                
                                <td class="text-center font-weight-bold text-dark" style="vertical-align: middle; background-color: #f8f9fc;" title="Sisa Tahun Ini">
                                    <?php echo $row['sisa_cuti_n']; ?>
                                </td>
                                <td class="text-center font-weight-bold text-secondary" style="vertical-align: middle; background-color: #f1f3f9;" title="Sisa Tahun Lalu">
                                    <?php echo $row['sisa_cuti_n1']; ?>
                                </td>
                                <td class="text-center font-weight-bold text-dark" style="vertical-align: middle; background-color: #f8f9fc;" title="Sisa Cuti Sakit">
                                    <?php echo $row['kuota_cuti_sakit']; ?>
                                </td>

                                <td class="text-center" style="vertical-align: middle;"><?php echo $badge; ?></td>
                                
                                <td class="text-center" style="vertical-align: middle;">
                                    <a href="pages/admin/cetak_cuti_admin.php?id=<?php echo $row['id_pengajuan']; ?>" target="_blank" class="btn btn-info btn-circle btn-sm shadow-sm" title="Cetak Surat">
                                        <i class="fas fa-print"></i>
                                    </a>

                                    <?php if(strpos($raw_status, 'menunggu') !== false || $raw_status == 'diajukan' || $raw_status == '') { ?>
                                        <span class="mx-1">|</span>
                                        <button class="btn btn-success btn-circle btn-sm shadow-sm" 
                                                onclick="konfirmasiValidasi('setuju', <?php echo $row['id_pengajuan']; ?>, '<?php echo $row['nama_lengkap']; ?>')" 
                                                title="Setujui">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        
                                        <button class="btn btn-danger btn-circle btn-sm shadow-sm" 
                                                onclick="konfirmasiValidasi('tolak', <?php echo $row['id_pengajuan']; ?>, '<?php echo $row['nama_lengkap']; ?>')" 
                                                title="Tolak">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <nav>
                        <ul class="pagination">
                            <li class="page-item <?php if($halaman <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="<?php if($halaman > 1){ echo "?page=validasi_cuti&hal=".($halaman-1)."&cari=$keyword"; } ?>">Previous</a>
                            </li>
                            <?php for($x = 1; $x <= $total_halaman; $x++): ?>
                                <li class="page-item <?php if($halaman == $x) echo 'active'; ?>">
                                    <a class="page-link" href="?page=validasi_cuti&hal=<?php echo $x; ?>&cari=<?php echo $keyword; ?>"><?php echo $x; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php if($halaman >= $total_halaman) echo 'disabled'; ?>">
                                <a class="page-link" href="<?php if($halaman < $total_halaman){ echo "?page=validasi_cuti&hal=".($halaman+1)."&cari=$keyword"; } ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php } ?>
            </div> </div>
    </div>
</div>

<script>
    // 1. LIVE SEARCH (JQUERY .load)
    $(document).ready(function() {
        $('#keyword').on('keyup', function() {
            var keyword = $(this).val();
            
            // Load ulang #area_tabel dengan parameter pencarian baru
            $('#area_tabel').load('index.php?page=validasi_cuti&cari=' + encodeURIComponent(keyword) + ' #area_tabel', function() {
                // Callback function jika diperlukan
            });
        });
    });

    // 2. SWEETALERT NOTIFIKASI SESSION
    <?php if (isset($_SESSION['swal'])) { ?>
        Swal.fire({
            icon: '<?php echo $_SESSION['swal']['icon']; ?>',
            title: '<?php echo $_SESSION['swal']['title']; ?>',
            text: '<?php echo $_SESSION['swal']['text']; ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['swal']); ?>
    <?php } ?>

    // 3. FUNGSI KONFIRMASI TOMBOL
    function konfirmasiValidasi(aksi, id, nama) {
        let judul, teks, warnaTombol, textTombol;

        if(aksi === 'setuju') {
            judul = 'Setujui Pengajuan?';
            teks = 'Pengajuan cuti pegawai ' + nama + ' akan disetujui.';
            warnaTombol = '#28a745'; 
            textTombol = 'Ya, Setujui!';
        } else {
            judul = 'Tolak Pengajuan?';
            teks = 'Pengajuan cuti ' + nama + ' akan DITOLAK dan kuota dikembalikan.';
            warnaTombol = '#d33';
            textTombol = 'Ya, Tolak!';
        }

        Swal.fire({
            title: judul,
            text: teks,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: warnaTombol,
            cancelButtonColor: '#6c757d',
            confirmButtonText: textTombol,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect ke file proses
                window.location.href = 'pages/admin/proses_validasi.php?aksi=' + aksi + '&id=' + id;
            }
        });
    }
</script>