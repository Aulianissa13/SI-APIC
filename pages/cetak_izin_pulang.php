<?php
include '../config/database.php'; 
/** @var mysqli $koneksi */

// 1. AMBIL DATA DARI URL
if (isset($_GET['id'])) {
    $id_izin = mysqli_real_escape_string($koneksi, $_GET['id']);

    // 2. QUERY DATA LENGKAP
    // Kita join ke tabel users untuk ambil NIP, Jabatan, dan Divisi
    $query = "SELECT 
                i.*,
                u.nama_lengkap AS nama_pemohon,
                u.nip AS nip_pemohon,           -- Asumsi ada kolom nip
                u.jabatan AS jabatan_pemohon,   -- Asumsi ada kolom jabatan
                u.divisi AS divisi_pemohon,     -- Asumsi ada kolom divisi
                atasan.nama_lengkap AS nama_atasan,
                atasan.nip AS nip_atasan
              FROM izin_pulang i
              LEFT JOIN users u ON i.id_user = u.id_user
              LEFT JOIN users atasan ON i.id_atasan = atasan.id_user
              WHERE i.id_izin_pulang = '$id_izin'";

    $result = mysqli_query($koneksi, $query);
    $data   = mysqli_fetch_assoc($result);

    if (!$data) {
        die("Data izin tidak ditemukan.");
    }
} else {
    die("ID tidak valid.");
}

// 3. HELPER TANGGAL INDONESIA
function tgl_indo($tanggal){
    $bulan = array (
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

// Format Hari
$hari = date('l', strtotime($data['tgl_izin']));
$daftar_hari = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];
$hari_indo = $daftar_hari[$hari];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Surat Izin Pulang - <?= $data['nama_pemohon']; ?></title>
    <style>
        /* SETTING HALAMAN F4 / LEGAL */
        @page {
            size: 215mm 330mm; /* Ukuran F4 */
            margin: 10mm 15mm; /* Margin Kiri-Kanan agak besar, Atas-Bawah kecil */
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        /* CONTAINER UTAMA (Agar muat 2 rangkap) */
        .sheet {
            width: 100%;
            height: 100%;
        }

        /* BOX UNTUK SETIAP SURAT (ATAS & BAWAH) */
        .surat-container {
            height: 46%; /* 46% + 46% + margin = pas 1 halaman */
            padding: 10px;
            position: relative;
            box-sizing: border-box;
            /* border: 1px solid #000; Debugging border, hapus nanti */ 
        }

        /* HEADER SURAT */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header p {
            margin: 2px 0;
            font-size: 10pt;
        }

        /* JUDUL SURAT */
        .judul-surat {
            text-align: center;
            margin-bottom: 20px;
        }
        .judul-surat u {
            font-weight: bold;
            font-size: 14pt;
        }
        .nomor-surat {
            display: block;
            font-size: 11pt;
            margin-top: 2px;
        }

        /* ISI SURAT (TABEL) */
        .tabel-isi {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .tabel-isi td {
            vertical-align: top;
            padding: 4px 0;
            font-size: 12pt;
        }
        .label { width: 30%; }
        .titik { width: 3%; text-align: center; }
        .isi { width: 67%; }

        /* TANDA TANGAN */
        .ttd-container {
            width: 100%;
            margin-top: 30px;
            display: table;
        }
        .ttd-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            vertical-align: top;
        }
        .ttd-box p { margin: 0; }
        .ttd-space { height: 60px; } /* Ruang tanda tangan */
        .nama-terang {
            font-weight: bold;
            text-decoration: underline;
        }

        /* GARIS POTONG (CUT LINE) */
        .cut-line {
            width: 100%;
            border-top: 2px dashed #999;
            margin: 25px 0;
            position: relative;
        }
        .cut-line::after {
            content: "✂ Potong di sini";
            position: absolute;
            left: 50%;
            top: -12px;
            transform: translateX(-50%);
            background: #fff;
            padding: 0 10px;
            font-size: 10pt;
            color: #666;
            font-style: italic;
        }

        /* TOMBOL PRINT (Hanya tampil di layar) */
        @media print {
            .no-print { display: none; }
        }
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #004d00;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-family: sans-serif;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            cursor: pointer;
            z-index: 9999;
        }
    </style>
</head>
<body onload="window.print()">

    <a href="javascript:window.print()" class="no-print">🖨️ Cetak Surat</a>

    <div class="sheet">
        
        <?php 
        // KITA BUAT FUNCTION UNTUK TEMPLATE AGAR TIDAK COPY PASTE KODE HTML 2 KALI
        function renderSurat($data, $hari_indo, $judul_copy) {
        ?>
        
        <div class="surat-container">
            <div class="header">
                <h2>PT. NAMA PERUSAHAAN ANDA</h2>
                <p>Jalan Alamat Perusahaan No. 123, Kota Anda, Provinsi Anda</p>
                <p>Telp: (021) 1234567 | Email: info@perusahaan.com</p>
            </div>

            <div class="judul-surat">
                <u>SURAT IZIN PULANG AWAL</u><br>
                <span class="nomor-surat">Nomor: ... / HRD / <?= date('m/Y'); ?></span>
            </div>

            <p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>

            <table class="tabel-isi">
                <tr>
                    <td class="label">Nama</td>
                    <td class="titik">:</td>
                    <td class="isi"><b><?= $data['nama_pemohon']; ?></b></td>
                </tr>
                <tr>
                    <td class="label">NIP / ID</td>
                    <td class="titik">:</td>
                    <td class="isi"><?= $data['nip_pemohon'] ?? '-'; ?></td>
                </tr>
                <tr>
                    <td class="label">Jabatan / Divisi</td>
                    <td class="titik">:</td>
                    <td class="isi"><?= ($data['jabatan_pemohon'] ?? '-') . ' / ' . ($data['divisi_pemohon'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <td class="label">Hari, Tanggal</td>
                    <td class="titik">:</td>
                    <td class="isi"><?= $hari_indo . ', ' . tgl_indo($data['tgl_izin']); ?></td>
                </tr>
                <tr>
                    <td class="label">Jam Pulang</td>
                    <td class="titik">:</td>
                    <td class="isi">Pukul <b><?= date('H:i', strtotime($data['jam_pulang'])); ?> WIB</b></td>
                </tr>
                <tr>
                    <td class="label">Keperluan / Alasan</td>
                    <td class="titik">:</td>
                    <td class="isi"><?= $data['keperluan']; ?></td>
                </tr>
            </table>

            <p>Demikian surat izin ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

            <div class="ttd-container">
                <div class="ttd-box">
                    <p>Mengetahui,</p>
                    <p>Atasan Langsung</p>
                    <div class="ttd-space">
                        <?php if($data['status'] == 1): ?>
                             <br><small style="color:green; border:1px solid green; padding:2px;"><i>Disetujui Digital</i></small>
                        <?php endif; ?>
                    </div>
                    <p class="nama-terang"><?= $data['nama_atasan'] ?? '......................'; ?></p>
                    <p>NIP. <?= $data['nip_atasan'] ?? '..........'; ?></p>
                </div>

                <div class="ttd-box">
                    <p>Mengetahui,</p>
                    <p>HRD / Security</p>
                    <div class="ttd-space"></div>
                    <p class="nama-terang">( ............................. )</p>
                </div>

                <div class="ttd-box">
                    <p><?= $data['divisi_pemohon'] ?? 'Kota'; ?>, <?= tgl_indo($data['tgl_izin']); ?></p>
                    <p>Pemohon</p>
                    <div class="ttd-space"></div>
                    <p class="nama-terang"><?= $data['nama_pemohon']; ?></p>
                    <p>NIP. <?= $data['nip_pemohon'] ?? '..........'; ?></p>
                </div>
            </div>
            
            <div style="position: absolute; bottom: 5px; right: 10px; font-size: 9pt; color: #888;">
                <i>*Lembar untuk: <?= $judul_copy; ?> (Dicetak dari Sistem SI-APIC)</i>
            </div>
        </div>

        <?php } // End Function ?>

        <?= renderSurat($data, $hari_indo, "Arsip / HRD"); ?>

        <div class="cut-line"></div>

        <?= renderSurat($data, $hari_indo, "Pegawai / Security"); ?>

    </div>

</body>
</html>