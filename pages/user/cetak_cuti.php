<?php
session_start();
error_reporting(0);

// --- 1. KONEKSI DATABASE (FALLBACK SYSTEM) ---
// Mencoba path user dulu, jika gagal coba path admin (biar fleksibel)
if (file_exists('../../config/database.php')) {
    include '../../config/database.php';
} else {
    include '../../assets/config/database.php';
}

// --- 2. KEAMANAN STRICT ---
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location='../../index.php';</script>";
    exit;
}

$id_pengajuan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_user_login = $_SESSION['id_user'];
$level_login   = isset($_SESSION['level']) ? $_SESSION['level'] : 'pegawai'; 

// --- 3. AMBIL DATA SETTING INSTANSI (KETUA) ---
// UPDATED: Mengambil dari database, bukan hardcode lagi!
$q_instansi = mysqli_query($koneksi, "SELECT * FROM tbl_setting_instansi WHERE id_setting='1'");
$instansi   = mysqli_fetch_array($q_instansi);
// Fallback jika database setting kosong
if(!$instansi) {
    $instansi = ['ketua_nama' => '..................', 'ketua_nip' => '..................'];
}

// --- 4. AMBIL DATA PENGAJUAN ---
$query = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti 
    JOIN users ON pengajuan_cuti.id_user = users.id_user 
    JOIN jenis_cuti ON pengajuan_cuti.id_jenis = jenis_cuti.id_jenis 
    WHERE id_pengajuan='$id_pengajuan'");

$data = mysqli_fetch_array($query);

// Validasi Kepemilikan Data
if (!$data) { echo "<script>alert('Data tidak ditemukan!'); window.close();</script>"; exit; }
if ($level_login != 'admin' && $data['id_user'] != $id_user_login) { echo "<script>alert('Akses Ditolak! Anda tidak berhak mencetak data orang lain.'); window.close();</script>"; exit; }

// ============================================================
// --- LOGIC ATASAN LANGSUNG ---
// ============================================================
$nama_atasan_langsung = "............................................."; 
$nip_atasan_langsung  = ".......................";
$id_atasan_terpilih   = isset($data['id_pejabat']) ? $data['id_pejabat'] : 0; // Tetap menggunakan id_pejabat sesuai file user asli

if ($id_atasan_terpilih > 0) {
    $cari_bos = mysqli_query($koneksi, "SELECT nama_lengkap, nip FROM users WHERE id_user = '$id_atasan_terpilih'");
    if ($bos = mysqli_fetch_array($cari_bos)) {
        $nama_atasan_langsung = $bos['nama_lengkap'];
        $nip_atasan_langsung  = $bos['nip'];
    }
}

// ============================================================
// --- LOGIC PERHITUNGAN CUTI (SUDAH BAGUS, DIPERTAHANKAN) ---
// ============================================================
$id_jenis   = $data['id_jenis']; 
$lama_ambil = $data['lama_hari'];
$sisa_n_tampil  = $data['sisa_cuti_n']; 
$sisa_n1_tampil = $data['sisa_cuti_n1']; 

$ket_tahunan_n = "-"; $ket_tahunan_n1 = "-";
$ket_besar = ""; $ket_sakit = ""; $ket_lahir = ""; $ket_penting = ""; $ket_luar = "";

switch ($id_jenis) {
    case '1': // Tahunan
        $sisa_akhir_n  = max(0, (int)$data['sisa_cuti_n']); 
        $sisa_akhir_n1 = max(0, (int)$data['sisa_cuti_n1']);
        
        $ambil_n  = (int) $data['dipotong_n'];
        $ambil_n1 = (int) $data['dipotong_n1'];

        $sisa_n_tampil  = $sisa_akhir_n + $ambil_n;
        $sisa_n1_tampil = $sisa_akhir_n1 + $ambil_n1;

        if ($ambil_n1 > 0 || $sisa_n1_tampil > 0) {
            if ($ambil_n1 > 0) {
                $ket_tahunan_n1 = "Diambil " . $ambil_n1 . " hari, Sisa " . $sisa_akhir_n1 . " hari";
            } else {
                $ket_tahunan_n1 = "-"; 
            }
        } else {
             $sisa_n1_tampil = 0; 
             $ket_tahunan_n1 = "-";
        }

        if ($ambil_n > 0) {
            $ket_tahunan_n = "Diambil " . $ambil_n . " hari, Sisa " . $sisa_akhir_n . " hari";
        } else {
            $ket_tahunan_n = "-"; 
        }
        break;

    case '2': // Sakit
        $sisa_sakit = max(0, (isset($data['kuota_cuti_sakit']) ? (int)$data['kuota_cuti_sakit'] : 0));
        $ket_sakit = "Diambil " . $lama_ambil . " hari, Sisa " . $sisa_sakit . " hari"; 
        break;

    case '4': $ket_besar = "Diambil " . $lama_ambil . " hari"; break;
    case '5': $ket_lahir = "Diambil " . $lama_ambil . " hari"; break;
    case '3': $ket_penting = "Diambil " . $lama_ambil . " hari"; break;
    case '6': $ket_luar = "Diambil " . $lama_ambil . " hari"; break;
}

$c1 = ($id_jenis == '1') ? '&#10003;' : '';
$c2 = ($id_jenis == '4') ? '&#10003;' : '';
$c3 = ($id_jenis == '2') ? '&#10003;' : '';
$c4 = ($id_jenis == '5') ? '&#10003;' : '';
$c5 = ($id_jenis == '3') ? '&#10003;' : '';
$c6 = ($id_jenis == '6') ? '&#10003;' : '';

$tahun_n  = date('Y'); $tahun_n1 = $tahun_n - 1; $tahun_n2 = $tahun_n - 2;

if (!function_exists('tgl_indo')) {
    function tgl_indo($tanggal){
        $bulan = array (1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
        $pecahkan = explode('-', $tanggal);
        return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Cuti F4 - <?php echo $data['nomor_surat']; ?></title>
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
        .nip-bottom { position: absolute; bottom: 5px; left: 5px; right: 5px; border-top: 2px solid #000; font-weight: bold; padding-top: 2px; text-align: left; }
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
                <td>Gol.Ruang</td><td><?php echo isset($data['pangkat']) ? $data['pangkat'] : ''; ?> / <?php echo isset($data['golongan']) ? $data['golongan'] : ''; ?></td>
            </tr>
            <tr>
                <td>Unit Kerja</td><td>Pengadilan Negeri Yogyakarta</td>
                <td>Masa Kerja</td><td><?php echo isset($data['masa_kerja']) ? $data['masa_kerja'] : '-'; ?></td>
            </tr>
        </table>

        <table>
            <tr><td colspan="4" class="font-bold">II. JENIS CUTI YANG DIAMBIL**</td></tr>
            <tr><td width="40%">1. Cuti Tahunan</td><td width="10%" class="check-col"><?php echo $c1; ?></td><td width="40%">4. Cuti Besar</td><td width="10%" class="check-col"><?php echo $c2; ?></td></tr>
            <tr><td>2. Cuti Sakit</td><td class="check-col"><?php echo $c3; ?></td><td>5. Cuti Melahirkan</td><td class="check-col"><?php echo $c4; ?></td></tr>
            <tr><td>3. Cuti Karena Alasan Penting</td><td class="check-col"><?php echo $c5; ?></td><td>6. Cuti di Luar Tanggungan Negara</td><td class="check-col"><?php echo $c6; ?></td></tr>
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
                <td width="20%" class="text-center"><?php echo date('d-m-Y', strtotime($data['tgl_mulai'])); ?></td>
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
                        <div style="position:absolute; bottom:30px; left:0; width:100%; text-align:center; font-weight: bold;"><?php echo $data['nama_lengkap']; ?></div>
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
                        <div style="position:absolute; bottom:30px; left:0; width:100%; text-align:center; font-weight: bold;"><?php echo $nama_atasan_langsung; ?></div>
                        <div class="nip-bottom">NIP. <?php echo $nip_atasan_langsung; ?></div>
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
                        <div style="text-align: center; margin-top: 5px;">Ketua,</div>
                        <div style="position:absolute; bottom:30px; left:0; width:100%; text-align:center; font-weight: bold;"><?php echo $instansi['ketua_nama']; ?></div>
                        <div class="nip-bottom">NIP. <?php echo $instansi['ketua_nip']; ?></div>
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