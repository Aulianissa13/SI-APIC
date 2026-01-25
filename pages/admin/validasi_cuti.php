<?php
// =========================================================
// 1. KONEKSI & SESSION (SMART LOCATOR) - TIDAK DIUBAH
// =========================================================
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Cek lokasi database
$kemungkinan_path = [
    '../../config/database.php',
    '../config/database.php',
    'config/database.php',
    '../database.php'
];

$db_found = false;
foreach ($kemungkinan_path as $path) {
    if (file_exists($path)) {
        include_once $path;
        $db_found = true;
        break; 
    }
}

if (!$db_found) {
    die("Error: File config/database.php tidak ditemukan.");
}

// =========================================================
// 2. LOGIC PROSES APPROVAL - TIDAK DIUBAH
// =========================================================
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    
    // Keamanan: Pastikan ID berupa angka
    $id_pengajuan = intval($_GET['id']); 
    $aksi         = $_GET['aksi']; 

    // Ambil data pengajuan & jenis cuti
    $query_cek = "SELECT pengajuan_cuti.*, jenis_cuti.nama_jenis 
                  FROM pengajuan_cuti 
                  JOIN jenis_cuti ON pengajuan_cuti.id_jenis = jenis_cuti.id_jenis 
                  WHERE id_pengajuan='$id_pengajuan'";
    
    $cek_data = mysqli_query($koneksi, $query_cek);
    
    if(mysqli_num_rows($cek_data) > 0) {
        $data       = mysqli_fetch_array($cek_data);
        $id_user    = $data['id_user'];
        $id_jenis   = $data['id_jenis']; 
        $lama       = $data['lama_hari'];
        $status_now = $data['status'];

        // Cek dulu, jangan sampai memproses yang sudah diproses
        if($status_now == 'Disetujui' || $status_now == 'Ditolak') {
            echo "<script>alert('Data ini sudah diproses sebelumnya!'); window.history.back();</script>";
            exit();
        }

        // --- SKENARIO 1: ADMIN SETUJU ---
        if ($aksi == 'setuju') {
            $up_status = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET status='Disetujui' WHERE id_pengajuan='$id_pengajuan'");
            
            if($up_status) {
                echo "<script>
                        alert('BERHASIL! Pengajuan Disetujui.'); 
                        window.location.href = window.location.href.split('?')[0]; 
                      </script>";
            }

        // --- SKENARIO 2: ADMIN TOLAK (REFUND KUOTA) ---
        } elseif ($aksi == 'tolak') {
            
            // 1. Update Status jadi Ditolak
            $up_tolak = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET status='Ditolak' WHERE id_pengajuan='$id_pengajuan'");
            
            // 2. LOGIKA REFUND BERDASARKAN ID
            $kolom_target = ''; 
            $nama_target  = '';

            switch ($id_jenis) {
                case '1': // Cuti Tahunan
                    $kolom_target = 'sisa_cuti_n';
                    $nama_target  = 'Sisa Cuti Tahunan';
                    break;
                
                case '2': // Cuti Sakit
                    $kolom_target = 'kuota_cuti_sakit';
                    $nama_target  = 'Kuota Cuti Sakit';
                    break;

                default:
                    $kolom_target = ''; 
                    $nama_target  = 'Tidak ada kuota';
                    break;
            }

            // 3. EKSEKUSI PENGEMBALIAN STOK
            if ($kolom_target != '') {
                // Query: Tambahkan kembali (Refund)
                mysqli_query($koneksi, "UPDATE users SET $kolom_target = $kolom_target + $lama WHERE id_user='$id_user'");
                
                $pesan = "Permohonan Ditolak. Kuota dikembalikan ke: $nama_target sebanyak $lama hari.";
            } else {
                $pesan = "Permohonan Ditolak. (Jenis cuti ini tidak memotong kuota).";
            }

            echo "<script>
                    alert('$pesan'); 
                    window.location.href = window.location.href.split('?')[0]; 
                  </script>";
        }
    }
    exit(); 
}

// =========================================================
// 3. QUERY DATA UNTUK TABEL
// =========================================================
$query = mysqli_query($koneksi, "SELECT pengajuan_cuti.*, 
                                        users.nama_lengkap, 
                                        users.nip, 
                                        users.sisa_cuti_n, 
                                        users.kuota_cuti_sakit, 
                                        jenis_cuti.nama_jenis 
    FROM pengajuan_cuti 
    JOIN users ON pengajuan_cuti.id_user = users.id_user 
    JOIN jenis_cuti ON pengajuan_cuti.id_jenis = jenis_cuti.id_jenis 
    ORDER BY CASE WHEN status='Menunggu' OR status='Diajukan' THEN 0 ELSE 1 END, tgl_pengajuan DESC");
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
    .badge-status {
        border-radius: 10px;
        padding: 8px 12px;
        font-weight: 600;
    }
    /* Style Table Head */
    .thead-pn {
        background-color: var(--pn-green);
        color: white;
    }
    .page-title-pn {
        font-weight: 700;
        border-left: 5px solid var(--pn-gold);
        padding-left: 15px;
        color: var(--pn-green) !important;
    }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 page-title-pn">Validasi Permohonan Cuti</h1>
    </div>

    <div class="card card-pn mb-4">
        <div class="card-header-pn d-flex align-items-center">
            <i class="fas fa-check-double mr-2"></i>
            <h6 class="m-0 font-weight-bold text-white">Daftar Pengajuan Masuk</h6>
        </div>
        <div class="card-body">
            
            <?php if(mysqli_num_rows($query) == 0) { ?>
                <div class="alert alert-info text-center" style="border-radius: 15px; border-left: 5px solid #36b9cc;">
                    <i class="fas fa-info-circle mr-2"></i>Belum ada data pengajuan cuti.
                </div>
            <?php } else { ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-pn">
                        <tr class="text-center">
                            <th width="5%">No</th>
                            <th>Pegawai</th>
                            <th>Jenis Cuti</th>
                            <th>Detail Pengajuan</th>
                            <th width="5%">Sisa<br>Thn</th>
                            <th width="5%">Sisa<br>Skt</th>
                            <th>Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($row = mysqli_fetch_array($query)){ 
                            
                            $raw_status = strtolower(trim($row['status'])); 
                            $bg_row = "";

                            // Logic Badge & Warna Baris
                            if ($raw_status == 'disetujui') {
                                $badge = '<span class="badge badge-success badge-status"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>';
                            } elseif ($raw_status == 'ditolak') {
                                $badge = '<span class="badge badge-danger badge-status"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>';
                                $bg_row = "style='background-color: #ffecec;'"; // Merah muda tipis
                            } else {
                                $badge = '<span class="badge badge-warning badge-status"><i class="fas fa-clock mr-1"></i>Menunggu</span>';
                                // REVISI TYPO KODE WARNA ASLI
                                $bg_row = "style='background-color: #fff3cd;'"; // Kuning tipis (Warning Light)
                            }
                        ?>
                        <tr <?php echo $bg_row; ?>>
                            <td class="text-center font-weight-bold" style="vertical-align: middle;"><?php echo $no++; ?></td>
                            <td style="vertical-align: middle;">
                                <div class="font-weight-bold text-dark" style="font-size: 1.05rem;"><?php echo $row['nama_lengkap']; ?></div>
                                <small class="text-muted"><i class="fas fa-id-badge mr-1"></i>NIP: <?php echo $row['nip']; ?></small>
                            </td>
                            <td style="vertical-align: middle;"><?php echo $row['nama_jenis']; ?></td>
                            <td style="vertical-align: middle;">
                                <div class="font-weight-bold text-primary"><?php echo $row['lama_hari']; ?> Hari</div>
                                <small class="text-dark">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    <?php echo date('d M', strtotime($row['tgl_mulai'])); ?> s/d 
                                    <?php echo date('d M Y', strtotime($row['tgl_selesai'])); ?>
                                </small>
                            </td>
                            
                            <td class="text-center font-weight-bold text-dark" style="vertical-align: middle; background-color: #f8f9fc;"><?php echo $row['sisa_cuti_n']; ?></td>
                            <td class="text-center font-weight-bold text-dark" style="vertical-align: middle; background-color: #f8f9fc;"><?php echo $row['kuota_cuti_sakit']; ?></td>

                            <td class="text-center" style="vertical-align: middle;"><?php echo $badge; ?></td>
                            
                            <td class="text-center" style="vertical-align: middle;">
                                <a href="pages/admin/cetak_cuti_admin.php?id=<?php echo $row['id_pengajuan']; ?>" target="_blank" class="btn btn-info btn-circle btn-sm shadow-sm" title="Cetak Surat">
                                    <i class="fas fa-print"></i>
                                </a>
                                    <?php if(strpos($raw_status, 'menunggu') !== false || $raw_status == 'diajukan' || $raw_status == '') { ?>
                                    
                                    <span class="mx-1">|</span>
                                    
                                    <a href="?page=validasi_cuti&aksi=setuju&id=<?php echo $row['id_pengajuan']; ?>" 
                                       class="btn btn-success btn-circle btn-sm shadow-sm" 
                                       onclick="return confirm('Yakin ingin MENYETUJUI pengajuan ini? Stok tidak akan berubah (sudah dipotong diawal).')" 
                                       title="Setujui">
                                       <i class="fas fa-check"></i>
                                    </a>
                                    
                                    <a href="?page=validasi_cuti&aksi=tolak&id=<?php echo $row['id_pengajuan']; ?>" 
                                       class="btn btn-danger btn-circle btn-sm shadow-sm" 
                                       onclick="return confirm('Yakin ingin MENOLAK? Stok cuti akan DIKEMBALIKAN (Refund) ke pegawai.')" 
                                       title="Tolak">
                                       <i class="fas fa-times"></i>
                                    </a>

                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php } ?>
        </div>
    </div>
</div>