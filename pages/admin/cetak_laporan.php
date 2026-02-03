<?php
/** @var mysqli $koneksi */

include '../../config/database.php';

$tgl_awal  = $_GET['tgl_awal'];
$tgl_akhir = $_GET['tgl_akhir'];
$status    = $_GET['status'];

$query_sql = "SELECT p.*, u.nama_lengkap, u.nip, j.nama_jenis 
              FROM pengajuan_cuti p
              JOIN users u ON p.id_user = u.id_user
              JOIN jenis_cuti j ON p.id_jenis = j.id_jenis 
              WHERE (p.tgl_pengajuan BETWEEN '$tgl_awal' AND '$tgl_akhir')";

if ($status != "Semua") {
    $query_sql .= " AND p.status = '$status'";
}

$query_sql .= " ORDER BY p.tgl_pengajuan ASC";
$result = mysqli_query($koneksi, $query_sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Cuti</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2, .header h3 { margin: 0; }
        .header hr { border: 1px solid black; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .ttd { float: right; margin-top: 50px; text-align: center; width: 200px; }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h2>LAPORAN REKAPITULASI CUTI PEGAWAI</h2>
        <h3>Periode: <?php echo date('d-m-Y', strtotime($tgl_awal)); ?> s/d <?php echo date('d-m-Y', strtotime($tgl_akhir)); ?></h3>
        <hr>
    </div>

    <p><strong>Filter Status:</strong> <?php echo $status; ?></p>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>NIP</th>
                <th>Nama Pegawai</th>
                <th>Jenis Cuti</th> <th>Tanggal Cuti</th>
                <th>Lama</th>
                <th>Alasan</th> <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if($result && mysqli_num_rows($result) > 0){
                while ($row = mysqli_fetch_assoc($result)) {
                    $tgl_mulai = date('d/m/Y', strtotime($row['tgl_mulai']));
                    $tgl_selesai = date('d/m/Y', strtotime($row['tgl_selesai']));
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td><?php echo $row['nip']; ?></td>
                <td><?php echo $row['nama_lengkap']; ?></td>
                
                <td><?php echo $row['nama_jenis']; ?></td>
                
                <td><?php echo $tgl_mulai . ' - ' . $tgl_selesai; ?></td>
                <td class="text-center"><?php echo $row['lama_hari']; ?> Hari</td>
                
                <td><?php echo $row['alasan']; ?></td>
                
                <td class="text-center">
                    <?php 
                    if($row['status'] == 'Disetujui') echo '<b>Disetujui</b>';
                    elseif($row['status'] == 'Ditolak') echo 'Ditolak';
                    else echo 'Menunggu';
                    ?>
                </td>
            </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'>Tidak ada data pada periode ini.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="ttd">
        <p>Mengetahui,<br>Kepala Bagian</p>
        <br><br><br>
        <p>______________________</p>
    </div>

</body>
</html>