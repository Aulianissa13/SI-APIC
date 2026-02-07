<?php
/** @var mysqli $koneksi */

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- KONFIGURASI KONEKSI ---
$path_db1 = '../../assets/config/database.php';
$path_db2 = '../../config/database.php';
$path_db3 = '../config/database.php'; 

if (file_exists($path_db1)) { include $path_db1; } 
elseif (file_exists($path_db2)) { include $path_db2; } 
elseif (file_exists($path_db3)) { include $path_db3; } 
else { die("<h3>ERROR FATAL: File database.php tidak ditemukan!</h3>"); }

if (empty($koneksi)) { die("<h3>ERROR KONEKSI:</h3> <p>Variabel <code>\$koneksi</code> kosong/gagal.</p>"); }
if (!isset($_SESSION['id_user']) && !isset($_SESSION['id_admin'])) { die("<h3>AKSES DITOLAK:</h3> <p>Harap login terlebih dahulu.</p>"); }

// --- AMBIL DATA SETTING INSTANSI ---
$q_instansi = mysqli_query($koneksi, "SELECT * FROM tbl_setting_instansi WHERE id_setting='1'");
$instansi = ($q_instansi) ? mysqli_fetch_array($q_instansi) : null;
if(!$instansi) {
    $instansi = [
        'ketua_nama' => '..................', 'ketua_nip' => '..................',
        'wakil_nama' => '..................', 'wakil_nip' => '..................'
    ];
}

// --- AMBIL DATA PENGAJUAN ---
if (!isset($_GET['id'])) { die("<h3>ERROR:</h3> <p>ID tidak ditemukan di URL.</p>"); }
$id_pengajuan = (int)$_GET['id'];

$sql = "SELECT 
    pengajuan_cuti.*, 
    jenis_cuti.nama_jenis,
    users.id_user, users.nama_lengkap, users.nip, users.jabatan, users.pangkat, users.unit_kerja, users.no_telepon,
    users.kuota_cuti_sakit,
    users.sisa_cuti_n AS u_sisa_n_realtime,   
    users.sisa_cuti_n1 AS u_sisa_n1_realtime,
    users.sisa_cuti_n2 AS u_sisa_n2_realtime,
    pengajuan_cuti.id_atasan AS id_atasan_fix 
    FROM pengajuan_cuti 
    JOIN users ON pengajuan_cuti.id_user = users.id_user 
    JOIN jenis_cuti ON pengajuan_cuti.id_jenis = jenis_cuti.id_jenis 
    WHERE id_pengajuan='$id_pengajuan'";

$query = mysqli_query($koneksi, $sql);
$data = mysqli_fetch_array($query);

if (!$data) { die("<h3>DATA TIDAK DITEMUKAN:</h3> <p>ID Pengajuan: $id_pengajuan tidak valid.</p>"); }

// --- LOGIC HITUNG HISTORY SALDO ---
$saldo_n_realtime  = (int)$data['u_sisa_n_realtime'];
$saldo_n1_realtime = (int)$data['u_sisa_n1_realtime'];
$potongan_ini_n    = (int)$data['dipotong_n'];
$potongan_ini_n1   = (int)$data['dipotong_n1'];

$q_future = mysqli_query($koneksi, "SELECT SUM(dipotong_n) as masa_depan_n, SUM(dipotong_n1) as masa_depan_n1 FROM pengajuan_cuti WHERE id_user = '".$data['id_user']."' AND id_pengajuan > '$id_pengajuan' AND status = 'Disetujui'");
$future = mysqli_fetch_array($q_future);
$kembalikan_n_future  = (int)$future['masa_depan_n'];
$kembalikan_n1_future = (int)$future['masa_depan_n1'];

$sisa_n_tampil  = $saldo_n_realtime + $kembalikan_n_future + $potongan_ini_n;
$sisa_n1_tampil = $saldo_n1_realtime + $kembalikan_n1_future + $potongan_ini_n1;

// --- LOGIC TAMPILAN KETERANGAN (FORM PAGE 2) ---
$id_jenis   = (int)$data['id_jenis']; 
$lama_ambil = (int)$data['lama_hari'];
$ket_tahunan_n = ""; $ket_tahunan_n1 = ""; $ket_sakit = ""; $ket_lahir = ""; $ket_penting = ""; $ket_luar = "";

switch ($id_jenis) {
   case 1: // CUTI TAHUNAN
        $ambil_n  = $potongan_ini_n;
        $ambil_n1 = $potongan_ini_n1;
        if (($ambil_n + $ambil_n1) == 0 && $lama_ambil > 0) {
            if ($sisa_n1_tampil >= $lama_ambil) { $ambil_n1 = $lama_ambil; $ambil_n = 0; } 
            else { $ambil_n1 = $sisa_n1_tampil; $ambil_n  = $lama_ambil - $ambil_n1; }
        }
        if ($ambil_n1 > 0) { $ket_tahunan_n1 = "Diambil " . $ambil_n1 . " hari, Sisa " . ($sisa_n1_tampil - $ambil_n1) . " hari"; } 
        if ($ambil_n > 0) { $ket_tahunan_n = "Diambil " . $ambil_n . " hari, Sisa " . ($sisa_n_tampil - $ambil_n) . " hari"; } 
        break;
    case 2: // SAKIT
        $sisa_sakit = isset($data['kuota_cuti_sakit']) ? (int)$data['kuota_cuti_sakit'] : 0;
        $ket_sakit = "Diambil " . $lama_ambil . " hari, Sisa " . $sisa_sakit . " hari";
        break;
    case 3: $ket_besar   = "Diambil " . $lama_ambil . " hari"; break;
    case 4: $ket_lahir   = "Diambil " . $lama_ambil . " hari"; break;
    case 5: $ket_penting = "Diambil " . $lama_ambil . " hari"; break;
    case 6: $ket_luar    = "Diambil " . $lama_ambil . " hari"; break;
}

// Simbol Centang
$c1 = ($id_jenis == 1) ? '&#10003;' : ''; $c2 = ($id_jenis == 3) ? '&#10003;' : '';
$c3 = ($id_jenis == 2) ? '&#10003;' : ''; $c4 = ($id_jenis == 4) ? '&#10003;' : '';
$c5 = ($id_jenis == 5) ? '&#10003;' : ''; $c6 = ($id_jenis == 6) ? '&#10003;' : '';

// Tahun & Tanggal
$tahun_n  = date('Y', strtotime($data['tgl_pengajuan'])); 
$tahun_n1 = $tahun_n - 1; 
$tahun_n2 = $tahun_n - 2;

// FUNGSI TANGGAL & HARI INDO
if (!function_exists('tgl_indo')) {
    function tgl_indo($tanggal){
        $bulan = array (1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
        $pecahkan = explode('-', $tanggal);
        return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
    }
}
if (!function_exists('hari_indo')) {
    function hari_indo($tanggal){
        $hari = date('l', strtotime($tanggal));
        $semua_hari = array("Sunday" => "Minggu", "Monday" => "Senin", "Tuesday" => "Selasa", "Wednesday" => "Rabu", "Thursday" => "Kamis", "Friday" => "Jumat", "Saturday" => "Sabtu");
        return $semua_hari[$hari];
    }
}
if (!function_exists('angka_terbilang')) {
    function angka_terbilang($angka) {
        $angka = (int)$angka;
        $terbilang = array(
            1 => 'satu', 2 => 'dua', 3 => 'tiga', 4 => 'empat', 5 => 'lima',
            6 => 'enam', 7 => 'tujuh', 8 => 'delapan', 9 => 'sembilan', 10 => 'sepuluh',
            11 => 'sebelas', 12 => 'dua belas', 13 => 'tiga belas', 14 => 'empat belas', 15 => 'lima belas',
            16 => 'enam belas', 17 => 'tujuh belas', 18 => 'delapan belas', 19 => 'sembilan belas', 20 => 'dua puluh',
            30 => 'tiga puluh', 40 => 'empat puluh', 50 => 'lima puluh', 60 => 'enam puluh',
            70 => 'tujuh puluh', 80 => 'delapan puluh', 90 => 'sembilan puluh', 100 => 'seratus'
        );
        
        if ($angka < 1 || $angka > 99) return $angka;
        if (isset($terbilang[$angka])) return $terbilang[$angka];
        
        $puluhan = (int)($angka / 10) * 10;
        $satuan = $angka % 10;
        return $terbilang[$puluhan] . ' ' . $terbilang[$satuan];
    }
}

$hari_mulai     = hari_indo($data['tgl_mulai']);
$tgl_mulai_indo = tgl_indo($data['tgl_mulai']);
$hari_selesai   = hari_indo($data['tgl_selesai']);
$tgl_selesai_indo = tgl_indo($data['tgl_selesai']);

// Data Atasan
$nama_atasan = "............................................."; 
$nip_atasan  = ".......................";
$id_atasan_terpilih = isset($data['id_atasan_fix']) ? $data['id_atasan_fix'] : 0; 

if ($id_atasan_terpilih > 0) {
    $cari_bos = mysqli_query($koneksi, "SELECT nama_lengkap, nip FROM users WHERE id_user = '$id_atasan_terpilih'");
    if ($bos = mysqli_fetch_array($cari_bos)) { $nama_atasan = $bos['nama_lengkap']; $nip_atasan = $bos['nip']; }
}

// Logic Pejabat
$tipe_ttd = isset($data['ttd_pejabat']) ? $data['ttd_pejabat'] : 'ketua'; 
$label_pejabat = "Ketua,";
$nama_pejabat  = isset($instansi['ketua_nama']) ? $instansi['ketua_nama'] : '..................';
$nip_pejabat   = isset($instansi['ketua_nip']) ? $instansi['ketua_nip'] : '..................';

if ($tipe_ttd == 'wakil') {
    $label_pejabat = "Wakil Ketua,";
    $nama_pejabat  = isset($instansi['wakil_nama']) ? $instansi['wakil_nama'] : '..................';
    $nip_pejabat   = isset($instansi['wakil_nip']) ? $instansi['wakil_nip'] : '..................';
} elseif (strpos($tipe_ttd, 'plh|') === 0) {
    // Format: "plh|nama|nip"
    $parts = explode('|', $tipe_ttd);
    $label_pejabat = "";
    $nama_pejabat = isset($parts[1]) ? $parts[1] : '';
    $nip_pejabat = isset($parts[2]) ? $parts[2] : '';
} elseif ($tipe_ttd == 'plh') {
    // Legacy: Empty PLH (jika ada data lama)
    $label_pejabat = ""; $nama_pejabat = ""; $nip_pejabat = "";
}

// TEXT JENIS CUTI UNTUK HALAMAN 1 & 3
$nama_jenis_final = $data['nama_jenis']; 
// Jika di database huruf kecil, ubah jadi Capitalize
$nama_jenis_final = ucwords(strtolower($nama_jenis_final));

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Cuti - <?php echo $data['nomor_surat']; ?></title>
    <style>
        @page { size: 215mm 330mm; margin: 0; } /* Ukuran F4 */
        body { font-family: Arial, sans-serif; color: #000; margin: 0; padding: 0; line-height: 1.3; }
        
        /* Container Per Halaman */
        .page-container {
            width: 215mm;
            min-height: 320mm;
            padding: 1.5cm 2cm;
            box-sizing: border-box;
            position: relative;
            background: white;
            page-break-after: always; 
        }
        .page-container:last-child { page-break-after: auto; }

        /* HEADER LAMPIRAN (Times New Roman, Size 8pt - KECIL) */
        .header-lampiran { 
            font-family: 'Times New Roman', serif; 
            font-size: 5pt; 
            font-weight: bold;
            text-align: right; 
            margin-bottom: 5px; 
        }

        /* STYLE UMUM */
        .judul-utama { text-align: center; font-weight: bold; font-size: 11pt; margin: 5px 0 2px 0; font-family: Arial, sans-serif; }
        .nomor-surat { text-align: center; font-size: 11pt; font-weight: bold; margin-bottom: 5px; font-family: Arial, sans-serif;}
        
        /* STYLE HALAMAN 1 (ARIAL 12) */
        .page-1-content { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; }
        .biodata-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .biodata-table td { padding: 4px 0; vertical-align: top; }
        .biodata-label { width: 170px; }
        .biodata-sep { width: 15px; text-align: center; }

        /* STYLE TANDA TANGAN KONSISTEN */
        .ttd-wrapper {
            float: right;
            width: 230px; /* Lebar area tanda tangan */
            text-align: center;
            margin-top: 40px;
        }
        .ttd-nama {
            margin-top: 70px;
            font-weight: bold;
        }
        .ttd-garis {
            border-top: 1px solid #000;
            width: 100%;
            margin: 2px 0;
        }
        .ttd-nip {
            text-align: center; /* NIP Rata Tengah dengan garis */
            font-weight: normal;
        }

        /* STYLE HALAMAN 2 (TABEL) */
        table.tbl-form { width: 100%; border-collapse: collapse; margin-bottom: 3px; font-family: Arial, sans-serif; }
        table.tbl-form th, table.tbl-form td { border: 1px solid #000; padding: 0 4px; vertical-align: middle; height: 0.5cm; font-size: 8pt; }
        
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .valign-top { vertical-align: top; }
        .no-border { border: none !important; }
        
        /* TTD KOTAK DI TABEL (FIXED SIZE) */
        .box-ttd-fixed { 
            height: 3cm; 
            width: 100%; 
            position: relative; 
            padding: 5px; 
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }
        .ttd-tbl-nama { width: 100%; text-align: center; font-weight: bold; margin-bottom: 2px; }
        .ttd-tbl-garis { border-top: 1px solid #000; width: 100%; margin: 0; }
        .ttd-tbl-nip { width: 100%; text-align: center; font-weight: bold; margin-top: 2px; }

        @media print {
            .no-print { display: none !important; }
            body { background: none; }
            .page-container { margin: 0; border: none; width: 100%; height: auto; page-break-after: always; }
        }

        /* Prevent breaking small blocks (like notes) across printed pages */
        .no-break {
            page-break-inside: avoid;
            break-inside: avoid;
            -webkit-column-break-inside: avoid;
            -webkit-page-break-inside: avoid;
        }
    </style>
</head>
<body>

    <div class="no-print" style="position:fixed; top:10px; right:10px; z-index:9999;">
        <button onclick="window.print()" style="padding:8px 20px; font-weight:bold; cursor:pointer; background:#006B3F; color:white; border:none; border-radius:4px;">CETAK</button>
        <button onclick="window.close()" style="padding:8px 20px; cursor:pointer; background:#d33; color:white; border:none; border-radius:4px;">TUTUP</button>
    </div>

    <div class="page-container page-1-content">
        <br><br>
        <div style="margin-bottom: 30px;">
            Kepada Yth.<br>
            Ketua Pengadilan Negeri Yogyakarta<br>
            di-<br>
            <span style="margin-left: 30px;">Yogyakarta</span>
        </div>

        <br>
        <p>Saya yang bertanda tangan di bawah ini :</p>

        <table class="biodata-table" style="margin-left: 30px;">
            <tr>
                <td class="biodata-label">Nama</td><td class="biodata-sep">:</td>
                <td><?php echo $data['nama_lengkap']; ?></td>
            </tr>
            <tr>
                <td>NIP</td><td>:</td>
                <td><?php echo $data['nip']; ?></td>
            </tr>
            <tr>
                <td>Pangkat/Gol.Ruang</td><td>:</td>
                <td><?php echo $data['pangkat']; ?></td>
            </tr>
            <tr>
                <td>Jabatan</td><td>:</td>
                <td><?php echo $data['jabatan']; ?></td>
            </tr>
            <tr>
                <td>No. Handphone</td><td>:</td>
                <td><?php echo $data['no_telepon']; ?></td>
            </tr>
        </table>

        <div style="text-align: justify;">
            dengan ini mengajukan <b><?php echo $nama_jenis_final; ?></b> selama <?php echo $data['lama_hari']; ?> hari, pada hari
            <?php echo $hari_mulai; ?> s.d <?php echo $hari_selesai; ?>, tanggal <?php echo $tgl_mulai_indo; ?> s.d <?php echo $tgl_selesai_indo; ?> untuk keperluan
            <?php echo $data['alasan']; ?>.
        </div>

        <p>Demikian permohonan <b><?php echo $nama_jenis_final; ?></b> ini, atas perkenananya saya ucapkan terima kasih.</p>

        <div class="ttd-wrapper">
            Yogyakarta, <?php echo tgl_indo($data['tgl_pengajuan']); ?><br>
            Hormat saya,
            <div class="ttd-nama"><?php echo $data['nama_lengkap']; ?></div>
            <div class="ttd-garis"></div>
            <div class="ttd-nip">NIP. <?php echo $data['nip']; ?></div>
        </div>
    </div>


    <div class="page-container">
        <div class="header-lampiran">
            LAMPIRAN II : SURAT EDARAN SEKRETARIS MAHKAMAH AGUNG <br>
            REPUBLIK INDONESIA <br>
            NOMOR 13 TAHUN 2019
        </div>

        <div style="text-align: right; font-size: 10pt; margin-bottom: 5px; font-family: Arial;">
            Yogyakarta, <?php echo tgl_indo($data['tgl_pengajuan']); ?>
        </div>

        <div style="margin-bottom: 5px; font-size: 10pt; font-family: Arial;">
            Kepada :<br>
            Yth. Ketua Pengadilan Negeri Yogyakarta Kelas IA<br>
            di - <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;YOGYAKARTA.
        </div>

        <div class="judul-utama">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>
        <div class="nomor-surat">Nomor : <?php echo $data['nomor_surat']; ?></div>

        <table class="tbl-form">
            <tr><td colspan="4" class="font-bold">I. DATA PEGAWAI</td></tr>
            <tr>
                <td style="width: 3cm;">Nama</td><td style="width: 6.5cm;"><?php echo $data['nama_lengkap']; ?></td>
                <td style="width: 3cm;">NIP</td><td style="width: 6.5cm;"><?php echo $data['nip']; ?></td>
            </tr>
            <tr>
                <td>Jabatan</td><td><?php echo $data['jabatan']; ?></td>
                <td>Gol.Ruang</td><td><?php echo $data['pangkat']; ?></td>
            </tr>
            <tr>
                <td>Unit Kerja</td><td>Pengadilan Negeri Yogyakarta</td>
                <td>Masa Kerja</td><td><?php echo isset($data['masa_kerja']) ? $data['masa_kerja'] : '-'; ?></td>
            </tr>
        </table>

        <table class="tbl-form">
            <tr><td colspan="4" class="font-bold">II. JENIS CUTI YANG DIAMBIL**</td></tr>
            <tr>
                <td width="40%">1. Cuti Tahunan</td><td width="10%" class="text-center font-bold"><?php echo $c1; ?></td>
                <td width="40%">4. Cuti Besar</td><td width="10%" class="text-center font-bold"><?php echo $c2; ?></td>
            </tr>
            <tr>
                <td>2. Cuti Sakit</td><td class="text-center font-bold"><?php echo $c3; ?></td>
                <td>5. Cuti Melahirkan</td><td class="text-center font-bold"><?php echo $c4; ?></td>
            </tr>
            <tr>
                <td>3. Cuti Karena Alasan Penting</td><td class="text-center font-bold"><?php echo $c5; ?></td>
                <td>6. Cuti di Luar Tanggungan Negara</td><td class="text-center font-bold"><?php echo $c6; ?></td>
            </tr>
        </table>

        <table class="tbl-form">
            <tr><td class="font-bold">III. ALASAN CUTI</td></tr>
            <tr><td style="height: 25px; padding: 5px; vertical-align: top;"><?php echo $data['alasan']; ?></td></tr>
        </table>

        <table class="tbl-form">
            <tr><td colspan="6" class="font-bold">IV. LAMANYA CUTI</td></tr>
            <tr>
                <td width="15%">Selama</td><td width="20%"><?php echo $data['lama_hari']; ?> Hari</td>
                <td width="15%" class="text-center">mulai tanggal</td>
                <td width="20%" class="text-center"><?php echo date('d-m-Y', strtotime($data['tgl_mulai'])); ?></td>
                <td width="5%" class="text-center">s/d</td>
                <td width="25%" class="text-center"><?php echo date('d-m-Y', strtotime($data['tgl_selesai'])); ?></td>
            </tr>
        </table>

        <table class="tbl-form">
            <tr><td colspan="6" class="font-bold">V. CATATAN CUTI***</td></tr>
            <tr>
                <td colspan="3" width="40%">1. CUTI TAHUNAN</td><td width="15%" class="text-center">PARAF PETUGAS CUTI</td>
                <td width="35%">2. CUTI BESAR</td><td width="10%" class="text-center" style="font-size: 8pt;"><?php echo $ket_besar; ?></td> 
            </tr>
            <tr>
                <td width="10%" class="text-center">Tahun</td><td width="10%" class="text-center">Sisa</td><td width="20%" class="text-center">Keterangan</td>
                <td rowspan="4" class="valign-top"></td> 
                <td>3. CUTI SAKIT</td><td class="text-center" style="font-size: 8pt;"><?php echo $ket_sakit; ?></td> 
            </tr>
            <tr>
                <td class="text-center"><?php echo $tahun_n2; ?></td><td class="text-center">-</td><td></td>
                <td>4. CUTI MELAHIRKAN</td><td class="text-center" style="font-size: 8pt;"><?php echo $ket_lahir; ?></td> 
            </tr>
            <tr>
                <td class="text-center"><?php echo $tahun_n1; ?></td><td class="text-center"><?php echo $sisa_n1_tampil; ?></td>
                <td class="text-center" style="font-size: 8pt;"><?php echo $ket_tahunan_n1; ?></td>
                <td>5. CUTI KARENA ALASAN PENTING</td><td class="text-center" style="font-size: 8pt;"><?php echo $ket_penting; ?></td> 
            </tr>
            <tr>
                <td class="text-center"><?php echo $tahun_n; ?></td><td class="text-center"><?php echo $sisa_n_tampil; ?></td>
                <td class="text-center" style="font-size: 8pt;"><?php echo $ket_tahunan_n; ?></td>
                <td>6. CUTI DI LUAR TANGGUNGAN NEGARA</td><td class="text-center" style="font-size: 8pt;"><?php echo $ket_luar; ?></td> 
            </tr>
        </table>

        <table class="tbl-form">
            <tr><td colspan="3" class="font-bold">VI. ALAMAT SELAMA MENJALANKAN CUTI</td></tr>
            <tr>
                <td rowspan="2" class="valign-top" style="padding: 5px;"><?php echo $data['alamat_cuti']; ?></td>
                <td class="bt-0 bb-0" style="width: 2cm;">Telp</td><td class="bt-0 bb-0" style="width: 4cm;"><?php echo $data['no_telepon']; ?></td>
            </tr>
            <tr>
                <td colspan="2" style="width: 6cm; padding: 0;">
                    <div class="box-ttd-fixed">
                        <div style="margin-top: 5px; text-align: center;">Hormat saya,</div>
                        <div style="margin-top:auto; width: 100%;">
                            <div class="ttd-tbl-nama"><?php echo $data['nama_lengkap']; ?></div>
                            <div class="ttd-tbl-garis"></div>
                            <div class="ttd-tbl-nip">NIP. <?php echo $data['nip']; ?></div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>    

        <table class="tbl-form">
            <tr><td colspan="4" class="font-bold">VII. PERTIMBANGAN ATASAN LANGSUNG**</td></tr>
            <tr><td class="text-center">DISETUJUI</td><td class="text-center">PERUBAHAN***</td><td class="text-center">DITANGGUHKAN***</td><td class="text-center" style="width:6cm;">TIDAK DISETUJUI****</td></tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr>
                <td colspan="3" class="no-border"></td>
                <td style="border: 1px solid #000; padding: 0; width: 6cm;">
                    <div class="box-ttd-fixed">
                        <div style="margin-top:auto; width: 100%;">
                            <div class="ttd-tbl-nama"><?php echo $nama_atasan; ?></div>
                            <div class="ttd-tbl-garis"></div>
                            <div class="ttd-tbl-nip">NIP. <?php echo $nip_atasan; ?></div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="tbl-form">
            <tr><td colspan="4" class="font-bold">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</td></tr>
            <tr><td class="text-center">DISETUJUI</td><td class="text-center">PERUBAHAN***</td><td class="text-center">DITANGGUHKAN****</td><td class="text-center" style="width:6cm;">TIDAK DISETUJUI****</td></tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr>
                <td colspan="3" class="no-border"></td>
                <td style="border: 1px solid #000; padding: 0; width: 6cm;">
                    <div class="box-ttd-fixed">
                        <div style="text-align: center; margin-top: 5px;"><?php echo $label_pejabat; ?></div>
                        <div style="margin-top:auto; width: 100%;">
                            <div class="ttd-tbl-nama"><?php echo $nama_pejabat; ?></div>
                            <div class="ttd-tbl-garis"></div>
                            <div class="ttd-tbl-nip">NIP. <?php echo $nip_pejabat; ?></div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        
        <div style="font-size: 5pt; margin-top: -8px; font-family: Arial;">
            Catatan : <br>
            * Coret yang tidak perlu,<br>
            ** Pilih salah satu dengan memberi tanda centang (&#10003;),<br>
            *** Diisi oleh pejabat yang menangani bidang kepegawaian sebelum PNS mengajukan cuti,<br>
            **** Diberi tanda centang dan alasannya<br>
        </div>
    </div>

    <div class="page-container page-1-content">
        <div class="header-lampiran">
            LAMPIRAN II : SURAT EDARAN SEKRETARIS MAHKAMAH AGUNG <br>
            REPUBLIK INDONESIA <br>
            NOMOR 13 TAHUN 2019
        </div>

        <br>
        <div class="judul-utama" style="text-decoration: underline;">SURAT PERNYATAAN</div>
        <br>

        <p>Yang bertanda tangan di bawah ini :</p>

        <table class="biodata-table" style="margin-left: 30px;">
            <tr>
                <td class="biodata-label">Nama</td><td class="biodata-sep">:</td>
                <td><?php echo $data['nama_lengkap']; ?></td>
            </tr>
            <tr>
                <td>NIP</td><td>:</td>
                <td><?php echo $data['nip']; ?></td>
            </tr>
            <tr>
                <td>Pangkat/Gol.Ruang</td><td>:</td>
                <td><?php echo $data['pangkat']; ?></td>
            </tr>
            <tr>
                <td>Jabatan</td><td>:</td>
                <td><?php echo $data['jabatan']; ?></td>
            </tr>
        </table>

        <div style="text-align: justify; line-height: 1.6;">
            Menyatakan dengan sesungguhnya bahwa selama saya melaksanakan <b><?php echo $nama_jenis_final; ?></b>
            selama <?php echo $data['lama_hari']; ?> (<?php echo angka_terbilang($data['lama_hari']); ?>) hari terhitung sejak tanggal <?php echo $tgl_mulai_indo; ?> s.d <?php echo $tgl_selesai_indo; ?>
            perkara yang harus saya sidangkan pada tanggal tersebut yaitu berkas perkara nomor :
        </div>

        <div style="margin-left: 50px; margin-top: 15px;">
            1. <br><br>
            2. <br><br>
            3. 
        </div>

        <br>
        <p>Adapun tugas saya sebagai Panitera Pengganti / Hakim * digantikan oleh :</p>
        <div style="margin-top:5px; margin-bottom: 20px;">
             .......................................................................................................................................................
        </div>

        <p>Demikian surat pernyataan ini saya buat dengan sesungguhnya untuk dipergunakan sebagaimana mestinya.</p>

        <div class="ttd-wrapper">
            Yogyakarta, <?php echo tgl_indo($data['tgl_pengajuan']); ?><br>
            Yang Menyatakan,
            <div class="ttd-nama"><?php echo $data['nama_lengkap']; ?></div>
            <div class="ttd-garis"></div>
            <div class="ttd-nip">NIP. <?php echo $data['nip']; ?></div>
        </div>
    </div>

</body>
</html>