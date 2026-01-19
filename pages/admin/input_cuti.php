<?php
// --- PROSES SIMPAN DATA ---
if (isset($_POST['simpan_cuti'])) {
    $id_user      = $_POST['id_user'];
    $jenis_cuti   = $_POST['jenis_cuti'];
    $tgl_mulai    = $_POST['tgl_mulai'];
    $tgl_selesai  = $_POST['tgl_selesai'];
    $alasan       = htmlspecialchars($_POST['alasan']);
    
    // Hitung Durasi Cuti (Total Hari)
    $start = new DateTime($tgl_mulai);
    $end   = new DateTime($tgl_selesai);
    $interval = $start->diff($end);
    $durasi = $interval->days + 1; // +1 agar hari pertama dihitung

    // Set Status langsung 'Disetujui' karena Admin yang input
    $status = 'Disetujui';
    $tgl_pengajuan = date('Y-m-d');

    // Query Simpan
    $query = "INSERT INTO pengajuan_cuti (id_user, jenis_cuti, tanggal_mulai, tanggal_selesai, durasi, alasan, status, tgl_pengajuan) 
              VALUES ('$id_user', '$jenis_cuti', '$tgl_mulai', '$tgl_selesai', '$durasi', '$alasan', '$status', '$tgl_pengajuan')";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Berhasil menginput data cuti pegawai!'); window.location='index.php?page=input_cuti';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800" style="font-weight: 700; color: var(--pn-green);">Input Cuti Pegawai</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4" style="border-radius: 15px;">
                <div class="card-header py-3 bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-edit mr-2"></i>Form Input Cuti (Oleh Admin)</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Pilih Pegawai</label>
                            <select name="id_user" class="form-control select2" required>
                                <option value="">-- Pilih Nama Pegawai --</option>
                                <?php
                                // Ambil daftar pegawai (role user)
                                $q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE role='user' ORDER BY nama_lengkap ASC");
                                while ($u = mysqli_fetch_array($q_user)) {
                                    echo "<option value='$u[id_user]'>$u[nama_lengkap] (NIP: $u[nip])</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Jenis Cuti</label>
                                    <select name="jenis_cuti" class="form-control" required>
                                        <option value="Cuti Tahunan">Cuti Tahunan</option>
                                        <option value="Cuti Sakit">Cuti Sakit</option>
                                        <option value="Cuti Melahirkan">Cuti Melahirkan</option>
                                        <option value="Cuti Alasan Penting">Cuti Alasan Penting</option>
                                        <option value="Cuti Besar">Cuti Besar</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info py-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-info-circle mr-1"></i> 
                                    Input oleh Admin akan otomatis berstatus <b>DISETUJUI</b>.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Tanggal Mulai</label>
                                    <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Tanggal Selesai</label>
                                    <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Alasan Cuti</label>
                            <textarea name="alasan" class="form-control" rows="3" placeholder="Contoh: Acara keluarga / Sakit Demam" required></textarea>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-secondary mr-2">Reset</button>
                            <button type="submit" name="simpan_cuti" class="btn btn-success px-4">
                                <i class="fas fa-paper-plane mr-2"></i>Simpan & Setujui
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Catatan Admin</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Gunakan fitur ini jika:</p>
                    <ul class="pl-3 text-muted">
                        <li>Pegawai sakit mendadak dan tidak bisa mengakses aplikasi.</li>
                        <li>Pegawai lupa mengajukan cuti namun sudah mendapat izin lisan.</li>
                        <li>Perbaikan data riwayat cuti manual.</li>
                    </ul>
                    <hr>
                    <small class="text-danger">*Data yang diinput di sini akan langsung masuk ke rekap laporan.</small>
                </div>
            </div>
        </div>
    </div>
</div>