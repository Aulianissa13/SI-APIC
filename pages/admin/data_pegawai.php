<?php
// --- 1. PROSES PHP: TAMBAH / EDIT / HAPUS ---

// A. TAMBAH PEGAWAI BARU
if (isset($_POST['simpan_user'])) {
    // 1. Ambil data dari form dan amankan input
    $nip        = htmlspecialchars($_POST['nip']);
    $nama       = htmlspecialchars($_POST['nama']);
    $username   = htmlspecialchars($_POST['username']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT); // Enkripsi Password
    
    // Data Kepegawaian
    $jabatan    = htmlspecialchars($_POST['jabatan']);
    $pangkat    = htmlspecialchars($_POST['pangkat']);
    $unit_kerja = htmlspecialchars($_POST['unit_kerja']);
    
    // Pengaturan Akun & Status (FITUR BARU)
    $role           = $_POST['role'];
    $status_akun    = $_POST['status_akun']; // aktif / nonaktif
    $is_pejabat     = $_POST['is_pejabat'];  // 0 / 1

    // 2. Cek duplikasi Username atau NIP
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' OR nip='$nip'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Gagal! Username atau NIP sudah terdaftar di sistem.');</script>";
    } else {
        // 3. Query Insert Data Lengkap
        $query = "INSERT INTO users (nip, nama_lengkap, username, password, jabatan, pangkat, unit_kerja, role, status_akun, is_pejabat) 
                  VALUES ('$nip', '$nama', '$username', '$password', '$jabatan', '$pangkat', '$unit_kerja', '$role', '$status_akun', '$is_pejabat')";
        
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Berhasil menambah pegawai baru!'); window.location='index.php?page=data_pegawai';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan database: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}

// B. EDIT DATA PEGAWAI
if (isset($_POST['edit_user'])) {
    $id_user    = $_POST['id_user'];
    $nip        = htmlspecialchars($_POST['nip']);
    $nama       = htmlspecialchars($_POST['nama']);
    $jabatan    = htmlspecialchars($_POST['jabatan']);
    $pangkat    = htmlspecialchars($_POST['pangkat']);
    $unit_kerja = htmlspecialchars($_POST['unit_kerja']);
    
    // Update Status (FITUR BARU)
    $role           = $_POST['role'];
    $status_akun    = $_POST['status_akun'];
    $is_pejabat     = $_POST['is_pejabat'];

    // Cek apakah Admin mengubah password user?
    if (!empty($_POST['password_baru'])) {
        // Jika password diisi, enkripsi password baru
        $pass_baru = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);
        $query = "UPDATE users SET 
                    nip='$nip', 
                    nama_lengkap='$nama', 
                    jabatan='$jabatan', 
                    pangkat='$pangkat', 
                    unit_kerja='$unit_kerja', 
                    role='$role', 
                    status_akun='$status_akun', 
                    is_pejabat='$is_pejabat', 
                    password='$pass_baru' 
                  WHERE id_user='$id_user'";
    } else {
        // Jika password kosong, jangan update kolom password
        $query = "UPDATE users SET 
                    nip='$nip', 
                    nama_lengkap='$nama', 
                    jabatan='$jabatan', 
                    pangkat='$pangkat', 
                    unit_kerja='$unit_kerja', 
                    role='$role', 
                    status_akun='$status_akun', 
                    is_pejabat='$is_pejabat' 
                  WHERE id_user='$id_user'";
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
    // Mencegah Admin menghapus akunnya sendiri saat sedang login
    if ($id_hapus == $_SESSION['id_user']) {
        echo "<script>alert('Anda tidak bisa menghapus akun yang sedang digunakan!'); window.location='index.php?page=data_pegawai';</script>";
    } else {
        mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$id_hapus'");
        echo "<script>alert('Data pegawai berhasil dihapus.'); window.location='index.php?page=data_pegawai';</script>";
    }
}
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800" style="font-weight: 700; color: var(--pn-green);">Kelola Data Pegawai</h1>
        <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambah">
            <i class="fas fa-user-plus fa-sm text-white-50 mr-2"></i>Tambah Pegawai
        </button>
    </div>

    <div class="card shadow mb-4" style="border-radius: 15px; border:none;">
        <div class="card-header py-3" style="background:white; border-radius: 15px 15px 0 0;">
            <h6 class="m-0 font-weight-bold" style="color: var(--pn-green);">Daftar Pengguna Sistem & Status</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="dataTableUser" width="100%" cellspacing="0">
                    <thead style="background: #f8f9fc;">
                        <tr>
                            <th width="5%">No</th>
                            <th>Identitas Pegawai</th>
                            <th>Jabatan & Status</th> <th>Unit Kerja</th>
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
                        <tr class="<?= ($data['status_akun'] == 'nonaktif') ? 'bg-light text-muted' : '' ?>"> 
                            <td><?= $no++ ?></td>
                            <td>
                                <div class="font-weight-bold text-dark"><?= $data['nama_lengkap'] ?></div>
                                <small class="text-muted">NIP: <?= $data['nip'] ?></small>
                                <div class="small text-primary font-italic mt-1">
                                    <i class="fas fa-user-circle"></i> <?= $data['nip'] ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-dark font-weight-bold"><?= $data['jabatan'] ?></div>
                                <small class="d-block"><?= isset($data['pangkat']) ? $data['pangkat'] : '-' ?></small>
                                
                                <div class="mt-2">
                                    <?php if($data['is_pejabat'] == '1'): ?>
                                        <span class="badge badge-success px-2 mb-1"><i class="fas fa-pen-nib"></i> Penandatangan</span>
                                    <?php endif; ?>

                                    <?php if($data['status_akun'] == 'aktif'): ?>
                                        <span class="badge badge-info px-2 mb-1">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger px-2 mb-1">Non-Aktif / Blokir</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><small><?= isset($data['unit_kerja']) ? $data['unit_kerja'] : '-' ?></small></td>
                            <td>
                                <?php if($data['role']=='admin'): ?>
                                    <span class="badge badge-warning text-dark">Administrator</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">User / Pegawai</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm btn-circle mb-1" 
                                        data-toggle="modal" data-target="#modalEdit<?= $data['id_user'] ?>" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="index.php?page=data_pegawai&hapus=<?= $data['id_user'] ?>" 
                                   class="btn btn-danger btn-sm btn-circle mb-1" 
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

                                            <div class="form-group">
                                                <label>Unit Kerja</label>
                                                <input type="text" name="unit_kerja" class="form-control" value="<?= isset($data['unit_kerja']) ? $data['unit_kerja'] : 'Pengadilan Negeri Yogyakarta' ?>" required>
                                            </div>

                                            <hr>
                                            <div class="alert alert-secondary">
                                                <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-cogs"></i> Pengaturan Akun & Status</h6>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold" style="font-size: 13px;">Status Akun (Login)</label>
                                                            <select name="status_akun" class="form-control">
                                                                <option value="aktif" <?= ($data['status_akun']=='aktif')?'selected':'' ?>>Aktif (Bisa Login)</option>
                                                                <option value="nonaktif" <?= ($data['status_akun']=='nonaktif')?'selected':'' ?>>Non-Aktif (Blokir)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold" style="font-size: 13px;">Hak Tanda Tangan</label>
                                                            <select name="is_pejabat" class="form-control">
                                                                <option value="0" <?= ($data['is_pejabat']=='0')?'selected':'' ?>>Tidak (Pegawai Biasa)</option>
                                                                <option value="1" <?= ($data['is_pejabat']=='1')?'selected':'' ?>>YA (Pejabat)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold" style="font-size: 13px;">Role Admin</label>
                                                            <select name="role" class="form-control">
                                                                <option value="user" <?= ($data['role']=='user')?'selected':'' ?>>User</option>
                                                                <option value="admin" <?= ($data['role']=='admin')?'selected':'' ?>>Administrator</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="text-danger font-weight-bold">Reset Password</label>
                                                <input type="password" name="password_baru" class="form-control" placeholder="Isi hanya jika ingin mengganti password">
                                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah password.</small>
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
                    <div class="alert alert-light border">
                        <h6 class="font-weight-bold text-primary mb-3">Akun & Status Login</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password Awal</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Role Akses</label>
                                    <select name="role" class="form-control">
                                        <option value="user">User (Pegawai)</option>
                                        <option value="admin">Administrator</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Status Akun</label>
                                    <select name="status_akun" class="form-control">
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Non-Aktif (Blokir)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Hak Tanda Tangan</label>
                                    <select name="is_pejabat" class="form-control">
                                        <option value="0">Tidak (Pegawai Biasa)</option>
                                        <option value="1">YA (Pejabat)</option>
                                    </select>
                                </div>
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