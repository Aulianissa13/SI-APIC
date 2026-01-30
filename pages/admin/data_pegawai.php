<?php
include_once "../../config/database.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$swal_script = "";
$list_atasan = [];
$q_all_users = mysqli_query($koneksi, "SELECT id_user, nama_lengkap FROM users ORDER BY nama_lengkap ASC");

// ==========================================
// 1. LOGIKA SIMPAN NAMA PEJABAT (MANUAL TEXT)
// ==========================================
if(isset($_POST['simpan_pejabat'])){
    $ketua_nama = mysqli_real_escape_string($koneksi, $_POST['ketua_nama']);
    $ketua_nip  = mysqli_real_escape_string($koneksi, $_POST['ketua_nip']);
    $wakil_nama = mysqli_real_escape_string($koneksi, $_POST['wakil_nama']);
    $wakil_nip  = mysqli_real_escape_string($koneksi, $_POST['wakil_nip']);
    
    // Cek apakah data setting sudah ada (row id=1)
    $cek_set = mysqli_query($koneksi, "SELECT * FROM tbl_setting_instansi WHERE id_setting='1'");
    if(mysqli_num_rows($cek_set) == 0){
        // Jika belum ada, buat baru
        mysqli_query($koneksi, "INSERT INTO tbl_setting_instansi (id_setting, nama_instansi) VALUES (1, 'Pengadilan Negeri')");
    }

    // Update Data
    $update = mysqli_query($koneksi, "UPDATE tbl_setting_instansi SET 
        ketua_nama='$ketua_nama', 
        ketua_nip='$ketua_nip',
        wakil_nama='$wakil_nama',
        wakil_nip='$wakil_nip'
        WHERE id_setting='1'");
    
    if($update){
        echo "<script>
            Swal.fire({
                title: 'Berhasil',
                text: 'Data Pejabat berhasil disimpan!',
                icon: 'success'
            }).then(() => {
                window.location.href='index.php?page=data_pegawai';
            });
        </script>";
    }
}

// 2. AMBIL DATA (DENGAN PENANGANAN ERROR)
$query_set   = mysqli_query($koneksi, "SELECT * FROM tbl_setting_instansi WHERE id_setting='1'");
$set_instansi = mysqli_fetch_array($query_set);

// Jaga-jaga jika data kosong agar tidak error "Undefined array key"
if(!$set_instansi) {
    $set_instansi = [
        'ketua_nama' => '', 'ketua_nip' => '',
        'wakil_nama' => '', 'wakil_nip' => ''
    ];
}
if (isset($_POST['tambah'])) {
    $nip          = $_POST['nip'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $password     = md5($_POST['password']); 
    $jabatan      = $_POST['jabatan']; 
    $pangkat      = $_POST['pangkat']; 
    $role         = isset($_POST['role']) ? $_POST['role'] : 'user';
    $id_atasan    = !empty($_POST['id_atasan']) ? $_POST['id_atasan'] : 0; 
    $status_akun  = 'aktif'; 
    $ct_n         = $_POST['sisa_cuti_n'];
    $ct_n1        = $_POST['sisa_cuti_n1'];
    $ct_sakit     = $_POST['kuota_cuti_sakit'];
    $cek_nip = mysqli_query($koneksi, "SELECT nip FROM users WHERE nip='$nip'");
    if (mysqli_num_rows($cek_nip) > 0) {
        $swal_script = "Swal.fire({ title: 'Gagal!', text: 'NIP sudah terdaftar!', icon: 'error', confirmButtonColor: '#006837' });";
    } else {
        $query = mysqli_query($koneksi, "INSERT INTO users (nip, nama_lengkap, password, jabatan, pangkat, role, id_atasan, status_akun, sisa_cuti_n, sisa_cuti_n1, kuota_cuti_sakit) VALUES ('$nip', '$nama_lengkap', '$password', '$jabatan', '$pangkat', '$role', '$id_atasan', '$status_akun', '$ct_n', '$ct_n1', '$ct_sakit')");

        if ($query) {
            $swal_script = "Swal.fire({ title: 'Berhasil!', text: 'Pegawai baru ditambahkan.', icon: 'success', confirmButtonColor: '#006837' }).then(() => { window.location = 'index.php?page=data_pegawai'; });";
        } else {
            $swal_script = "Swal.fire({ title: 'Error!', text: '" . mysqli_error($koneksi) . "', icon: 'error', confirmButtonColor: '#006837' });";
        }
    }
}
if (isset($_POST['edit'])) {
    $id_user      = $_POST['id_user'];
    $nip          = $_POST['nip'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $jabatan      = $_POST['jabatan'];
    $pangkat      = $_POST['pangkat']; 
    $role         = isset($_POST['role']) ? $_POST['role'] : 'user';
    $status_akun  = $_POST['status_akun']; 
    $id_atasan    = !empty($_POST['id_atasan']) ? $_POST['id_atasan'] : 0;
    $ct_n         = $_POST['sisa_cuti_n'];
    $ct_n1        = $_POST['sisa_cuti_n1'];
    $ct_sakit     = $_POST['kuota_cuti_sakit'];
    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        $query_update = "UPDATE users SET nip='$nip', nama_lengkap='$nama_lengkap', password='$password', jabatan='$jabatan', pangkat='$pangkat', role='$role', status_akun='$status_akun', id_atasan='$id_atasan', sisa_cuti_n='$ct_n', sisa_cuti_n1='$ct_n1', kuota_cuti_sakit='$ct_sakit' WHERE id_user='$id_user'";
    } else {
        $query_update = "UPDATE users SET nip='$nip', nama_lengkap='$nama_lengkap', jabatan='$jabatan', pangkat='$pangkat', role='$role', status_akun='$status_akun', id_atasan='$id_atasan', sisa_cuti_n='$ct_n', sisa_cuti_n1='$ct_n1', kuota_cuti_sakit='$ct_sakit' WHERE id_user='$id_user'";
    }
    $run_update = mysqli_query($koneksi, $query_update);
    if ($run_update) {
        $swal_script = "Swal.fire({ title: 'Berhasil!', text: 'Data Pegawai diperbarui.', icon: 'success', confirmButtonColor: '#006837' }).then(() => { window.location = 'index.php?page=data_pegawai'; });";
    } else {
        $swal_script = "Swal.fire({ title: 'Gagal!', text: '" . mysqli_error($koneksi) . "', icon: 'error', confirmButtonColor: '#006837' });";
    }
}
if (isset($_GET['toggle_status'])) {
    $id = $_GET['toggle_status'];
    $cek = mysqli_query($koneksi, "SELECT status_akun FROM users WHERE id_user='$id'");
    $row = mysqli_fetch_array($cek);
    $new_status = ($row['status_akun'] == 'aktif') ? 'tidak_aktif' : 'aktif';
    $update_status = mysqli_query($koneksi, "UPDATE users SET status_akun='$new_status' WHERE id_user='$id'");
    if ($update_status) {
        echo "<script>window.location='index.php?page=data_pegawai';</script>";
    }
}
// ==========================================
// --- LOGIKA BARU: PENCARIAN & PAGINATION ---
// ==========================================
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

$keyword = "";
$where_clause = "";
$url_pencarian = "";
if (isset($_GET['cari'])) {
    $keyword = $_GET['cari'];
    $where_clause = "WHERE u.nama_lengkap LIKE '%$keyword%' OR u.nip LIKE '%$keyword%'";
    $url_pencarian = "&cari=" . $keyword;
}
$query_jumlah = mysqli_query($koneksi, "SELECT count(*) AS total FROM users u $where_clause");
$data_jumlah = mysqli_fetch_assoc($query_jumlah);
$total_data = $data_jumlah['total'];
$total_halaman = ceil($total_data / $batas);

$nomor = $halaman_awal + 1;
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --pn-green: #004d00;       /* Hijau Tua Pengadilan */
        --pn-green-light: #e6f0eb; /* Hijau Soft */
        --pn-gold: #F9A825;        /* Emas */
        --pn-gold-light: #fff8e1;  /* Emas Soft */
        --text-dark: #2c3e50;
    }

    body, h1, h2, h3, h4, h5, table, input, button, .btn, .modal-content {
        font-family: 'Poppins', sans-serif !important;
    }

    /* Card Utama */
    .card-pn {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        background-color: #fff;
        overflow: hidden;
    }

    .card-header-pn {
        background: linear-gradient(135deg, var(--pn-green) 0%, #003300 100%);
        color: white;
        padding: 20px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Tombol */
    .btn-pn {
        border-radius: 10px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        padding: 8px 16px;
    }
    .btn-add {
        background-color: var(--pn-gold);
        color: var(--pn-green);
        font-weight: 700;
    }
    .btn-add:hover {
        background-color: #f0a020;
        color: #003300;
        transform: translateY(-2px);
    }
    .btn-setting {
        background-color: rgba(255,255,255,0.2);
        color: white;
    }
    .btn-setting:hover {
        background-color: rgba(255,255,255,0.3);
    }

    /* Search Box */
    .search-box {
        position: relative;
        max-width: 300px;
    }
    .search-input {
        border-radius: 50px !important;
        padding-left: 20px;
        padding-right: 40px;
        border: 1px solid #e3e6f0;
        background-color: #f8f9fc;
        height: 40px;
        font-size: 0.9rem;
    }
    .search-input:focus {
        background-color: #fff;
        border-color: var(--pn-green);
        box-shadow: 0 0 0 0.2rem rgba(0, 77, 0, 0.1);
    }
    .search-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
    }

    /* Tabel Custom */
    .table-custom {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .table-custom thead th {
        background-color: var(--pn-green);
        color: white;
        padding: 15px;
        font-weight: 500;
        font-size: 0.85rem;
        text-transform: uppercase;
        border: none;
    }
    .table-custom thead th:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    .table-custom thead th:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

    .table-custom tbody tr {
        background-color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        transition: transform 0.2s;
    }
    .table-custom tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .table-custom td {
        padding: 12px 15px;
        vertical-align: middle;
        border-top: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
        color: #555;
        font-size: 0.9rem;
    }
    .table-custom td:first-child { border-left: 1px solid #f0f0f0; border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    .table-custom td:last-child { border-right: 1px solid #f0f0f0; border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

    /* Badges & Actions */
    .badge-status-active { background-color: var(--pn-green-light); color: var(--pn-green); border: 1px solid var(--pn-green); padding: 3px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; }
    .badge-status-inactive { background-color: #ffebee; color: #c62828; border: 1px solid #ef9a9a; padding: 3px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; }
    .badge-role-admin { background-color: var(--pn-gold-light); color: #b07d0b; border: 1px solid var(--pn-gold); padding: 3px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; }

    .btn-action-edit { background-color: #fff3cd; color: #856404; padding: 6px 10px; border-radius: 8px; margin-right: 5px; border:none; }
    .btn-action-edit:hover { background-color: #ffeeba; }
    
    .btn-action-off { background-color: #f8d7da; color: #721c24; padding: 6px 10px; border-radius: 8px; border:none; }
    .btn-action-off:hover { background-color: #f5c6cb; }

    .btn-action-on { background-color: #d4edda; color: #155724; padding: 6px 10px; border-radius: 8px; border:none; }
    .btn-action-on:hover { background-color: #c3e6cb; }

    /* Pagination */
    .pagination .page-link { color: var(--pn-green); border-radius: 8px; margin: 0 2px; border: none; font-weight: 500; }
    .pagination .page-item.active .page-link { background-color: var(--pn-green); color: white; }
    .pagination .page-item.disabled .page-link { color: #ccc; }
</style>

<div class="container-fluid mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-gray-800 font-weight-bold">Data Pegawai & Cuti</h3>
    </div>

    <div class="card card-pn">
        
        <div class="card-header-pn">
            <div>
                <h5 class="m-0 font-weight-bold"><i class="fas fa-users mr-2"></i> Daftar Pegawai</h5>
            </div>
            <div>
                <button class="btn btn-pn btn-setting mr-2" data-toggle="modal" data-target="#modalPejabat">
                    <i class="fas fa-user-tie"></i> Atur Pejabat
                </button>
                <button class="btn btn-pn btn-add shadow-sm" data-toggle="modal" data-target="#modalTambah">
                    <i class="fas fa-plus"></i> Tambah Pegawai
                </button>
            </div>
        </div>

        <div class="card-body">
            
            <div class="row mb-4 justify-content-between align-items-center">
                <div class="col-md-4">
                     <form action="index.php" method="GET" onsubmit="return false;">
                        <input type="hidden" name="page" value="data_pegawai">
                        <div class="search-box">
                            <input type="text" name="cari" id="keyword" class="form-control search-input" 
                                   placeholder="Cari nama atau NIP..." value="<?php echo $keyword; ?>" autocomplete="off">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                    </form>
                </div>
                <div class="col-md-8 text-right">
                    <?php if(!empty($keyword)): ?>
                        <span class="badge badge-light border text-dark p-2">
                            Hasil: "<strong><?php echo $keyword; ?></strong>" 
                            <a href="index.php?page=data_pegawai" class="text-danger ml-2"><i class="fas fa-times"></i> Reset</a>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div id="area_tabel">
                
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th class="text-left" width="25%">Pegawai</th>
                                <th class="text-left">Jabatan</th>
                                <th width="10%">Sisa Cuti</th>
                                <th width="10%">Cuti Lalu</th>
                                <th width="10%">Sakit</th>
                                <th>Atasan</th>
                                <th width="12%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query PHP Asli Anda
                            $query_string = "SELECT u.*, a.nama_lengkap AS nama_atasan 
                                             FROM users u 
                                             LEFT JOIN users a ON u.id_atasan = a.id_user 
                                             $where_clause
                                             ORDER BY u.id_user DESC 
                                             LIMIT $halaman_awal, $batas";
                            
                            $query_pegawai = mysqli_query($koneksi, $query_string);
                            
                            if (!$query_pegawai) {
                                echo "<tr><td colspan='8' class='text-center text-danger'>Error: " . mysqli_error($koneksi) . "</td></tr>";
                            } elseif (mysqli_num_rows($query_pegawai) == 0) {
                                echo "<tr><td colspan='8' class='text-center text-secondary py-5'><i class='fas fa-folder-open fa-3x mb-3'></i><br>Data tidak ditemukan.</td></tr>";
                            } else {

                            while ($data = mysqli_fetch_array($query_pegawai)) {
                                $is_active = ($data['status_akun'] == 'aktif');
                                $row_opacity = $is_active ? '' : 'style="opacity: 0.6; background-color: #f9f9f9;"';
                                $pangkat_text = isset($data['pangkat']) && !empty($data['pangkat']) ? $data['pangkat'] : '-';
                            ?>
                            <tr <?php echo $row_opacity; ?>>
                                <td class="text-center font-weight-bold text-secondary"><?php echo $nomor++; ?></td>
                                
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 0.95rem;"><?php echo $data['nama_lengkap']; ?></div>
                                    <div class="small text-secondary mb-1">NIP: <?php echo $data['nip']; ?></div>
                                    
                                    <?php if($is_active): ?>
                                        <span class="badge-status-active">AKTIF</span>
                                    <?php else: ?>
                                        <span class="badge-status-inactive">NONAKTIF</span>
                                    <?php endif; ?>

                                    <?php if($data['role'] == 'admin'): ?>
                                        <span class="badge-role-admin ml-1">ADMIN</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <div class="font-weight-600 text-main-green"><?php echo $data['jabatan']; ?></div>
                                    <small class="text-muted"><?php echo $pangkat_text; ?></small>
                                </td>

                                <td class="text-center">
                                    <span class="font-weight-bold text-dark" style="font-size:1.1rem;"><?php echo $data['sisa_cuti_n']; ?></span>
                                </td>
                                <td class="text-center text-secondary"><?php echo $data['sisa_cuti_n1']; ?></td>
                                <td class="text-center text-info"><?php echo $data['kuota_cuti_sakit']; ?></td>
                                
                                <td><small class="font-weight-600 text-secondary"><?php echo $data['nama_atasan'] ? $data['nama_atasan'] : '-'; ?></small></td>
                                
                                <td class="text-center">
                                    
                                    <button type="button" class="btn-action-edit" 
                                        data-toggle="modal" data-target="#modalEdit<?php echo $data['id_user']; ?>" title="Edit Data">
                                        <i class="fas fa-pen fa-sm"></i>
                                    </button>
                                    
                                    <?php if($is_active): ?>
                                        <button onclick="konfirmasiStatus('<?php echo $data['id_user']; ?>', 'nonaktifkan', '<?php echo addslashes($data['nama_lengkap']); ?>')" 
                                            class="btn-action-off" title="Nonaktifkan Akun">
                                            <i class="fas fa-power-off fa-sm"></i>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="konfirmasiStatus('<?php echo $data['id_user']; ?>', 'aktifkan', '<?php echo addslashes($data['nama_lengkap']); ?>')" 
                                            class="btn-action-on" title="Aktifkan Akun">
                                            <i class="fas fa-check fa-sm"></i>
                                        </button>
                                    <?php endif; ?>

                                    <div class="modal fade" id="modalEdit<?php echo $data['id_user']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg"> 
                                            <div class="modal-content">
                                                <div class="modal-header" style="background-color: var(--pn-gold); color: var(--pn-green);">
                                                    <h5 class="modal-title font-weight-bold"><i class="fas fa-edit"></i> Edit Pegawai</h5>
                                                    <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body text-left text-dark">
                                                        <input type="hidden" name="id_user" value="<?php echo $data['id_user']; ?>">
                                                        
                                                        <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3">Identitas</h6>
                                                        <div class="row">
                                                            <div class="col-md-6 form-group">
                                                                <label class="font-weight-bold small">NIP</label>
                                                                <input type="text" name="nip" class="form-control" value="<?php echo $data['nip']; ?>" required>
                                                            </div>
                                                            <div class="col-md-6 form-group">
                                                                <label class="font-weight-bold small">Nama Lengkap</label>
                                                                <input type="text" name="nama_lengkap" class="form-control" value="<?php echo $data['nama_lengkap']; ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-4 form-group">
                                                                <label class="font-weight-bold small">Jabatan</label>
                                                                <input type="text" name="jabatan" class="form-control" value="<?php echo $data['jabatan']; ?>" required>
                                                            </div>
                                                            <div class="col-md-4 form-group">
                                                                <label class="font-weight-bold small">Pangkat</label>
                                                                <input type="text" name="pangkat" class="form-control" value="<?php echo isset($data['pangkat']) ? $data['pangkat'] : ''; ?>">
                                                            </div>
                                                            <div class="col-md-4 form-group">
                                                                <label class="font-weight-bold small">Status Akun</label>
                                                                <select name="status_akun" class="form-control">
                                                                    <option value="aktif" <?php echo ($data['status_akun']=='aktif')?'selected':''; ?>>Aktif</option>
                                                                    <option value="tidak_aktif" <?php echo ($data['status_akun']=='tidak_aktif')?'selected':''; ?>>Tidak Aktif</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3 mt-3">Kuota Cuti</h6>
                                                        <div class="row bg-light p-3 rounded mx-0 mb-3">
                                                            <div class="col-md-4"><label class="small font-weight-bold">Sisa Tahun Ini</label><input type="number" name="sisa_cuti_n" class="form-control" value="<?php echo $data['sisa_cuti_n']; ?>"></div>
                                                            <div class="col-md-4"><label class="small font-weight-bold">Sisa Tahun Lalu</label><input type="number" name="sisa_cuti_n1" class="form-control" value="<?php echo $data['sisa_cuti_n1']; ?>"></div>
                                                            <div class="col-md-4"><label class="small font-weight-bold">Cuti Sakit</label><input type="number" name="kuota_cuti_sakit" class="form-control" value="<?php echo $data['kuota_cuti_sakit']; ?>"></div>
                                                        </div>

                                                        <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3 mt-3">Akses & Peran</h6>
                                                        <div class="row">
                                                            <div class="col-md-12 form-group">
                                                                <label class="font-weight-bold small">Password <span class="text-muted">(Isi jika ingin ubah password)</span></label>
                                                                <input type="password" name="password" class="form-control" placeholder="******">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 form-group">
                                                                <label class="font-weight-bold small">Role</label>
                                                                <select name="role" class="form-control">
                                                                    <option value="user" <?php echo ($data['role']=='user')?'selected':''; ?>>User</option>
                                                                    <option value="admin" <?php echo ($data['role']=='admin')?'selected':''; ?>>Admin</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 form-group">
                                                                <label class="font-weight-bold small">Atasan Langsung</label>
                                                                <select name="id_atasan" class="form-control">
                                                                    <option value="0">- Tidak Ada -</option>
                                                                    <?php 
                                                                    foreach($list_atasan as $boss) {
                                                                        if($boss['id_user'] != $data['id_user']) { 
                                                                            $selected = ($boss['id_user'] == $data['id_atasan']) ? 'selected' : '';
                                                                            echo "<option value='{$boss['id_user']}' $selected>{$boss['nama_lengkap']}</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                        <button type="submit" name="edit" class="btn btn-warning font-weight-bold text-dark">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    </td>
                            </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3 align-items-center">
                    <div class="col-md-6">
                        <p class="text-secondary small mb-0">
                            Total: <strong class="text-dark"><?php echo $total_data; ?></strong> Pegawai. 
                            Halaman <strong><?php echo $halaman; ?></strong> dari <?php echo $total_halaman; ?>.
                        </p>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm m-0">
                                <?php if($halaman > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=data_pegawai&halaman=<?php echo $halaman - 1; ?><?php echo $url_pencarian; ?>">&laquo;</a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                                <?php endif; ?>

                                <?php for($x = 1; $x <= $total_halaman; $x++): 
                                    $active_class = ($x == $halaman) ? 'active' : ''; ?>
                                    <li class="page-item <?php echo $active_class; ?>">
                                        <a class="page-link" href="index.php?page=data_pegawai&halaman=<?php echo $x; ?><?php echo $url_pencarian; ?>"><?php echo $x; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if($halaman < $total_halaman): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=data_pegawai&halaman=<?php echo $halaman + 1; ?><?php echo $url_pencarian; ?>">&raquo;</a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>

            </div> </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--pn-green); color: white;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus text-warning"></i> Tambah Pegawai Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3">Identitas</h6>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mt-1">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Pangkat</label>
                            <input type="text" name="pangkat" class="form-control" placeholder="Contoh: Penata Muda (III/a)">
                        </div>
                    </div>
                    
                    <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3 mt-3">Kuota Cuti Awal</h6>
                    <div class="row bg-light p-3 rounded mx-0 mb-3">
                        <div class="col-md-4"><label class="small font-weight-bold">Cuti Tahun Ini</label><input type="number" name="sisa_cuti_n" class="form-control" value="12"></div>
                        <div class="col-md-4"><label class="small font-weight-bold">Sisa Tahun Lalu</label><input type="number" name="sisa_cuti_n1" class="form-control" value="0"></div>
                        <div class="col-md-4"><label class="small font-weight-bold">Sakit</label><input type="number" name="kuota_cuti_sakit" class="form-control" value="0"></div>
                    </div>

                    <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3 mt-3">Akses & Atasan</h6>
                    <div class="form-group">
                        <label class="small font-weight-bold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Role</label>
                            <select name="role" class="form-control">
                                <option value="user">User Biasa</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Atasan</label>
                            <select name="id_atasan" class="form-control">
                                <option value="0">- Pilih Atasan -</option>
                                <?php 
                                foreach($list_atasan as $boss) {
                                    echo "<option value='{$boss['id_user']}'>{$boss['nama_lengkap']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-add">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPejabat" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--pn-gold); color: var(--pn-green);">
                <h5 class="modal-title font-weight-bold">Atur Pejabat Penandatangan</h5>
                <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="alert alert-light border border-warning small p-2 mb-3 text-dark">
                        <i class="fas fa-info-circle text-warning"></i> Data ini akan muncul di cetakan surat.
                    </div>

                    <datalist id="list_nama_pegawai">
                        <?php
                        $q_all = mysqli_query($koneksi, "SELECT nama_lengkap, nip FROM users ORDER BY nama_lengkap ASC");
                        while($user = mysqli_fetch_array($q_all)){
                            echo "<option value='".$user['nama_lengkap']."' data-nip='".$user['nip']."'>";
                        }
                        ?>
                    </datalist>

                    <h6 class="font-weight-bold text-dark border-bottom pb-2">1. Ketua Pengadilan</h6>
                    <div class="form-group mb-2">
                        <label class="small mb-0">Nama Lengkap</label>
                        <input type="text" name="ketua_nama" id="ketua_nama" class="form-control" list="list_nama_pegawai"
                               value="<?= $set_instansi['ketua_nama'] ?? '' ?>" required autocomplete="off" onchange="autoIsiNip('ketua')">
                    </div>
                    <div class="form-group">
                        <label class="small mb-0">NIP</label>
                        <input type="text" name="ketua_nip" id="ketua_nip" class="form-control" 
                               value="<?= $set_instansi['ketua_nip'] ?? '' ?>" required>
                    </div>

                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mt-4">2. Wakil Ketua</h6>
                    <div class="form-group mb-2">
                        <label class="small mb-0">Nama Lengkap</label>
                        <input type="text" name="wakil_nama" id="wakil_nama" class="form-control" list="list_nama_pegawai"
                               value="<?= $set_instansi['wakil_nama'] ?? '' ?>" autocomplete="off" onchange="autoIsiNip('wakil')">
                    </div>
                    <div class="form-group">
                        <label class="small mb-0">NIP</label>
                        <input type="text" name="wakil_nip" id="wakil_nip" class="form-control" 
                               value="<?= $set_instansi['wakil_nip'] ?? '' ?>">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" name="simpan_pejabat" class="btn btn-warning font-weight-bold text-dark">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fungsi Auto Isi NIP dari Datalist
function autoIsiNip(tipe) {
    var inputNama = document.getElementById(tipe + '_nama').value;
    var listOptions = document.getElementById('list_nama_pegawai').options;
    
    for (var i = 0; i < listOptions.length; i++) {
        if (listOptions[i].value === inputNama) {
            var nip = listOptions[i].getAttribute('data-nip');
            document.getElementById(tipe + '_nip').value = nip;
            break;
        }
    }
}

// SweetAlert Konfirmasi
function konfirmasiStatus(id, aksi, nama) {
    Swal.fire({
        title: 'Konfirmasi',
        text: "Apakah Anda yakin ingin " + aksi + " akses untuk: " + nama + "?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: aksi == 'aktifkan' ? '#004d00' : '#d33',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Ya, Lakukan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php?page=data_pegawai&toggle_status=' + id;
        }
    })
}

// AJAX Live Search
$(document).ready(function() {
    $('#keyword').on('keyup', function() {
        var keyword = $(this).val();
        // Load hanya bagian #area_tabel
        $('#area_tabel').load('index.php?page=data_pegawai&cari=' + encodeURIComponent(keyword) + ' #area_tabel', function(response, status, xhr) {
            if (status == "error") {
                console.log("Error loading search results: " + xhr.status + " " + xhr.statusText);
            }
        });
    });
});
</script>

<?php if (!empty($swal_script)): ?>
    <script><?php echo $swal_script; ?></script>
<?php endif; ?>