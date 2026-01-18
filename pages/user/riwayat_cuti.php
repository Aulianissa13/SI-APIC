<?php
// =========================================================
// 1. CEK KONEKSI & HELPER TANGGAL
// =========================================================

// Cek apakah variabel $koneksi sudah ada (dari index.php)
// Jika belum, cari file database.php
if (!isset($koneksi)) {
    $kemungkinan_path = ['../../config/database.php', '../config/database.php', 'config/database.php'];
    foreach ($kemungkinan_path as $path) {
        if (file_exists($path)) { include_once $path; break; }
    }
}

// Fungsi Tanggal Indo (Agar konsisten dengan surat cetak)
if (!function_exists('tgl_indo')) {
    function tgl_indo($tanggal){
        $bulan = array (
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        );
        $pecahkan = explode('-', $tanggal);
        // Format: 20 Januari 2025
        return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
    }
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Riwayat Pengajuan Saya</h1>
    <a href="index.php?page=form_cuti" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Ajukan Baru
    </a>
</div>

<?php if(isset($_GET['pesan']) && $_GET['pesan']=="sukses"): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <strong><i class="fas fa-check-circle"></i> Berhasil!</strong> Pengajuan cuti Anda telah terkirim dan menunggu validasi Admin.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Riwayat Cuti</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Tgl Pengajuan</th>
                        <th>Jenis Cuti</th>
                        <th>Periode Pelaksanaan</th>
                        <th>Lama</th>
                        <th>Status</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $id_user = $_SESSION['id_user'];
                    
                    // Query mengambil data pengajuan user ini
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
                        <td class="text-center"><?php echo $no++; ?></td>
                        
                        <td><?php echo tgl_indo($data['tgl_pengajuan']); ?></td>
                        
                        <td>
                            <span class="badge badge-secondary" style="font-size: 0.9em;">
                                <?php echo $data['nama_jenis']; ?>
                            </span>
                        </td>
                        
                        <td>
                            <small class="font-weight-bold text-primary">Mulai:</small> 
                            <?php echo date('d/m/Y', strtotime($data['tgl_mulai'])); ?> <br>
                            <small class="font-weight-bold text-primary">Selesai:</small> 
                            <?php echo date('d/m/Y', strtotime($data['tgl_selesai'])); ?>
                        </td>
                        
                        <td class="text-center font-weight-bold"><?php echo $data['lama_hari']; ?> Hari</td>
                        
                        <td class="text-center">
                            <?php 
                            if($status == 'diajukan' || $status == 'menunggu'){
                                echo '<span class="badge badge-warning badge-pill px-3">Menunggu Validasi</span>';
                            } else if($status == 'disetujui'){
                                echo '<span class="badge badge-success badge-pill px-3">Disetujui</span>';
                            } else if($status == 'ditolak'){
                                echo '<span class="badge badge-danger badge-pill px-3">Ditolak</span>';
                            }
                            ?>
                        </td>
                        
                        <td class="text-center">
                            <a href="pages/user/cetak_cuti.php?id=<?php echo $data['id_pengajuan']; ?>" 
                               target="_blank" 
                               class="btn btn-info btn-circle btn-sm" 
                               title="Cetak Formulir">
                                <i class="fas fa-print"></i>
                            </a>
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
                    <div style="font-size: 4rem; color: #d1d3e2;">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <p class="mt-3 text-muted font-weight-bold">Belum ada riwayat pengajuan cuti.</p>
                    <a href="index.php?page=form_cuti" class="btn btn-sm btn-primary">
                        Buat Pengajuan Sekarang
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>