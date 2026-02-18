<?php
// --- 1. SETTING ANTI-CRASH & MEMORY ---
/** @var mysqli $koneksi */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
ini_set('memory_limit', '512M');
set_time_limit(300);

require '../../vendor/autoload.php'; // Sesuaikan path jika perlu
include '../../config/database.php'; // Sesuaikan path jika perlu

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Ambil Tipe Laporan (bulanan / harian)
$tipe_laporan = isset($_GET['tipe_laporan']) ? $_GET['tipe_laporan'] : 'bulanan';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// ===================================================================================
// KONDISI 1: LAPORAN HARIAN (RANGE TANGGAL) - F4 LANDSCAPE + NO SURAT
// ===================================================================================
if ($tipe_laporan == 'harian') {
    
    $tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
    $tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

    // --- SETUP HALAMAN F4 LANDSCAPE ---
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    
    // PAPERSIZE_FOLIO biasanya digunakan untuk F4 (8.5 x 13 inch)
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_FOLIO); 
    
    $sheet->getPageMargins()->setTop(0.5)->setRight(0.5)->setLeft(0.5)->setBottom(0.5);

    // Judul (Diperlebar dari A sampai I karena ada 9 kolom)
    $sheet->mergeCells("A1:I1");
    $sheet->setCellValue("A1", "LAPORAN CUTI PEGAWAI");
    $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells("A2:I2");
    $sheet->setCellValue("A2", "PERIODE: " . date('d-m-Y', strtotime($tgl_awal)) . " s/d " . date('d-m-Y', strtotime($tgl_akhir)));
    $sheet->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Header Tabel (9 Kolom)
    $headers = [
        'NO', 
        'NAMA PEGAWAI', 
        'NIP', 
        'JABATAN', 
        'JENIS CUTI', 
        'NOMOR SURAT',  // <--- Kolom Baru
        'TGL MULAI', 
        'TGL SELESAI', 
        'LAMA (HARI)'
    ];

    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '4', $header);
        $sheet->getColumnDimension($col)->setAutoSize(true);
        $col++;
    }

    // Style Header (Range diperlebar sampai I4)
    $styleHeader = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '004d00']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle("A4:I4")->applyFromArray($styleHeader);

    // Query Data
    // Pastikan select p.* mengambil kolom no_surat
    $query = "SELECT p.*, u.nama_lengkap, u.nip, u.jabatan, j.nama_jenis
              FROM pengajuan_cuti p
              JOIN users u ON p.id_user = u.id_user
              JOIN jenis_cuti j ON p.id_jenis = j.id_jenis
              WHERE p.status = 'Disetujui'
              AND (p.tgl_mulai BETWEEN '$tgl_awal' AND '$tgl_akhir')
              ORDER BY p.tgl_mulai ASC";
    
    $result = mysqli_query($koneksi, $query);
    $row_num = 5;
    $no = 1;

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Hitung hari jika tidak ada di DB atau 0
            $lama = $row['lama_hari'];
            if ($lama <= 0) {
                $d1 = new DateTime($row['tgl_mulai']);
                $d2 = new DateTime($row['tgl_selesai']);
                $lama = $d2->diff($d1)->days + 1;
            }

            // --- ISI DATA ---
            $sheet->setCellValue("A$row_num", $no++);
            $sheet->setCellValue("B$row_num", $row['nama_lengkap']);
            $sheet->setCellValueExplicit("C$row_num", $row['nip'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("D$row_num", strtoupper($row['jabatan'])); 
            $sheet->setCellValue("E$row_num", $row['nama_jenis']);
            
            // Kolom F: Nomor Surat
            // Ubah 'no_surat' sesuai nama kolom di database Anda jika berbeda
            $sheet->setCellValue("F$row_num", $row['nomor_surat'] ?? '-'); 

            $sheet->setCellValue("G$row_num", date('d-m-Y', strtotime($row['tgl_mulai'])));
            $sheet->setCellValue("H$row_num", date('d-m-Y', strtotime($row['tgl_selesai'])));
            $sheet->setCellValue("I$row_num", $lama);
            
            // Formatting Alignment
            $sheet->getStyle("A$row_num")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C$row_num")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 
            $sheet->getStyle("F$row_num")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No Surat Center
            $sheet->getStyle("G$row_num:I$row_num")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tanggal Center

            $row_num++;
        }
    } else {
        $sheet->mergeCells("A5:I5");
        $sheet->setCellValue("A5", "Tidak ada data cuti disetujui pada periode ini.");
        $sheet->getStyle("A5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row_num++;
    }

    // Border Data (Sampai I)
    $styleData = [
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $sheet->getStyle("A5:I" . ($row_num - 1))->applyFromArray($styleData);

    $filename = "Laporan_Cuti_Range_" . date('Ymd', strtotime($tgl_awal)) . "_sd_" . date('Ymd', strtotime($tgl_akhir)) . ".xlsx";

} 

// ===================================================================================
// KONDISI 2: LAPORAN BULANAN (MATRIX TANGGAL) - TIDAK DIUBAH
// ===================================================================================
else {

    // --- SETUP PARAMETER BULANAN ---
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
    // Fallback manual jika tabel kosong
    if(empty($libur_nasional)) {
        $libur_nasional = ["$tahun-01-01", "$tahun-08-17", "$tahun-12-25"];
    }

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
                $row_curr += 2; 
            } else {
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

        // Spasi utk iterasi selanjutnya
        $row_curr += 2;
    }

    // --- FINAL SIZE KOLOM UTAMA ---
    $sheet->getColumnDimension('A')->setWidth(5);   
    $sheet->getColumnDimension('B')->setWidth(40);  
    $sheet->getColumnDimension('C')->setWidth(8);   
    $sheet->getColumnDimension('D')->setWidth(8);   

    $filename = "Rekap_Cuti_{$jenis_label}_{$nama_bulan}_{$tahun}.xlsx";
}

// ===================================================================================
// OUTPUT FILE (SAMA UNTUK KEDUA KONDISI)
// ===================================================================================
ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');
header('Expires: 0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>