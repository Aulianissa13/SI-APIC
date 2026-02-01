<?php
/** @var mysqli $koneksi */

// FILE: pages/admin/proses_export_excel.php

// 1. KONEKSI
include '../../config/database.php'; 

// 2. INPUT
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$id_jenis = isset($_GET['id_jenis']) ? $_GET['id_jenis'] : '1'; 

// 3. VARIABEL BANTU
$nama_bulan_arr = ['01'=>'JANUARI','02'=>'FEBRUARI','03'=>'MARET','04'=>'APRIL','05'=>'MEI','06'=>'JUNI','07'=>'JULI','08'=>'AGUSTUS','09'=>'SEPTEMBER','10'=>'OKTOBER','11'=>'NOVEMBER','12'=>'DESEMBER'];
$nama_bulan = $nama_bulan_arr[$bulan];
$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// LIST TANGGAL MERAH
$libur_nasional = [
    "$tahun-01-01", "$tahun-05-01", "$tahun-06-01", 
    "$tahun-08-17", "$tahun-12-25"
];

// Label File
$jenis_label = ($id_jenis == '1') ? 'TAHUNAN' : 'SAKIT';
$timestamp   = date('His'); 
$filename    = "Rekap_Cuti_" . $jenis_label . "_$bulan-$tahun" . "_$timestamp.xls";

// 4. HEADER DOWNLOAD EXCEL
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

// STYLE CSS (INLINE)
// Catatan: width:28px kira-kira setara dengan Column Width 3.40 di Excel
$style_border = "border: 1px solid #000000;";
$style_header = "border: 1px solid #000000; font-weight: bold; text-align: center; vertical-align: middle; background-color: #FFFFFF;";
$style_judul  = "border: none; font-weight: bold; font-size: 14pt; text-align: center; vertical-align: middle;";
$style_blok_hitam = "border: 1px solid #000000; background-color: #000000; color: #000000;";
$style_tengah = "border: 1px solid #000000; text-align: center; vertical-align: middle;";
$style_kiri   = "border: 1px solid #000000; text-align: left; vertical-align: middle;";
$style_bold_tengah = "border: 1px solid #000000; text-align: center; font-weight: bold; vertical-align: middle;";

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        table { border-collapse: collapse; width: 100%; }
        /* Paksa lebar kolom tanggal agar tidak melar */
        .kolom-tanggal { width: 28px; min-width: 28px; max-width: 28px; }
    </style>
</head>
<body>

<?php 
// Hitung Total Kolom
$cols = ($id_jenis == '1') ? 35 : 34; 
?>

<table>
    
    <tr>
        <td colspan="<?php echo $cols; ?>" style="<?php echo $style_judul; ?> height: 40px;">
            REKAPITULASI SISA CUTI <?php echo $jenis_label; ?> SAMPAI BULAN <?php echo $nama_bulan; ?> <?php echo $tahun; ?>
        </td>
    </tr>

    <tr>
        <td colspan="<?php echo $cols; ?>" style="border: none; height: 10px;"></td>
    </tr>

    <tr>
        <th rowspan="2" style="<?php echo $style_header; ?>" width="40">NO.</th>
        <th rowspan="2" style="<?php echo $style_header; ?>" width="250">NAMA PEGAWAI</th>
        
        <?php if($id_jenis == '1'): ?>
            <th rowspan="2" style="<?php echo $style_header; ?>" width="80">SISA CUTI <?php echo $tahun-1; ?></th>
            <th rowspan="2" style="<?php echo $style_header; ?>" width="80">SISA CUTI <?php echo $tahun; ?></th>
        <?php else: ?>
            <th rowspan="2" style="<?php echo $style_header; ?>" width="100">SISA CUTI SAKIT <?php echo $tahun; ?></th>
        <?php endif; ?>

        <th colspan="31" style="<?php echo $style_header; ?>">TANGGAL CUTI</th>
    </tr>

    <tr>
        <?php for($d=1; $d<=31; $d++): ?>
            <?php 
                if($d > $jumlah_hari) {
                    // Tanggal Tidak Valid (30 Feb dll) -> Blok Hitam
                    // Width 28px = Setara 3.40 Excel
                    echo "<th style='$style_blok_hitam' width='28'></th>";
                } else {
                    echo "<th style='$style_header' width='28'>$d</th>";
                }
            ?>
        <?php endfor; ?>
    </tr>

    <?php
    $no = 1;
    $query = mysqli_query($koneksi, "SELECT * FROM users ORDER BY nama_lengkap ASC");

    while($row = mysqli_fetch_assoc($query)) {
        echo "<tr>";
        
        // 1. NOMOR
        echo "<td style='$style_tengah'>$no</td>";
        
        // 2. NAMA
        echo "<td style='$style_kiri'><strong>".strtoupper($row['nama_lengkap'])."</strong></td>";
        
        // 3. SISA CUTI
        if($id_jenis == '1'){
            echo "<td style='$style_tengah'>".$row['sisa_cuti_n1']."</td>";
            echo "<td style='$style_tengah'>".$row['sisa_cuti_n']."</td>";
        } else {
            $sisa = isset($row['kuota_cuti_sakit']) ? $row['kuota_cuti_sakit'] : '-';
            echo "<td style='$style_tengah'>$sisa</td>";
        }

        // 4. LOOP TANGGAL 1-31
        for($d=1; $d<=31; $d++){
            $tgl_cek = sprintf("%04d-%02d-%02d", $tahun, $bulan, $d);
            
            // Cek Libur
            $is_weekend = (date('N', strtotime($tgl_cek)) >= 6);
            $is_nasional = in_array($tgl_cek, $libur_nasional);
            $is_libur = ($is_weekend || $is_nasional);
            
            // Default Style
            $current_style = $style_tengah; 
            $content = "";

            // LOGIKA WARNA BLOK
            if($d > $jumlah_hari) {
                $current_style = $style_blok_hitam;
            } elseif ($is_libur) {
                $current_style = $style_blok_hitam;
            }

            // LOGIKA ISI DATA (Checklist)
            if($d <= $jumlah_hari) {
                $q_cuti = mysqli_query($koneksi, "SELECT id_pengajuan FROM pengajuan_cuti 
                          WHERE id_user='{$row['id_user']}' 
                          AND id_jenis='$id_jenis' 
                          AND status='Disetujui' 
                          AND '$tgl_cek' BETWEEN tgl_mulai AND tgl_selesai");
                
                if(mysqli_num_rows($q_cuti) > 0) {
                    if($is_libur) {
                        $current_style = $style_blok_hitam;
                        $content = ""; 
                    } else {
                        $content = "&#10003;"; // Centang
                        $current_style = $style_bold_tengah; 
                    }
                }
            }

            // PENTING: Width tetap dijaga di setiap sel agar konsisten
            echo "<td style='$current_style' width='28'>$content</td>";
        }

        echo "</tr>";
        $no++;
    }
    ?>

</table>

</body>
</html>