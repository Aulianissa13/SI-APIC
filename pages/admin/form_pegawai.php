<?php
/** @var mysqli $koneksi */

// Cek apakah mode EDIT atau TAMBAH
if (isset($_GET['id'])) {
    $id_user = $_GET['id'];
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
    $data = mysqli_fetch_array($query);
    $action = "edit";
    $judul = "Edit Data Pegawai";
    $pass_help = "Kosongkan jika tidak ingin mengubah password.";
    $btn_text = "Update Data";
} else {
    // Mode TAMBAH: Set Default Value (Ganti 'username' jadi 'nip')
    $data = [
        'id_user' => '', 'nama_lengkap' => '', 'nip' => '', 
        'role' => 'pegawai', 'no_telepon' => '',
        'sisa_cuti_n' => 12, 'sisa_cuti_n1' => 0, 'kuota_cuti_sakit' => 3
    ];
    $action = "tambah";
    $judul = "Tambah Pegawai Baru";
    $pass_help = "Wajib diisi untuk pegawai baru.";
    $btn_text = "Simpan Data";
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?php echo $judul; ?></h1>
    <a href="index.php?page=data_pegawai" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="pages/admin/proses_pegawai.php" method="POST">
            <input type="hidden" name="act" value="<?php echo $action; ?>">
            <input type="hidden" name="id_user" value="<?php echo $data['id_user']; ?>">

            <div class="row">
                <div class="col-md-6 border-right">
                    <h6 class="font-weight-bold text-primary mb-3">Informasi Akun</h6>
                    
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?php echo $data['nama_lengkap']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Role / Jabatan</label>
                        <select name="role" class="form-control">
                            <option value="pegawai" <?php echo ($data['role']=='pegawai') ? 'selected' : ''; ?>>Pegawai</option>
                            <option value="admin" <?php echo ($data['role']=='admin') ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>No. Telepon (WhatsApp)</label>
                        <input type="number" name="no_telepon" class="form-control" value="<?php echo $data['no_telepon']; ?>">
                    </div>

                    <div class="form-group">
                        <label>NIP (Nomor Induk Pegawai)</label>
                        <input type="number" name="nip" class="form-control" value="<?php echo $data['nip']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" <?php echo ($action == 'tambah') ? 'required' : ''; ?>>
                        <small class="text-danger"><?php echo $pass_help; ?></small>
                    </div>
                </div>

                <div class="col-md-6">
                    <h6 class="font-weight-bold text-success mb-3">Pengaturan Kuota Cuti</h6>
                    <div class="alert alert-info small">
                        Atur jatah cuti awal untuk pegawai ini. Angka akan berkurang otomatis saat cuti disetujui.
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-8 col-form-label">Cuti Tahunan (N) - Tahun Berjalan</label>
                        <div class="col-sm-4">
                            <input type="number" name="sisa_cuti_n" class="form-control" value="<?php echo $data['sisa_cuti_n']; ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-8 col-form-label">Sisa Cuti Tahun Lalu (N-1)</label>
                        <div class="col-sm-4">
                            <input type="number" name="sisa_cuti_n1" class="form-control" value="<?php echo $data['sisa_cuti_n1']; ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-8 col-form-label">Kuota Cuti Sakit</label>
                        <div class="col-sm-4">
                            <input type="number" name="kuota_cuti_sakit" class="form-control" value="<?php echo $data['kuota_cuti_sakit']; ?>">
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <div class="text-right">
                <button type="submit" class="btn btn-primary btn-lg px-5 icon-btn">
                    <i class="fas fa-save mr-2"></i> <?php echo $btn_text; ?>
                </button>
            </div>
        </form>
    </div>
</div>