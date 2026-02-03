<?php
/** @var mysqli $koneksi */

$bln = date('m');
$thn = date('Y');

// Query preview: 5 data TERBARU, status Disetujui, bulan ini
$query_preview = "SELECT p.*, u.nama_lengkap, u.nip, j.nama_jenis
                  FROM pengajuan_cuti p
                  JOIN users u ON p.id_user = u.id_user
                  JOIN jenis_cuti j ON p.id_jenis = j.id_jenis
                  WHERE p.status = 'Disetujui'
                    AND MONTH(p.tgl_mulai) = '$bln'
                    AND YEAR(p.tgl_mulai) = '$thn'
                  ORDER BY p.created_at DESC, p.id_pengajuan DESC
                  LIMIT 5";
$result_preview = mysqli_query($koneksi, $query_preview);

// Map nama bulan
$nama_bulan = [
  '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
  '04' => 'April',   '05' => 'Mei',      '06' => 'Juni',
  '07' => 'Juli',    '08' => 'Agustus',  '09' => 'September',
  '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* --- STYLE KONSISTEN PN GREEN --- */
    :root { --pn-green: #004d00; --pn-gold: #F9A825; --text-dark: #2c3e50; }
    body { font-family: 'Poppins', sans-serif !important; background-color: #f4f6f9; }
    
    /* JUDUL HALAMAN (HIJAU PN) */
    .page-header-title { 
        border-left: 5px solid var(--pn-gold); 
        padding-left: 15px; 
        color: var(--pn-green) !important; 
        font-weight: 700; 
        font-size: 1.6rem; 
    }
    
    /* Card Custom */
    .card-pn-custom { 
        border: none; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        background: #fff; 
        overflow: hidden; 
    }
    .card-header-green { 
        background-color: #1b5e20; 
        color: white; 
        padding: 15px 25px; 
        border-bottom: 4px solid var(--pn-gold); 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
    }
    
    /* Tombol Solid (Download) */
    .btn-pn-solid {
        background-color: var(--pn-green);
        color: white;
        border: 2px solid var(--pn-green);
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 15px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        width: 100%;
    }
    .btn-pn-solid:hover {
        background-color: #003800;
        color: var(--pn-gold);
        border-color: #003800;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 77, 0, 0.3);
    }

    /* Form Inputs */
    .form-control-pn {
        border-radius: 8px;
        height: 44px;
        border: 1px solid #ced4da;
        font-size: 0.95rem;
    }
    .form-control-pn:focus {
        border-color: var(--pn-green);
        box-shadow: 0 0 0 0.2rem rgba(0, 77, 0, 0.25);
    }
    label { font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }

    /* --- TABLE CUSTOM STYLE --- */
    .table-custom { width: 100%; border-collapse: separate; border-spacing: 0 5px; }
    
    .thead-pn {
        background-color: var(--pn-green);
        color: white;
    }
    .thead-pn th {
        padding: 12px 15px;
        border-bottom: 3px solid var(--pn-gold) !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        vertical-align: middle !important;
        border-top: none;
        white-space: nowrap; 
    }

    .table-custom tbody tr { background-color: white; transition: 0.2s; }
    .table-custom tbody tr:hover { background-color: #f1f8e9; transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    
    /* GLOBAL TD STYLE (Supaya Font Konsisten) */
    .table-custom td { 
        padding: 12px 15px; 
        vertical-align: middle !important;
        border-bottom: 1px solid #eee; 
        font-size: 0.9rem; /* Ukuran standar agar konsisten */
        color: #444; 
    }

    .text-pn { color: var(--pn-green) !important; }
    
    /* --- BADGE CUTI (Hijau, Pill Shape, 1 Baris) --- */
    .badge-jenis-cuti {
        background-color: #e9f5e9;
        color: var(--pn-green);
        border: 1px solid #c3e6cb;
        padding: 5px 12px;
        border-radius: 50px; 
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        display: inline-block;
        white-space: nowrap; 
    }

    .divider-soft { height: 1px; background: #e9ecef; margin: 20px 0; }
    .small-note { font-size: 12.5px; color: #6c757d; margin-top: 15px; }
</style>

<div class="container-fluid mb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <h1 class="h3 mb-0 page-header-title">Laporan Rekapitulasi Cuti</h1>
    </div>

    <div class="card card-pn-custom">

        <div class="card-header-green">
            <div>
                <i class="fas fa-file-alt mr-2"></i>
                <span class="font-weight-bold" style="font-size: 1.05rem;">Filter & Export Laporan</span>
            </div>
        </div>

        <div class="card-body p-4">

            <form id="formExport" action="pages/admin/proses_export_excel.php" method="GET" target="_blank">
                <div class="form-row align-items-end">

                    <div class="col-md-3 mb-3">
                        <label>Bulan</label>
                        <select name="bulan" id="bulan" class="form-control form-control-pn" required>
                            <?php
                            $bulan_sekarang = date('m');
                            foreach ($nama_bulan as $kode => $nama) {
                                $selected = ($kode == $bulan_sekarang) ? 'selected' : '';
                                echo "<option value='$kode' $selected>$nama</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label>Tahun</label>
                        <select name="tahun" id="tahun" class="form-control form-control-pn" required>
                            <?php
                            $tahun_mulai = 2026;
                            $tahun_akhir = $tahun_mulai + 4;
                            $tahun_sekarang = (int)date('Y');
                            for ($i = $tahun_mulai; $i <= $tahun_akhir; $i++) {
                                $selected = '';
                                if ($tahun_sekarang >= $tahun_mulai && $tahun_sekarang <= $tahun_akhir) {
                                    $selected = ($i == $tahun_sekarang) ? 'selected' : '';
                                } else {
                                    $selected = ($i == $tahun_mulai) ? 'selected' : '';
                                }
                                echo "<option value='$i' $selected>$i</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Jenis Laporan</label>
                        <select name="id_jenis" id="id_jenis" class="form-control form-control-pn">
                            <option value="1">Rekap Cuti Tahunan</option>
                            <option value="2">Rekap Cuti Sakit</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <button type="submit" class="btn-pn-solid">
                            <i class="fas fa-file-excel mr-2"></i>Download Excel
                        </button>
                    </div>

                </div>
            </form>

            <div class="divider-soft"></div>

            <div class="font-weight-bold mb-3 d-flex align-items-center" style="color:var(--pn-green); font-size:1.1rem;">
                <i class="fas fa-list mr-2"></i>Daftar Pengajuan Disetujui (5 Terbaru)
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead class="thead-pn">
                        <tr class="text-center">
                            <th style="width: 50px; min-width: 50px;">No</th>
                            
                            <th>Pegawai</th> 
                            <th width="20%">Jenis Cuti</th>
                            <th width="15%">Tanggal Mulai</th>
                            <th width="15%">Tanggal Selesai</th>
                            <th width="10%">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($result_preview && mysqli_num_rows($result_preview) > 0) {
                            while ($row = mysqli_fetch_assoc($result_preview)) {

                                // Logika hitung hari
                                $total_hari = isset($row['lama_hari']) ? (int)$row['lama_hari'] : 0;
                                if ($total_hari <= 0) {
                                    $tgl1 = new DateTime($row['tgl_mulai']);
                                    $tgl2 = new DateTime($row['tgl_selesai']);
                                    $total_hari = $tgl2->diff($tgl1)->days + 1;
                                }
                        ?>
                        <tr class="align-middle">
                            <td class="text-center text-dark"><?php echo $no; ?></td>
                            
                            <td class="text-left">
                                <div class="d-flex flex-column justify-content-center">
                                    <span class="font-weight-bold text-dark" style="font-size: 0.95rem;"><?php echo $row['nama_lengkap']; ?></span>
                                    <span class="small text-secondary">NIP: <?php echo $row['nip']; ?></span>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="badge-jenis-cuti">
                                    <?php echo $row['nama_jenis']; ?>
                                </span>
                            </td>

                            <td class="text-center text-dark">
                                <?php echo date('d-m-Y', strtotime($row['tgl_mulai'])); ?>
                            </td>

                            <td class="text-center text-dark">
                                <?php echo date('d-m-Y', strtotime($row['tgl_selesai'])); ?>
                            </td>

                            <td class="text-center">
                                <span class="font-weight-bold text-pn" style="font-size: 1rem;">
                                    <?php echo $total_hari; ?> Hari
                                </span>
                            </td>
                        </tr>
                        <?php 
                                $no++;
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-3 text-gray-300"></i><br>
                                    Belum ada data cuti disetujui bulan ini.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <div class="small-note">
                    <i class="fas fa-info-circle mr-1"></i>
                    Export akan membuka tab baru. Preview di atas hanya menampilkan <b>maks. 5 data terbaru</b> cuti <b>Disetujui</b> bulan ini.
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('formExport').addEventListener('submit', function(e){
        e.preventDefault();
        const bulanEl = document.getElementById('bulan');
        const bulanNama = bulanEl.options[bulanEl.selectedIndex].text;
        const tahun = document.getElementById('tahun').value;
        const jenisSelect = document.getElementById('id_jenis');
        const jenisText = jenisSelect.options[jenisSelect.selectedIndex].text;

        Swal.fire({
            title: 'Download Laporan?',
            html: `Anda akan mengunduh <b>${jenisText}</b><br>Bulan <b>${bulanNama}</b> Tahun <b>${tahun}</b>.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#004d00',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-download mr-1"></i> Ya, Download',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if(result.isConfirmed){
                e.target.submit(); 
            }
        });
    });
</script>