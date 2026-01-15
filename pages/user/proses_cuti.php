<?php
session_start();
include '../../config/database.php';

// 1. Tangkap Data dari Form
$id_user     = $_POST['id_user'];
$id_jenis    = $_POST['id_jenis'];
$tgl_mulai   = $_POST['tgl_mulai'];
$tgl_selesai = $_POST['tgl_selesai'];
$lama_hari   = (int) $_POST['lama_hari']; // Pastikan jadi angka integer
$alasan      = $_POST['alasan'];
$alamat_cuti = $_POST['alamat_cuti'];
$no_telepon  = $_POST['no_telepon'];
$status      = 'Diajukan'; 
$tgl_pengajuan = date('Y-m-d');

// =========================================================
// A. GENERATOR NOMOR SURAT DINAS (Format Pengadilan)
// =========================================================
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

// =========================================================
// B. VALIDASI & LOGIKA FIFO (PENGURANGAN KUOTA)
// =========================================================

// Ambil data user terbaru
$cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
$user     = mysqli_fetch_array($cek_user);

$boleh_cuti = true;
$pesan_error = "";

// Siapkan variabel untuk update saldo nanti
$update_n   = $user['sisa_cuti_n'];
$update_n1  = $user['sisa_cuti_n1'];
$update_sakit = $user['kuota_cuti_sakit'];
$perlu_update_user = false;

if ($id_jenis == '1') { // --- CUTI TAHUNAN (FIFO) ---
    
    $total_jatah = $user['sisa_cuti_n'] + $user['sisa_cuti_n1'];
    
    if ($lama_hari > $total_jatah) {
        $boleh_cuti = false;
        $pesan_error = "Sisa cuti tahunan tidak mencukupi! Total sisa: $total_jatah hari.";
    } else {
        // LOGIKA FIFO:
        // 1. Cek apakah sisa tahun lalu (N-1) cukup?
        if ($lama_hari <= $user['sisa_cuti_n1']) {
            // Jika cukup, potong full dari tahun lalu
            $update_n1 = $user['sisa_cuti_n1'] - $lama_hari;
            // Tahun ini (N) tidak berubah
        } else {
            // Jika tidak cukup, habiskan tahun lalu, sisanya ambil tahun ini
            $kekurangan = $lama_hari - $user['sisa_cuti_n1'];
            $update_n1  = 0; // Tahun lalu habis
            $update_n   = $user['sisa_cuti_n'] - $kekurangan; // Potong sisanya dari tahun ini
        }
        $perlu_update_user = true;
    }

} else if ($id_jenis == '2') { // --- CUTI SAKIT ---
    
    if ($lama_hari > $user['kuota_cuti_sakit']) {
        $boleh_cuti = false;
        $pesan_error = "Kuota cuti sakit tidak mencukupi!";
    } else {
        $update_sakit = $user['kuota_cuti_sakit'] - $lama_hari;
        $perlu_update_user = true;
    }
}

// =========================================================
// C. EKSEKUSI SIMPAN
// =========================================================

if ($boleh_cuti) {
    
    // 1. Update Tabel Users (Potong Saldo & Update No HP)
    // Kita update saldo SEKARANG (sistem booking), nanti kalau ditolak Admin baru dikembalikan.
    $query_update_user = "UPDATE users SET no_telepon='$no_telepon'";
    
    if($perlu_update_user && $id_jenis == '1'){
        $query_update_user .= ", sisa_cuti_n='$update_n', sisa_cuti_n1='$update_n1'";
    } else if ($perlu_update_user && $id_jenis == '2'){
        $query_update_user .= ", kuota_cuti_sakit='$update_sakit'";
    }
    
    $query_update_user .= " WHERE id_user='$id_user'";
    mysqli_query($koneksi, $query_update_user);


    // 2. Simpan Pengajuan
    $simpan = mysqli_query($koneksi, "INSERT INTO pengajuan_cuti 
        (nomor_surat, id_user, id_jenis, tgl_mulai, tgl_selesai, lama_hari, alasan, alamat_cuti, status, tgl_pengajuan) 
        VALUES 
        ('$no_surat_final', '$id_user', '$id_jenis', '$tgl_mulai', '$tgl_selesai', '$lama_hari', '$alasan', '$alamat_cuti', '$status', '$tgl_pengajuan')
    ");

    if($simpan) {
        header("location:../../index.php?page=riwayat_cuti&pesan=sukses");
    } else {
        echo "Gagal menyimpan: " . mysqli_error($koneksi);
    }

} else {
    echo "<script>alert('$pesan_error'); window.location.href = '../../index.php?page=form_cuti';</script>";
}
?>