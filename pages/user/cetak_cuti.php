<?php
include '../../config/database.php';

// Ambil ID dari URL
$id_pengajuan = $_GET['id'];

// Ambil Data Lengkap (Pastikan kolom kuota_cuti_sakit terpanggil di SELECT *)
$query = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti 
    JOIN users ON pengajuan_cuti.id_user = users.id_user 
    JOIN jenis_cuti ON pengajuan_cuti.id_jenis = jenis_cuti.id_jenis 
    WHERE id_pengajuan='$id_pengajuan'");

$data = mysqli_fetch_array($query);

// ============================================================
// --- LOGIC PERBAIKAN: KETERANGAN DINAMIS ---
// ============================================================

$id_jenis   = $data['id_jenis']; 
$lama_ambil = $data['lama_hari'];

// Variabel default
$sisa_n_tampil = $data['sisa_cuti_n']; 

// Siapkan variabel kosong untuk setiap kolom keterangan di Tabel V
$ket_tahunan = "";
$ket_besar   = "";
$ket_sakit   = "";
$ket_lahir   = "";
$ket_penting = "";
$ket_luar    = "";

// LOGIKA PENEMPATAN KETERANGAN
switch ($id_jenis) {
    case '1': // Cuti Tahunan
        // Hitung mundur sisa
        $sisa_awal     = $data['sisa_cuti_n'] + $lama_ambil;
        $sisa_n_tampil = $sisa_awal; // Tampilkan sisa awal di kolom angka
        
        $ket_tahunan   = "Diambil " . $lama_ambil . " hari, sisa " . $data['sisa_cuti_n'] . " hari";
        break;

    case '2': // Cuti Sakit
        // Ambil kuota sakit dari database
        $sisa_sakit_db   = $data['kuota_cuti_sakit'];
        // Hitung sisa sakit sebelum diambil (Logic Matematika Balik)
        $sisa_sakit_awal = $sisa_sakit_db + $lama_ambil;
        
        // Isi keterangan di kolom Cuti Sakit
        $ket_sakit       = "Diambil " . $lama_ambil . " hari, sisa " . $sisa_sakit_db . " hari";
        break;

    case '4': // Cuti Besar
        // Karena biasanya Cuti Besar itu Hak (tanpa kuota di DB), cukup tampilkan diambilnya
        $ket_besar       = "Diambil " . $lama_ambil . " hari";
        break;

    case '5': // Cuti Melahirkan
        $ket_lahir       = "Diambil " . $lama_ambil . " hari"; // Atau 3 Bulan
        break;

    case '3': // Alasan Penting
        $ket_penting     = "Diambil " . $lama_ambil . " hari";
        break;

    case '6': // Luar Tanggungan Negara
        $ket_luar        = "Diambil " . $lama_ambil . " hari";
        break;
}

// ============================================================
// --- LOGIC CHECKLIST (CENTANG) ---
// ============================================================
$c1 = ($id_jenis == '1') ? '&#10003;' : ''; // Tahunan
$c2 = ($id_jenis == '4') ? '&#10003;' : ''; // Besar
$c3 = ($id_jenis == '2') ? '&#10003;' : ''; // Sakit
$c4 = ($id_jenis == '5') ? '&#10003;' : ''; // Melahirkan
$c5 = ($id_jenis == '3') ? '&#10003;' : ''; // Penting
$c6 = ($id_jenis == '6') ? '&#10003;' : ''; // Luar Tanggungan

// ============================================================
// --- LOGIC TAHUN ---
// ============================================================
$tahun_n  = date('Y');
$tahun_n1 = $tahun_n - 1;
$tahun_n2 = $tahun_n - 2;

function tgl_indo($tanggal){
    $bulan = array (
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Cuti F4 - <?php echo $data['nomor_surat']; ?></title>
    <style>
        /* --- SETUP KERTAS F4 (FOLIO) --- */
        @page {
            size: 215mm 330mm;
            margin: 0.5cm 1.5cm 1cm 1.5cm; 
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt; 
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1;
        }

        .container { width: 100%; }

        /* Header */
        .header-lampiran {
            text-align: right;
            font-size: 8pt;
            margin-bottom: 5px;
            margin-top: 5px;
        }

        .tgl-lokasi { text-align: right; margin-bottom: 5px; font-size: 10pt; }
        .tujuan-surat { margin-bottom: 5px; font-size: 10pt; }

        .judul-utama {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin-top: 5px;
            margin-bottom: 2px;
        }
        
        .nomor-surat {
            text-align: center;
            font-size: 11pt;
            font-weight: bold; 
            margin-bottom: 5px;
        }

        /* --- TABEL GLOBAL --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }

        /* --- ATURAN SEL (CELL) --- */
        th, td {
            border: 1px solid #000;
            padding: 0 4px;
            vertical-align: middle;
            height: 0.5cm; /* Default Tinggi */
            font-size: 9pt; 
        }

        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .valign-top { vertical-align: top; }
        
        /* Helper Border */
        .bt-0 { border-top: none !important; }
        .bl-0 { border-left: none !important; }
        .br-0 { border-right: none !important; }
        .bb-0 { border-bottom: none !important; }
        .no-border { border: none !important; }

        .check-col { text-align: center; font-size: 11pt; font-weight: bold; }

        /* --- CSS KHUSUS --- */
        .cell-alasan { height: 25px !important; padding: 5px !important; vertical-align: top; }
        .cell-alamat { height: auto !important; padding: 5px !important; vertical-align: top; }
        .col-right-fixed { width: 6cm !important; min-width: 6cm !important; max-width: 6cm !important; }
        .box-ttd-fixed { height: 3cm; width: 100%; position: relative; box-sizing: border-box; padding: 5px; }
        .nip-bottom {
            position: absolute; bottom: 5px; left: 5px; right: 5px;
            border-bottom: none; border-top: 2px solid #000;
            font-weight: bold; padding-top: 2px;
        }
        
        /* Utility font kecil untuk keterangan */
        .small-text { font-size: 8pt; }

        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

    <div class="no-print" style="position:fixed; top:10px; right:10px; z-index:9999;">
        <button onclick="window.print()" style="padding:5px 15px; font-weight:bold; cursor:pointer;">CETAK</button>
        <button onclick="window.history.back()" style="padding:5px 15px; cursor:pointer;">KEMBALI</button>
    </div>

    <div class="container">
        
        <div class="header-lampiran">
            LAMPIRAN II : SURAT EDARAN SEKRETARIS MAHKAMAH AGUNG <br>
            REPUBLIK INDONESIA <br>
            NOMOR 13 TAHUN 2019
        </div>

        <div class="tgl-lokasi">
            Yogyakarta, <?php echo tgl_indo($data['tgl_pengajuan']); ?>
        </div>

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
                <td style="width: 3cm;">Nama</td>
                <td style="width: 6.5cm;"><?php echo $data['nama_lengkap']; ?></td>
                <td style="width: 3cm;">NIP</td>
                <td style="width: 6.5cm;"><?php echo $data['nip']; ?></td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td><?php echo $data['jabatan']; ?></td>
                <td>Gol.Ruang</td>
                <td>-</td>
            </tr>
            <tr>
                <td>Unit Kerja</td>
                <td>Pengadilan Negeri Yogyakarta</td>
                <td>Masa Kerja</td>
                <td>-</td>
            </tr>
        </table>

        <table>
            <tr><td colspan="4" class="font-bold">II. JENIS CUTI YANG DIAMBIL**</td></tr>
            <tr>
                <td width="40%">1. Cuti Tahunan</td>
                <td width="10%" class="check-col"><?php echo $c1; ?></td>
                <td width="40%">4. Cuti Besar</td>
                <td width="10%" class="check-col"><?php echo $c2; ?></td>
            </tr>
            <tr>
                <td>2. Cuti Sakit</td>
                <td class="check-col"><?php echo $c3; ?></td>
                <td>5. Cuti Melahirkan</td>
                <td class="check-col"><?php echo $c4; ?></td>
            </tr>
            <tr>
                <td>3. Cuti Karena Alasan Penting</td>
                <td class="check-col"><?php echo $c5; ?></td>
                <td>6. Cuti di Luar Tanggungan Negara</td>
                <td class="check-col"><?php echo $c6; ?></td>
            </tr>
        </table>

        <table>
            <tr><td class="font-bold">III. ALASAN CUTI</td></tr>
            <tr><td class="cell-alasan"><?php echo $data['alasan']; ?></td></tr>
        </table>

        <table>
            <tr><td colspan="6" class="font-bold">IV. LAMANYA CUTI</td></tr>
            <tr>
                <td width="15%">Selama</td>
                <td width="20%"><?php echo $data['lama_hari']; ?> (Hari/Bulan/Tahun)*</td>
                <td width="15%">mulai tanggal</td>
                <td width="20%" class="text-center"><?php echo date('d-m-Y', strtotime($data['tgl_mulai'])); ?></td>
                <td width="5%" class="text-center">s/d</td>
                <td width="25%" class="text-center"><?php echo date('d-m-Y', strtotime($data['tgl_selesai'])); ?></td>
            </tr>
        </table>

        <table>
            <tr><td colspan="6" class="font-bold">V. CATATAN CUTI***</td></tr>
            <tr>
                <td colspan="3" width="40%">1. CUTI TAHUNAN</td>
                <td width="15%" class="text-center">PARAF PETUGAS CUTI</td>
                <td width="35%">2. CUTI BESAR</td>
                <td width="10%"></td> 
            </tr>
            <tr>
                <td width="10%" class="text-center">Tahun</td>
                <td width="10%" class="text-center">Sisa</td>
                <td width="20%" class="text-center">Keterangan</td>
                <td rowspan="4" class="valign-top" style="height: auto;"></td> 
                
                <td>3. CUTI SAKIT</td>
                <td class="text-center small-text"><?php echo $ket_sakit; ?></td> 
            </tr>
            <tr>
                <td class="text-center"><?php echo $tahun_n2; ?></td>
                <td class="text-center">-</td>
                <td></td>
                
                <td>4. CUTI MELAHIRKAN</td>
                <td class="text-center small-text"><?php echo $ket_lahir; ?></td> 
            </tr>
            <tr>
                <td class="text-center"><?php echo $tahun_n1; ?></td>
                <td class="text-center"><?php echo $data['sisa_cuti_n1']; ?></td>
                <td></td>
                
                <td>5. CUTI KARENA ALASAN PENTING</td>
                <td class="text-center small-text"><?php echo $ket_penting; ?></td> 
            </tr>
            <tr>
                <td class="text-center"><?php echo $tahun_n; ?></td>
                <td class="text-center"><?php echo $sisa_n_tampil; ?></td>
                <td class="text-center small-text"><?php echo $ket_tahunan; ?></td>
                
                <td>6. CUTI DI LUAR TANGGUNGAN NEGARA</td>
                <td class="text-center small-text"><?php echo $ket_luar; ?></td> 
            </tr>
        </table>

        <table>
            <tr><td colspan="3" class="font-bold">VI. ALAMAT SELAMA MENJALANKAN CUTI</td></tr>
            <tr>
                <td rowspan="2" class="valign-top cell-alamat" style="width: auto;">
                    <?php echo $data['alamat_cuti']; ?>
                </td>
                
                <td class="bt-0 bb-0" style="width: 2cm;">Telp</td>
                
                <td class="bt-0 bb-0" style="width: 4cm;"><?php echo $data['no_telepon']; ?></td>
            </tr>
            <tr>
                <td colspan="2" class="col-right-fixed" style="padding: 0;">
                    <div class="box-ttd-fixed">
                        <div style="text-align: center; margin-top: 5px;">Hormat saya,</div>
                        
                        <div class="nip-bottom">
                            NIP. <?php echo $data['nip']; ?>
                        </div>
                        <div style="position:absolute; bottom:25px; left:0; width:100%; text-align:center;">
                            <?php echo $data['nama_lengkap']; ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <tr><td colspan="4" class="font-bold">VII. PERTIMBANGAN ATASAN LANGSUNG**</td></tr>
            <tr>
                <td class="text-center">DISETUJUI</td>
                <td class="text-center">PERUBAHAN***</td>
                <td class="text-center">DITANGGUHKAN***</td>
                <td class="text-center col-right-fixed">TIDAK DISETUJUI****</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            
            <tr>
                <td colspan="3" class="no-border"></td>
                
                <td class="col-right-fixed" style="border: 1px solid #000; padding: 0;">
                    <div class="box-ttd-fixed">
                        <div class="nip-bottom">NIP.</div>
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <tr><td colspan="4" class="font-bold">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</td></tr>
            <tr>
                <td class="text-center">DISETUJUI</td>
                <td class="text-center">PERUBAHAN***</td>
                <td class="text-center">DITANGGUHKAN****</td>
                <td class="text-center col-right-fixed">TIDAK DISETUJUI****</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            
            <tr>
                <td colspan="3" class="no-border"></td>
                
                <td class="col-right-fixed" style="border: 1px solid #000; padding: 0;">
                    <div class="box-ttd-fixed">
                        <div class="nip-bottom">NIP.</div>
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