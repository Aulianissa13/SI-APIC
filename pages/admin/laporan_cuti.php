<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 border-left-primary pl-3">Laporan Cuti Pegawai</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
            </div>
            <div class="card-body">
                <form action="pages/admin/cetak_laporan.php" method="GET" target="_blank">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="tgl_awal" class="form-control" value="<?php echo date('Y-m-01'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="tgl_akhir" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Status Pengajuan</label>
                        <select name="status" class="form-control">
                            <option value="Semua">Semua Status</option>
                            <option value="Disetujui" selected>Hanya Disetujui</option>
                            <option value="Ditolak">Ditolak</option>
                            <option value="Diajukan">Menunggu Konfirmasi</option>
                        </select>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">Informasi</h6>
            </div>
            <div class="card-body">
                <p>Fitur ini digunakan untuk mencetak rekapitulasi pengajuan cuti pegawai dalam periode tertentu.</p>
                <ul class="small text-muted pl-4">
                    <li>Pilih <b>Tanggal Awal</b> dan <b>Tanggal Akhir</b>.</li>
                    <li>Pilih <b>Status</b> (Disarankan pilih 'Hanya Disetujui' untuk laporan resmi).</li>
                    <li>Klik tombol <b>Cetak</b>, maka akan terbuka tab baru siap print/simpan PDF.</li>
                </ul>
            </div>
        </div>
    </div>
</div>