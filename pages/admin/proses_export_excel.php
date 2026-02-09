<?php
// --- 1. SETTING ANTI-CRASH & MEMORY ---
/** @var mysqli $koneksi */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
ini_set('memory_limit', '512M');
set_time_limit(300);


require '../../vendor/autoload.php';
include '../../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// --- 2. PERSIAPAN DATA ---
$bulan    = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun    = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$id_jenis = isset($_GET['id_jenis']) ? $_GET['id_jenis'] : '1';

$nama_bulan_arr = [
    '01'=>'JANUARI','02'=>'FEBRUARI','03'=>'MARET','04'=>'APRIL','05'=>'MEI','06'=>'JUNI',
    '07'=>'JULI','08'=>'AGUSTUS','09'=>'SEPTEMBER','10'=>'OKTOBER','11'=>'NOVEMBER','12'=>'DESEMBER'
];
$nama_bulan  = $nama_bulan_arr[$bulan] ?? strtoupper(date('F'));
$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, (int)$bulan, (int)$tahun);
$jenis_label = ($id_jenis == '1') ? 'TAHUNAN' : 'SAKIT';

// Libur Nasional
$libur_nasional = [];
$q_libur = mysqli_query($koneksi, "SELECT tanggal FROM libur_nasional WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun'");
if ($q_libur) {
    while ($r = mysqli_fetch_assoc($q_libur)) { $libur_nasional[] = $r['tanggal']; }
}
if(empty($libur_nasional)) {
    $libur_nasional = ["$tahun-01-01", "$tahun-08-17", "$tahun-12-25"];
}

// --- 3. MULAI BUAT EXCEL ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- SETUP HALAMAN (LEGAL LANDSCAPE & CENTER) ---
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LEGAL);
$sheet->getPageSetup()->setHorizontalCentered(true);
$sheet->getPageSetup()->setVerticalCentered(false);
$sheet->getPageMargins()->setTop(0.4)->setRight(0.5)->setLeft(0.5)->setBottom(0.2);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->setShowGridlines(false);

$last_col_str = 'AI'; 

// URUTAN INI PENTING: JURUSITA -> JURUSITA PENGGANTI
$kategori_list = [
    'HAKIM KARIR DAN AD HOC', 
    'PANITERA DAN PANMUD', 
    'SEKRETARIS DAN KASUBBAG',
    'PANITERA PENGGANTI', 
    'JURUSITA', 
    'JURUSITA PENGGANTI', 
    'STAF'
];

$row_curr = 1;
$first_page = true;

foreach ($kategori_list as $kategori) {
    $qUsers = mysqli_query($koneksi, "SELECT * FROM users WHERE TRIM(kategori_laporan) = '$kategori' ORDER BY nama_lengkap ASC");
    if (mysqli_num_rows($qUsers) == 0) continue;

    // --- LOGIKA PAGE BREAK SPESIAL ---
    if (!$first_page) {
        if ($kategori == 'JURUSITA PENGGANTI') {
            // KHUSUS JURUSITA PENGGANTI: JANGAN PAGE BREAK
            // Cukup kasih jarak 2 baris dari tabel atasnya
            $row_curr += 2; 
        } else {
            // SELAIN ITU: WAJIB PAGE BREAK (GANTI HALAMAN)
            $sheet->setBreak('A' . ($row_curr - 1), \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
        }
    }
    $first_page = false;

    // --- JUDUL UTAMA ---
    $sheet->mergeCells("A$row_curr:$last_col_str$row_curr");
    $sheet->setCellValue("A$row_curr", "REKAPITULASI SISA CUTI $jenis_label SAMPAI BULAN $nama_bulan $tahun");
    $sheet->getStyle("A$row_curr")->getFont()->setBold(true)->setSize(14)->setName('Arial');
    $sheet->getStyle("A$row_curr")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A$row_curr")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getRowDimension($row_curr)->setRowHeight(30); 
    $row_curr++; 

    // --- HEADER TABEL ---
    $start_row = $row_curr;
    
    // Header Kiri
    $sheet->mergeCells("A$row_curr:A".($row_curr+1))->setCellValue("A$row_curr", "NO");
    $sheet->mergeCells("B$row_curr:B".($row_curr+1))->setCellValue("B$row_curr", $kategori);
    
    if($id_jenis == '1') {
        $sheet->mergeCells("C$row_curr:C".($row_curr+1))->setCellValue("C$row_curr", "SISA\n".($tahun-1));
        $sheet->mergeCells("D$row_curr:D".($row_curr+1))->setCellValue("D$row_curr", "SISA\n$tahun");
    } else {
        $sheet->mergeCells("C$row_curr:D".($row_curr+1))->setCellValue("C$row_curr", "SISA\nSAKIT");
    }

    // Header Tanggal
    $sheet->mergeCells("E$row_curr:$last_col_str$row_curr")->setCellValue("E$row_curr", "TANGGAL");
    
    // Styling Header
    $styleHeader = [
        'font' => ['bold' => true, 'name' => 'Arial', 'size' => 10],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFEEEEEE'] 
        ]
    ];
    $sheet->getStyle("A$row_curr:$last_col_str".($row_curr+1))->applyFromArray($styleHeader);

    // Baris 2 Header (Angka 1-31)
    $row_tgl = $row_curr + 1;
    $col_idx = 5; 
    
    for($d=1; $d<=31; $d++) {
        $col_str = Coordinate::stringFromColumnIndex($col_idx);
        
        $tgl_cek = sprintf("%04d-%02d-%02d", $tahun, $bulan, $d);
        $is_weekend = (date('N', strtotime($tgl_cek)) >= 6);
        $is_libur   = in_array($tgl_cek, $libur_nasional);
        $is_invalid = ($d > $jumlah_hari); 
        
        if ($is_invalid || $is_weekend || $is_libur) {
            $val = ""; 
        } else {
            $val = $d;
        }

        $sheet->setCellValue($col_str . $row_tgl, $val);
        
        if($is_invalid || $is_weekend || $is_libur) {
            $sheet->getStyle($col_str . $row_tgl)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF000000');
        } else {
            $sheet->getStyle($col_str . $row_tgl)->getFill()->setFillType(Fill::FILL_NONE);
        }

        $sheet->getColumnDimension($col_str)->setAutoSize(false);
        $sheet->getColumnDimension($col_str)->setWidth(2.7);

        $col_idx++;
    }
    
    $row_curr += 2; 
    
    // --- ISI DATA PEGAWAI ---
    $no = 1;
    while($row = mysqli_fetch_assoc($qUsers)) {
        $sheet->setCellValue("A$row_curr", $no);
        $sheet->setCellValue("B$row_curr", strtoupper($row['nama_lengkap']));
        
        if($id_jenis == '1') {
            $sheet->setCellValue("C$row_curr", $row['sisa_cuti_n1'] ?? 0);
            $sheet->setCellValue("D$row_curr", $row['sisa_cuti_n'] ?? 0);
        } else {
             $sheet->mergeCells("C$row_curr:D$row_curr");
             $sheet->setCellValue("C$row_curr", $row['kuota_cuti_sakit'] ?? '-');
        }
        
        // Loop Cuti
        $col_idx = 5;
        for($d=1; $d<=31; $d++) {
            $col_str = Coordinate::stringFromColumnIndex($col_idx);
            $tgl_cek = sprintf("%04d-%02d-%02d", $tahun, $bulan, $d);
            $is_weekend = (date('N', strtotime($tgl_cek)) >= 6);
            $is_libur   = in_array($tgl_cek, $libur_nasional);
            $is_invalid = ($d > $jumlah_hari);
            
            if($is_invalid || $is_weekend || $is_libur) {
                $sheet->setCellValue($col_str . $row_curr, ""); 
                $sheet->getStyle($col_str . $row_curr)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF000000');
            } else {
                $q_cuti = mysqli_query($koneksi, "SELECT id_pengajuan FROM pengajuan_cuti 
                    WHERE id_user='{$row['id_user']}' AND id_jenis='$id_jenis' AND status='Disetujui'
                    AND '$tgl_cek' BETWEEN tgl_mulai AND tgl_selesai LIMIT 1");
                
                if(mysqli_num_rows($q_cuti) > 0) {
                    $sheet->setCellValue($col_str . $row_curr, "v"); 
                    $sheet->getStyle($col_str . $row_curr)->getFont()->setBold(true);
                    $sheet->getStyle($col_str . $row_curr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            }
            $col_idx++;
        }
        
        $sheet->getRowDimension($row_curr)->setRowHeight(18);
        $row_curr++;
        $no++;
    }
    
    // --- BORDER ---
    $last_row = $row_curr - 1;
    $styleBorder = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
            'horizontal' => Alignment::HORIZONTAL_CENTER
        ],
        'font' => ['name' => 'Arial', 'size' => 10]
    ];
    $sheet->getStyle("A$start_row:$last_col_str$last_row")->applyFromArray($styleBorder);
    
    // Rata Kiri Nama + Indent
    $sheet->getStyle("B".($start_row+2).":B$last_row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle("B".($start_row+2).":B$last_row")->getAlignment()->setIndent(1);

    // Spasi utk iterasi selanjutnya (Kalau kena page break, spasi ini diabaikan Excel)
    $row_curr += 2;
}

// --- FINAL SIZE KOLOM UTAMA ---
$sheet->getColumnDimension('A')->setWidth(5);   
$sheet->getColumnDimension('B')->setWidth(40);  
$sheet->getColumnDimension('C')->setWidth(8);   
$sheet->getColumnDimension('D')->setWidth(8);   

// --- OUTPUT ---
$filename = "Rekap_Cuti_{$jenis_label}_{$nama_bulan}_{$tahun}.xlsx";

ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');
header('Expires: 0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();