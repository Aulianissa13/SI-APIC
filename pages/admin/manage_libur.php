<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
// --- A. PROSES PHP ---

// 1. Tambah Libur
if (isset($_POST['tambah_libur'])) {
    $tanggal     = $_POST['tanggal'];
    $keterangan  = htmlspecialchars($_POST['keterangan']);
    $jenis_libur = $_POST['jenis_libur'];

    $cek = mysqli_query($koneksi, "SELECT * FROM libur_nasional WHERE tanggal='$tanggal'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
            Swal.fire({icon: 'error', title: 'Gagal!', text: 'Tanggal sudah ada!', confirmButtonColor: '#3085d6'});
        </script>";
    } else {
        $simpan = mysqli_query($koneksi, "INSERT INTO libur_nasional (tanggal, jenis_libur, keterangan) VALUES ('$tanggal', '$jenis_libur', '$keterangan')");
        if ($simpan) {
            echo "<script>
                Swal.fire({
                    icon: 'success', title: 'Berhasil!', text: 'Data tersimpan.', showConfirmButton: false, timer: 1500
                }).then(() => { window.location='index.php?page=manage_libur'; });
            </script>";
        }
    }
}

// 2. Hapus Satu
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $hapus = mysqli_query($koneksi, "DELETE FROM libur_nasional WHERE id_libur='$id'");
    if ($hapus) {
        echo "<script>
            Swal.fire({icon: 'success', title: 'Terhapus!', text: 'Data berhasil dihapus.', showConfirmButton: false, timer: 1000})
            .then(() => { window.location='index.php?page=manage_libur'; });
        </script>";
    }
}

// 3. Hapus Semua
if (isset($_GET['hapus_semua'])) {
    $reset = mysqli_query($koneksi, "TRUNCATE TABLE libur_nasional");
    if ($reset) {
        echo "<script>
            Swal.fire({icon: 'success', title: 'Bersih!', text: 'Semua data telah dihapus.', showConfirmButton: false, timer: 1000})
            .then(() => { window.location='index.php?page=manage_libur'; });
        </script>";
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
                <div class="card-header py-3" style="border-radius: 15px 15px 0 0;">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-plus mr-2"></i>Tambah Hari Libur
                    </h6>
                </div>
                
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="font-weight-bold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Jenis Libur</label>
                            <select name="jenis_libur" class="form-control" required>
                                <option value="nasional">🔴 Libur Nasional</option>
                                <option value="cuti_bersama">🟢 Cuti Bersama</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Tahun Baru Imlek" required>
                        </div>
                        <button type="submit" name="tambah_libur" class="btn btn-primary btn-block shadow-sm">
                            <i class="fas fa-save mr-2"></i>Simpan ke Database
                        </button>
                    </form>
                </div>
            </div>
            <div class="alert alert-warning shadow-sm"><small><b>Info:</b> Tanggal ini tidak memotong kuota cuti.</small></div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4" style="border-radius: 15px;">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="border-radius: 15px 15px 0 0;">
                    <h6 class="m-0 font-weight-bold" style="color: var(--pn-green);">Daftar Hari Libur</h6>
                    
                    <?php 
                    $cek_data = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM libur_nasional"));
                    if($cek_data > 0): 
                    ?>
                        <button onclick="konfirmasiHapusSemua('index.php?page=manage_libur&hapus_semua=true')" 
                                class="btn btn-danger btn-sm shadow-sm">
                            <i class="fas fa-trash-alt mr-2"></i>Hapus Semua Data
                        </button>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="dataTableLibur" width="100%" cellspacing="0">
                            <thead class="bg-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                // ORDER BY tanggal ASC
                                $qry = mysqli_query($koneksi, "SELECT * FROM libur_nasional ORDER BY tanggal ASC");
                                
                                // Array Konversi
                                $hari_indo = [
                                    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                                    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
                                ];
                                $bulan_indo = [
                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', 
                                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', 
                                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                ];

                                while($d = mysqli_fetch_array($qry)):
                                    $day_en = date('l', strtotime($d['tanggal']));
                                    $nama_hari = $hari_indo[$day_en];
                                    $tgl_split = explode('-', $d['tanggal']);
                                    $nama_bulan = $bulan_indo[$tgl_split[1]];
                                    $tanggal_full_indo = $tgl_split[2] . " " . $nama_bulan . " " . $tgl_split[0];
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?= $tanggal_full_indo ?></div>
                                        <small class="text-muted"><?= $nama_hari ?></small>
                                    </td>
                                    <td>
                                        <?php if($d['jenis_libur'] == 'nasional'): ?>
                                            <span class="badge badge-danger">Nasional</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Cuti Bersama</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $d['keterangan'] ?></td>
                                    <td class="text-center">
                                        <a href="javascript:void(0);" 
                                           onclick="konfirmasiHapus('index.php?page=manage_libur&hapus=<?= $d['id_libur'] ?>')"
                                           class="btn btn-danger btn-sm btn-circle shadow-sm">
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
        $('#dataTableLibur').DataTable({
            "ordering": false 
        });
    });

    function konfirmasiHapus(url) {
        Swal.fire({
            title: 'Hapus data ini?',
            text: "Data akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }

    function konfirmasiHapusSemua(url) {
        Swal.fire({
            title: 'AWAS! Hapus SEMUA Data?',
            text: "Tabel akan dikosongkan total!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Kosongkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }
</script>