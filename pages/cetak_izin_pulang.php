<?php
include '../config/database.php';
/** @var mysqli $koneksi */

// 1. AMBIL ID DARI URL
if (isset($_GET['id'])) {
    $id_izin = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // REVISI QUERY: Menambahkan pengambilan kolom 'pangkat'
    $query = "SELECT 
                i.*,
                u.nama_lengkap AS nama_pemohon,
                u.nip AS nip_pemohon,
                u.jabatan AS jabatan_pemohon,
                u.pangkat AS pangkat_pemohon,     -- Pastikan kolom ini ada di tabel users
                
                a.nama_lengkap AS nama_atasan,
                a.nip AS nip_atasan,
                a.jabatan AS jabatan_atasan,
                a.pangkat AS pangkat_atasan       -- Pastikan kolom ini ada di tabel users
              FROM izin_pulang i
              LEFT JOIN users u ON i.id_user = u.id_user
              LEFT JOIN users a ON i.id_atasan = a.id_user
              WHERE i.id_izin_pulang = '$id_izin'";

    $result = mysqli_query($koneksi, $query);
    
    // Cek error query jika tabel pangkat tidak ada
    if (!$result) {
        die("Error Query: " . mysqli_error($koneksi) . "<br>Solusi: Cek apakah kolom 'pangkat' ada di tabel 'users'.");
    }

    $data = mysqli_fetch_assoc($result);

    if (!$data) { die("Data izin tidak ditemukan."); }
} else {
    die("ID tidak valid.");
}

// Helper Tanggal
function tgl_indo($tanggal){
    $bulan = array (
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecahkan = explode('-', $tanggal);
    if(count($pecahkan) == 3) {
        return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
    }
    return $tanggal;
}

$hari_inggris = date('l', strtotime($data['tgl_izin']));
$hari_indo_map = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];
$hari_ini = $hari_indo_map[$hari_inggris];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Izin Pulang - <?= $data['nama_pemohon']; ?></title>
    <style>
        /* SETUP KERTAS F4 */
        @page {
            size: F4; 
            margin: 2cm 2cm;
        }
        
        /* 1. REVISI FONT JADI ARIAL */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background: #fff;
        }

        /* TOMBOL PRINT */
        .no-print { display: none; }
        .btn-print {
            position: fixed; top: 10px; right: 10px;
            background: #004d00; color: white; padding: 10px 15px;
            border: none; border-radius: 5px; cursor: pointer;
            font-weight: bold; z-index: 999;
        }

        /* --- STYLING HALAMAN --- */
        .page {
            width: 100%;
            display: block;
            position: relative;
            min-height: 25cm; 
        }

        .page-break {
            page-break-before: always;
            display: block;
            height: 1px;
        }

        /* TABLE DATA */
        .table-data { width: 100%; margin-top: 10px; margin-bottom: 10px; }
        .table-data td { vertical-align: top; padding-bottom: 5px; }
        .label { width: 180px; }
        .sep { width: 20px; text-align: center; }

        /* REVISI TANDA TANGAN (CENTER, NOWRAP) */
        .ttd-wrapper { 
            margin-top: 40px; 
            width: 100%; 
            display: flex; 
            justify-content: flex-end; /* Posisi di Kanan Halaman */
        }
        
        .ttd-box { 
            /* Box tanda tangan */
            text-align: center; /* Teks di dalam box rata tengah */
            padding-right: 0;
            min-width: 200px;
        }
        
        .ttd-space { height: 70px; }
        
        .nama-terang { 
            font-weight: bold; 
            text-decoration: underline; 
            /* REVISI: Agar nama panjang tetap 1 baris */
            white-space: nowrap; 
            display: inline-block;
        }
        
        .nip-text {
            display: block;
            margin-top: 2px;
        }

        @media print {
            .btn-print { display: none; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body onload="window.print()">

    <button class="btn-print" onclick="window.print()">🖨️ Cetak Dokumen</button>

    <div class="page">
        <div style="text-align: right; margin-bottom: 20px;">
            Yogyakarta, <?= tgl_indo(date('Y-m-d')); ?>
        </div>

        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td width="80px">Perihal</td>
                <td width="20px">:</td>
                <td>Permohonan Izin Pulang Awal</td>
            </tr>
        </table>

        <div style="margin-bottom: 30px;">
            Kepada :<br>
            Yth. Ketua Pengadilan Negeri Yogyakarta Kelas IA<br>
            Di -<br>
            <span style="padding-left: 30px;">Yogyakarta</span>
        </div>

        <p>Dengan hormat,</p>
        <p>Yang bertanda tangan dibawah ini saya :</p>

        <table class="table-data" style="margin-left: 20px;">
            <tr>
                <td class="label">Nama</td>
                <td class="sep">:</td>
                <td><?= $data['nama_pemohon']; ?></td>
            </tr>
            <tr>
                <td class="label">NIP</td>
                <td class="sep">:</td>
                <td><?= $data['nip_pemohon'] ? $data['nip_pemohon'] : '-'; ?></td>
            </tr>
            <tr>
                <td class="label">Pangkat/Gol.Ruang</td>
                <td class="sep">:</td>
                <td><?= isset($data['pangkat_pemohon']) ? $data['pangkat_pemohon'] : '-'; ?></td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td class="sep">:</td>
                <td><?= $data['jabatan_pemohon']; ?></td>
            </tr>
            <tr>
                <td class="label">Unit Kerja</td>
                <td class="sep">:</td>
                <td>Pengadilan Negeri Yogyakarta Kelas IA</td>
            </tr>
        </table>

        <p>Mengajukan permohonan izin Pulang Awal pada :</p>

        <table class="table-data" style="margin-left: 20px;">
            <tr>
                <td class="label">Hari / Tanggal</td>
                <td class="sep">:</td>
                <td><?= $hari_ini . ' / ' . tgl_indo($data['tgl_izin']); ?></td>
            </tr>
            <tr>
                <td class="label">Jam</td>
                <td class="sep">:</td>
                <td><?= date('H:i', strtotime($data['jam_pulang'])); ?> WIB</td>
            </tr>
            <tr>
                <td class="label">Keperluan</td>
                <td class="sep">:</td>
                <td><?= $data['keperluan']; ?></td>
            </tr>
        </table>

        <p>Demikian permohonan izin ini disampaikan, atas perkenanannya diucapkan terima kasih.</p>

        <div class="ttd-wrapper">
            <div class="ttd-box">
                <p>Hormat saya,</p>
                <div class="ttd-space"></div>
                <span class="nama-terang"><?= $data['nama_pemohon']; ?></span>
                <span class="nip-text">NIP. <?= $data['nip_pemohon'] ? $data['nip_pemohon'] : '.........................'; ?></span>
            </div>
        </div>
    </div>
    
    <div class="page-break"></div>

    <div class="page">
        <div style="text-align: center; margin-bottom: 40px; margin-top: 20px;">
            <span style="font-size: 14pt;">IZIN PULANG AWAL</span>
        </div>

        <table class="table-data">
            <tr>
                <td class="label">Yang bertanda tangan di bawah ini</td>
                <td class="sep">:</td>
                <td>
                    <?= $data['nama_atasan'] ? $data['nama_atasan'] : '.........................................................................'; ?>
                </td>
            </tr>
            <tr>
                <td class="label">Selaku</td>
                <td class="sep">:</td>
                <td>
                    <?= $data['jabatan_atasan'] ? $data['jabatan_atasan'] : '.........................................................................'; ?>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="padding-top: 15px; padding-bottom: 15px;">
                    Dengan ini memberikan izin kepada :
                </td>
            </tr>
            
            <tr>
                <td class="label">Nama</td>
                <td class="sep">:</td>
                <td><?= $data['nama_pemohon']; ?></td>
            </tr>
            <tr>
                <td class="label">NIP</td>
                <td class="sep">:</td>
                <td><?= $data['nip_pemohon'] ? $data['nip_pemohon'] : '-'; ?></td>
            </tr>
            <tr>
                <td class="label">Pangkat/Gol.Ruang</td>
                <td class="sep">:</td>
                <td><?= isset($data['pangkat_pemohon']) ? $data['pangkat_pemohon'] : '-'; ?></td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td class="sep">:</td>
                <td><?= $data['jabatan_pemohon']; ?></td>
            </tr>
            <tr>
                <td class="label">Unit Kerja</td>
                <td class="sep">:</td>
                <td>Pengadilan Negeri Yogyakarta Kelas IA</td>
            </tr>
        </table>

        <div style="margin-top: 10px;">
            <table class="table-data">
                <tr>
                    <td class="label" style="width: 180px;">Untuk pulang kantor lebih awal pada</td>
                    <td class="sep">:</td>
                    <td style="width: 50px;">Hari:</td>
                    <td><?= $hari_ini; ?></td>
                    <td style="width: 60px;">Tanggal:</td>
                    <td><?= tgl_indo($data['tgl_izin']); ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td>Pukul:</td>
                    <td colspan="3"><?= date('H:i', strtotime($data['jam_pulang'])); ?> WIB</td>
                </tr>
            </table>
        </div>

        <table class="table-data">
            <tr>
                <td class="label" style="width: 180px;">Untuk Keperluan</td>
                <td class="sep">:</td>
                <td><?= $data['keperluan']; ?></td>
            </tr>
        </table>

        <p style="margin-top: 20px;">Demikian izin ini diberikan kepada yang bersangkutan untuk digunakan sebagaimana mestinya.</p>

        <div class="ttd-wrapper" style="margin-top: 50px;">
            <div class="ttd-box">
                <p>Yogyakarta, <?= tgl_indo(date('Y-m-d')); ?></p>
                <p>Atasan Langsung</p>
                
                <div class="ttd-space">
                    </div>
                
                <span class="nama-terang">
                    <?= $data['nama_atasan'] ? $data['nama_atasan'] : '......................................'; ?>
                </span>
                <span class="nip-text">
                    <?= $data['nip_atasan'] ? 'NIP. '.$data['nip_atasan'] : 'NIP. .....................................'; ?>
                </span>
            </div>
        </div>
    </div>
</body>
</html>