<?php
/** @var mysqli $koneksi */

if (session_status() == PHP_SESSION_NONE) { session_start(); }

$kemungkinan_path = ['../../config/database.php', '../config/database.php', 'config/database.php', '../database.php'];
$db_found = false;
foreach ($kemungkinan_path as $path) {
    if (file_exists($path)) { include_once $path; $db_found = true; break; }
}
if (!$db_found) { die("Error: File config/database.php tidak ditemukan."); }

if (isset($_GET['aksi']) && isset($_GET['id'])) {
    
    $id_pengajuan = intval($_GET['id']); 
    $aksi         = $_GET['aksi']; 

    $query_cek = "SELECT pengajuan_cuti.*, jenis_cuti.nama_jenis 
                  FROM pengajuan_cuti 
                  JOIN jenis_cuti ON pengajuan_cuti.id_jenis = jenis_cuti.id_jenis 
                  WHERE id_pengajuan='$id_pengajuan'";
    
    $cek_data = mysqli_query($koneksi, $query_cek);
    
    if(mysqli_num_rows($cek_data) > 0) {
        $data       = mysqli_fetch_array($cek_data);
        $id_user    = $data['id_user'];
        $status_now = $data['status'];

        $dipotong_n  = intval($data['dipotong_n']);
        $dipotong_n1 = intval($data['dipotong_n1']);
        $lama        = intval($data['lama_hari']);


        if($status_now == 'Disetujui' || $status_now == 'Ditolak') {
            $_SESSION['swal'] = [
                'icon'  => 'warning',
                'title' => 'Sudah Diproses',
                'text'  => 'Data ini sudah diverifikasi sebelumnya.'
            ];
            header("Location: ../../index.php?page=validasi_cuti");
            exit();
        }

        mysqli_begin_transaction($koneksi);
        
        try {

            if ($aksi == 'setuju') {

                $q_user = mysqli_query($koneksi, "SELECT sisa_cuti_n, sisa_cuti_n1, kuota_cuti_sakit FROM users WHERE id_user='$id_user'");
                $d_user = mysqli_fetch_array($q_user);

                $sisa_n_current  = intval($d_user['sisa_cuti_n']);
                $sisa_n1_current = intval($d_user['sisa_cuti_n1']);

                $q_setuju = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET 
                                            status='Disetujui', 
                                            sisa_cuti_n='$sisa_n_current', 
                                            sisa_cuti_n1='$sisa_n1_current'
                                            WHERE id_pengajuan='$id_pengajuan'");
                                            
                if (!$q_setuju) { throw new Exception("Gagal update status setuju."); }

                $_SESSION['swal'] = [
                    'icon'  => 'success',
                    'title' => 'Disetujui!',
                    'text'  => 'Pengajuan disetujui.' 
                ];

            // ================================================================
            // LOGIKA AKSI: TOLAK
            // ================================================================
            } elseif ($aksi == 'tolak') {

                $q_tolak = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET status='Ditolak' WHERE id_pengajuan='$id_pengajuan'");
                if (!$q_tolak) { throw new Exception("Gagal update status tolak."); }
                
                $pesan_tambahan = "";

                if (stripos($data['nama_jenis'], 'Tahunan') !== false) {
                    // Balikin Cuti N dan N-1
                    $q_refund = mysqli_query($koneksi, "UPDATE users SET 
                                                        sisa_cuti_n = sisa_cuti_n + $dipotong_n, 
                                                        sisa_cuti_n1 = sisa_cuti_n1 + $dipotong_n1 
                                                        WHERE id_user='$id_user'");
                    if (!$q_refund) { throw new Exception("Gagal refund kuota tahunan."); }
                    $pesan_tambahan = "Kuota Cuti Tahunan dikembalikan.";
                    
                } elseif (stripos($data['nama_jenis'], 'Sakit') !== false) { 
                    // Balikin Cuti Sakit
                    $q_refund = mysqli_query($koneksi, "UPDATE users SET 
                                                        kuota_cuti_sakit = kuota_cuti_sakit + $lama 
                                                        WHERE id_user='$id_user'");
                    if (!$q_refund) { throw new Exception("Gagal refund kuota sakit."); }
                    $pesan_tambahan = "Kuota Cuti Sakit dikembalikan.";
                }

                $_SESSION['swal'] = [
                    'icon'  => 'info',
                    'title' => 'Ditolak',
                    'text'  => 'Pengajuan telah ditolak. ' . $pesan_tambahan
                ];

            } else {
                throw new Exception("Aksi tidak valid.");
            }

            mysqli_commit($koneksi);

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $_SESSION['swal'] = [
                'icon'  => 'error',
                'title' => 'Gagal',
                'text'  => 'Error: ' . $e->getMessage()
            ];
        }

    } else {
        $_SESSION['swal'] = [
            'icon'  => 'error',
            'title' => 'Error',
            'text'  => 'Data tidak ditemukan.'
        ];
    }

    // Redirect
    header("Location: ../../index.php?page=validasi_cuti");
    exit(); 
}
?>