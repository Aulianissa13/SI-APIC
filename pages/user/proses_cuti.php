<?php
session_start();

$paths = [
    '../../config/database.php', 
    '../config/database.php', 
    'config/database.php'
];

$koneksi = null;
foreach ($paths as $path) {
    if (file_exists($path)) {
        include $path;
        break;
    }
}

if (!$koneksi) {
    die("Error: Database tidak ditemukan.");
}

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_user     = $_POST['id_user'];
    $id_jenis    = $_POST['id_jenis'];
    $tgl_mulai   = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $lama_hari   = (int) $_POST['lama_hari'];

    $alasan      = mysqli_real_escape_string($koneksi, $_POST['alasan']);
    $alamat_cuti = mysqli_real_escape_string($koneksi, $_POST['alamat_cuti']);

    // =========================
    // FIX NOMOR SURAT
    // =========================
    $nomor_surat = mysqli_real_escape_string($koneksi, $_POST['nomor_surat']);

    $masa_kerja = mysqli_real_escape_string($koneksi, $_POST['masa_kerja']);

    // =========================
    // ATASAN
    // =========================
    $id_atasan = 0;

    if (!empty($_POST['id_atasan'])) {
        $id_atasan = $_POST['id_atasan'];
    } elseif (!empty($_POST['id_pejabat'])) {
        $id_atasan = $_POST['id_pejabat'];
    }

    // =========================
    // FIX LOGIC PLH
    // =========================
    $ttd_pejabat = isset($_POST['ttd_pejabat']) ? $_POST['ttd_pejabat'] : 'ketua';

    $plh_nama = NULL;
    $plh_nip  = NULL;

    if ($ttd_pejabat == 'plh') {

        $plh_nama = mysqli_real_escape_string($koneksi, $_POST['plh_nama']);
        $plh_nip  = mysqli_real_escape_string($koneksi, $_POST['plh_nip']);

        if (empty($plh_nama) || empty($plh_nip)) {

            $_SESSION['swal'] = [
                'icon' => 'warning',
                'title' => 'Data PLH belum lengkap',
                'text' => 'Nama dan NIP PLH wajib diisi'
            ];

            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }

    $tgl_pengajuan = date('Y-m-d');
    $status = 'diajukan';


    // =========================
    // CEK BENTROK
    // =========================

    $cek_bentrok = mysqli_query($koneksi, "
        SELECT *
        FROM pengajuan_cuti
        WHERE id_user='$id_user'
        AND status!='Ditolak'
        AND (
            tgl_mulai <= '$tgl_selesai'
            AND tgl_selesai >= '$tgl_mulai'
        )
    ");

    if (mysqli_num_rows($cek_bentrok) > 0) {

        $_SESSION['swal'] = [
            'icon' => 'error',
            'title' => 'Tanggal bentrok',
            'text' => 'Anda sudah mengajukan cuti di tanggal tersebut'
        ];

        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }


    // =========================
    // AMBIL DATA USER
    // =========================

    $q_user = mysqli_query($koneksi,
        "SELECT * FROM users WHERE id_user='$id_user'"
    );

    $user = mysqli_fetch_array($q_user);


    $q_jenis = mysqli_query($koneksi,
        "SELECT * FROM jenis_cuti WHERE id_jenis='$id_jenis'"
    );

    $jenis = mysqli_fetch_array($q_jenis);

    $nama_jenis = strtolower($jenis['nama_jenis']);


    $sisa_n_awal  = $user['sisa_cuti_n'];
    $sisa_n1_awal = $user['sisa_cuti_n1'];

    $dipotong_n  = 0;
    $dipotong_n1 = 0;


    // =========================
    // LOGIC POTONG CUTI
    // =========================

    if (strpos($nama_jenis, 'tahunan') !== false) {

        if ($lama_hari <= $sisa_n1_awal) {

            $dipotong_n1 = $lama_hari;

        } else {

            $dipotong_n1 = $sisa_n1_awal;
            $dipotong_n  = $lama_hari - $sisa_n1_awal;
        }

        $sisa_n1_baru = $sisa_n1_awal - $dipotong_n1;
        $sisa_n_baru  = $sisa_n_awal - $dipotong_n;

        if ($sisa_n_baru < 0) $sisa_n_baru = 0;

        mysqli_query($koneksi,
            "UPDATE users SET
            sisa_cuti_n='$sisa_n_baru',
            sisa_cuti_n1='$sisa_n1_baru'
            WHERE id_user='$id_user'"
        );

    }
    else if (strpos($nama_jenis, 'sakit') !== false) {

        $sisa_sakit_baru = $user['kuota_cuti_sakit'] - $lama_hari;

        if ($sisa_sakit_baru < 0) $sisa_sakit_baru = 0;

        mysqli_query($koneksi,
            "UPDATE users SET
            kuota_cuti_sakit='$sisa_sakit_baru'
            WHERE id_user='$id_user'"
        );
    }



    // =========================
    // INSERT DATABASE
    // =========================

    $query_insert = "
        INSERT INTO pengajuan_cuti (
            nomor_surat,
            id_user,
            id_jenis,
            tgl_mulai,
            tgl_selesai,
            lama_hari,
            alasan,
            alamat_cuti,
            status,
            tgl_pengajuan,
            id_atasan,
            masa_kerja,
            ttd_pejabat,
            plh_nama,
            plh_nip,
            sisa_cuti_n,
            sisa_cuti_n1,
            dipotong_n,
            dipotong_n1
        )
        VALUES (
            '$nomor_surat',
            '$id_user',
            '$id_jenis',
            '$tgl_mulai',
            '$tgl_selesai',
            '$lama_hari',
            '$alasan',
            '$alamat_cuti',
            '$status',
            '$tgl_pengajuan',
            '$id_atasan',
            '$masa_kerja',
            '$ttd_pejabat',
            " . ($plh_nama ? "'$plh_nama'" : "NULL") . ",
            " . ($plh_nip ? "'$plh_nip'" : "NULL") . ",
            '$sisa_n_awal',
            '$sisa_n1_awal',
            '$dipotong_n',
            '$dipotong_n1'
        )
    ";



    if (mysqli_query($koneksi, $query_insert)) {

        $_SESSION['swal'] = [
            'icon' => 'success',
            'title' => 'Berhasil',
            'text' => 'Pengajuan cuti berhasil dikirim'
        ];

        header("Location: ../../index.php?page=riwayat_cuti");

    } else {

        $_SESSION['swal'] = [
            'icon' => 'error',
            'title' => 'Database error',
            'text' => mysqli_error($koneksi)
        ];

        header("Location: " . $_SERVER['HTTP_REFERER']);
    }

    exit();
}
?>
