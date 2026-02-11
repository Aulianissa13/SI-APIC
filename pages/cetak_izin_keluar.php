<?php
session_start();
include '../config/database.php'; 
/** @var mysqli $koneksi */

// Cek ID
if (!isset($_GET['id'])) { die("ID Surat tidak ditemukan."); }
$id_izin = $_GET['id'];

// Query Data
$query = "SELECT i.*, 
          u.nama_lengkap AS nama_pemohon, u.nip AS nip_pemohon, u.jabatan AS jab_pemohon, u.pangkat AS pangkat_pemohon,
          a.nama_lengkap AS nama_atasan, a.nip AS nip_atasan, a.jabatan AS jab_atasan
          FROM izin_keluar i
          JOIN users u ON i.id_user = u.id_user
          LEFT JOIN users a ON i.id_atasan = a.id_user
          WHERE i.id_izin = '$id_izin'";

$result = mysqli_query($koneksi, $query);
$data   = mysqli_fetch_array($result);

if (!$data) { die("Data izin tidak ditemukan."); }

// Helper Tanggal Indo
function tgl_indo($tanggal){
    $bulan = array (
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

function hari_indo($tanggal){
    $hari = array ( 
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    );
    return $hari[date('l', strtotime($tanggal))];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Cetak Izin Keluar</title>
    <style>
        /* ================= SETTING KERTAS F4 ================= */
        @page {
            size: 215mm 330mm; 
            margin: 0; 
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #ccc; 
        }
        
        /* Container Halaman Umum */
        .page {
            width: 215mm;
            height: 330mm;
            /* Margin Kertas: Tetap kecil sesuai request sebelumnya */
            padding: 15mm 20mm; 
            margin: 10mm auto;
            background: white;
            box-sizing: border-box;
            position: relative; 
        }

        /* ================= KHUSUS HALAMAN 1 (SURAT) ================= */
        .page-surat {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.6; /* Line height sedikit diperlega */
            font-weight: normal; 
        }

        /* ================= KHUSUS HALAMAN 2 (FORM/LAMPIRAN) ================= */
        .page-form {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            font-weight: bold; 
        }

        /* PENGECUALIAN: Isian Data di Halaman 2 TIDAK TEBAL */
        .page-form .data-isian {
            font-weight: normal;
        }

        /* Print Settings */
        @media print {
            body { background: white; margin: 0; }
            .page { 
                margin: 0; 
                width: 215mm; 
                height: 330mm; 
                box-shadow: none;
                page-break-after: always;
            }
            .page:last-child { page-break-after: auto; }
            .no-print { display: none; }
        }

        /* Utilitas Teks */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        
        /* ================= POSISI LAMPIRAN (POJOK KANAN ATAS) ================= */
        .header-lampiran {
            position: absolute;
            top: 15mm;         /* Sesuai padding atas */
            right: 20mm;       /* Sesuai padding kanan */
            width: auto;
            text-align: left;
            font-size: 10pt;
            z-index: 10;
            line-height: 1.4;
        }
        
        /* Tabel Form - SPACING DIPERLEBAR DISINI */
        .table-form { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; }
        .table-form td { 
            vertical-align: top; 
            padding: 6px 0; /* Padding diperbesar agar baris lebih renggang */
        }
        
        /* Lebar Kolom Label */
        .page-surat .col-label { width: 170px; } 
        .page-form .col-label { width: 230px; } 

        .col-separator { width: 20px; text-align: center; }
        
        /* Area Tanda Tangan */
        .ttd-area {
            float: right;
            width: 250px;
            text-align: center;
            margin-top: 50px; /* Jarak TTD diperjauh */
        }
        .ttd-space { height: 75px; } /* Ruang tanda tangan diperlega */
        
        /* Utilitas Jarak Antar Elemen (Diperlebar) */
        .mb-small { margin-bottom: 20px; }
        .mb-medium { margin-bottom: 35px; }
        .mt-big { margin-top: 140px; } /* Untuk judul halaman 2 */
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
        <button onclick="window.print()" style="padding: 10px 20px; font-weight:bold; cursor: pointer; background: #004d00; color: white; border: none; border-radius: 5px;">
            🖨️ CETAK (F4)
        </button>
    </div>

    <div class="page page-surat">
        <div class="text-right mb-medium">
            Yogyakarta, <?php echo tgl_indo(date('Y-m-d')); ?>
        </div>

        <div class="mb-medium">
            Perihal : Permohonan Ijin Keluar Kantor
        </div>

        <div class="mb-medium">
            Kepada : <br>
            Yth. <?php echo $data['nama_atasan']; ?> <br>
            Di - <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>Yogyakarta</b>
        </div>

        <div class="mb-small">
            Dengan hormat,<br>
            Yang bertanda tangan dibawah ini saya :
        </div>

        <table class="table-form" style="margin-left: 30px;">
            <tr>
                <td class="col-label">Nama</td>
                <td class="col-separator">:</td>
                <td><?php echo $data['nama_pemohon']; ?></td>
            </tr>
            <tr>
                <td class="col-label">NIP</td>
                <td class="col-separator">:</td>
                <td><?php echo $data['nip_pemohon']; ?></td>
            </tr>
            <tr>
                <td class="col-label">Pangkat/Gol.Ruang</td>
                <td class="col-separator">:</td>
                <td><?php echo $data['pangkat_pemohon'] ? $data['pangkat_pemohon'] : '-'; ?></td>
            </tr>
            <tr>
                <td class="col-label">Jabatan</td>
                <td class="col-separator">:</td>
                <td><?php echo $data['jab_pemohon']; ?></td>
            </tr>
            <tr>
                <td class="col-label">Unit Kerja</td>
                <td class="col-separator">:</td>
                <td>Pengadilan Negeri Yogyakarta Kelas IA</td>
            </tr>
        </table>

        <div style="margin: 20px 0;">
            Mengajukan permohonan ijin Keluar Kantor pada :
        </div>

        <table class="table-form" style="margin-left: 30px;">
            <tr>
                <td class="col-label">Hari / Tanggal</td>
                <td class="col-separator">:</td>
                <td>
                    <?php echo hari_indo($data['tgl_izin']) . ' / ' . tgl_indo($data['tgl_izin']); ?>
                </td>
            </tr>
            <tr>
                <td class="col-label">Jam</td>
                <td class="col-separator">:</td>
                <td>
                    <?php echo date('H:i', strtotime($data['jam_keluar'])); ?> WIB 
                    s.d. 
                    <?php echo date('H:i', strtotime($data['jam_kembali'])); ?> WIB
                </td>
            </tr>
            <tr>
                <td class="col-label">Keperluan</td>
                <td class="col-separator">:</td>
                <td><?php echo $data['keperluan']; ?></td>
            </tr>
        </table>

        <div style="margin-top: 30px;" class="text-justify">
            Demikian permohonan ijin ini disampaikan, atas perkenanannya diucapkan terima kasih.
        </div>

        <div class="ttd-area">
            Hormat saya,
            <div class="ttd-space"></div>
            <span class="bold underline"><?php echo $data['nama_pemohon']; ?></span><br>
            NIP. <?php echo $data['nip_pemohon']; ?>
        </div>
    </div>

    <div class="page page-form">
        
        <div class="header-lampiran">
            Lampiran II : Formulir Izin Keluar Kantor<br>
            PERMA No. 07 Tahun 2016<br>
            Tentang Penegakan Disiplin Kerja Hakim<br>
            Pada Mahkamah Agung RI dan Badan<br>
            Peradilan yang berada dibawahnya.
        </div>

        <div class="text-center mt-big mb-medium" style="font-size: 13pt;">
            IZIN KELUAR KANTOR
        </div>

        <table class="table-form">
            <tr>
                <td class="col-label">Yang bertanda tangan di bawah ini</td>
                <td class="col-separator">:</td>
                <td><span class="data-isian"><?php echo $data['nama_atasan']; ?></span></td>
            </tr>
            <tr>
                <td class="col-label">Selaku</td>
                <td class="col-separator">:</td>
                <td><span class="data-isian"><?php echo $data['jab_atasan'] ? $data['jab_atasan'] : 'Atasan Langsung'; ?></span></td>
            </tr>

            <tr><td colspan="3" style="padding: 15px 0;">Dengan ini memberikan izin kepada :</td></tr>

            <tr>
                <td class="col-label">Nama</td>
                <td class="col-separator">:</td>
                <td><span class="data-isian"><?php echo $data['nama_pemohon']; ?></span></td>
            </tr>

            <tr>
                <td class="col-label">Untuk Keluar Kantor pada</td>
                <td class="col-separator">:</td>
                <td>
                    Hari: <span class="data-isian"><?php echo hari_indo($data['tgl_izin']); ?></span> 
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    Tanggal: <span class="data-isian"><?php echo date('d-m-Y', strtotime($data['tgl_izin'])); ?></span>
                </td>
            </tr>
            <tr>
                <td class="col-label"></td>
                <td class="col-separator"></td>
                <td>
                    Pukul: <span class="data-isian"><?php echo date('H:i', strtotime($data['jam_keluar'])); ?></span>
                    s/d 
                    <span class="data-isian"><?php echo date('H:i', strtotime($data['jam_kembali'])); ?></span>
                </td>
            </tr>

            <tr>
                <td class="col-label">Untuk Keperluan</td>
                <td class="col-separator">:</td>
                <td><span class="data-isian"><?php echo $data['keperluan']; ?></span></td>
            </tr>
        </table>

        <div style="margin-top: 25px;" class="text-justify">
            Demikian izin ini diberikan kepada yang bersangkutan untuk digunakan sebagaimana mestinya.
        </div>

        <div class="ttd-area">
            Yogyakarta, <span class="data-isian"><?php echo tgl_indo($data['tgl_izin']); ?></span><br>
            Atasan Langsung,
            <div class="ttd-space"></div>
            ( <span><?php echo $data['nama_atasan']; ?></span> )<br>
        </div>

    </div>

</body>
</html>