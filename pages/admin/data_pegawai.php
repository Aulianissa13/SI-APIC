<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 border-left-primary pl-3">Data Pegawai</h1>
    <a href="index.php?page=form_pegawai" class="btn btn-primary btn-sm shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Pegawai
    </a>
</div>

<?php if (isset($_SESSION['alert'])) : ?>
    <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['alert']['text']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Akun Pengguna</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama & NIP</th> <th>Role</th>
                        <th>Kontak</th>
                        <th>Sisa Kuota Cuti (Hari)</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $query = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id_user DESC");
                    while ($row = mysqli_fetch_array($query)) {
                    ?>
                    <tr>
                        <td class="text-center align-middle"><?php echo $no++; ?></td>
                        <td class="align-middle">
                            <div class="font-weight-bold text-dark"><?php echo $row['nama_lengkap']; ?></div>
                            <div class="small text-muted"><i class="fas fa-id-card"></i> <?php echo $row['nip']; ?></div>
                        </td>
                        <td class="text-center align-middle">
                            <?php if($row['role'] == 'admin'): ?>
                                <span class="badge badge-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-info">Pegawai</span>
                            <?php endif; ?>
                        </td>
                        <td class="align-middle small">
                            <i class="fas fa-phone fa-fw"></i> <?php echo $row['no_telepon']; ?>
                        </td>
                        <td class="align-middle">
                            <div class="row text-center small">
                                <div class="col-4 border-right">
                                    <span class="d-block text-gray-600">N</span>
                                    <strong class="text-success"><?php echo $row['sisa_cuti_n']; ?></strong>
                                </div>
                                <div class="col-4 border-right">
                                    <span class="d-block text-gray-600">N-1</span>
                                    <strong class="text-warning"><?php echo $row['sisa_cuti_n1']; ?></strong>
                                </div>
                                <div class="col-4">
                                    <span class="d-block text-gray-600">Sakit</span>
                                    <strong class="text-danger"><?php echo $row['kuota_cuti_sakit']; ?></strong>
                                </div>
                            </div>
                        </td>
                        <td class="text-center align-middle">
                            <a href="index.php?page=form_pegawai&id=<?php echo $row['id_user']; ?>" class="btn btn-warning btn-sm btn-circle" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <a href="pages/admin/proses_pegawai.php?act=hapus&id=<?php echo $row['id_user']; ?>" class="btn btn-danger btn-sm btn-circle btn-hapus" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </a>
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
        if (e.target && e.target.closest('.btn-hapus')) {
            e.preventDefault();
            const link = e.target.closest('.btn-hapus').getAttribute('href');

            Swal.fire({
                title: 'Hapus Pegawai?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            })
        }
    });
</script>