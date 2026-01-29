<?php
include 'config/database.php';

$query = "UPDATE pengajuan_cuti SET sisa_cuti_n = 12, sisa_cuti_n1 = 6 WHERE sisa_cuti_n = 0 AND sisa_cuti_n1 = 0";
mysqli_query($koneksi, $query);

$query2 = "UPDATE pengajuan_cuti SET kuota_cuti_sakit_awal = 14 WHERE kuota_cuti_sakit_awal = 0";
mysqli_query($koneksi, $query2);

echo "Old records updated successfully.";
?>
