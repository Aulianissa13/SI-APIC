<?php
/** @var mysqli $koneksi */

// --- FILE: pages/admin/proses_validasi.php ---

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 1. SMART LOCATOR DATABASE
$kemungkinan_path = ['../../config/database.php', '../config/database.php', 'config/database.php', '../database.php'];
$db_found = false;
foreach ($kemungkinan_path as $path) {
    if (file_exists($path)) { include_once $path; $db_found = true; break; }
}
if (!$db_found) { die("Error: File config/database.php tidak ditemukan."); }

// 2. LOGIC PROSES
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    
    $id_pengajuan = intval($_GET['id']); 
    $aksi         = $_GET['aksi']; 

    // Ambil data pengajuan & jenis cuti
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

        // Cek Double Process (Supaya gak diklik 2x)
        if($status_now == 'Disetujui' || $status_now == 'Ditolak') {
            $_SESSION['swal'] = [
                'icon'  => 'warning',
                'title' => 'Sudah Diproses',
                'text'  => 'Data ini sudah diverifikasi sebelumnya.'
            ];
            header("Location: ../../index.php?page=validasi_cuti");
            exit();
        }

        // --- MULAI TRANSAKSI (SUPAYA KUOTA AMAN) ---
        mysqli_begin_transaction($koneksi);
        
        try {
        // --- SKENARIO 1: SETUJU ---
            if ($aksi == 'setuju') {
                
                // A. Ambil Data Sisa Cuti User SAAT INI (Sebelum dipotong)
                $q_user = mysqli_query($koneksi, "SELECT sisa_cuti_n, sisa_cuti_n1, kuota_cuti_sakit FROM users WHERE id_user='$id_user'");
                $d_user = mysqli_fetch_array($q_user);

                // B. Hitung Sisa Cuti BARU (Matematika di PHP)
                $sisa_n_baru     = intval($d_user['sisa_cuti_n']);
                $sisa_n1_baru    = intval($d_user['sisa_cuti_n1']);
                $sisa_sakit_baru = intval($d_user['kuota_cuti_sakit']);

                // Logic Pengurangan
                if (stripos($data['nama_jenis'], 'Tahunan') !== false) {
                    $potong_n1 = $dipotong_n1; 
                    $potong_n  = $dipotong_n;  
                    
                    $sisa_n1_baru = $sisa_n1_baru - $potong_n1;
                    $sisa_n_baru  = $sisa_n_baru - $potong_n;
                }
                elseif (stripos($data['nama_jenis'], 'Sakit') !== false) {
                    $sisa_sakit_baru = $sisa_sakit_baru - $lama;
                }

                // C. Update Tabel USERS (Saldo Pegawai Berubah)
                $q_update_user = "UPDATE users SET 
                                  sisa_cuti_n = '$sisa_n_baru', 
                                  sisa_cuti_n1 = '$sisa_n1_baru',
                                  kuota_cuti_sakit = '$sisa_sakit_baru'
                                  WHERE id_user='$id_user'";
                
                $run_up_user = mysqli_query($koneksi, $q_update_user);
                if (!$run_up_user) { throw new Exception("Gagal update saldo user."); }

                // D. Update Tabel PENGAJUAN (Simpan Status & SNAPSHOT Sisa Cuti ke kolom yang sudah ada)
                // Di sini kita masukkan nilai sisa terbaru ke tabel pengajuan_cuti
                $q_setuju = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET 
                            status='Disetujui', 
                            sisa_cuti_n='$sisa_n_baru', 
                            sisa_cuti_n1='$sisa_n1_baru'
                            WHERE id_pengajuan='$id_pengajuan'");
                            
                if (!$q_setuju) { throw new Exception("Gagal update status setuju."); }

                $_SESSION['swal'] = [
                    'icon'  => 'success',
                    'title' => 'Disetujui!',
                    'text'  => 'Pengajuan disetujui. Sisa N: ' . $sisa_n_baru . ', Sisa N-1: ' . $sisa_n1_baru
                ];

            // --- SKENARIO 2: TOLAK (REFUND KUOTA) ---
            } elseif ($aksi == 'tolak') {
                
                // 1. Update Status jadi Ditolak
                $q_tolak = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET status='Ditolak' WHERE id_pengajuan='$id_pengajuan'");
                if (!$q_tolak) { throw new Exception("Gagal update status tolak."); }
                
                $pesan_tambahan = "";

                // 2. Balikin Kuota User (Refund)
                if (stripos($data['nama_jenis'], 'Tahunan') !== false) {
                    // Balikin N dan N-1
                    $q_refund = mysqli_query($koneksi, "UPDATE users SET sisa_cuti_n = sisa_cuti_n + $dipotong_n, sisa_cuti_n1 = sisa_cuti_n1 + $dipotong_n1 WHERE id_user='$id_user'");
                    if (!$q_refund) { throw new Exception("Gagal refund kuota tahunan."); }
                    $pesan_tambahan = "Kuota Cuti Tahunan dikembalikan.";
                    
                } elseif (stripos($data['nama_jenis'], 'Sakit') !== false) { 
                    // Balikin Kuota Sakit
                    $q_refund = mysqli_query($koneksi, "UPDATE users SET kuota_cuti_sakit = kuota_cuti_sakit + $lama WHERE id_user='$id_user'");
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

            // Simpan Perubahan Permanen
            mysqli_commit($koneksi);

        } catch (Exception $e) {
            // Batalkan Semua Jika Error
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