<?php
/** @var mysqli $koneksi */

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$path_db1 = '../../assets/config/database.php';
$path_db2 = '../../config/database.php';
$path_db3 = '../config/database.php'; 

if (file_exists($path_db1)) {
    include $path_db1;
} elseif (file_exists($path_db2)) {
    include $path_db2;
} elseif (file_exists($path_db3)) {
    include $path_db3;
} else {
    die("<h3>ERROR FATAL: File database.php tidak ditemukan!</h3>");
}

if (empty($koneksi)) {
    die("<h3>ERROR KONEKSI:</h3> <p>Variabel <code>\$koneksi</code> kosong/gagal.</p>");
}

if (!isset($_SESSION['id_user']) && !isset($_SESSION['id_admin'])) {
     die("<h3>AKSES DITOLAK:</h3> <p>Harap login terlebih dahulu.</p>");
}

// --- 3. AMBIL DATA SETTING INSTANSI ---
$q_instansi = mysqli_query($koneksi, "SELECT * FROM tbl_setting_instansi WHERE id_setting='1'");
$instansi = ($q_instansi) ? mysqli_fetch_array($q_instansi) : null;
if(!$instansi) {
    // Fallback default
    $instansi = [
        'ketua_nama' => '..................', 'ketua_nip' => '..................',
        'wakil_nama' => '..................', 'wakil_nip' => '..................'
    ];
}

// --- 4. AMBIL DATA PENGAJUAN ---
if (!isset($_GET['id'])) {
    die("<h3>ERROR:</h3> <p>ID tidak ditemukan di URL.</p>");
}

$id_pengajuan = (int)$_GET['id'];

$sql = "SELECT 
    pengajuan_cuti.*, 
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

if (!$data) {
    die("<h3>DATA TIDAK DITEMUKAN:</h3> <p>ID Pengajuan: $id_pengajuan tidak valid.</p>");
}

// ============================================================
// --- LOGIC HITUNG HISTORY (SNAPSHOT SALDO SEBELUM TERPOTONG) ---
// ============================================================

// 1. Ambil saldo user SAAT INI (Realtime)
$saldo_n_realtime  = (int)$data['u_sisa_n_realtime'];
$saldo_n1_realtime = (int)$data['u_sisa_n1_realtime'];

// 2. Ambil potongan dari pengajuan INI
$potongan_ini_n  = (int)$data['dipotong_n'];
$potongan_ini_n1 = (int)$data['dipotong_n1'];

// 3. Hitung jumlah cuti yang disetujui DI MASA DEPAN (ID lebih besar dari ID ini)
$q_future = mysqli_query($koneksi, "SELECT 
    SUM(dipotong_n) as masa_depan_n, 
    SUM(dipotong_n1) as masa_depan_n1 
    FROM pengajuan_cuti 
    WHERE id_user = '".$data['id_user']."' 
    AND id_pengajuan > '$id_pengajuan' 
    AND status = 'Disetujui'");

$future = mysqli_fetch_array($q_future);
$kembalikan_n_future  = (int)$future['masa_depan_n'];
$kembalikan_n1_future = (int)$future['masa_depan_n1'];

// 4. Tentukan Saldo "Sebelum Terpotong" untuk pengajuan ini
// Saldo Realtime + Potongan Masa Depan + Potongan Cuti Ini
$sisa_n_tampil  = $saldo_n_realtime + $kembalikan_n_future + $potongan_ini_n;
$sisa_n1_tampil = $saldo_n1_realtime + $kembalikan_n1_future + $potongan_ini_n1;


// --- LOGIC TAMPILAN KETERANGAN ---
$id_jenis   = (int)$data['id_jenis']; 
$lama_ambil = (int)$data['lama_hari'];

$ket_tahunan_n  = ""; 
$ket_tahunan_n1 = "";
$ket_besar = ""; $ket_sakit = ""; $ket_lahir = ""; $ket_penting = ""; $ket_luar = "";

switch ($id_jenis) {
   case 1: // CUTI TAHUNAN
        $ambil_n  = $potongan_ini_n;
        $ambil_n1 = $potongan_ini_n1;

        // Fallback jika database belum nyatat potongan (misal manual/legacy)
        if (($ambil_n + $ambil_n1) == 0 && $lama_ambil > 0) {
            if ($sisa_n1_tampil >= $lama_ambil) { 
                 $ambil_n1 = $lama_ambil;
                 $ambil_n = 0;
            } else {
                 $ambil_n1 = $sisa_n1_tampil;
                 $ambil_n  = $lama_ambil - $ambil_n1;
            }
        }

        // Keterangan Sisa di sini adalah sisa SETELAH dipotong pengajuan ini
        if ($ambil_n1 > 0) {
            $sisa_akhir_n1 = $sisa_n1_tampil - $ambil_n1;
            $ket_tahunan_n1 = "Diambil " . $ambil_n1 . " hari, Sisa " . $sisa_akhir_n1 . " hari";
        } 
        
        if ($ambil_n > 0) {
            $sisa_akhir_n = $sisa_n_tampil - $ambil_n;
            $ket_tahunan_n = "Diambil " . $ambil_n . " hari, Sisa " . $sisa_akhir_n . " hari";
        } 
        break;

    case 2: // SAKIT
        $sisa_sakit = isset($data['kuota_cuti_sakit']) ? (int)$data['kuota_cuti_sakit'] : 0;
        // Asumsi kuota_cuti_sakit di user adalah sisa yang sudah terpotong
        $sisa_sebelum_sakit = $sisa_sakit + $lama_ambil;
        $ket_sakit = "Diambil " . $lama_ambil . " hari, Sisa " . $sisa_sakit . " hari";
        // Override tampilan sisa sakit jika ingin konsisten "sebelum terpotong"
        $sisa_sakit_tampil = $sisa_sebelum_sakit; 
        break;

    case 3: $ket_besar   = "Diambil " . $lama_ambil . " hari"; break;
    case 4: $ket_lahir   = "Diambil " . $lama_ambil . " hari"; break;
    case 5: $ket_penting = "Diambil " . $lama_ambil . " hari"; break;
    case 6: $ket_luar    = "Diambil " . $lama_ambil . " hari"; break;
}

// Simbol Centang (Fix Mapping)
$c1 = ($id_jenis == 1) ? '&#10003;' : '';
$c2 = ($id_jenis == 3) ? '&#10003;' : '';
$c3 = ($id_jenis == 2) ? '&#10003;' : '';
$c4 = ($id_jenis == 4) ? '&#10003;' : '';
$c5 = ($id_jenis == 5) ? '&#10003;' : '';
$c6 = ($id_jenis == 6) ? '&#10003;' : '';

// Tahun & Tanggal
$tahun_n  = date('Y', strtotime($data['tgl_pengajuan'])); 
$tahun_n1 = $tahun_n - 1; 
$tahun_n2 = $tahun_n - 2;

if (!function_exists('tgl_indo')) {
    function tgl_indo($tanggal){
        $bulan = array (1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
        $pecahkan = explode('-', $tanggal);
        return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
    }
}

// Data Atasan
$nama_atasan = "............................................."; 
$nip_atasan  = ".......................";
$id_atasan_terpilih = isset($data['id_atasan_fix']) ? $data['id_atasan_fix'] : 0; 

if ($id_atasan_terpilih > 0) {
    $cari_bos = mysqli_query($koneksi, "SELECT nama_lengkap, nip FROM users WHERE id_user = '$id_atasan_terpilih'");
    if ($bos = mysqli_fetch_array($cari_bos)) {
        $nama_atasan = $bos['nama_lengkap'];
        $nip_atasan  = $bos['nip'];
    }
}

// --- LOGIC PEJABAT BERWENANG (SECTION VIII) ---
// Default ke 'ketua' jika tidak ada di DB
$tipe_ttd = isset($data['ttd_pejabat']) ? $data['ttd_pejabat'] : 'ketua'; 

// Variabel default (Ketua)
$label_pejabat = "Ketua,";
// Cek berbagai kemungkinan nama kolom di database (ketua_nama atau nama_ketua)
$nama_pejabat  = isset($instansi['ketua_nama']) ? $instansi['ketua_nama'] : (isset($instansi['nama_ketua']) ? $instansi['nama_ketua'] : '..................');
$nip_pejabat   = isset($instansi['ketua_nip']) ? $instansi['ketua_nip'] : (isset($instansi['nip_ketua']) ? $instansi['nip_ketua'] : '..................');

if ($tipe_ttd == 'wakil') {
    $label_pejabat = "Wakil Ketua,";
    // Cek kemungkinan nama kolom wakil
    $nama_pejabat  = isset($instansi['wakil_nama']) ? $instansi['wakil_nama'] : (isset($instansi['nama_wakil']) ? $instansi['nama_wakil'] : '..................');
    $nip_pejabat   = isset($instansi['wakil_nip']) ? $instansi['wakil_nip'] : (isset($instansi['nip_wakil']) ? $instansi['nip_wakil'] : '..................');
} elseif ($tipe_ttd == 'plh') {
    $label_pejabat = "";
    $nama_pejabat  = "";
    $nip_pejabat   = "";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Cuti - <?php echo $data['nomor_surat']; ?></title>
    <style>
        @page { size: 215mm 330mm; margin: 1cm 1.5cm 1cm 1.5cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; margin: 0; padding: 0; line-height: 1; }
        .container { width: 100%; }
        .header-lampiran { text-align: right; font-size: 8pt; margin-bottom: 5px; margin-top: 5px; }
        .tgl-lokasi { text-align: right; margin-bottom: 5px; font-size: 10pt; }
        .tujuan-surat { margin-bottom: 5px; font-size: 10pt; }
        .judul-utama { text-align: center; font-weight: bold; font-size: 11pt; margin-top: 5px; margin-bottom: 2px; }
        .nomor-surat { text-align: center; font-size: 11pt; font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
        th, td { border: 1px solid #000; padding: 0 4px; vertical-align: middle; height: 0.5cm; font-size: 9pt; }
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .valign-top { vertical-align: top; }
        .bt-0 { border-top: none !important; } .bl-0 { border-left: none !important; }
        .br-0 { border-right: none !important; } .bb-0 { border-bottom: none !important; }
        .no-border { border: none !important; }
        .check-col { text-align: center; font-size: 11pt; font-weight: bold; }
        .cell-alasan { height: 25px !important; padding: 5px !important; vertical-align: top; }
        .cell-alamat { height: auto !important; padding: 5px !important; vertical-align: top; }
        .col-right-fixed { width: 6cm !important; min-width: 6cm !important; max-width: 6cm !important; }
        .box-ttd-fixed { height: 3cm; width: 100%; position: relative; box-sizing: border-box; padding: 5px; }
        .nip-bottom { position: absolute; 
            bottom: 5px; 
            left: 5px; 
            right: 5px; 
            border-top: 2px solid #000; 
            font-weight: bold; 
            padding-top: 2px; 
            text-align: left;
            letter-spacing: 0.8px; /* Angka NIP jadi berjarak */
        }
        .small-text { font-size: 8pt; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print" style="position:fixed; top:10px; right:10px; z-index:9999;">
        <button onclick="window.print()" style="padding:8px 20px; font-weight:bold; cursor:pointer; background:#006B3F; color:white; border:none; border-radius:4px;">CETAK</button>
        <button onclick="window.close()" style="padding:8px 20px; cursor:pointer; background:#d33; color:white; border:none; border-radius:4px;">TUTUP</button>
    </div>

    <div class="container">
        <div class="header-lampiran">
            LAMPIRAN II : SURAT EDARAN SEKRETARIS MAHKAMAH AGUNG <br>
            REPUBLIK INDONESIA <br>
            NOMOR 13 TAHUN 2019
        </div>

        <div class="tgl-lokasi">Yogyakarta, <?php echo tgl_indo($data['tgl_pengajuan']); ?></div>

        <div class="tujuan-surat">
            Kepada :<br>
            Yth. Ketua Pengadilan Negeri, HI dan Tipikor Yogyakarta Kelas IA<br>
            di - <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;YOGYAKARTA.
        </div>

        <div class="judul-utama">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>
        <div class="nomor-surat">Nomor : <?php echo $data['nomor_surat']; ?></div>

        <table>
            <tr><td colspan="4" class="font-bold">I. DATA PEGAWAI</td></tr>
            <tr>
                <td style="width: 3cm;">Nama</td><td style="width: 6.5cm;"><?php echo $data['nama_lengkap']; ?></td>
                <td style="width: 3cm;">NIP</td><td style="width: 6.5cm;"><?php echo $data['nip']; ?></td>
            </tr>
            <tr>
                <td>Jabatan</td><td><?php echo $data['jabatan']; ?></td>
                <td>Gol.Ruang</td><td><?php echo isset($data['pangkat']) ? $data['pangkat'] : '-'; ?></td>
            </tr>
            <tr>
                <td>Unit Kerja</td><td>Pengadilan Negeri Yogyakarta</td>
                <td>Masa Kerja</td><td><?php echo isset($data['masa_kerja']) ? $data['masa_kerja'] : '-'; ?></td>
            </tr>
        </table>

        <table>
            <tr><td colspan="4" class="font-bold">II. JENIS CUTI YANG DIAMBIL**</td></tr>
            <tr>
                <td width="40%">1. Cuti Tahunan</td><td width="10%" class="check-col"><?php echo $c1; ?></td>
                <td width="40%">4. Cuti Besar</td><td width="10%" class="check-col"><?php echo $c2; ?></td>
            </tr>
            <tr>
                <td>2. Cuti Sakit</td><td class="check-col"><?php echo $c3; ?></td>
                <td>5. Cuti Melahirkan</td><td class="check-col"><?php echo $c4; ?></td>
            </tr>
            <tr>
                <td>3. Cuti Karena Alasan Penting</td><td class="check-col"><?php echo $c5; ?></td>
                <td>6. Cuti di Luar Tanggungan Negara</td><td class="check-col"><?php echo $c6; ?></td>
            </tr>
        </table>

        <table>
            <tr><td class="font-bold">III. ALASAN CUTI</td></tr>
            <tr><td class="cell-alasan"><?php echo $data['alasan']; ?></td></tr>
        </table>

        <table>
            <tr><td colspan="6" class="font-bold">IV. LAMANYA CUTI</td></tr>
            <tr>
                <td width="15%">Selama</td><td width="20%"><?php echo $data['lama_hari']; ?> (Hari)*</td>
                <td width="15%" class="text-center">mulai tanggal</td>
                <td width="20%" class="text-center"><?php echo isset($data['tgl_mulai']) ? date('d-m-Y', strtotime($data['tgl_mulai'])) : '-'; ?></td>
                <td width="5%" class="text-center">s/d</td>
                <td width="25%" class="text-center"><?php echo date('d-m-Y', strtotime($data['tgl_selesai'])); ?></td>
            </tr>
        </table>

        <table>
            <tr><td colspan="6" class="font-bold">V. CATATAN CUTI***</td></tr>
            <tr>
                <td colspan="3" width="40%">1. CUTI TAHUNAN</td><td width="15%" class="text-center">PARAF PETUGAS CUTI</td>
                <td width="35%">2. CUTI BESAR</td><td width="10%"></td> 
            </tr>
            <tr>
                <td width="10%" class="text-center">Tahun</td><td width="10%" class="text-center">Sisa</td><td width="20%" class="text-center">Keterangan</td>
                <td rowspan="4" class="valign-top" style="height: auto;"></td> 
                <td>3. CUTI SAKIT</td><td class="text-center small-text"><?php echo $ket_sakit; ?></td> 
            </tr>
            <tr>
                <td class="text-center"><?php echo $tahun_n2; ?></td><td class="text-center">-</td><td></td>
                <td>4. CUTI MELAHIRKAN</td><td class="text-center small-text"><?php echo $ket_lahir; ?></td> 
            </tr>
            <tr>
                <td class="text-center"><?php echo $tahun_n1; ?></td><td class="text-center"><?php echo $sisa_n1_tampil; ?></td>
                <td class="text-center small-text"><?php echo $ket_tahunan_n1; ?></td>
                <td>5. CUTI KARENA ALASAN PENTING</td><td class="text-center small-text"><?php echo $ket_penting; ?></td> 
            </tr>
            <tr>
                <td class="text-center"><?php echo $tahun_n; ?></td><td class="text-center"><?php echo $sisa_n_tampil; ?></td>
                <td class="text-center small-text"><?php echo $ket_tahunan_n; ?></td>
                <td>6. CUTI DI LUAR TANGGUNGAN NEGARA</td><td class="text-center small-text"><?php echo $ket_luar; ?></td> 
            </tr>
        </table>

        <table>
            <tr><td colspan="3" class="font-bold">VI. ALAMAT SELAMA MENJALANKAN CUTI</td></tr>
            <tr>
                <td rowspan="2" class="valign-top cell-alamat" style="width: auto;"><?php echo $data['alamat_cuti']; ?></td>
                <td class="bt-0 bb-0" style="width: 2cm;">Telp</td><td class="bt-0 bb-0" style="width: 4cm;"><?php echo $data['no_telepon']; ?></td>
            </tr>
            <tr>
                <td colspan="2" class="col-right-fixed" style="padding: 0;">
                    <div class="box-ttd-fixed">
                        <div style="text-align: center; margin-top: 5px;">Hormat saya,</div>
                        <div style="position:absolute; bottom:25px; left:0; width:100%; text-align:center; font-weight: bold;"><?php echo $data['nama_lengkap']; ?></div>
                        <div class="nip-bottom">NIP. <?php echo $data['nip']; ?></div>
                    </div>
                </td>
            </tr>
        </table>    

        <table>
            <tr><td colspan="4" class="font-bold">VII. PERTIMBANGAN ATASAN LANGSUNG**</td></tr>
            <tr><td class="text-center">DISETUJUI</td><td class="text-center">PERUBAHAN***</td><td class="text-center">DITANGGUHKAN***</td><td class="text-center col-right-fixed">TIDAK DISETUJUI****</td></tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr>
                <td colspan="3" class="no-border"></td>
                <td class="col-right-fixed" style="border: 1px solid #000; padding: 0;">
                    <div class="box-ttd-fixed">
                        <div style="position:absolute; bottom:25px; left:0; width:100%; text-align:center; font-weight: bold;"><?php echo $nama_atasan; ?></div>
                        <div class="nip-bottom">NIP. <?php echo $nip_atasan; ?></div>
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <tr><td colspan="4" class="font-bold">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</td></tr>
            <tr><td class="text-center">DISETUJUI</td><td class="text-center">PERUBAHAN***</td><td class="text-center">DITANGGUHKAN****</td><td class="text-center col-right-fixed">TIDAK DISETUJUI****</td></tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr>
                <td colspan="3" class="no-border"></td>
                <td class="col-right-fixed" style="border: 1px solid #000; padding: 0;">
                    <div class="box-ttd-fixed">
                        <div style="text-align: center; margin-top: 5px;"><?php echo $label_pejabat; ?></div>
                        <div style="position:absolute; bottom:25px; left:0; width:100%; text-align:center; font-weight: bold;"><?php echo $nama_pejabat; ?></div>
                        <div class="nip-bottom">NIP. <?php echo $nip_pejabat; ?></div>
                    </div>
                </td>
            </tr>
        </table>

        <div style="font-size: 8pt; margin-top: 2px;">
            Catatan :<br>
            * Coret yang tidak perlu<br>
            ** Pilih salah satu dengan memberi tanda centang (&#10003;)<br>
            *** Diisi oleh pejabat yang menangani bidang kepegawaian sebelum PNS mengajukan cuti<br>
            **** Diberi tanda centang dan alasannya
        </div>
    </div>
</body>
</html>