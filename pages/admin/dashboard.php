<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Admin</h1>
</div>

<div class="row">

    <?php
    // Menghitung status 'Diajukan'
    $sql_tunggu = mysqli_query($koneksi, "SELECT count(*) as jumlah FROM pengajuan_cuti WHERE status='Diajukan'");
    $data_tunggu = mysqli_fetch_assoc($sql_tunggu);
    ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Perlu Validasi</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data_tunggu['jumlah']; ?> Pengajuan</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    $bulan_ini = date('m');
    $sql_setuju = mysqli_query($koneksi, "SELECT count(*) as jumlah FROM pengajuan_cuti WHERE status='Disetujui' AND MONTH(tgl_pengajuan) = '$bulan_ini'");
    $data_setuju = mysqli_fetch_assoc($sql_setuju);
    ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Disetujui (Bulan Ini)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data_setuju['jumlah']; ?> Pengajuan</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // KOREKSI: Menghitung SEMUA user (Admin + User Biasa)
    // Karena semua yang punya akun dianggap Pegawai.
    $sql_user = mysqli_query($koneksi, "SELECT count(*) as jumlah FROM users");
    
    // Cek error query
    if (!$sql_user) {
        $jumlah_pegawai = 0; 
    } else {
        $data_user = mysqli_fetch_assoc($sql_user);
        $jumlah_pegawai = $data_user['jumlah'];
    }
    ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Pegawai</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $jumlah_pegawai; ?> Orang</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    $sql_tolak = mysqli_query($koneksi, "SELECT count(*) as jumlah FROM pengajuan_cuti WHERE status='Ditolak' AND MONTH(tgl_pengajuan) = '$bulan_ini'");
    $data_tolak = mysqli_fetch_assoc($sql_tolak);
    ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Ditolak (Bulan Ini)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $data_tolak['jumlah']; ?> Pengajuan</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Selamat Datang, Admin!</h6>
            </div>
            <div class="card-body">
                <p>Silakan kelola pengajuan cuti pegawai melalui menu <strong>Validasi Cuti</strong>. Pastikan untuk memeriksa sisa kuota dan alasan pegawai sebelum menyetujui pengajuan.</p>
            </div>
        </div>
    </div>
</div>