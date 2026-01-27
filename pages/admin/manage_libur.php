<?php
// --- A. PROSES PHP (LOGIKA TIDAK DIUBAH) ---

// 1. Tambah Libur
if (isset($_POST['tambah_libur'])) {
    $tanggal     = $_POST['tanggal'];
    $keterangan  = htmlspecialchars($_POST['keterangan']);
    $jenis_libur = $_POST['jenis_libur'];

    $cek = mysqli_query($koneksi, "SELECT * FROM libur_nasional WHERE tanggal='$tanggal'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>Swal.fire({icon: 'error', title: 'Gagal!', text: 'Tanggal sudah ada!', confirmButtonColor: '#3085d6'});</script>";
    } else {
        $simpan = mysqli_query($koneksi, "INSERT INTO libur_nasional (tanggal, jenis_libur, keterangan) VALUES ('$tanggal', '$jenis_libur', '$keterangan')");
        if ($simpan) {
            echo "<script>Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Data tersimpan.', showConfirmButton: false, timer: 1500}).then(() => { window.location='index.php?page=manage_libur'; });</script>";
        }
    }
}

// 2. Hapus Satu
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $hapus = mysqli_query($koneksi, "DELETE FROM libur_nasional WHERE id_libur='$id'");
    if ($hapus) {
        echo "<script>Swal.fire({icon: 'success', title: 'Terhapus!', text: 'Data berhasil dihapus.', showConfirmButton: false, timer: 1000}).then(() => { window.location='index.php?page=manage_libur'; });</script>";
    }
}

// 3. Hapus Semua
if (isset($_GET['hapus_semua'])) {
    $reset = mysqli_query($koneksi, "TRUNCATE TABLE libur_nasional");
    if ($reset) {
        echo "<script>Swal.fire({icon: 'success', title: 'Bersih!', text: 'Semua data telah dihapus.', showConfirmButton: false, timer: 1000}).then(() => { window.location='index.php?page=manage_libur'; });</script>";
    }
}
?>

<style>
    :root {
        --pn-green: #004d00;
        --pn-gold: #FFD700;
    }
    .card-pn {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .card-header-pn {
        background: linear-gradient(135deg, var(--pn-green) 0%, #006400 100%);
        color: white;
        border-bottom: 4px solid var(--pn-gold);
        padding: 15px 20px;
    }
    .btn-pn {
        background-color: var(--pn-green);
        color: white;
        border-radius: 8px;
    }
    .btn-pn:hover {
        background-color: #003300;
        color: var(--pn-gold);
    }
    
    /* Styling Search Bar di Header */
    .header-search {
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.3);
        background: rgba(255,255,255,0.15);
        color: white;
        padding: 5px 15px;
        font-size: 0.9rem;
        transition: all 0.3s;
    }
    .header-search:focus {
        background: white;
        color: #333;
        outline: none;
        border-color: white;
        box-shadow: 0 0 10px rgba(255,255,255,0.5);
    }
    .header-search::placeholder {
        color: rgba(255,255,255,0.7);
    }

    /* Pagination Styling agar rapi */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3em 0.8em;
        margin-left: 5px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--pn-green) !important;
        color: white !important;
        border: 1px solid var(--pn-green);
    }
    
    /* Sembunyikan Search & Length Bawaan DataTables */
    .dataTables_filter, .dataTables_length {
        display: none !important;
    }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800" style="font-weight: 700; border-left: 5px solid var(--pn-gold); padding-left: 15px; color: var(--pn-green) !important;">
            Kelola Hari Libur & Cuti Bersama
        </h1>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card card-pn mb-4">
                <div class="card-header-pn">
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-calendar-plus mr-2"></i>Tambah Hari Libur</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" style="border-radius: 8px;" required>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Jenis Libur</label>
                            <select name="jenis_libur" class="form-control" style="border-radius: 8px;" required>
                                <option value="nasional">🔴 Libur Nasional</option>
                                <option value="cuti_bersama">🟢 Cuti Bersama</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" style="border-radius: 8px;" placeholder="Contoh: Tahun Baru Imlek" required>
                        </div>
                        <button type="submit" name="tambah_libur" class="btn btn-pn btn-block py-2 font-weight-bold">
                            <i class="fas fa-save mr-2"></i>Simpan Data
                        </button>
                    </form>
                </div>
            </div>
            <div class="alert alert-warning shadow-sm" style="border-left: 4px solid #f6c23e; border-radius: 10px;">
                <small><b>Info:</b> Tanggal ini tidak memotong kuota cuti.</small>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-pn mb-4">
                <div class="card-header-pn d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-white mb-2 mb-md-0">
                        <i class="fas fa-list mr-2"></i>Daftar Hari Libur
                    </h6>
                    
                    <div class="d-flex align-items-center">
                        <div class="mr-2 position-relative">
                            <input type="text" id="customSearchBox" class="header-search" placeholder="Cari data..." style="width: 200px;">
                        </div>

                        <?php 
                        $cek_data = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM libur_nasional"));
                        if($cek_data > 0): 
                        ?>
                            <button onclick="konfirmasiHapusSemua('index.php?page=manage_libur&hapus_semua=true')" 
                                    class="btn btn-danger btn-sm shadow-sm" style="border-radius: 20px; white-space: nowrap;">
                                <i class="fas fa-trash-alt"></i> Reset
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="dataTableLibur" width="100%" cellspacing="0">
                            <thead style="background-color: var(--pn-green); color: white;">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Keterangan</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $qry = mysqli_query($koneksi, "SELECT * FROM libur_nasional ORDER BY tanggal ASC");
                                
                                $hari_indo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                                $bulan_indo = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];

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
                                           class="btn btn-danger btn-sm btn-circle" title="Hapus">
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
    document.addEventListener("DOMContentLoaded", function(event) { 
        
        // Cek jQuery
        if (typeof jQuery == 'undefined') {
            console.error('jQuery belum diload!');
            return;
        }

        $(document).ready(function() {
            // Hancurkan datatable lama jika ada
            if ($.fn.DataTable.isDataTable('#dataTableLibur')) {
                $('#dataTableLibur').DataTable().destroy();
            }

            // Inisialisasi DataTable Baru dengan Pagination
            var table = $('#dataTableLibur').DataTable({
                "bDestroy": true,
                "ordering": false, // Matikan sorting default agar sesuai query
                "dom": 'rtp',      // r=processing, t=table, p=pagination (Hapus 'i' info biar bersih)
                "pageLength": 5,   // Tampilkan 5 baris per halaman (bisa diganti 10)
                "language": {
                    "emptyTable": "Belum ada data hari libur",
                    "zeroRecords": "Data tidak ditemukan",
                    "paginate": {
                        "next": "<i class='fas fa-chevron-right'></i>",
                        "previous": "<i class='fas fa-chevron-left'></i>"
                    }
                }
            });

            // Hubungkan Custom Search Input di Header ke DataTables
            $('#customSearchBox').on('keyup', function() {
                table.search(this.value).draw();
            });
        });
    });

    // Alert Hapus Satu
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
            title: 'Hapus SEMUA Data?',
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