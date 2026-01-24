<?php
// --- FILE: pages/admin/input_cuti.php ---

// ==========================================
// 1. AMBIL DATA LIBUR DARI DATABASE
// ==========================================
$libur_nasional = [];
$q_libur = mysqli_query($koneksi, "SELECT tanggal FROM libur_nasional");
if ($q_libur) {
    while ($row = mysqli_fetch_assoc($q_libur)) {
        $libur_nasional[] = $row['tanggal'];
    }
}

// ==========================================
// 2. FUNGSI HITUNG HARI KERJA
// ==========================================
function hitungHariKerja($start, $end, $libur_arr) {
    $iterasi = new DateTime($start);
    $akhir   = new DateTime($end);
    $akhir->modify('+1 day'); 
    
    $interval = new DateInterval('P1D');
    $period   = new DatePeriod($iterasi, $interval, $akhir);
    
    $jumlah_hari = 0;
    foreach ($period as $dt) {
        $curr = $dt->format('Y-m-d');
        $day  = $dt->format('N'); 
        
        if ($day < 6 && !in_array($curr, $libur_arr)) {
            $jumlah_hari++;
        }
    }
    return $jumlah_hari;
}

$swal_script = "";

// --- PROSES SIMPAN DATA ---
if (isset($_POST['simpan_cuti'])) {
    $id_user      = $_POST['id_user'];
    $id_jenis     = $_POST['id_jenis']; // Mengambil ID (Angka/Kode), bukan Nama
    $tgl_mulai    = $_POST['tgl_mulai'];
    $tgl_selesai  = $_POST['tgl_selesai'];
    $alasan       = htmlspecialchars($_POST['alasan']);
    
    // Validasi Tanggal
    if ($tgl_selesai < $tgl_mulai) {
        $swal_script = "Swal.fire({ title: 'Gagal!', text: 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.', icon: 'error', confirmButtonColor: '#006837' });";
    } else {
        
        // Hitung Durasi
        $durasi = hitungHariKerja($tgl_mulai, $tgl_selesai, $libur_nasional);

        if ($durasi <= 0) {
             $swal_script = "Swal.fire({ title: 'Gagal!', text: 'Durasi cuti 0 hari kerja (terdeteksi libur/weekend).', icon: 'warning', confirmButtonColor: '#006837' });";
        } else {
            
            // CEK APAKAH INI CUTI TAHUNAN?
            // Kita perlu ambil nama jenis cuti berdasarkan ID yang dipilih untuk validasi kuota
            $q_cek_jenis = mysqli_query($koneksi, "SELECT nama_jenis FROM jenis_cuti WHERE id_jenis = '$id_jenis'");
            $d_jenis = mysqli_fetch_assoc($q_cek_jenis);
            $nama_jenis_cuti = $d_jenis['nama_jenis']; // Misal: "Cuti Tahunan"

            // Cek Saldo Cuti
            $lanjut_simpan = true;
            
            // Logika: Jika nama mengandung kata "Tahunan", cek kuota
            if (stripos($nama_jenis_cuti, 'Tahunan') !== false) {
                $cek_user = mysqli_query($koneksi, "SELECT sisa_cuti_n FROM users WHERE id_user = '$id_user'");
                $data_user = mysqli_fetch_assoc($cek_user);
                
                if ($data_user['sisa_cuti_n'] < $durasi) {
                    $lanjut_simpan = false;
                    $swal_script = "Swal.fire({ title: 'Gagal!', text: 'Sisa kuota cuti pegawai tidak mencukupi.', icon: 'error', confirmButtonColor: '#006837' });";
                }
            }

            if ($lanjut_simpan) {
                $status = 'Menunggu Konfirmasi';
                $tgl_pengajuan = date('Y-m-d');
                
                // === QUERY INSERT UPDATE ===
                // Kolom diganti menjadi `id_jenis` sesuai pesan error Foreign Key
                $query_insert = "INSERT INTO pengajuan_cuti (id_user, id_jenis, tgl_mulai, tgl_selesai, lama_hari, alasan, status, tgl_pengajuan) 
                                 VALUES ('$id_user', '$id_jenis', '$tgl_mulai', '$tgl_selesai', '$durasi', '$alasan', '$status', '$tgl_pengajuan')";

                if (mysqli_query($koneksi, $query_insert)) {
                    $swal_script = "Swal.fire({ title: 'Berhasil Input!', text: 'Data cuti tersimpan. Status: Menunggu TTD Basah.', icon: 'success', confirmButtonColor: '#006837' }).then(() => { window.location='index.php?page=input_cuti'; });";
                } else {
                    $swal_script = "Swal.fire({ title: 'Error Database!', text: '" . mysqli_error($koneksi) . "', icon: 'error', confirmButtonColor: '#006837' });";
                }
            }
        }
    }
}
?>

<style>
    :root { --main-green: #006837; --light-green: #e8f5e9; --accent-yellow: #F9A825; }
    .text-main-green { color: var(--main-green) !important; }
    .bg-main-green { background-color: var(--main-green) !important; color: white; }
    .card-custom { border-top: 4px solid var(--accent-yellow); border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .card-header-custom { background-color: white; border-bottom: 1px solid #f0f0f0; padding: 15px 20px; }
    .form-control { border-radius: 6px; padding: 10px; height: auto; }
    .form-control:focus { border-color: var(--main-green); box-shadow: 0 0 0 0.2rem rgba(0, 104, 55, 0.25); }
    .form-label-custom { font-weight: 600; font-size: 0.85rem; color: #555; text-transform: uppercase; margin-bottom: 6px; }
    .btn-custom-green { background-color: var(--main-green); border: none; color: white; padding: 10px 25px; border-radius: 50px; font-weight: 600; }
    .btn-custom-green:hover { background-color: #004e2a; transform: translateY(-2px); }
    .info-box { background-color: var(--light-green); border-left: 4px solid var(--main-green); padding: 15px; border-radius: 4px; color: var(--main-green); font-size: 0.9rem; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-main-green font-weight-bold">Input Cuti Pegawai</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-custom mb-4">
                <div class="card-header card-header-custom">
                    <h6 class="m-0 font-weight-bold text-main-green"><i class="fas fa-edit mr-2"></i> Form Input Cuti (Admin)</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        
                        <div class="form-group mb-4">
                            <label class="form-label-custom">Pilih Pegawai <span class="text-danger">*</span></label>
                            <select name="id_user" class="form-control" required>
                                <option value="">-- Cari Nama Pegawai --</option>
                                <?php
                                $q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE role='user' ORDER BY nama_lengkap ASC");
                                while ($u = mysqli_fetch_array($q_user)) {
                                    echo "<option value='$u[id_user]'>$u[nama_lengkap] (NIP: $u[nip]) - Sisa: $u[sisa_cuti_n]</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label-custom">Jenis Cuti <span class="text-danger">*</span></label>
                                    <select name="id_jenis" id="id_jenis" class="form-control" required>
                                        <option value="">-- Pilih Jenis Cuti --</option>
                                        <?php
                                        // Asumsi tabel master bernama 'jenis_cuti' dengan kolom 'id_jenis' dan 'nama_jenis'
                                        $q_jc = mysqli_query($koneksi, "SELECT * FROM jenis_cuti ORDER BY nama_jenis ASC");
                                        while ($j = mysqli_fetch_array($q_jc)) {
                                            echo "<option value='$j[id_jenis]'>$j[nama_jenis]</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                     <label class="form-label-custom">Status Pengajuan</label>
                                     <input type="text" class="form-control bg-light text-warning font-weight-bold" value="MENUNGGU KONFIRMASI" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="form-label-custom">Tanggal Mulai</label>
                                    <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="form-label-custom">Tanggal Selesai</label>
                                    <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label-custom">Durasi</label>
                                    <input type="text" id="durasi_hari" class="form-control text-center font-weight-bold bg-light" value="0" readonly>
                                    <small class="text-center d-block mt-1">Hari Kerja</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <small id="info_libur" class="text-danger font-italic" style="display:none;">
                                    * Sabtu, Minggu, dan Hari Libur (DB) tidak dihitung.
                                </small>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label-custom">Alasan Cuti</label>
                            <textarea name="alasan" class="form-control" rows="3" placeholder="Alasan cuti..." required></textarea>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted font-italic">*Kuota belum berkurang sebelum di-Approve.</small>
                            <div>
                                <button type="reset" class="btn btn-light border mr-2" style="border-radius: 50px;">Reset</button>
                                <button type="submit" name="simpan_cuti" class="btn btn-custom-green shadow"><i class="fas fa-save mr-2"></i> Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-custom mb-4">
                <div class="card-header card-header-custom">
                    <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-info-circle mr-2"></i> Aturan Cuti</h6>
                </div>
                <div class="card-body">
                    <div class="info-box mb-3">
                        <strong>Perhatian!</strong><br>Admin menginput sebagai "Pengajuan". Pegawai wajib TTD Basah.
                    </div>
                    <ul class="pl-3 text-secondary small mb-4">
                        <li><span class="text-danger font-weight-bold">Sabtu, Minggu & Libur Nasional</span> tidak dihitung.</li>
                        <li>Kuota berkurang setelah Status <b>DISETUJUI</b>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const holidays = <?php echo json_encode($libur_nasional); ?>;
    const tglMulai = document.getElementById('tgl_mulai');
    const tglSelesai = document.getElementById('tgl_selesai');
    const durasiTxt = document.getElementById('durasi_hari');
    const infoLibur = document.getElementById('info_libur');

    function isHoliday(dateStr) { return holidays.includes(dateStr); }

    function hitungDurasiKerja() {
        if (tglMulai.value && tglSelesai.value) {
            let start = new Date(tglMulai.value);
            let end = new Date(tglSelesai.value);
            let count = 0;
            let loop = new Date(start);

            if (end < start) { durasiTxt.value = "Err"; return; }

            while (loop <= end) {
                let dayOfWeek = loop.getDay();
                let year = loop.getFullYear();
                let month = String(loop.getMonth() + 1).padStart(2, '0');
                let day = String(loop.getDate()).padStart(2, '0');
                let dateString = `${year}-${month}-${day}`;

                if (dayOfWeek !== 0 && dayOfWeek !== 6 && !isHoliday(dateString)) { count++; }
                loop.setDate(loop.getDate() + 1);
            }
            durasiTxt.value = count;
            
            let totalDays = (end - start) / (1000 * 60 * 60 * 24) + 1;
            if(count < totalDays) { infoLibur.style.display = 'block'; } 
            else { infoLibur.style.display = 'none'; }
        }
    }
    tglMulai.addEventListener('change', hitungDurasiKerja);
    tglSelesai.addEventListener('change', hitungDurasiKerja);
</script>

<?php if (!empty($swal_script)) echo "<script>$swal_script</script>"; ?>