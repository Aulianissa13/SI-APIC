<?php
// --- PROSES TAMBAH LIBUR ---
if (isset($_POST['tambah_libur'])) {
    $tanggal    = $_POST['tanggal'];
    $keterangan = htmlspecialchars($_POST['keterangan']);

    // Cek apakah tanggal sudah ada?
    $cek = mysqli_query($koneksi, "SELECT * FROM libur_nasional WHERE tanggal='$tanggal'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Tanggal tersebut sudah ada di daftar libur!');</script>";
    } else {
        $simpan = mysqli_query($koneksi, "INSERT INTO libur_nasional (tanggal, keterangan) VALUES ('$tanggal', '$keterangan')");
        if ($simpan) {
            echo "<script>alert('Berhasil menambah hari libur.'); window.location='index.php?page=manage_libur';</script>";
        }
    }
}

// --- PROSES HAPUS LIBUR ---
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $hapus = mysqli_query($koneksi, "DELETE FROM libur_nasional WHERE id_libur='$id'");
    if ($hapus) {
        echo "<script>window.location='index.php?page=manage_libur';</script>";
    }
}
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800" style="font-weight: 700; color: var(--pn-green);">Kelola Hari Libur & Cuti Bersama</h1>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4" style="border-radius: 15px;">
                <div class="card-header py-3 bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-calendar-plus mr-2"></i>Tambah Hari Libur</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Tahun Baru Imlek" required>
                        </div>
                        <button type="submit" name="tambah_libur" class="btn btn-primary btn-block">
                            Simpan ke Database
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="alert alert-info shadow-sm">
                <i class="fas fa-info-circle mr-2"></i>
                <small>Tanggal merah yang diinput di sini <b>tidak akan memotong</b> jatah cuti tahunan pegawai secara otomatis (jika sistem hitung durasi nanti sudah diupdate).</small>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4" style="border-radius: 15px;">
                <div class="card-header py-3" style="border-radius: 15px 15px 0 0;">
                    <h6 class="m-0 font-weight-bold" style="color: var(--pn-green);">Daftar Hari Libur Nasional</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="dataTableLibur" width="100%" cellspacing="0">
                            <thead class="bg-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                // Urutkan dari tanggal terbaru
                                $qry = mysqli_query($koneksi, "SELECT * FROM libur_nasional ORDER BY tanggal DESC");
                                while($d = mysqli_fetch_array($qry)):
                                    $tgl_indo = date('d-m-Y', strtotime($d['tanggal']));
                                    // Cek hari
                                    $nama_hari = date('l', strtotime($d['tanggal']));
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td class="font-weight-bold text-danger"><?= $tgl_indo ?></td>
                                    <td><?= $d['keterangan'] ?></td>
                                    <td class="text-center">
                                        <a href="index.php?page=manage_libur&hapus=<?= $d['id_libur'] ?>" 
                                           class="btn btn-danger btn-sm btn-circle"
                                           onclick="return confirm('Hapus tanggal merah ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#dataTableLibur').DataTable();
    });
</script>