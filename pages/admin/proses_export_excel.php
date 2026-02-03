<?php
/** @var mysqli $koneksi */

include '../../config/database.php'; 

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$id_jenis = isset($_GET['id_jenis']) ? $_GET['id_jenis'] : '1'; 

$nama_bulan_arr = ['01'=>'JANUARI','02'=>'FEBRUARI','03'=>'MARET','04'=>'APRIL','05'=>'MEI','06'=>'JUNI','07'=>'JULI','08'=>'AGUSTUS','09'=>'SEPTEMBER','10'=>'OKTOBER','11'=>'NOVEMBER','12'=>'DESEMBER'];
$nama_bulan = $nama_bulan_arr[$bulan];
$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

$libur_nasional = ["$tahun-01-01", "$tahun-05-01", "$tahun-06-01", "$tahun-08-17", "$tahun-12-25"];

$jenis_label = ($id_jenis == '1') ? 'TAHUNAN' : 'SAKIT';
$timestamp = date('His'); 
$filename = "Rekap_Cuti_" . $jenis_label . "_$bulan-$tahun" . "_$timestamp.xls";

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

$style_header = "border: 1px solid #000; font-weight: bold; text-align: center; vertical-align: middle; background-color: #f2f2f2;";
$style_judul  = "font-weight: bold; font-size: 14pt; text-align: center; vertical-align: middle;";
$style_blok_hitam = "border: 1px solid #000; background-color: #000; color: #000;";
$style_tengah = "border: 1px solid #000; text-align: center; vertical-align: middle;";
$style_kiri   = "border: 1px solid #000; text-align: left; vertical-align: middle; white-space: nowrap; padding-left: 5px;";
$style_bold_tengah = "border: 1px solid #000; text-align: center; font-weight: bold; vertical-align: middle;";

$cols = ($id_jenis == '1') ? 35 : 34; 
?>

<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
                        </x:Print>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        table { border-collapse: collapse; }
        td { height: 25px; font-family: Arial; font-size: 9pt; }
        .tgl { width: 23px; min-width: 23px; } 
    </style>
</head>
<body>

<table>
    <tr>
        <td colspan="<?php echo $cols; ?>" style="<?php echo $style_judul; ?> height: 45px;">
            REKAPITULASI SISA CUTI <?php echo $jenis_label; ?> SAMPAI BULAN <?php echo $nama_bulan; ?> <?php echo $tahun; ?>
        </td>
    </tr>

    <tr>
        <th rowspan="2" style="<?php echo $style_header; ?>" width="30">NO.</th>
        <th rowspan="2" style="<?php echo $style_header; ?>" width="240">NAMA PEGAWAI</th> <?php if($id_jenis == '1'): ?>
            <th rowspan="2" style="<?php echo $style_header; ?>" width="55">SISA <?php echo $tahun-1; ?></th>
            <th rowspan="2" style="<?php echo $style_header; ?>" width="55">SISA <?php echo $tahun; ?></th>
        <?php else: ?>
            <th rowspan="2" style="<?php echo $style_header; ?>" width="80">SISA SAKIT</th>
        <?php endif; ?>

        <th colspan="31" style="<?php echo $style_header; ?>">TANGGAL</th>
    </tr>

    <tr>
        <?php for($d=1; $d<=31; $d++): ?>
            <th class="tgl" style="<?php echo ($d > $jumlah_hari) ? $style_blok_hitam : $style_header; ?>">
                <?php echo ($d > $jumlah_hari) ? '' : $d; ?>
            </th>
        <?php endfor; ?>
    </tr>

    <?php
    $no = 1;
    $query = mysqli_query($koneksi, "SELECT * FROM users ORDER BY nama_lengkap ASC");
    while($row = mysqli_fetch_assoc($query)) {
        echo "<tr>";
        echo "<td style='$style_tengah'>$no</td>";
        echo "<td style='$style_kiri'>".strtoupper($row['nama_lengkap'])."</td>";
        
        if($id_jenis == '1'){
            echo "<td style='$style_tengah'>".$row['sisa_cuti_n1']."</td>";
            echo "<td style='$style_tengah'>".$row['sisa_cuti_n']."</td>";
        } else {
            echo "<td style='$style_tengah'>".(isset($row['kuota_cuti_sakit']) ? $row['kuota_cuti_sakit'] : '-')."</td>";
        }

        for($d=1; $d<=31; $d++){
            $tgl_cek = sprintf("%04d-%02d-%02d", $tahun, $bulan, $d);
            $is_weekend = (date('N', strtotime($tgl_cek)) >= 6);
            $is_nasional = in_array($tgl_cek, $libur_nasional);
            $is_libur = ($is_weekend || $is_nasional);
            
            $current_style = $style_tengah; 
            $content = "";

            if($d > $jumlah_hari || $is_libur) {
                $current_style = $style_blok_hitam;
            } else {
                $q_cuti = mysqli_query($koneksi, "SELECT id_pengajuan FROM pengajuan_cuti 
                          WHERE id_user='{$row['id_user']}' AND id_jenis='$id_jenis' 
                          AND status='Disetujui' AND '$tgl_cek' BETWEEN tgl_mulai AND tgl_selesai");
                if(mysqli_num_rows($q_cuti) > 0) {
                    $content = "v"; 
                    $current_style = $style_bold_tengah; 
                }
            }
            echo "<td class='tgl' style='$current_style'>$content</td>";
        }
        echo "</tr>";
        $no++;
    }
    ?>
</table>

</body>
</html>