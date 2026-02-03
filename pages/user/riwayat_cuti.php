<?php
/** @var mysqli $koneksi */

if (!isset($koneksi)) {
    $kemungkinan_path = ['../../config/database.php', '../config/database.php', 'config/database.php'];
    foreach ($kemungkinan_path as $path) {
        if (file_exists($path)) { include_once $path; break; }
    }
}

if (!function_exists('tgl_indo')) {
    function tgl_indo($tanggal){
        $bulan = array (
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        );
        $pecahkan = explode('-', $tanggal);
        return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
    }
}

$id_user = $_SESSION['id_user'];
$batas   = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

$query_jumlah = mysqli_query($koneksi, "SELECT count(*) AS total FROM pengajuan_cuti WHERE id_user='$id_user'");
$data_jumlah  = mysqli_fetch_assoc($query_jumlah);
$total_data   = $data_jumlah['total'];
$total_halaman = ceil($total_data / $batas);

$nomor = $halaman_awal + 1;

$page_current = isset($_GET['page']) ? $_GET['page'] : 'riwayat';
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root { --pn-green: #004d00; --pn-gold: #F9A825; --text-dark: #2c3e50; }
    body { font-family: 'Poppins', sans-serif !important; background-color: #f4f6f9; }
    
    /* Judul Halaman */
    .page-header-title { border-left: 5px solid var(--pn-gold); padding-left: 15px; color: var(--pn-green); font-weight: 700; font-size: 1.6rem; }
    
    /* Card Custom */
    .card-pn-custom { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); background: #fff; overflow: hidden; }
    .card-header-green { background-color: #1b5e20; color: white; padding: 15px 25px; border-bottom: 4px solid var(--pn-gold); display: flex; justify-content: space-between; align-items: center; }
    
    /* Button Styles */
    .btn-pn-solid { background-color: var(--pn-green); color: white; border: 2px solid var(--pn-green); font-weight: 600; border-radius: 8px; padding: 8px 15px; transition: all 0.3s ease; }
    .btn-pn-solid:hover { background-color: #003800; color: var(--pn-gold); border-color: #003800; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 77, 0, 0.3); }

    /* Table Styles */
    .table-custom { width: 100%; border-collapse: separate; border-spacing: 0 5px; }
    .thead-pn { background-color: var(--pn-green); color: white; }
    .thead-pn th {
        padding: 12px 15px;
        border-bottom: 3px solid var(--pn-gold) !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        vertical-align: middle !important;
        border-top: none;
        white-space: nowrap;
    }
    .table-custom tbody tr { background-color: white; transition: 0.2s; }
    .table-custom tbody tr:hover { background-color: #f1f8e9; transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .table-custom td { padding: 12px 15px; vertical-align: middle !important; border-bottom: 1px solid #eee; font-size: 0.95rem; color: #333; }

    /* Badges Status (Pill Shape) */
    .badge-status-ok { background-color: #d4edda; color: #155724; padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; border: 1px solid #c3e6cb; }
    .badge-status-no { background-color: #f8d7da; color: #721c24; padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; border: 1px solid #f5c6cb; }
    .badge-status-wait { background-color: #fff3cd; color: #856404; padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; border: 1px solid #ffeeba; }

    /* Action Buttons (Circle) */
    .btn-circle-action { width: 35px; height: 35px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; }
    .btn-print { background-color: #e3f2fd; color: #0d47a1; }
    .btn-print:hover { background-color: #bbdefb; transform: scale(1.1); }
    .btn-delete { background-color: #ffebee; color: #c62828; }
    .btn-delete:hover { background-color: #ffcdd2; transform: scale(1.1); }

    /* Pagination CSS */
    .pagination .page-link { color: var(--pn-green); border-radius: 5px; margin: 0 3px; }
    .pagination .page-item.active .page-link { background-color: var(--pn-green); border-color: var(--pn-green); color: white; }
    .pagination .page-item.disabled .page-link { color: #6c757d; }

    .text-pn { color: var(--pn-green) !important; }
</style>

<div class="container-fluid mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h3 class="page-header-title">Riwayat Pengajuan Saya</h3>
        <a href="index.php?page=form_cuti" class="btn btn-pn-solid shadow-sm">
            <i class="fas fa-plus mr-2"></i>Ajukan Cuti Baru
        </a>
    </div>

    <div class="card card-pn-custom">
        <div class="card-header-green">
            <div class="font-weight-bold" style="font-size: 1.1rem;">
                <i class="fas fa-history mr-2"></i> Data Riwayat Pengajuan
            </div>
        </div>

        <div class="card-body p-0">
            <div class="p-3">
                <div class="table-responsive">
                    <table class="table table-custom table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="thead-pn text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th>Tanggal Pengajuan</th>
                                <th class="text-left">Detail Cuti</th>
                                <th>Periode</th>
                                <th>Durasi</th>
                                <th>Status</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query Data dengan LIMIT pagination
                            $query = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti 
                                                             JOIN jenis_cuti ON pengajuan_cuti.id_jenis = jenis_cuti.id_jenis 
                                                             WHERE pengajuan_cuti.id_user='$id_user' 
                                                             ORDER BY id_pengajuan DESC 
                                                             LIMIT $halaman_awal, $batas");
                            
                            if(mysqli_num_rows($query) > 0) {
                                while($data = mysqli_fetch_array($query)){
                                    $status = strtolower(trim($data['status']));
                                    
                                    // Tentukan Badge Status
                                    if ($status == 'disetujui') {
                                        $badge = '<span class="badge-status-ok"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>';
                                    } elseif ($status == 'ditolak') {
                                        $badge = '<span class="badge-status-no"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>';
                                    } else {
                                        $badge = '<span class="badge-status-wait"><i class="fas fa-clock mr-1"></i>Menunggu</span>';
                                    }
                            ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?php echo $nomor++; ?></td>
                                
                                <td class="text-center">
                                    <span class="font-weight-bold" style="color:#555;"><?php echo tgl_indo($data['tgl_pengajuan']); ?></span>
                                </td>
                                
                                <td class="text-left">
                                    <span class="text-pn font-weight-bold" style="font-size: 0.95rem;"><?php echo $data['nama_jenis']; ?></span>
                                    <div class="small text-muted mt-1 font-italic">
                                        "<?php echo (strlen($data['alasan']) > 40) ? substr($data['alasan'], 0, 40).'...' : $data['alasan']; ?>"
                                    </div>
                                </td>
                                
                                <td class="text-center">
                                    <div class="small font-weight-bold text-dark"><?php echo date('d/m/Y', strtotime($data['tgl_mulai'])); ?></div>
                                    <div class="small text-muted" style="font-size: 0.75rem;">s/d</div>
                                    <div class="small font-weight-bold text-dark"><?php echo date('d/m/Y', strtotime($data['tgl_selesai'])); ?></div>
                                </td>
                                
                                <td class="text-center">
                                    <span class="font-weight-bold text-dark" style="font-size: 1.1em;"><?php echo $data['lama_hari']; ?></span>
                                    <span class="small text-muted d-block" style="font-size: 0.7rem;">Hari</span>
                                </td>
                                
                                <td class="text-center"><?php echo $badge; ?></td>
                                
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        <a href="pages/user/cetak_cuti.php?id=<?php echo $data['id_pengajuan']; ?>" 
                                           target="_blank" class="btn-circle-action btn-print shadow-sm mr-2" title="Cetak Surat">
                                            <i class="fas fa-print fa-sm"></i>
                                        </a>
                                        
                                        <?php if($status == 'diajukan' || $status == 'menunggu'): ?>
                                            <a href="pages/user/hapus_cuti.php?id=<?php echo $data['id_pengajuan']; ?>" 
                                               class="btn-circle-action btn-delete btn-batal shadow-sm" title="Batalkan">
                                                <i class="fas fa-trash fa-sm"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                } 
                            } else {
                                echo "<tr><td colspan='7' class='text-center py-5 text-muted'><i class='fas fa-folder-open fa-3x mb-3'></i><br>Belum ada riwayat pengajuan cuti.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if($total_data > 0): ?>
                <div class="row mt-3 align-items-center px-2">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <p class="text-secondary small mb-0">Halaman <strong><?php echo $halaman; ?></strong> dari <?php echo $total_halaman; ?>. Total: <?php echo $total_data; ?> Data.</p>
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end justify-content-center">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm m-0">
                                <?php if($halaman > 1): ?>
                                    <li class="page-item"><a class="page-link" href="index.php?page=<?php echo $page_current; ?>&halaman=<?php echo $halaman - 1; ?>">&laquo;</a></li>
                                <?php else: ?>
                                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                                <?php endif; ?>
                                
                                <?php for($x = 1; $x <= $total_halaman; $x++): 
                                    $active_class = ($x == $halaman) ? 'active' : ''; ?>
                                    <li class="page-item <?php echo $active_class; ?>"><a class="page-link" href="index.php?page=<?php echo $page_current; ?>&halaman=<?php echo $x; ?>"><?php echo $x; ?></a></li>
                                <?php endfor; ?>
                                
                                <?php if($halaman < $total_halaman): ?>
                                    <li class="page-item"><a class="page-link" href="index.php?page=<?php echo $page_current; ?>&halaman=<?php echo $halaman + 1; ?>">&raquo;</a></li>
                                <?php else: ?>
                                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
                </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('click', function(e) {
        const target = e.target.closest('.btn-batal');
        if (target) {
            e.preventDefault();
            const href = target.getAttribute('href');
            Swal.fire({
                title: 'Batalkan Pengajuan?',
                text: "Kuota cuti Anda akan dikembalikan otomatis.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#004d00',
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Kembali'
            }).then((result) => {
                if (result.isConfirmed) { document.location.href = href; }
            });
        }
    });
</script>