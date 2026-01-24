<?php
// --- FILE: pages/admin/data_pegawai.php ---

// Cek session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Variabel notifikasi
$swal_script = "";

// --- 1. AMBIL DATA USERS UNTUK DROPDOWN ATASAN ---
$list_atasan = [];
$q_all_users = mysqli_query($koneksi, "SELECT id_user, nama_lengkap FROM users ORDER BY nama_lengkap ASC");
if ($q_all_users) {
    while ($user_row = mysqli_fetch_assoc($q_all_users)) {
        $list_atasan[] = $user_row;
    }
}

// --- LOGIKA: TAMBAH DATA ---
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

    // Cek duplikasi NIP
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

// --- LOGIKA: EDIT DATA ---
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

// --- LOGIKA: GANTI STATUS ---
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

<style>
    /* Styling Global */
    .page-wrapper-custom {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #333;
    }
    
    .bg-main-green { background-color: #006837 !important; color: white !important; }
    .text-main-green { color: #006837 !important; }
    .border-main-green { border-color: #006837 !important; }

    .bg-accent-yellow { background-color: #F9A825 !important; color: #006837 !important; }
    .text-accent-yellow { color: #F9A825 !important; }
    
    .btn-custom-green {
        background-color: #006837;
        color: white;
        border: 1px solid #006837;
    }
    .btn-custom-green:hover {
        background-color: #004e2a; 
        color: #F9A825;
    }

    .btn-custom-yellow {
        background-color: #F9A825;
        color: #006837;
        font-weight: bold;
        border: 1px solid #F9A825;
    }

    .table-custom-head th {
        background-color: #006837;
        color: white;
        border-color: #004e2a;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
    }
    
    .card-custom-border {
        border-top: 4px solid #F9A825;
        border-radius: 0.35rem;
    }

    .badge-custom-green { background-color: rgba(0, 104, 55, 0.1); color: #006837; border: 1px solid #006837; }
    .badge-custom-yellow { background-color: rgba(249, 168, 37, 0.15); color: #c27d0e; border: 1px solid #F9A825; }

    .pagination .page-item .page-link { color: #006837; border-color: #dee2e6; }
    .pagination .page-item.active .page-link { background-color: #006837; border-color: #006837; color: white; }

    /* CSS BARU UNTUK SEARCH BAR DI DALAM */
    .search-wrapper {
        position: relative;
        width: 100%;
        max-width: 300px; /* Lebar maksimal search bar */
    }
    
    .search-input-inside {
        width: 100%;
        padding-right: 40px !important; /* Memberi ruang untuk ikon di kanan */
        padding-left: 15px !important;
        border-radius: 50px !important; /* Membuat ujung bulat (pill shape) */
        border: 1px solid #ddd;
        background-color: #f8f9fc;
        transition: all 0.3s ease;
    }

    .search-input-inside:focus {
        background-color: #fff;
        border-color: #006837;
        box-shadow: 0 0 0 0.2rem rgba(0, 104, 55, 0.25);
    }

    .search-icon-inside {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
        pointer-events: none; /* Supaya klik tembus ke input */
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="page-wrapper-custom">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-main-green font-weight-bold">Data Pegawai & Kuota Cuti</h1>
    </div>

    <div class="card shadow mb-4 card-custom-border">
        <div class="card-header py-3 bg-white">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h6 class="m-0 font-weight-bold text-main-green"><i class="fas fa-users mr-2"></i> Daftar Pegawai</h6>
                </div>
                
                <div class="col-md-8 d-flex justify-content-end align-items-center">
                    <form action="index.php" method="GET" class="mr-3" onsubmit="return false;">
                        <input type="hidden" name="page" value="data_pegawai">
                        <div class="search-wrapper">
                            <input type="text" name="cari" id="keyword" class="form-control search-input-inside" 
                                   placeholder="Cari pegawai..." value="<?php echo $keyword; ?>" autocomplete="off">
                            <i class="fas fa-search search-icon-inside"></i>
                        </div>
                    </form>
                    
                    <button type="button" class="btn btn-custom-green shadow-sm btn-sm px-3 py-2 rounded-pill" data-toggle="modal" data-target="#modalTambah">
                        <i class="fas fa-plus-circle mr-1 text-accent-yellow"></i> Tambah
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div id="area_tabel">
                
                <?php if(!empty($keyword)): ?>
                    <div class="alert alert-light border border-main-green text-main-green py-2 mb-3">
                        <small>Hasil pencarian: <strong>"<?php echo $keyword; ?>"</strong>. <a href="index.php?page=data_pegawai" class="text-danger font-weight-bold">Reset</a></small>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0" style="font-size: 0.9rem;">
                        <thead class="table-custom-head">
                            <tr class="text-center">
                                <th rowspan="2" style="vertical-align: middle;">No</th>
                                <th rowspan="2" style="vertical-align: middle;">Pegawai</th>
                                <th rowspan="2" style="vertical-align: middle; width: 20%;">Jabatan & Pangkat</th> <th colspan="3">Sisa Kuota Cuti</th>
                                <th rowspan="2" style="vertical-align: middle;">Atasan</th>
                                <th rowspan="2" style="vertical-align: middle;">Aksi</th>
                            </tr>
                            <tr class="text-center">
                                <th>Tahun Ini</th>
                                <th>Tahun Lalu</th>
                                <th>Sakit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
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
                                echo "<tr><td colspan='8' class='text-center text-secondary py-4'>Data tidak ditemukan.</td></tr>";
                            } else {

                            while ($data = mysqli_fetch_array($query_pegawai)) {
                                $is_active = ($data['status_akun'] == 'aktif');
                                $row_bg = $is_active ? '' : 'style="background-color: #f1f1f1; opacity: 0.8;"';
                                $pangkat_text = isset($data['pangkat']) && !empty($data['pangkat']) ? $data['pangkat'] : '-';
                            ?>
                            <tr <?php echo $row_bg; ?>>
                                <td class="text-center"><?php echo $nomor++; ?></td>
                                <td>
                                    <div class="font-weight-bold text-dark"><?php echo $data['nama_lengkap']; ?></div>
                                    <small class="text-secondary font-weight-bold">NIP: <?php echo $data['nip']; ?></small>
                                    <div class="mt-1">
                                        <?php if($is_active): ?>
                                            <span class="badge badge-custom-green" style="font-size: 0.7rem;">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary" style="font-size: 0.7rem;">Nonaktif</span>
                                        <?php endif; ?>
                                        
                                        <?php if($data['role'] == 'admin'): ?>
                                            <span class="badge badge-custom-yellow ml-1" style="font-size: 0.7rem;">ADMIN</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                
                                <td style="vertical-align: middle;">
                                    <div class="font-weight-bold text-main-green" style="font-size: 0.95rem;">
                                        <?php echo $data['jabatan']; ?>
                                    </div>
                                    <div class="text-secondary small mt-1" style="font-style: italic;">
                                        <?php echo $pangkat_text; ?>
                                    </div>
                                </td>
                                <td class="text-center font-weight-bold text-main-green"><?php echo $data['sisa_cuti_n']; ?></td>
                                <td class="text-center text-secondary"><?php echo $data['sisa_cuti_n1']; ?></td>
                                <td class="text-center text-info"><?php echo $data['kuota_cuti_sakit']; ?></td>
                                <td><small><?php echo $data['nama_atasan'] ? $data['nama_atasan'] : '-'; ?></small></td>
                                <td class="text-center" style="vertical-align: middle;">
        
                                <button type="button" class="btn btn-outline-warning btn-sm rounded-circle mx-1" 
                                    data-toggle="modal" data-target="#modalEdit<?php echo $data['id_user']; ?>" 
                                    title="Edit Data"
                                    style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-width: 2px;">
                                    <i class="fas fa-pen" style="font-size: 0.85rem;"></i>
                                </button>
                                
                                <?php if($is_active): ?>
                                    <button onclick="konfirmasiStatus('<?php echo $data['id_user']; ?>', 'nonaktifkan', '<?php echo addslashes($data['nama_lengkap']); ?>')" 
                                        class="btn btn-outline-danger btn-sm rounded-circle mx-1" 
                                        title="Nonaktifkan Akun"
                                        style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-width: 2px;">
                                        <i class="fas fa-power-off" style="font-size: 0.85rem;"></i>
                                    </button>
                                <?php else: ?>
                                    <button onclick="konfirmasiStatus('<?php echo $data['id_user']; ?>', 'aktifkan', '<?php echo addslashes($data['nama_lengkap']); ?>')" 
                                        class="btn btn-outline-success btn-sm rounded-circle mx-1" 
                                        title="Aktifkan Akun"
                                        style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-width: 2px;">
                                        <i class="fas fa-check" style="font-size: 0.85rem;"></i>
                                    </button>
                                <?php endif; ?>

                            </td>

                            <div class="modal fade" id="modalEdit<?php echo $data['id_user']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg"> 
                                    <div class="modal-content">
                                        <div class="modal-header bg-accent-yellow">
                                            <h5 class="modal-title font-weight-bold"><i class="fas fa-edit"></i> Edit Pegawai</h5>
                                            <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body text-left text-dark">
                                                <input type="hidden" name="id_user" value="<?php echo $data['id_user']; ?>">
                                                
                                                <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3">Identitas</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold small">NIP <span class="text-danger">*</span></label>
                                                            <input type="text" name="nip" class="form-control" value="<?php echo $data['nip']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold small">Nama Lengkap <span class="text-danger">*</span></label>
                                                            <input type="text" name="nama_lengkap" class="form-control" value="<?php echo $data['nama_lengkap']; ?>" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold small">Jabatan</label>
                                                            <input type="text" name="jabatan" class="form-control" value="<?php echo $data['jabatan']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold small">Pangkat</label>
                                                            <input type="text" name="pangkat" class="form-control" value="<?php echo isset($data['pangkat']) ? $data['pangkat'] : ''; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold small">Status Akun</label>
                                                            <select name="status_akun" class="form-control">
                                                                <option value="aktif" <?php echo ($data['status_akun']=='aktif')?'selected':''; ?>>Aktif</option>
                                                                <option value="tidak_aktif" <?php echo ($data['status_akun']=='tidak_aktif')?'selected':''; ?>>Tidak Aktif</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3 mt-3">Kuota Cuti</h6>
                                                <div class="row bg-light p-2 rounded mx-1">
                                                    <div class="col-md-4"><label class="small font-weight-bold">Sisa Tahun Ini</label><input type="number" name="sisa_cuti_n" class="form-control" value="<?php echo $data['sisa_cuti_n']; ?>"></div>
                                                    <div class="col-md-4"><label class="small font-weight-bold">Sisa Tahun Lalu</label><input type="number" name="sisa_cuti_n1" class="form-control" value="<?php echo $data['sisa_cuti_n1']; ?>"></div>
                                                    <div class="col-md-4"><label class="small font-weight-bold">Cuti Sakit</label><input type="number" name="kuota_cuti_sakit" class="form-control" value="<?php echo $data['kuota_cuti_sakit']; ?>"></div>
                                                </div>

                                                <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3 mt-4">Akses & Peran</h6>
                                                <div class="form-group">
                                                    <label class="font-weight-bold small">Password <small class="text-muted">(Kosongkan jika tidak diganti)</small></label>
                                                    <input type="password" name="password" class="form-control">
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="font-weight-bold small">Role</label>
                                                        <select name="role" class="form-control">
                                                            <option value="user" <?php echo ($data['role']=='user')?'selected':''; ?>>User</option>
                                                            <option value="admin" <?php echo ($data['role']=='admin')?'selected':''; ?>>Admin</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="font-weight-bold small">Atasan</label>
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
                                                <button type="submit" name="edit" class="btn btn-custom-yellow">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php } } ?>
                        </tbody>
                    </table>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-secondary small mb-0">
                                Total Data: <strong class="text-main-green"><?php echo $total_data; ?></strong> Pegawai. 
                                Halaman <?php echo $halaman; ?> dari <?php echo $total_halaman; ?>.
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

                                    <?php 
                                    for($x = 1; $x <= $total_halaman; $x++): 
                                        $active_class = ($x == $halaman) ? 'active' : '';
                                    ?>
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
                </div> 
            </div> 

        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-main-green">
                <h5 class="modal-title"><i class="fas fa-user-plus text-accent-yellow"></i> Tambah Pegawai Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3">Identitas</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="small font-weight-bold">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="small font-weight-bold">Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold">Pangkat</label>
                            <input type="text" name="pangkat" class="form-control" placeholder="Contoh: Penata Muda (III/a)">
                        </div>
                    </div>
                    
                    <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3 mt-4">Kuota Cuti</h6>
                    <div class="row bg-light p-2 rounded mx-1">
                        <div class="col-md-4"><label class="small font-weight-bold">Cuti Tahun Ini</label><input type="number" name="sisa_cuti_n" class="form-control" value="12"></div>
                        <div class="col-md-4"><label class="small font-weight-bold">Sisa Tahun Lalu</label><input type="number" name="sisa_cuti_n1" class="form-control" value="0"></div>
                        <div class="col-md-4"><label class="small font-weight-bold">Sakit</label><input type="number" name="kuota_cuti_sakit" class="form-control" value="0"></div>
                    </div>

                    <h6 class="font-weight-bold text-main-green border-bottom pb-2 mb-3 mt-4">Akses</h6>
                    <div class="form-group">
                        <label class="small font-weight-bold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="small font-weight-bold">Role</label>
                            <select name="role" class="form-control">
                                <option value="user">User Biasa</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
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
                    <button type="submit" name="tambah" class="btn btn-custom-green">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function konfirmasiStatus(id, aksi, nama) {
        Swal.fire({
            title: 'Konfirmasi',
            text: "Apakah Anda yakin ingin " + aksi + " akses untuk: " + nama + "?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: aksi == 'aktifkan' ? '#006837' : '#d33',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Ya, Lakukan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php?page=data_pegawai&toggle_status=' + id;
            }
        })
    }

    // --- SCRIPT LIVE SEARCH (AJAX) ---
    $(document).ready(function() {
        $('#keyword').on('keyup', function() {
            var keyword = $(this).val();
            $('#area_tabel').load('index.php?page=data_pegawai&cari=' + encodeURIComponent(keyword) + ' #area_tabel', function() {
                // Callback jika perlu
            });
        });
    });
</script>

<?php if (!empty($swal_script)): ?>
    <script><?php echo $swal_script; ?></script>
<?php endif; ?>