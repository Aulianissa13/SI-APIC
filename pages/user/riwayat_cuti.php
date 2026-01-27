<?php
// =========================================================
// 1. CEK KONEKSI & HELPER TANGGAL
// =========================================================
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
?>

<style>
    :root {
        --pn-green: #004d00;
        --pn-green-light: #006B3F;
        --pn-gold: #FFD700;
    }

    .page-title-pn {
        font-weight: 700;
        border-left: 5px solid var(--pn-gold);
        padding-left: 15px;
        color: var(--pn-green) !important;
    }

    .card-pn {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .card-header-pn {
        background: linear-gradient(135deg, var(--pn-green) 0%, var(--pn-green-light) 100%);
        color: white;
        border-bottom: 4px solid var(--pn-gold);
        padding: 15px 20px;
    }

    .thead-pn {
        background-color: var(--pn-green) !important;
        color: white !important;
    }

    /* Menghapus background status, memaksa tetap putih */
    .table-pn tbody tr {
        background-color: #ffffff !important;
    }

    .table-align-middle td {
        vertical-align: middle !important;
    }

    .badge-status {
        border-radius: 10px;
        padding: 8px 12px;
        font-weight: 600;
    }

    .btn-custom-green {
        background: linear-gradient(135deg, var(--pn-green) 0%, var(--pn-green-light) 100%);
        color: white;
        border: none;
        font-weight: 600;
    }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 page-title-pn">Riwayat Pengajuan Saya</h1>
    <a href="index.php?page=form_cuti" class="btn btn-custom-green shadow-sm rounded-pill px-4">
        <i class="fas fa-plus-circle fa-sm text-white-50 mr-2"></i>Ajukan Cuti Baru
    </a>
</div>

<div class="card card-pn mb-4">
    <div class="card-header-pn d-flex align-items-center">
        <i class="fas fa-history mr-2"></i>
        <h6 class="m-0 font-weight-bold">Data Riwayat Pengajuan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-align-middle table-pn" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-pn text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Jenis & Detail</th>
                        <th>Periode</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th width="12%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $id_user = $_SESSION['id_user'];
                    $query = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti 
                                                     JOIN jenis_cuti ON pengajuan_cuti.id_jenis = jenis_cuti.id_jenis 
                                                     WHERE pengajuan_cuti.id_user='$id_user' 
                                                     ORDER BY id_pengajuan DESC");
                    
                    $no = 1;
                    while($data = mysqli_fetch_array($query)){
                        $status = strtolower(trim($data['status']));
                        
                        if ($status == 'disetujui') {
                            $badge = '<span class="badge badge-success badge-status shadow-sm"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>';
                        } elseif ($status == 'ditolak') {
                            $badge = '<span class="badge badge-danger badge-status shadow-sm"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>';
                        } else {
                            $badge = '<span class="badge badge-warning badge-status shadow-sm text-white"><i class="fas fa-clock mr-1"></i>Menunggu</span>';
                        }
                    ?>
                    <tr>
                        <td class="text-center font-weight-bold text-muted"><?php echo $no++; ?></td>
                        
                        <td class="text-center">
                            <span class="text-dark font-weight-bold"><?php echo tgl_indo($data['tgl_pengajuan']); ?></span>
                        </td>
                        
                        <td class="text-center">
                            <span class="badge badge-info badge-pill px-3 mb-1">
                                <?php echo $data['nama_jenis']; ?>
                            </span>
                            <div class="small text-muted mt-1">
                                <em>"<?php echo (strlen($data['alasan']) > 35) ? substr($data['alasan'], 0, 35).'...' : $data['alasan']; ?>"</em>
                            </div>
                        </td>
                        
                        <td class="text-center text-dark">
                            <div class="small font-weight-bold text-gray-600"><?php echo date('d/m/Y', strtotime($data['tgl_mulai'])); ?></div>
                            <div class="small text-muted">s/d</div>
                            <div class="small font-weight-bold text-gray-600"><?php echo date('d/m/Y', strtotime($data['tgl_selesai'])); ?></div>
                        </td>
                        
                        <td class="text-center">
                            <span class="font-weight-bold text-dark" style="font-size: 1.1em;"><?php echo $data['lama_hari']; ?></span>
                            <span class="small text-muted d-block">Hari</span>
                        </td>
                        
                        <td class="text-center"><?php echo $badge; ?></td>
                        
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="pages/user/cetak_cuti.php?id=<?php echo $data['id_pengajuan']; ?>" 
                                   target="_blank" class="btn btn-outline-info btn-sm btn-circle shadow-sm" title="Cetak Surat">
                                    <i class="fas fa-print"></i>
                                </a>
                                <?php if($status == 'diajukan' || $status == 'menunggu'): ?>
                                    <span class="mx-1"></span>
                                    <a href="pages/user/hapus_cuti.php?id=<?php echo $data['id_pengajuan']; ?>" 
                                       class="btn btn-outline-danger btn-sm btn-circle btn-batal shadow-sm" title="Batalkan">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
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
                cancelButtonColor: '#006B3F',
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Kembali'
            }).then((result) => {
                if (result.isConfirmed) { document.location.href = href; }
            });
        }
    });
</script>