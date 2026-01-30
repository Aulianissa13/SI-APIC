<?php

// =========================================================
// A. PROSES PHP (LOGIKA TIDAK DIUBAH)
// =========================================================

// 1. Tambah Libur
if (isset($_POST['tambah_libur'])) {
    $tanggal     = $_POST['tanggal'];
    $keterangan  = htmlspecialchars($_POST['keterangan']);
    $jenis_libur = $_POST['jenis_libur'];

    $cek = mysqli_query($koneksi, "SELECT * FROM libur_nasional WHERE tanggal='$tanggal'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function(){
                Swal.fire({icon:'error', title:'Gagal!', text:'Tanggal sudah ada!', confirmButtonColor:'#004d00'});
            });
        </script>";
    } else {
        $simpan = mysqli_query($koneksi, "INSERT INTO libur_nasional (tanggal, jenis_libur, keterangan) VALUES ('$tanggal', '$jenis_libur', '$keterangan')");
        if ($simpan) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function(){
                    Swal.fire({icon:'success', title:'Berhasil!', text:'Data tersimpan.', showConfirmButton:false, timer:1500})
                        .then(() => { window.location='index.php?page=manage_libur'; });
                });
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
            document.addEventListener('DOMContentLoaded', function(){
                Swal.fire({icon:'success', title:'Terhapus!', text:'Data berhasil dihapus.', showConfirmButton:false, timer:1000})
                    .then(() => { window.location='index.php?page=manage_libur'; });
            });
        </script>";
    }
}

// 3. Hapus Semua
if (isset($_GET['hapus_semua'])) {
    $reset = mysqli_query($koneksi, "TRUNCATE TABLE libur_nasional");
    if ($reset) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function(){
                Swal.fire({icon:'success', title:'Bersih!', text:'Semua data telah dihapus.', showConfirmButton:false, timer:1000})
                    .then(() => { window.location='index.php?page=manage_libur'; });
            });
        </script>";
    }
}
?>

<!-- Pastikan SweetAlert2 sudah ada (kalau sudah dari layout utama, boleh hapus baris ini) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Tambahkan DataTables jika belum ada di layout -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<style>
    :root{
        --pn-green:#004d00;
        --pn-gold:#FFD700;
        --bg:#f8f9fc;
        --text:#1f2937;
    }

    .page-title-pn{
        font-weight:800;
        border-left:5px solid var(--pn-gold);
        padding-left:15px;
        color:var(--pn-green)!important;
        margin: 0;
    }

    .card-pn{
        border:none;
        border-radius:16px;
        box-shadow:0 5px 15px rgba(0,0,0,.1);
        overflow:hidden;
        background:#fff;
    }

    .card-header-pn{
        background:linear-gradient(135deg,var(--pn-green) 0%, #006400 100%);
        color:#fff;
        border-bottom:4px solid var(--pn-gold);
        padding:14px 18px;
    }

    .thead-pn{ background:var(--pn-green); color:#fff; }

    .btn-pn{
        background:var(--pn-green);
        color:#fff;
        border:none;
        border-radius:12px;
        font-weight:800;
        height:44px;
    }
    .btn-pn:hover{ background:#003300; color:var(--pn-gold); }

    .btn-reset{
        border-radius:12px;
        height:40px;
        font-weight:800;
        white-space:nowrap;
    }

    .form-control{
        border-radius:12px;
        height:44px;
    }

    /* Search bar putih pill seperti halaman lain */
    .search-wrap{ position:relative; }
    .header-search{
        border-radius:999px;
        border:1px solid #e5e7eb;
        background:#fff;
        color:#333;
        padding:10px 44px 10px 16px;
        font-size:.95rem;
        height:40px;
        width:280px;
        transition:.2s;
    }
    .header-search:focus{
        outline:none;
        border-color:#006837;
        box-shadow:0 0 0 .2rem rgba(0,104,55,.18);
    }
    .header-search::placeholder{ color:#9aa0a6; }
    .search-ico{
        position:absolute;
        right:14px;
        top:50%;
        transform:translateY(-50%);
        color:#9aa0a6;
        pointer-events:none;
    }

    /* Table rapi */
    .table td, .table th{ padding:.75rem .75rem; vertical-align:middle; }
    .table-hover tbody tr:hover{ background:#f7fbf7; }

    .badge-pill-soft{
        border-radius:999px;
        padding:8px 12px;
        font-weight:800;
        font-size:13px;
    }

    /* DataTables: sembunyikan bawaan search & length */
    .dataTables_filter, .dataTables_length { display:none !important; }
    .dataTables_info{ color:#6b7280; font-size:12px; padding-top:8px; }
    .dataTables_wrapper .dataTables_paginate{ padding-top:10px; }

    .dataTables_wrapper .dataTables_paginate .paginate_button{
        padding:.25rem .75rem !important;
        margin-left:6px !important;
        border-radius:10px !important;
        border:1px solid #e5e7eb !important;
        background:#fff !important;
        color:var(--pn-green) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current{
        background:var(--pn-green) !important;
        color:#fff !important;
        border:1px solid var(--pn-green) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
        background:#eaf6ea !important;
        border-color:#c7e6c7 !important;
        color:var(--pn-green) !important;
    }
</style>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <h1 class="h3 text-gray-800 page-title-pn">Kelola Hari Libur & Cuti Bersama</h1>
    </div>

    <div class="row">

        <!-- KIRI: FORM TAMBAH -->
        <div class="col-lg-4">
            <div class="card card-pn mb-4">
                <div class="card-header-pn">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-calendar-plus mr-2"></i>Tambah Hari Libur
                    </h6>
                </div>
                <div class="card-body">

                    <form method="POST" autocomplete="off">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Jenis Libur</label>
                            <select name="jenis_libur" class="form-control" required>
                                <option value="nasional">🔴 Libur Nasional</option>
                                <option value="cuti_bersama">🟢 Cuti Bersama</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Tahun Baru Imlek" required>
                        </div>

                        <button type="submit" name="tambah_libur" class="btn btn-pn btn-block">
                            <i class="fas fa-save mr-2"></i>Simpan Data
                        </button>
                    </form>

                </div>
            </div>

            <div class="alert alert-warning shadow-sm" style="border-left:4px solid #f6c23e; border-radius:12px;">
                <small><b>Info:</b> Tanggal ini tidak memotong kuota cuti.</small>
            </div>
        </div>

        <!-- KANAN: TABEL -->
        <div class="col-lg-8">
            <div class="card card-pn mb-4">

                <div class="card-header-pn d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-white mb-2 mb-md-0">
                        <i class="fas fa-list mr-2"></i>Daftar Hari Libur
                    </h6>

                    <div class="d-flex align-items-center">
                        <div class="search-wrap mr-2">
                            <input type="text" id="customSearchBox" class="header-search" placeholder="Cari Nama / Tanggal...">
                            <i class="fas fa-search search-ico"></i>
                        </div>

                        <?php
                        $cek_data = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM libur_nasional"));
                        if($cek_data > 0):
                        ?>
                            <button type="button"
                                    onclick="konfirmasiHapusSemua('index.php?page=manage_libur&hapus_semua=true')"
                                    class="btn btn-danger btn-reset shadow-sm">
                                <i class="fas fa-trash-alt mr-1"></i>Reset
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table table-bordered table-hover" id="dataTableLibur" width="100%" cellspacing="0">
                            <thead class="thead-pn">
                                <tr class="text-center">
                                    <th width="5%">No</th>
                                    <th width="25%">Tanggal</th>
                                    <th width="18%">Jenis</th>
                                    <th>Keterangan</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $qry = mysqli_query($koneksi, "SELECT * FROM libur_nasional ORDER BY tanggal ASC");

                                $hari_indo = [
                                    'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
                                    'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
                                ];
                                $bulan_indo = [
                                    '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                                    '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
                                ];

                                while($d = mysqli_fetch_array($qry)):
                                    $day_en = date('l', strtotime($d['tanggal']));
                                    $nama_hari = $hari_indo[$day_en] ?? $day_en;

                                    $tgl_split = explode('-', $d['tanggal']); // YYYY-MM-DD
                                    $nama_bulan = $bulan_indo[$tgl_split[1]] ?? $tgl_split[1];
                                    $tanggal_full_indo = $tgl_split[2]." ".$nama_bulan." ".$tgl_split[0];
                                ?>
                                <tr>
                                    <td class="text-center font-weight-bold"><?= $no++ ?></td>

                                    <td>
                                        <div class="font-weight-bold text-dark"><?= $tanggal_full_indo ?></div>
                                        <small class="text-muted"><?= $nama_hari ?></small>
                                    </td>

                                    <td class="text-center">
                                        <?php if($d['jenis_libur'] == 'nasional'): ?>
                                            <span class="badge badge-danger badge-pill-soft">Nasional</span>
                                        <?php else: ?>
                                            <span class="badge badge-success badge-pill-soft">Cuti Bersama</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= $d['keterangan'] ?></td>

                                    <td class="text-center">
                                        <a href="javascript:void(0);"
                                           onclick="konfirmasiHapus('index.php?page=manage_libur&hapus=<?= $d['id_libur'] ?>')"
                                           class="btn btn-danger btn-sm shadow-sm" style="border-radius:12px; padding:8px 12px;" title="Hapus">
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
// DataTable initialization moved to footer.php

// SweetAlert hapus 1
function konfirmasiHapus(url) {
    Swal.fire({
        title: 'Hapus data ini?',
        text: 'Data akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = url;
    });
}

// SweetAlert reset semua
function konfirmasiHapusSemua(url) {
    Swal.fire({
        title: 'Hapus SEMUA data?',
        text: 'Tabel akan dikosongkan total!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Kosongkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = url;
    });
}
</script>
