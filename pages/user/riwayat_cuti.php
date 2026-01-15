<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Riwayat Pengajuan Saya</h1>
    <a href="index.php?page=form_cuti" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Ajukan Baru
    </a>
</div>

<?php if(isset($_GET['pesan']) && $_GET['pesan']=="sukses"): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>Berhasil!</strong> Pengajuan cuti Anda telah terkirim dan menunggu validasi Admin.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Riwayat Cuti</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Tgl Pengajuan</th>
                        <th>Jenis Cuti</th>
                        <th>Periode</th>
                        <th>Lama</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $id_user = $_SESSION['id_user'];
                    
                    // UBAH DISINI: Ganti 'cuti' jadi 'pengajuan_cuti'
                    // Asumsi Primary Key tabel kamu adalah 'id_pengajuan' (sesuaikan jika beda, misal id_cuti)
                    
                    $query = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti 
                                                     JOIN jenis_cuti ON pengajuan_cuti.id_jenis = jenis_cuti.id_jenis 
                                                     WHERE pengajuan_cuti.id_user='$id_user' 
                                                     ORDER BY id_pengajuan DESC"); // Sesuaikan nama ID primary key-nya
                    
                    $no = 1;
                    while($data = mysqli_fetch_array($query)){
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($data['tgl_pengajuan'])); ?></td>
                        <td>
                            <span class="badge badge-secondary"><?php echo $data['nama_jenis']; ?></span>
                        </td>
                        <td>
                            <small><?php echo date('d/m/Y', strtotime($data['tgl_mulai'])); ?> s.d. <br> 
                            <?php echo date('d/m/Y', strtotime($data['tgl_selesai'])); ?></small>
                        </td>
                        <td class="text-center font-weight-bold"><?php echo $data['lama_hari']; ?> Hari</td>
                        <td class="text-center">
                            <?php 
                            if($data['status'] == 'Diajukan'){
                                echo '<span class="badge badge-warning">Menunggu Validasi</span>';
                            } else if($data['status'] == 'Disetujui'){
                                echo '<span class="badge badge-success">Disetujui</span>';
                            } else {
                                echo '<span class="badge badge-danger">Ditolak</span>';
                            }
                            ?>
                        </td>
                        <td class="text-center">
                            <a href="pages/user/cetak_cuti.php?id=<?php echo $data['id_pengajuan']; ?>" target="_blank" class="btn btn-info btn-circle btn-sm" title="Cetak Formulir">
                                <i class="fas fa-print"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <?php if(mysqli_num_rows($query) == 0): ?>
                <div class="text-center py-5">
                    <img src="assets/img/undraw_empty.svg" style="width: 200px; opacity: 0.5;">
                    <p class="mt-3 text-muted">Belum ada riwayat pengajuan cuti.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>