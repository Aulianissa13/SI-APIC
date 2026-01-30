<?php
// --- FILE: pages/admin/cuti/input_cuti.php ---

// 1. DATA PENDUKUNG (Libur & Users)
$libur_nasional = [];
$q_libur = mysqli_query($koneksi, "SELECT tanggal FROM libur_nasional");
while ($row = mysqli_fetch_assoc($q_libur)) { $libur_nasional[] = $row['tanggal']; }

// Ambil data User & Atasan untuk Datalist
$data_users = [];
$q_u = mysqli_query($koneksi, "SELECT id_user, nama_lengkap, nip, sisa_cuti_n, sisa_cuti_n1 FROM users ORDER BY nama_lengkap ASC");
while ($u = mysqli_fetch_assoc($q_u)) {
    $data_users[] = $u;
}

// --- LOGIKA GENERATE NO SURAT (TAMPIL DI AWAL) ---
$thn_now = date('Y');
$bln_now = date('n');
$romawi  = [1=>"I", 2=>"II", 3=>"III", 4=>"IV", 5=>"V", 6=>"VI", 7=>"VII", 8=>"VIII", 9=>"IX", 10=>"X", 11=>"XI", 12=>"XII"];

// Cari nomor terakhir di DB
$q_last = mysqli_query($koneksi, "SELECT nomor_surat FROM pengajuan_cuti WHERE nomor_surat LIKE '%/$thn_now' ORDER BY id_pengajuan DESC LIMIT 1");
$no_urut = 1; // Default

if (mysqli_num_rows($q_last) > 0) {
    $d_last = mysqli_fetch_assoc($q_last);
    $parts  = explode('/', $d_last['nomor_surat']);
    if (isset($parts[0]) && is_numeric($parts[0])) {
        $no_urut = intval($parts[0]) + 1;
    }
}
$nomor_surat_auto = sprintf("%03d", $no_urut)."/KPN/W13.U1/KP.05.3/".$romawi[$bln_now]."/".$thn_now;
// -------------------------------------------------------


// Fungsi Hitung Hari Kerja (PHP Side)
function hitungHariKerja($start, $end, $libur_arr) {
    $iterasi = new DateTime($start);
    $akhir   = new DateTime($end);
    $akhir->modify('+1 day'); 
    $period  = new DatePeriod($iterasi, new DateInterval('P1D'), $akhir);
    $jumlah  = 0;
    foreach ($period as $dt) {
        $curr = $dt->format('Y-m-d');
        $day  = $dt->format('N'); 
        if ($day < 6 && !in_array($curr, $libur_arr)) { $jumlah++; }
    }
    return $jumlah;
}

$swal_script = "";

// 2. PROSES PENYIMPANAN
if (isset($_POST['simpan_cuti'])) {
    
    $id_user   = $_POST['id_user_hidden']; 
    $id_atasan = $_POST['id_atasan_hidden']; 
    
    if (empty($id_user)) {
        $swal_script = "Swal.fire({ title: 'Pegawai Tidak Dikenal!', text: 'Mohon pilih nama pegawai dari saran yang muncul.', icon: 'error' });";
    } elseif (empty($id_atasan)) {
        $swal_script = "Swal.fire({ title: 'Atasan Belum Dipilih!', text: 'Mohon pilih atasan penandatangan.', icon: 'warning' });";
    } else {
        $id_jenis     = $_POST['id_jenis']; 
        $tgl_mulai    = $_POST['tgl_mulai'];
        $tgl_selesai  = $_POST['tgl_selesai'];
        $alamat_cuti  = htmlspecialchars($_POST['alamat']); 
        $alasan       = htmlspecialchars($_POST['alasan']);
        $nomor_surat  = htmlspecialchars($_POST['nomor_surat']); 

        if ($tgl_selesai < $tgl_mulai) {
            $swal_script = "Swal.fire({ title: 'Tanggal Salah!', text: 'Tanggal selesai lebih kecil dari mulai.', icon: 'error' });";
        } else {
            $durasi = hitungHariKerja($tgl_mulai, $tgl_selesai, $libur_nasional);
            
            if ($durasi <= 0) {
                $swal_script = "Swal.fire({ title: 'Durasi Nol!', text: 'Hari kerja 0 (mungkin hari libur semua).', icon: 'warning' });";
            } else {
                // LOGIKA POTONG SALDO
                $q_cek_jenis = mysqli_query($koneksi, "SELECT nama_jenis FROM jenis_cuti WHERE id_jenis = '$id_jenis'");
                $d_jenis = mysqli_fetch_assoc($q_cek_jenis);
                $nama_jenis_cuti = $d_jenis['nama_jenis']; 
                
                $lanjut_simpan = true;
                $potong_n1 = 0; $potong_n = 0;

                $cek_user = mysqli_query($koneksi, "SELECT sisa_cuti_n, sisa_cuti_n1, kuota_cuti_sakit FROM users WHERE id_user = '$id_user'");
                $data_user = mysqli_fetch_assoc($cek_user);

                if (stripos($nama_jenis_cuti, 'Tahunan') !== false) {
                    $sisa_n = $data_user['sisa_cuti_n']; $sisa_n1 = $data_user['sisa_cuti_n1'];
                    $total_cuti = $sisa_n + $sisa_n1;

                    if ($total_cuti < $durasi) {
                        $lanjut_simpan = false;
                        $swal_script = "Swal.fire({ title: 'Kuota Habis!', text: 'Sisa cuti pegawai ini tidak mencukupi.', icon: 'error' });";
                    } else {
                        if ($durasi <= $sisa_n1) { 
                            $potong_n1 = $durasi; 
                        } else { 
                            $potong_n1 = $sisa_n1; 
                            $potong_n = $durasi - $sisa_n1; 
                        }
                        
                        $sisa_n_baru = $sisa_n - $potong_n;
                        $sisa_n1_baru = $sisa_n1 - $potong_n1;
                        mysqli_query($koneksi, "UPDATE users SET sisa_cuti_n='$sisa_n_baru', sisa_cuti_n1='$sisa_n1_baru' WHERE id_user='$id_user'");
                    }
                }
                else if (stripos($nama_jenis_cuti, 'Sakit') !== false) {
                   $sisa_sakit = $data_user['kuota_cuti_sakit'] - $durasi;
                   if($sisa_sakit < 0) $sisa_sakit = 0;
                   mysqli_query($koneksi, "UPDATE users SET kuota_cuti_sakit='$sisa_sakit' WHERE id_user='$id_user'");
                }

                if ($lanjut_simpan) {
                    $status = 'disetujui';
                    $tgl_pengajuan = date('Y-m-d');
                    
                    $query_insert = "INSERT INTO pengajuan_cuti 
                        (id_user, id_atasan, id_jenis, tgl_mulai, tgl_selesai, lama_hari, dipotong_n, dipotong_n1, alasan, alamat_cuti, status, tgl_pengajuan, nomor_surat) 
                        VALUES 
                        ('$id_user', '$id_atasan', '$id_jenis', '$tgl_mulai', '$tgl_selesai', '$durasi', '$potong_n', '$potong_n1', '$alasan', '$alamat_cuti', '$status', '$tgl_pengajuan', '$nomor_surat')";

                    if (mysqli_query($koneksi, $query_insert)) {
                        // REVISI: Mengarahkan ke validasi_cuti
                        $swal_script = "Swal.fire({ title: 'Berhasil!', text: 'Cuti pegawai berhasil diinput & kuota terpotong.', icon: 'success' }).then(() => { window.location='index.php?page=validasi_cuti'; });";
                    } else {
                        $swal_script = "Swal.fire({ title: 'Error!', text: 'Database Error: ".mysqli_error($koneksi)."', icon: 'error' });";
                    }
                }
            }
        }
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root { --pn-green: #004d00; --pn-gold: #FFD700; --bg-light: #f8f9fc; }
    body { font-family: 'Poppins', sans-serif !important; background-color: var(--bg-light); }
    .card-pn { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); overflow: hidden; background: white; }
    .card-header-pn { background: linear-gradient(135deg, var(--pn-green) 0%, #006400 100%); color: white; border-bottom: 4px solid var(--pn-gold); padding: 15px 20px; }
    .page-title-pn { font-weight: 700; border-left: 5px solid var(--pn-gold); padding-left: 15px; color: var(--pn-green) !important; margin-bottom: 20px; }
    .form-label { color: var(--pn-green); font-weight: 600; margin-bottom: 5px; font-size: 0.9rem; }
    .form-control, .form-select { border-radius: 8px; border: 1px solid #ced4da; height: 45px; }
    .form-control:focus { border-color: var(--pn-green); box-shadow: 0 0 0 0.2rem rgba(0, 77, 0, 0.25); }
    .btn-pn { background-color: var(--pn-green); color: white; border-radius: 8px; font-weight: 600; padding: 10px 25px; border: none; transition: 0.3s; }
    .btn-pn:hover { background-color: #003300; color: var(--pn-gold); }
    .input-durasi { background-color: rgba(255, 215, 0, 0.1) !important; color: var(--pn-green) !important; border-color: var(--pn-gold) !important; font-weight: bold; text-align: center; }
</style>

<div class="container-fluid mb-5">
    <div class="d-flex align-items-center justify-content-between mt-4 mb-4">
        <h1 class="h3 page-title-pn">Input Cuti (Mode Admin)</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-pn mb-4">
                <div class="card-header-pn">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-user-edit mr-2"></i>Formulir Input Admin</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" autocomplete="off">
                        
                        <div class="form-group mb-4">
                            <label class="form-label">Pilih Pegawai <span class="text-danger">*</span></label>
                            <input class="form-control" list="list_pegawai" id="input_pegawai" placeholder="Ketik nama pegawai..." required>
                            <datalist id="list_pegawai">
                                <?php foreach($data_users as $u) { 
                                    echo "<option data-id='$u[id_user]' value='$u[nama_lengkap] (NIP: $u[nip]) | Sisa N: $u[sisa_cuti_n]'>"; 
                                } ?>
                            </datalist>
                            <input type="hidden" name="id_user_hidden" id="id_user_hidden">
                            <small class="text-muted ml-1"><i>*Otomatis cari saat diketik.</i></small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Cuti <span class="text-danger">*</span></label>
                                <select name="id_jenis" class="form-control" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <?php
                                    $q_jc = mysqli_query($koneksi, "SELECT * FROM jenis_cuti ORDER BY nama_jenis ASC");
                                    while ($j = mysqli_fetch_array($q_jc)) { echo "<option value='$j[id_jenis]'>$j[nama_jenis]</option>"; }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nomor Surat <small class="text-muted fw-light">(Auto Generated)</small></label>
                                <input type="text" name="nomor_surat" class="form-control" value="<?= $nomor_surat_auto ?>" readonly>
                            </div>
                        </div>

                        <div class="row align-items-end">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label d-block text-center">Hari</label>
                                <input type="text" id="durasi_hari" class="form-control input-durasi" value="0" readonly>
                            </div>
                        </div>
                        
                        <div id="info_libur" class="alert alert-warning border-left-warning shadow-sm small py-2 mb-3" style="display:none; border-left: 5px solid var(--pn-gold);">
                            <i class="fas fa-exclamation-circle mr-1"></i> Hari libur tidak dihitung dalam durasi.
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Alamat Selama Cuti</label>
                            <input type="text" name="alamat" class="form-control" placeholder="Alamat lengkap..." required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Alasan Cuti</label>
                            <textarea name="alasan" class="form-control" rows="2" placeholder="Keperluan..." required></textarea>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">Atasan Langsung (Tanda Tangan) <span class="text-danger">*</span></label>
                            <input class="form-control" list="list_atasan" id="input_atasan" placeholder="Ketik nama atasan..." required>
                            <datalist id="list_atasan">
                                <?php foreach($data_users as $u) { 
                                    echo "<option data-id='$u[id_user]' value='$u[nama_lengkap] (NIP: $u[nip])'>"; 
                                } ?>
                            </datalist>
                            <input type="hidden" name="id_atasan_hidden" id="id_atasan_hidden">
                        </div>

                        <div class="d-flex justify-content-end pt-2 border-top">
                            <a href="?page=validasi_cuti" class="btn btn-light mr-2 border">Batal</a>
                            <button type="submit" name="simpan_cuti" class="btn btn-pn"><i class="fas fa-save mr-2"></i> Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-pn mb-4">
                <div class="card-header-pn"><h6 class="m-0 font-weight-bold">Ketentuan Admin</h6></div>
                <div class="card-body small text-secondary">
                    <ul class="pl-3 mb-0" style="line-height:1.8">
                        <li><b>Pencarian Nama:</b> Cukup ketik nama pegawai/atasan, lalu klik opsi yang muncul.</li>
                        <li><b>Otomatis Potong:</b> Stok cuti pegawai yang dipilih akan langsung berkurang.</li>
                        <li><b>Hitungan Hari:</b> Sabtu, Minggu & Tanggal Merah otomatis dilewati.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- LOGIC 1: AUTOCOMPLETE ---
    function setupAutocomplete(inputId, listId, hiddenId) {
        const inputEl = document.getElementById(inputId);
        const hiddenEl = document.getElementById(hiddenId);
        
        inputEl.addEventListener('input', function(e) {
            var inputVal = this.value;
            var listOptions = document.querySelectorAll('#' + listId + ' option');
            hiddenEl.value = ""; 

            for (var i = 0; i < listOptions.length; i++) {
                if (listOptions[i].value === inputVal) {
                    hiddenEl.value = listOptions[i].getAttribute('data-id');
                    break;
                }
            }
        });
    }

    setupAutocomplete('input_pegawai', 'list_pegawai', 'id_user_hidden');
    setupAutocomplete('input_atasan', 'list_atasan', 'id_atasan_hidden');

    // --- LOGIC 2: HITUNG HARI KERJA ---
    const holidays = <?php echo json_encode($libur_nasional); ?>;
    const tglMulai = document.getElementById('tgl_mulai');
    const tglSelesai = document.getElementById('tgl_selesai');
    const durasiTxt = document.getElementById('durasi_hari');
    const infoLibur = document.getElementById('info_libur');

    function hitung() {
        if (tglMulai.value && tglSelesai.value) {
            let start = new Date(tglMulai.value);
            let end = new Date(tglSelesai.value);
            let count = 0;
            let loop = new Date(start);

            if (end < start) { durasiTxt.value = "Err"; return; }

            while (loop <= end) {
                let d = loop.getDay(); 
                let dateStr = loop.toISOString().split('T')[0]; 
                
                if (d !== 0 && d !== 6 && !holidays.includes(dateStr)) { 
                    count++; 
                }
                loop.setDate(loop.getDate() + 1);
            }
            durasiTxt.value = count;
            
            let totalDays = (end - start) / (1000 * 60 * 60 * 24) + 1;
            infoLibur.style.display = (count < totalDays) ? 'block' : 'none';
        }
    }

    tglMulai.addEventListener('change', hitung);
    tglSelesai.addEventListener('change', hitung);
</script>

<?php if (!empty($swal_script)) echo "<script>$swal_script</script>"; ?>