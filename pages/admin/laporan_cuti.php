<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Laporan Rekapitulasi Cuti</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-filter mr-2"></i>Filter & Export Data
            </h6>
        </div>
        <div class="card-body">
            
            <form action="pages/admin/proses_export_excel.php" method="GET" target="_blank">
                
                <div class="form-row align-items-end">
                    
                    <div class="col-md-3 mb-3">
                        <label for="bulan">Bulan</label>
                        <select name="bulan" id="bulan" class="form-control" required>
                            <?php
                            $bulan_sekarang = date('m');
                            $nama_bulan = [
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                            ];
                            foreach ($nama_bulan as $kode => $nama) {
                                $selected = ($kode == $bulan_sekarang) ? 'selected' : '';
                                echo "<option value='$kode' $selected>$nama</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="tahun">Tahun</label>
                        <select name="tahun" id="tahun" class="form-control" required>
                            <?php
                            $tahun_sekarang = date('Y');
                            // Tampilkan 5 tahun ke belakang & 1 tahun ke depan
                            for ($i = $tahun_sekarang - 5; $i <= $tahun_sekarang + 1; $i++) {
                                $selected = ($i == $tahun_sekarang) ? 'selected' : '';
                                echo "<option value='$i' $selected>$i</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="id_jenis">Jenis Laporan</label>
                        <select name="id_jenis" id="id_jenis" class="form-control">
                            <option value="1">Rekap Cuti Tahunan</option>
                            <option value="2">Rekap Cuti Sakit</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-file-excel mr-2"></i>Download Excel (Rekap)
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pengajuan Cuti (Disetujui) Bulan Ini</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Pegawai</th>
                            <th>Jenis Cuti</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Total Hari</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil data bulan ini untuk preview di layar (bukan Excel)
                        $bln = date('m');
                        $thn = date('Y');

                        $query = "SELECT p.*, u.nama_lengkap, j.nama_jenis 
                                  FROM pengajuan_cuti p
                                  JOIN users u ON p.id_user = u.id_user
                                  JOIN jenis_cuti j ON p.id_jenis = j.id_jenis
                                  WHERE p.status = 'Disetujui' 
                                  AND MONTH(p.tgl_mulai) = '$bln' 
                                  AND YEAR(p.tgl_mulai) = '$thn'
                                  ORDER BY p.tgl_mulai DESC";
                        
                        $result = mysqli_query($koneksi, $query);
                        $no = 1;

                        if(mysqli_num_rows($result) > 0){
                            while($row = mysqli_fetch_assoc($result)){
                                // Hitung durasi
                                $tgl1 = new DateTime($row['tgl_mulai']);
                                $tgl2 = new DateTime($row['tgl_selesai']);
                                $jarak = $tgl2->diff($tgl1);
                                $total_hari = $jarak->d + 1;

                                echo "<tr>";
                                echo "<td>".$no++."</td>";
                                echo "<td>".$row['nama_lengkap']."</td>";
                                echo "<td><span class='badge badge-info'>".$row['nama_jenis']."</span></td>";
                                echo "<td>".date('d-m-Y', strtotime($row['tgl_mulai']))."</td>";
                                echo "<td>".date('d-m-Y', strtotime($row['tgl_selesai']))."</td>";
                                echo "<td>".$total_hari." Hari</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>Belum ada data cuti disetujui bulan ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted">* Tabel di atas hanya preview data bulan ini. Untuk laporan lengkap dengan format matriks tanggal 1-31, silakan gunakan tombol <b>Download Excel</b> di atas.</small>
        </div>
    </div>

</div>