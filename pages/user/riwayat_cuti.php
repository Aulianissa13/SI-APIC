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
    .bg-custom-green {
        background-color: #006B3F !important;
        color: white;
    }
    .btn-custom-green {
        background-color: #006B3F;
        color: white;
        border: none;
    }
    .btn-custom-green:hover {
        background-color: #00502f;
        color: white;
    }
    .table-align-middle td {
        vertical-align: middle !important;
    }
    .badge-lg {
        font-size: 90%;
        padding: 8px 12px;
    }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 border-left-success pl-3">Riwayat Pengajuan Saya</h1>
    <a href="index.php?page=form_cuti" class="btn btn-custom-green shadow-sm">
        <i class="fas fa-plus-circle fa-sm text-white-50 mr-2"></i>Ajukan Cuti Baru
    </a>
</div>

<?php if(isset($_GET['pesan']) && $_GET['pesan']=="sukses"): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm border-left-success" role="alert">
    <strong><i class="fas fa-check-circle"></i> Berhasil!</strong> Formulir pengajuan cuti Anda telah terkirim.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-history mr-1"></i> Data Riwayat</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-align-middle" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-custom-green">
                    <tr class="text-center">
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
                    $cek_data = mysqli_num_rows($query);

                    if($cek_data > 0) {
                        while($data = mysqli_fetch_array($query)){
                            $status = strtolower($data['status']);
                    ?>
                    <tr>
                        <td class="text-center font-weight-bold"><?php echo $no++; ?></td>
                        
                        <td class="text-center">
                            <span class="text-dark font-weight-bold"><?php echo tgl_indo($data['tgl_pengajuan']); ?></span>
                        </td>
                        
                        <td>
                            <span class="badge badge-info badge-pill px-3 mb-1">
                                <?php echo $data['nama_jenis']; ?>
                            </span>
                            <div class="small text-muted mt-1" style="line-height: 1.2;">
                                <em>"<?php echo substr($data['alasan'], 0, 30); ?>..."</em>
                            </div>
                        </td>
                        
                        <td class="text-center">
                            <div class="small font-weight-bold text-gray-600">Mulai</div>
                            <div><?php echo date('d/m/Y', strtotime($data['tgl_mulai'])); ?></div>
                            <div class="small font-weight-bold text-gray-600 mt-1">Selesai</div>
                            <div><?php echo date('d/m/Y', strtotime($data['tgl_selesai'])); ?></div>
                        </td>
                        
                        <td class="text-center">
                            <span class="font-weight-bold text-dark" style="font-size: 1.1em;"><?php echo $data['lama_hari']; ?></span>
                            <span class="small text-muted d-block">Hari</span>
                        </td>
                        
                        <td class="text-center">
                            <?php 
                            if($status == 'diajukan' || $status == 'menunggu'){
                                echo '<span class="badge badge-warning badge-pill px-3 py-2 shadow-sm"><i class="fas fa-spinner fa-spin mr-1"></i> Menunggu</span>';
                            } else if($status == 'disetujui'){
                                echo '<span class="badge badge-success badge-pill px-3 py-2 shadow-sm"><i class="fas fa-check mr-1"></i> Disetujui</span>';
                            } else if($status == 'ditolak'){
                                echo '<span class="badge badge-danger badge-pill px-3 py-2 shadow-sm"><i class="fas fa-times mr-1"></i> Ditolak</span>';
                            }
                            ?>
                        </td>
                        
                        <td class="text-center">
                            <a href="pages/user/cetak_cuti.php?id=<?php echo $data['id_pengajuan']; ?>" 
                               target="_blank" 
                               class="btn btn-secondary btn-sm btn-circle shadow-sm mb-1" 
                               data-toggle="tooltip" 
                               title="Cetak Surat">
                                <i class="fas fa-print"></i>
                            </a>

                            <?php if($status == 'diajukan' || $status == 'menunggu'): ?>
                                <a href="pages/user/hapus_cuti.php?id=<?php echo $data['id_pengajuan']; ?>" 
                                   class="btn btn-danger btn-sm btn-circle btn-batal shadow-sm mb-1" 
                                   data-toggle="tooltip" 
                                   title="Batalkan Pengajuan">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        } // end while
                    } // end if data > 0
                    ?>
                </tbody>
            </table>
            
            <?php if($cek_data == 0): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <span class="fa-stack fa-2x">
                          <i class="fas fa-circle fa-stack-2x text-gray-200"></i>
                          <i class="fas fa-file-alt fa-stack-1x text-gray-400"></i>
                        </span>
                    </div>
                    <h5 class="text-gray-600 font-weight-bold">Belum ada riwayat pengajuan</h5>
                    <p class="text-muted small mb-4">Anda belum pernah mengajukan cuti sebelumnya.</p>
                    <a href="index.php?page=form_cuti" class="btn btn-custom-green btn-sm px-4 py-2">
                        Buat Pengajuan Sekarang
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['alert'])) : ?>
    <script>
        Swal.fire({
            icon: '<?php echo $_SESSION['alert']['icon']; ?>',
            title: '<?php echo $_SESSION['alert']['title']; ?>',
            text: '<?php echo $_SESSION['alert']['text']; ?>',
            confirmButtonColor: '#006B3F',
            confirmButtonText: 'Oke'
        });
    </script>
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>

<script>
    // Menggunakan Event Delegation (Aman untuk DataTables)
    document.addEventListener('click', function(e) {
        // Cek apakah yang diklik adalah tombol .btn-batal atau icon di dalamnya
        const target = e.target.closest('.btn-batal');

        // Jika benar tombol batal
        if (target) {
            e.preventDefault(); // 1. Wajib: Cegah link langsung jalan
            
            const href = target.getAttribute('href'); // 2. Ambil link tujuan

            // 3. Tampilkan SweetAlert
            Swal.fire({
                title: 'Batalkan Pengajuan?',
                text: "Kuota cuti Anda akan dikembalikan otomatis.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',     // Merah
                cancelButtonColor: '#006B3F',   // Hijau
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Kembali'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 4. Jika user klik Ya, baru pindah halaman
                    document.location.href = href; 
                }
            });
        }
    });
    
    // Aktifkan Tooltip Bootstrap (Opsional, agar tulisan hover muncul)
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
</script>