<?php
/** @var mysqli $koneksi */

$batas   = 10; 
$halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;
$keyword = "";
$where_clause = ""; 

if (isset($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['cari']);
    $where_clause = " AND (users.nama_lengkap LIKE '%$keyword%' OR pengajuan_cuti.tgl_pengajuan LIKE '%$keyword%' OR pengajuan_cuti.status LIKE '%$keyword%')";
}


$query_count_str = "SELECT count(id_pengajuan) as jumlah 
                    FROM pengajuan_cuti 
                    JOIN users ON pengajuan_cuti.id_user = users.id_user 
                    WHERE 1=1 $where_clause";
$query_count = mysqli_query($koneksi, $query_count_str);
$data_count  = mysqli_fetch_assoc($query_count);
$jumlah_data = $data_count['jumlah'];
$total_halaman = ceil($jumlah_data / $batas);


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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root { 
        --pn-green: #004d00; 
        --pn-gold: #F9A825; 
        --text-dark: #333;
    }
    
    body { font-family: 'Poppins', sans-serif; }

    .card-pn { 
        border: none; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        background-color: #fff;
        overflow: hidden; 
    }
    .card-header-pn { 
        background: linear-gradient(135deg, #004d00 0%, #003300 100%); 
        color: white; 
        border-bottom: 4px solid var(--pn-gold); 
        padding: 18px 25px; 
    }

    .page-title-pn { 
        font-weight: 700; 
        border-left: 5px solid var(--pn-gold); 
        padding-left: 15px; 
        color: var(--pn-green) !important; 
        font-size: 1.5rem;
    }

    .table-pn-head {
        background-color: var(--pn-green);
        color: #fff;
        font-weight: 600;
        border-top: none;
    }
    .table-pn-head th {
        border-bottom: 3px solid var(--pn-gold) !important;
        vertical-align: middle !important;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 77, 0, 0.03) !important;
    }
    
    .badge-status { 
        border-radius: 50px;
        padding: 6px 12px; 
        font-weight: 600; 
        font-size: 12px; 
        letter-spacing: 0.5px; 
    }

    /* --- STYLE BARU UNTUK JENIS CUTI --- */
    .badge-jenis-cuti {
        background-color: #e9f5e9;
        color: var(--pn-green);
        border: 1px solid #c3e6cb;
        padding: 5px 12px;
        border-radius: 50px; 
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        display: inline-block;
        white-space: nowrap; 
    }
    
    .btn-circle-action { width: 35px; height: 35px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; cursor: pointer; }
    .btn-print { background-color: #e3f2fd; color: #0d47a1; }
    .btn-print:hover { background-color: #bbdefb; transform: scale(1.1); }
    .btn-approve { background-color: #e8f5e9; color: #2e7d32; }
    .btn-approve:hover { background-color: #c8e6c9; transform: scale(1.1); }
    .btn-delete { background-color: #ffebee; color: #c62828; }
    .btn-delete:hover { background-color: #ffcdd2; transform: scale(1.1); }
    .pagination { margin-top: 10px; }
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
    }

    .search-wrapper { position: relative; width: 100%; max-width: 300px; }
    .search-input-inside {
        width: 100%;
        padding: 6px 35px 6px 15px !important;
        border-radius: 20px !important;
        border: none;
        background-color: #fff;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        outline: none;
    }
    .search-input-inside:focus {
        background-color: #fff;
        box-shadow: 0 0 0 0.2rem rgba(0, 77, 0, 0.18);
        outline: none;
    }
    .search-icon-inside { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #999; pointer-events: none; }
</style>

<div class="container-fluid mb-5 mt-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="page-title-pn mb-0">Validasi Permohonan Cuti</h1>
    </div>

    <div class="card card-pn mb-4">
        <div class="card-header-pn d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="mb-2 mb-md-0">
                <i class="fas fa-check-double mr-2"></i>
                <span class="font-weight-bold" style="font-size: 1.1rem;">Daftar Pengajuan Masuk</span>
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
                    <div class="alert alert-light text-center border shadow-sm" style="border-radius: 12px;">
                        <h6 class="text-muted m-3"><i class="fas fa-info-circle mr-2"></i>Data pengajuan tidak ditemukan.</h6>
                    </div>
                <?php } else { ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="table-pn-head">
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th>Pegawai</th>
                                <th>Jenis Cuti</th>
                                <th>Detail Pengajuan</th>
                                <th>Nomor Surat</th>
                                <th>Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            while($row = mysqli_fetch_array($query)){ 
                                $raw_status = strtolower(trim($row['status'])); 
                                $bg_row = "";
                                if ($raw_status == 'disetujui') {
                                    $badge = '<span class="badge badge-status" style="background-color: #d4edda; color: #155724;"><i class="fas fa-check mr-1"></i>DISETUJUI</span>';
                                } elseif ($raw_status == 'ditolak') {
                                    $badge = '<span class="badge badge-status" style="background-color: #f8d7da; color: #721c24;"><i class="fas fa-times mr-1"></i>DITOLAK</span>';
                                    $bg_row = "style='background-color: #fff5f5;'"; 
                                } else {
                                    $badge = '<span class="badge badge-status" style="background-color: #fff3cd; color: #856404;"><i class="fas fa-clock mr-1"></i>MENUNGGU</span>';
                                    $bg_row = "style='background-color: #fffdf0;'"; 
                                }
                            ?>
                            <tr <?php echo $bg_row; ?>>
                                <td class="text-center font-weight-bold align-middle"><?php echo $nomor++; ?></td>
                                <td class="align-middle">
                                    <div class="font-weight-bold text-dark" style="font-size: 15px;"><?php echo $row['nama_lengkap']; ?></div>
                                    <small class="text-muted"><i class="fas fa-id-badge mr-1"></i>NIP: <?php echo $row['nip']; ?></small>
                                </td>
                                
                                <td class="align-middle text-center">
                                    <span class="badge-jenis-cuti">
                                        <?php echo $row['nama_jenis']; ?>
                                    </span>
                                </td>

                                <td class="align-middle">
                                    <div class="font-weight-bold text-primary"><?php echo $row['lama_hari']; ?> Hari Kerja</div>
                                    <small class="text-dark d-block mt-1">
                                        <i class="far fa-calendar-alt mr-1 text-muted"></i>
                                        <?php echo date('d/m/Y', strtotime($row['tgl_mulai'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($row['tgl_selesai'])); ?>
                                    </small>
                                    <small class="text-muted font-italic d-block mt-1" style="font-size: 11px;">
                                        Diajukan: <?php echo date('d M Y', strtotime($row['tgl_pengajuan'])); ?>
                                    </small>
                                </td>
                                
                                <td class="text-center align-middle">
                                    <?php if(!empty($row['nomor_surat'])) { ?>
                                        <span class="font-weight-bold text-dark small" style="display:inline-block; max-width:150px; line-height:1.2;">
                                            <?php echo $row['nomor_surat']; ?>
                                        </span>
                                    <?php } else { echo '-'; } ?>
                                </td>

                                <td class="text-center align-middle"><?php echo $badge; ?></td>
                                
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center">
                                        <a href="pages/admin/cetak_cuti_admin.php?id=<?php echo $row['id_pengajuan']; ?>" target="_blank" class="btn-circle-action btn-print shadow-sm mr-2" title="Cetak Surat">
                                            <i class="fas fa-print fa-sm"></i>
                                        </a>

                                        <?php if(strpos($raw_status, 'menunggu') !== false || $raw_status == 'diajukan' || $raw_status == '') { ?>
                                            <button class="btn-circle-action btn-approve shadow-sm mr-2"
                                                    onclick="konfirmasiValidasi('setuju', <?php echo $row['id_pengajuan']; ?>, '<?php echo $row['nama_lengkap']; ?>')" 
                                                    title="Setujui">
                                                <i class="fas fa-check fa-sm"></i>
                                            </button>
                                            
                                            <button class="btn-circle-action btn-delete shadow-sm"
                                                    onclick="konfirmasiValidasi('tolak', <?php echo $row['id_pengajuan']; ?>, '<?php echo $row['nama_lengkap']; ?>')" 
                                                    title="Tolak">
                                                <i class="fas fa-times fa-sm"></i>
                                            </button>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="text-muted small mb-2 mb-md-0">
                        Halaman <?php echo $halaman; ?> dari <?php echo $total_halaman; ?>. total: <?php echo $jumlah_data; ?> pengajuan
                    </div>
                    <nav>
                        <ul class="pagination mb-0">
                            <li class="page-item <?php if($halaman <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="<?php if($halaman > 1){ echo "?page=validasi_cuti&hal=".($halaman-1)."&cari=$keyword"; } ?>"><i class="fas fa-chevron-left"></i></a>
                            </li>
                            <?php for($x = 1; $x <= $total_halaman; $x++): ?>
                                <li class="page-item <?php if($halaman == $x) echo 'active'; ?>">
                                    <a class="page-link" href="?page=validasi_cuti&hal=<?php echo $x; ?>&cari=<?php echo $keyword; ?>"><?php echo $x; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php if($halaman >= $total_halaman) echo 'disabled'; ?>">
                                <a class="page-link" href="<?php if($halaman < $total_halaman){ echo "?page=validasi_cuti&hal=".($halaman+1)."&cari=$keyword"; } ?>"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php } ?>
            </div> 
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#keyword').on('keyup', function() {
            var keyword = $(this).val();
            $('#area_tabel').load('index.php?page=validasi_cuti&cari=' + encodeURIComponent(keyword) + ' #area_tabel', function() {
            });
        });
    });

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
                window.location.href = 'pages/admin/proses_validasi.php?aksi=' + aksi + '&id=' + id;
            }
        });
    }
</script>