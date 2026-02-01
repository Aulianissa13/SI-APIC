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

// Map nama bulan (buat dropdown + SweetAlert tampil nama bulan)
$nama_bulan = [
  '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
  '04' => 'April',   '05' => 'Mei',      '06' => 'Juni',
  '07' => 'Juli',    '08' => 'Agustus',  '09' => 'September',
  '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { --pn-green:#004d00; --pn-gold:#FFD700; --bg-light:#f8f9fc; }

    .page-title-pn{
        font-weight:700;
        border-left:5px solid var(--pn-gold);
        padding-left:15px;
        color:var(--pn-green)!important;
    }
    .card-pn{
        border:none;
        border-radius:15px;
        box-shadow:0 5px 15px rgba(0,0,0,0.1);
        overflow:hidden;
        background:#fff;
    }
    .card-header-pn{
        background:linear-gradient(135deg,var(--pn-green) 0%,#006400 100%);
        color:#fff;
        border-bottom:4px solid var(--pn-gold);
        padding:14px 18px;
    }
    .thead-pn{ background:var(--pn-green); color:#fff; }
    .badge-pill-soft{
        border-radius:999px;
        padding:7px 12px;
        font-weight:700;
        font-size: 13px;
    }

    /* FORM lebih rapih & compact */
    .form-control{ border-radius:12px; height:44px; }
    .btn{ border-radius:12px; height:44px; font-weight:700; }

    .divider-soft{ height:1px; background:#e9ecef; margin:12px 0 14px; }

    /* TABLE: diperbesar lagi */
    .table-preview{ font-size: 15px; }
    .table-preview th{
        padding: 10px 8px;
        font-size: 15px;
        letter-spacing: .2px;
        white-space: nowrap;
    }
    .table-preview td{
        padding: 10px 8px;
        vertical-align: middle;
    }
    .emp-name{
        font-weight: 800;
        color: #222;
        font-size: 15.5px;
        line-height: 1.2;
    }
    .emp-nip{
        font-size: 12.5px;
        color: #6c757d;
        margin-top: 2px;
        display: block;
    }
    .table-hover tbody tr:hover{ background:#f7fbf7; }

    .small-note{
        font-size: 12.5px;
        color: #6c757d;
        margin-top: 8px;
    }

    /* bikin footer turun dikit (biar scroll sedikit baru kelihatan) */
    .footer-spacer{ height: 5px; } /* bisa kecilin/besarin kalau perlu */
</style>

<div class="container-fluid mb-3">

    <div class="d-sm-flex align-items-center justify-content-between mb-3 mt-4">
        <h1 class="h3 mb-0 text-gray-800 page-title-pn">Laporan Rekapitulasi Cuti</h1>
    </div>

    <!-- 1 KOTAK: FILTER + EXPORT + PREVIEW -->
    <div class="card card-pn">

        <div class="card-header-pn">
            <i class="fas fa-filter mr-2"></i>
            <h6 class="d-inline m-0 font-weight-bold text-white">Filter, Export & Preview</h6>
        </div>

        <div class="card-body">

            <!-- FORM EXPORT -->
            <form id="formExport" action="pages/admin/proses_export_excel.php" method="GET" target="_blank">
                <div class="form-row align-items-end">

                    <div class="col-md-3 mb-2">
                        <label class="font-weight-bold text-dark">Bulan</label>
                        <select name="bulan" id="bulan" class="form-control" required>
                            <?php
                            $bulan_sekarang = date('m');
                            foreach ($nama_bulan as $kode => $nama) {
                                $selected = ($kode == $bulan_sekarang) ? 'selected' : '';
                                echo "<option value='$kode' $selected>$nama</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="font-weight-bold text-dark">Tahun</label>
                        <select name="tahun" id="tahun" class="form-control" required>
                            <?php
                            // 5 tahun mulai 2026 (2026-2030)
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

                    <div class="col-md-3 mb-2">
                        <label class="font-weight-bold text-dark">Jenis Laporan</label>
                        <select name="id_jenis" id="id_jenis" class="form-control">
                            <option value="1">Rekap Cuti Tahunan</option>
                            <option value="2">Rekap Cuti Sakit</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-2">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-file-excel mr-2"></i>Download Excel (Rekap)
                        </button>
                    </div>

                </div>
            </form>

            <div class="divider-soft"></div>

            <!-- PREVIEW TABLE -->
            <div class="font-weight-bold" style="color:var(--pn-green); font-size:15px; margin-bottom:10px;">
                <i class="fas fa-list mr-2"></i>Daftar Pengajuan Cuti (Disetujui) Bulan Ini — 5 Terbaru
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-preview mb-0" width="100%" cellspacing="0">
                    <thead class="thead-pn">
                        <tr class="text-center">
                            <th width="5%">No</th>
                            <th>Nama Pegawai</th>
                            <th width="18%">Jenis Cuti</th>
                            <th width="16%">Tanggal Mulai</th>
                            <th width="16%">Tanggal Selesai</th>
                            <th width="12%">Total Hari</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($result_preview && mysqli_num_rows($result_preview) > 0) {
                            while ($row = mysqli_fetch_assoc($result_preview)) {

                                // utamakan lama_hari dari DB (sudah sesuai aturan kerja/libur)
                                $total_hari = isset($row['lama_hari']) ? (int)$row['lama_hari'] : 0;
                                if ($total_hari <= 0) {
                                    $tgl1 = new DateTime($row['tgl_mulai']);
                                    $tgl2 = new DateTime($row['tgl_selesai']);
                                    $total_hari = $tgl2->diff($tgl1)->days + 1;
                                }

                                echo "<tr>";
                                echo "<td class='text-center font-weight-bold'>{$no}</td>";
                                echo "<td>
                                        <div class='font-weight-bold text-dark'>{$row['nama_lengkap']}</div>
                                        <small class='text-secondary font-weight-bold'>NIP: {$row['nip']}</small>
                                      </td>";
                                echo "<td class='text-center'>
                                        <span class='badge badge-info badge-pill-soft'>{$row['nama_jenis']}</span>
                                      </td>";
                                echo "<td class='text-center'>".date('d-m-Y', strtotime($row['tgl_mulai']))."</td>";
                                echo "<td class='text-center'>".date('d-m-Y', strtotime($row['tgl_selesai']))."</td>";
                                echo "<td class='text-center font-weight-bold'>{$total_hari} Hari</td>";
                                echo "</tr>";
                                $no++;
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>Belum ada data cuti disetujui bulan ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <div class="small-note">
                    * Export akan membuka tab baru. Preview di atas hanya menampilkan <b>maks. 5 data terbaru</b> cuti <b>Disetujui</b> bulan ini.
                </div>
            </div>

        </div>
    </div>

    <div class="footer-spacer"></div>
</div>

<script>
    // Konfirmasi SweetAlert sebelum download (bulan pakai NAMA, bukan "01")
    document.getElementById('formExport').addEventListener('submit', function(e){
        e.preventDefault();

        const bulanEl = document.getElementById('bulan');
        const bulanNama = bulanEl.options[bulanEl.selectedIndex].text; // <-- nama bulan

        const tahun = document.getElementById('tahun').value;

        const jenisSelect = document.getElementById('id_jenis');
        const jenisText = jenisSelect.options[jenisSelect.selectedIndex].text;

        Swal.fire({
            title: 'Download laporan?',
            html: `Anda akan mengunduh <b>${jenisText}</b><br>Bulan <b>${bulanNama}</b> Tahun <b>${tahun}</b>.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#004d00',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Download',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if(result.isConfirmed){
                e.target.submit(); // lanjut download (tab baru)
            }
        });
    });
</script>
