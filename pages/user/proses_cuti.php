<?php
session_start(); // Wajib ada untuk kirim pesan antar halaman
include '../../config/database.php';

// ============================================================
// 1. TANGKAP DATA & VALIDASI AWAL
// ============================================================
$id_user       = $_POST['id_user'];
$id_jenis      = $_POST['id_jenis'];
$tgl_mulai     = $_POST['tgl_mulai'];
$tgl_selesai   = $_POST['tgl_selesai'];
$lama_hari     = (int) $_POST['lama_hari'];
$alasan        = $_POST['alasan'];
$alamat_cuti   = $_POST['alamat_cuti'];
$no_telepon    = $_POST['no_telepon'];
$status        = 'Diajukan'; 
$tgl_pengajuan = date('Y-m-d');

// --- VALIDASI 1: TANGGAL MUNDUR ---
if ($tgl_selesai < $tgl_mulai) {
    $_SESSION['alert'] = [
        'icon' => 'error',
        'title' => 'Tanggal Tidak Valid',
        'text' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'
    ];
    header("Location: ../../index.php?page=form_cuti");
    exit;
}

// --- VALIDASI 2: CEK BENTROK TANGGAL (ANTI DOUBLE BOOKING) ---
// Mencari apakah ada pengajuan lain (kecuali yang ditolak) yang beririsan dengan tanggal ini
$cek_bentrok = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti 
    WHERE id_user = '$id_user' 
    AND status != 'ditolak' 
    AND (
        (tgl_mulai BETWEEN '$tgl_mulai' AND '$tgl_selesai') 
        OR 
        (tgl_selesai BETWEEN '$tgl_mulai' AND '$tgl_selesai')
        OR 
        ('$tgl_mulai' BETWEEN tgl_mulai AND tgl_selesai)
    )");

if (mysqli_num_rows($cek_bentrok) > 0) {
    $_SESSION['alert'] = [
        'icon' => 'error',
        'title' => 'Gagal Mengajukan',
        'text' => 'Anda sudah memiliki pengajuan cuti pada rentang tanggal tersebut.'
    ];
    header("Location: ../../index.php?page=form_cuti");
    exit;
}

// ============================================================
// 2. GENERATOR NOMOR SURAT
// ============================================================
$tahun_ini = date('Y');
$bulan_ini = date('n'); 
$romawi = [ 1 => "I", 2 => "II", 3 => "III", 4 => "IV", 5 => "V", 6 => "VI", 7 => "VII", 8 => "VIII", 9 => "IX", 10 => "X", 11 => "XI", 12 => "XII" ];
$bulan_romawi = $romawi[$bulan_ini];

$query_no = mysqli_query($koneksi, "SELECT nomor_surat FROM pengajuan_cuti WHERE YEAR(tgl_pengajuan) = '$tahun_ini' ORDER BY id_pengajuan DESC LIMIT 1");
$data_no = mysqli_fetch_array($query_no);

if ($data_no) {
    $pecah = explode('/', $data_no['nomor_surat']);
    $angka_terakhir = (int) $pecah[0]; 
    $nomor_baru = $angka_terakhir + 1;
} else {
    $nomor_baru = 1;
}
$no_urut_format = sprintf("%03d", $nomor_baru);
$no_surat_final = "$no_urut_format/KPN/W13.U1/KP.05.3/$bulan_romawi/$tahun_ini";

// ============================================================
// 3. CEK SISA CUTI (LOGIKA PERHITUNGAN)
// ============================================================
$cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
$user     = mysqli_fetch_array($cek_user);

$boleh_cuti = true;
$pesan_error = "";
$update_n   = $user['sisa_cuti_n'];
$update_n1  = $user['sisa_cuti_n1'];
$update_sakit = $user['kuota_cuti_sakit'];
$perlu_update_user = false;

// Logika FIFO (Tahunan) & Sakit
if ($id_jenis == '1') { // Cuti Tahunan
    $total_jatah = $user['sisa_cuti_n'] + $user['sisa_cuti_n1'];
    
    if ($lama_hari > $total_jatah) {
        $boleh_cuti = false;
        $pesan_error = "Sisa cuti tahunan tidak mencukupi! Total sisa: $total_jatah hari.";
    } else {
        // Habiskan N-1 dulu
        if ($lama_hari <= $user['sisa_cuti_n1']) {
            $update_n1 = $user['sisa_cuti_n1'] - $lama_hari;
        } else {
            $kekurangan = $lama_hari - $user['sisa_cuti_n1'];
            $update_n1  = 0;
            $update_n   = $user['sisa_cuti_n'] - $kekurangan;
        }
        $perlu_update_user = true;
    }
} else if ($id_jenis == '2') { // Cuti Sakit
    if ($lama_hari > $user['kuota_cuti_sakit']) {
        $boleh_cuti = false;
        $pesan_error = "Kuota cuti sakit tidak mencukupi!";
    } else {
        $update_sakit = $user['kuota_cuti_sakit'] - $lama_hari;
        $perlu_update_user = true;
    }
}
// Jenis lain (Cuti Besar, Melahirkan, dll) biasanya tidak memotong kuota tahunan
// Logic bisa ditambahkan di sini jika ada kuota khusus lainnya.

// ============================================================
// 4. EKSEKUSI DATABASE
// ============================================================
if ($boleh_cuti) {
    // A. Update Data User (Kuota & No Telp)
    $query_update_user = "UPDATE users SET no_telepon='$no_telepon'";
    
    if($perlu_update_user && $id_jenis == '1'){
        $query_update_user .= ", sisa_cuti_n='$update_n', sisa_cuti_n1='$update_n1'";
    } else if ($perlu_update_user && $id_jenis == '2'){
        $query_update_user .= ", kuota_cuti_sakit='$update_sakit'";
    }
    
    $query_update_user .= " WHERE id_user='$id_user'";
    mysqli_query($koneksi, $query_update_user);

    // B. Insert Pengajuan Baru
    $simpan = mysqli_query($koneksi, "INSERT INTO pengajuan_cuti 
        (nomor_surat, id_user, id_jenis, tgl_mulai, tgl_selesai, lama_hari, alasan, alamat_cuti, status, tgl_pengajuan) 
        VALUES 
        ('$no_surat_final', '$id_user', '$id_jenis', '$tgl_mulai', '$tgl_selesai', '$lama_hari', '$alasan', '$alamat_cuti', '$status', '$tgl_pengajuan')
    ");

    if($simpan) {
        // SUKSES
        $_SESSION['alert'] = [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Pengajuan cuti berhasil dibuat. Menunggu persetujuan.'
        ];
        // Redirect ke Riwayat
        header("Location: ../../index.php?page=riwayat_cuti");
    } else {
        // GAGAL QUERY
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'Error Sistem',
            'text' => mysqli_error($koneksi)
        ];
        header("Location: ../../index.php?page=form_cuti");
    }

} else {
    // GAGAL VALIDASI KUOTA
    $_SESSION['alert'] = [
        'icon' => 'error',
        'title' => 'Gagal Mengajukan',
        'text' => $pesan_error
    ];
    header("Location: ../../index.php?page=form_cuti");
}
exit;
?>