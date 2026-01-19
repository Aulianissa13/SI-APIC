<?php
// --- 1. PROSES TAMBAH / EDIT / HAPUS ---

// A. TAMBAH PEGAWAI BARU
if (isset($_POST['simpan_user'])) {
    // Ambil data dari form
    $nip        = htmlspecialchars($_POST['nip']);
    $nama       = htmlspecialchars($_POST['nama']);
    $username   = htmlspecialchars($_POST['username']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT); // Enkripsi
    
    // Data Kepegawaian
    $jabatan    = htmlspecialchars($_POST['jabatan']);
    $pangkat    = htmlspecialchars($_POST['pangkat']); // UBAH KE PANGKAT
    $unit_kerja = htmlspecialchars($_POST['unit_kerja']);
    
    // Hak Akses
    $role       = $_POST['role'];

    // Cek duplikasi username/NIP
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' OR nip='$nip'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Gagal! Username atau NIP sudah terdaftar.');</script>";
    } else {
        // Query Insert Data Lengkap (Gunakan kolom 'pangkat')
        $query = "INSERT INTO users (nip, nama_lengkap, username, password, jabatan, pangkat, unit_kerja, role) 
                  VALUES ('$nip', '$nama', '$username', '$password', '$jabatan', '$pangkat', '$unit_kerja', '$role')";
        
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Berhasil menambah pegawai baru!'); window.location='index.php?page=data_pegawai';</script>";
        } else {
            // Tampilkan error SQL jika ada (untuk debugging)
            echo "<script>alert('Gagal menyimpan: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}

// B. EDIT DATA PEGAWAI
if (isset($_POST['edit_user'])) {
    $id_user    = $_POST['id_user'];
    $nip        = htmlspecialchars($_POST['nip']);
    $nama       = htmlspecialchars($_POST['nama']);
    $jabatan    = htmlspecialchars($_POST['jabatan']);
    $pangkat    = htmlspecialchars($_POST['pangkat']); // UBAH KE PANGKAT
    $unit_kerja = htmlspecialchars($_POST['unit_kerja']);
    $role       = $_POST['role'];

    // Cek update password
    if (!empty($_POST['password_baru'])) {
        $pass_baru = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);
        $query = "UPDATE users SET nip='$nip', nama_lengkap='$nama', jabatan='$jabatan', pangkat='$pangkat', unit_kerja='$unit_kerja', role='$role', password='$pass_baru' WHERE id_user='$id_user'";
    } else {
        $query = "UPDATE users SET nip='$nip', nama_lengkap='$nama', jabatan='$jabatan', pangkat='$pangkat', unit_kerja='$unit_kerja', role='$role' WHERE id_user='$id_user'";
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data pegawai berhasil diperbarui!'); window.location='index.php?page=data_pegawai';</script>";
    } else {
        echo "<script>alert('Gagal update: " . mysqli_error($koneksi) . "');</script>";
    }
}

// C. HAPUS PEGAWAI
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    if ($id_hapus == $_SESSION['id_user']) {
        echo "<script>alert('Anda tidak bisa menghapus akun sendiri!'); window.location='index.php?page=data_pegawai';</script>";
    } else {
        mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$id_hapus'");
        echo "<script>alert('Pegawai berhasil dihapus.'); window.location='index.php?page=data_pegawai';</script>";
    }
}
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800" style="font-weight: 700; color: var(--pn-green);">Data Pegawai</h1>
        <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambah">
            <i class="fas fa-user-plus fa-sm text-white-50 mr-2"></i>Tambah Pegawai
        </button>
    </div>

    <div class="card shadow mb-4" style="border-radius: 15px; border:none;">
        <div class="card-header py-3" style="background:white; border-radius: 15px 15px 0 0;">
            <h6 class="m-0 font-weight-bold" style="color: var(--pn-green);">Daftar Pengguna Sistem</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="dataTableUser" width="100%" cellspacing="0">
                    <thead style="background: #f8f9fc;">
                        <tr>
                            <th>No</th>
                            <th>Pegawai</th>
                            <th>Jabatan & Pangkat</th>
                            <th>Unit Kerja</th>
                            <th>Role Akses</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $tampil = mysqli_query($koneksi, "SELECT * FROM users ORDER BY role ASC, nama_lengkap ASC");
                        while ($data = mysqli_fetch_array($tampil)) :
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <div class="font-weight-bold text-dark"><?= $data['nama_lengkap'] ?></div>
                                <small class="text-muted">NIP: <?= $data['nip'] ?></small>
                            </td>
                            <td>
                                <div class="text-dark"><?= $data['jabatan'] ?></div>
                                <small class="text-info"><?= isset($data['pangkat']) ? $data['pangkat'] : '-' ?></small>
                            </td>
                            <td><small><?= isset($data['unit_kerja']) ? $data['unit_kerja'] : '-' ?></small></td>
                            <td>
                                <?php if($data['role']=='admin'): ?>
                                    <span class="badge badge-danger">Administrator</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">User/Pegawai</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm btn-circle" 
                                        data-toggle="modal" data-target="#modalEdit<?= $data['id_user'] ?>" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="index.php?page=data_pegawai&hapus=<?= $data['id_user'] ?>" 
                                   class="btn btn-danger btn-sm btn-circle" 
                                   onclick="return confirm('Yakin ingin menghapus data pegawai ini?')" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalEdit<?= $data['id_user'] ?>" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-white">
                                        <h5 class="modal-title">Edit Data Pegawai</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_user" value="<?= $data['id_user'] ?>">
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>NIP</label>
                                                        <input type="text" name="nip" class="form-control" value="<?= $data['nip'] ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Nama Lengkap</label>
                                                        <input type="text" name="nama" class="form-control" value="<?= $data['nama_lengkap'] ?>" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Jabatan</label>
                                                        <input type="text" name="jabatan" class="form-control" value="<?= $data['jabatan'] ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Pangkat / Golongan</label>
                                                        <input type="text" name="pangkat" class="form-control" value="<?= isset($data['pangkat']) ? $data['pangkat'] : '' ?>" placeholder="Contoh: III/a">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Unit Kerja</label>
                                                        <input type="text" name="unit_kerja" class="form-control" value="<?= isset($data['unit_kerja']) ? $data['unit_kerja'] : 'Pengadilan Negeri Yogyakarta' ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Role Sistem</label>
                                                        <select name="role" class="form-control">
                                                            <option value="user" <?= ($data['role']=='user')?'selected':'' ?>>User (Pegawai)</option>
                                                            <option value="admin" <?= ($data['role']=='admin')?'selected':'' ?>>Administrator</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>
                                            <div class="form-group">
                                                <label class="text-danger font-weight-bold">Reset Password</label>
                                                <input type="password" name="password_baru" class="form-control" placeholder="Isi hanya jika ingin mengganti password">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit_user" class="btn btn-warning">Update Data</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Tambah Pegawai Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    
                    <h6 class="font-weight-bold text-primary mb-3">Data Diri & Jabatan</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIP</label>
                                <input type="number" name="nip" class="form-control" placeholder="NIP Tanpa Spasi" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" placeholder="Nama + Gelar" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jabatan</label>
                                <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Panitera Pengganti" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pangkat / Golongan</label>
                                <input type="text" name="pangkat" class="form-control" placeholder="Contoh: Penata Muda (III/a)">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Unit Kerja</label>
                        <input type="text" name="unit_kerja" class="form-control" value="Pengadilan Negeri Yogyakarta" readonly>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold text-primary mb-3">Akun Login Sistem</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Password Awal</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Role Akses</label>
                                <select name="role" class="form-control">
                                    <option value="user">User (Pegawai)</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_user" class="btn btn-primary">Simpan Pegawai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#dataTableUser').DataTable();
    });
</script>