<?php
// =========================================================
// 1. KONEKSI & SESSION (SMART LOCATOR)
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
// 2. LOGIC PROSES APPROVAL
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
        $id_jenis   = $data['id_jenis']; // Kita pakai ID biar konsisten
        $lama       = $data['lama_hari'];
        $status_now = $data['status'];

        // Cek dulu, jangan sampai memproses yang sudah diproses (Mencegah Double Klik)
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
            
            // 2. LOGIKA REFUND BERDASARKAN ID (Lebih Aman)
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

                // Kasus Lain (Besar, Melahirkan, Penting, dll) -> Tidak ada Refund Kuota
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
// Kita ambil semua data, urutkan yang terbaru di atas
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
    // Trik ORDER BY di atas: Menampilkan status 'Menunggu' paling atas, sisanya di bawah berdasarkan tanggal
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Validasi Permohonan Cuti</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pengajuan Masuk</h6>
        </div>
        <div class="card-body">
            
            <?php if(mysqli_num_rows($query) == 0) { ?>
                <div class="alert alert-info text-center">Belum ada data pengajuan cuti.</div>
            <?php } else { ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr class="text-center">
                            <th>No</th>
                            <th>Pegawai</th>
                            <th>Jenis Cuti</th>
                            <th>Detail Pengajuan</th>
                            <th>Sisa<br>Thn</th>
                            <th>Sisa<br>Skt</th>
                            <th>Status</th>
                            <th>Aksi</th>
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
                                $badge = '<span class="badge badge-success">Disetujui</span>';
                            } elseif ($raw_status == 'ditolak') {
                                $badge = '<span class="badge badge-danger">Ditolak</span>';
                                $bg_row = "style='background-color: #ffecec;'"; // Merah muda tipis
                            } else {
                                $badge = '<span class="badge badge-warning">Menunggu</span>';
                                $bg_row = "style='background-color: #fffnot 8e1;'"; // Kuning tipis
                            }
                        ?>
                        <tr <?php echo $bg_row; ?>>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td>
                                <b><?php echo $row['nama_lengkap']; ?></b><br>
                                <small class="text-muted">NIP: <?php echo $row['nip']; ?></small>
                            </td>
                            <td><?php echo $row['nama_jenis']; ?></td>
                            <td>
                                <b><?php echo $row['lama_hari']; ?> Hari</b><br>
                                <small>
                                    <?php echo date('d M', strtotime($row['tgl_mulai'])); ?> s/d 
                                    <?php echo date('d M Y', strtotime($row['tgl_selesai'])); ?>
                                </small>
                            </td>
                            
                            <td class="text-center font-weight-bold text-primary"><?php echo $row['sisa_cuti_n']; ?></td>
                            <td class="text-center font-weight-bold text-danger"><?php echo $row['kuota_cuti_sakit']; ?></td>

                            <td class="text-center"><?php echo $badge; ?></td>
                            
                            <td class="text-center">
                                <a href="pages/admin/cetak_cuti_admin.php?id=<?php echo $row['id_pengajuan']; ?>" target="_blank" class="btn btn-info btn-circle btn-sm" title="Cetak Surat">
                                    <i class="fas fa-print"></i>
                                </a>
                                    <?php if(strpos($raw_status, 'menunggu') !== false || $raw_status == 'diajukan' || $raw_status == '') { ?>
                                    
                                    <span class="mx-1">|</span>
                                    
                                    <a href="?page=validasi_cuti&aksi=setuju&id=<?php echo $row['id_pengajuan']; ?>" 
                                       class="btn btn-success btn-circle btn-sm" 
                                       onclick="return confirm('Yakin ingin MENYETUJUI pengajuan ini? Stok tidak akan berubah (sudah dipotong diawal).')" 
                                       title="Setujui">
                                       <i class="fas fa-check"></i>
                                    </a>
                                    
                                    <a href="?page=validasi_cuti&aksi=tolak&id=<?php echo $row['id_pengajuan']; ?>" 
                                       class="btn btn-danger btn-circle btn-sm" 
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