<?php
/** @var mysqli $koneksi */

// --- 1. AMBIL DATA HARI LIBUR ---
$libur_nasional = [];
$q_libur = mysqli_query($koneksi, "SELECT tanggal FROM libur_nasional");
while ($row = mysqli_fetch_assoc($q_libur)) { $libur_nasional[] = $row['tanggal']; }

// --- 2. AMBIL DATA PEGAWAI & ATASAN ---
$list_pegawai = [];
$list_atasan  = [];
$q_u = mysqli_query($koneksi, "SELECT id_user, nama_lengkap, nip, sisa_cuti_n, sisa_cuti_n1, is_atasan FROM users WHERE status_akun='aktif' ORDER BY nama_lengkap ASC");
while ($u = mysqli_fetch_assoc($q_u)) {
    $list_pegawai[] = $u;
    if ($u['is_atasan'] == '1') { $list_atasan[] = $u; }
}

// --- 3. AMBIL DATA INSTANSI (UNTUK NAMA KETUA/WAKIL) ---
$q_instansi = mysqli_query($koneksi, "SELECT * FROM tbl_setting_instansi LIMIT 1");
$instansi   = mysqli_fetch_assoc($q_instansi);

// --- 4. GENERATE NOMOR SURAT OTOMATIS ---
$thn_now = date('Y');
$bln_now = date('n');
$romawi  = [1=>"I", 2=>"II", 3=>"III", 4=>"IV", 5=>"V", 6=>"VI", 7=>"VII", 8=>"VIII", 9=>"IX", 10=>"X", 11=>"XI", 12=>"XII"];
$q_last = mysqli_query($koneksi, "SELECT nomor_surat FROM pengajuan_cuti WHERE nomor_surat LIKE '%/$thn_now' ORDER BY id_pengajuan DESC LIMIT 1");
$no_urut = 1; 
if (mysqli_num_rows($q_last) > 0) {
    $d_last = mysqli_fetch_assoc($q_last);
    $parts  = explode('/', $d_last['nomor_surat']);
    if (isset($parts[0]) && is_numeric($parts[0])) { $no_urut = intval($parts[0]) + 1; }
}
$nomor_surat_auto = sprintf("%03d", $no_urut)."/KPN/W13.U1/KP.05.3/".$romawi[$bln_now]."/".$thn_now;

// --- FUNGSI HITUNG HARI KERJA ---
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

// --- PROSES SIMPAN DATA ---
if (isset($_POST['simpan_cuti'])) {
    $id_user   = $_POST['id_user_hidden']; 
    // Mengambil langsung dari select name="id_atasan_hidden"
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
        $ttd_pejabat  = $_POST['ttd_pejabat']; 

        if ($tgl_selesai < $tgl_mulai) {
            $swal_script = "Swal.fire({ title: 'Tanggal Salah!', text: 'Tanggal selesai lebih kecil dari mulai.', icon: 'error' });";
        } else {
            $cek_bentrok = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_user = '$id_user' AND status != 'ditolak' AND ((tgl_mulai <= '$tgl_selesai' AND tgl_selesai >= '$tgl_mulai'))");
            if(mysqli_num_rows($cek_bentrok) > 0){
                $data_bentrok = mysqli_fetch_assoc($cek_bentrok);
                $swal_script = "Swal.fire({ title: 'Tanggal Bentrok!', text: 'Pegawai ini sudah ada pengajuan pada tanggal tersebut.', icon: 'warning' });";
            } else {
                $durasi = hitungHariKerja($tgl_mulai, $tgl_selesai, $libur_nasional);
                if ($durasi <= 0) {
                    $swal_script = "Swal.fire({ title: 'Durasi Nol!', text: 'Hari kerja 0.', icon: 'warning' });";
                } else {
                    $q_cek_jenis = mysqli_query($koneksi, "SELECT nama_jenis FROM jenis_cuti WHERE id_jenis = '$id_jenis'");
                    $d_jenis = mysqli_fetch_assoc($q_cek_jenis);
                    $nama_jenis_cuti = $d_jenis['nama_jenis']; 
                    
                    $lanjut_simpan = true; $potong_n1 = 0; $potong_n = 0;
                    $cek_user = mysqli_query($koneksi, "SELECT sisa_cuti_n, sisa_cuti_n1, kuota_cuti_sakit FROM users WHERE id_user = '$id_user'");
                    $data_user = mysqli_fetch_assoc($cek_user);

                    if (stripos($nama_jenis_cuti, 'Tahunan') !== false) {
                        $sisa_n = $data_user['sisa_cuti_n']; $sisa_n1 = $data_user['sisa_cuti_n1'];
                        if (($sisa_n + $sisa_n1) < $durasi) {
                            $lanjut_simpan = false;
                            $swal_script = "Swal.fire({ title: 'Kuota Habis!', text: 'Sisa cuti tidak mencukupi.', icon: 'error' });";
                        } else {
                            if ($durasi <= $sisa_n1) { $potong_n1 = $durasi; } 
                            else { $potong_n1 = $sisa_n1; $potong_n = $durasi - $sisa_n1; }
                            mysqli_query($koneksi, "UPDATE users SET sisa_cuti_n=sisa_cuti_n-$potong_n, sisa_cuti_n1=sisa_cuti_n1-$potong_n1 WHERE id_user='$id_user'");
                        }
                    } else if (stripos($nama_jenis_cuti, 'Sakit') !== false) {
                        $sisa_sakit = $data_user['kuota_cuti_sakit'] - $durasi;
                        if($sisa_sakit < 0) $sisa_sakit = 0;
                        mysqli_query($koneksi, "UPDATE users SET kuota_cuti_sakit='$sisa_sakit' WHERE id_user='$id_user'");
                    }

                    if ($lanjut_simpan) {
                        $query_insert = "INSERT INTO pengajuan_cuti (id_user, id_atasan, id_jenis, tgl_mulai, tgl_selesai, lama_hari, dipotong_n, dipotong_n1, alasan, alamat_cuti, status, tgl_pengajuan, nomor_surat, ttd_pejabat) VALUES ('$id_user', '$id_atasan', '$id_jenis', '$tgl_mulai', '$tgl_selesai', '$durasi', '$potong_n', '$potong_n1', '$alasan', '$alamat_cuti', 'disetujui', '".date('Y-m-d')."', '$nomor_surat', '$ttd_pejabat')";
                        
                        if (mysqli_query($koneksi, $query_insert)) {
                            $swal_script = "Swal.fire({ title: 'Berhasil!', text: 'Data disimpan.', icon: 'success' }).then(() => { window.location='index.php?page=validasi_cuti'; });";
                        } else {
                            $swal_script = "Swal.fire({ title: 'Error!', text: 'DB Error', icon: 'error' });";
                        }
                    }
                }
            }
        }
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root { 
        --pn-green: #004d00; 
        --pn-gold: #F9A825; 
        --text-color: #333;
        --border-color: #d1d3e2;
        --input-bg: #fff;
    }

    body, input, select, textarea, button, .form-control { 
        font-family: 'Poppins', sans-serif !important; 
    }

    .card-pn-modern { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); background: #fff; }
    .card-header-green { background: linear-gradient(135deg, #004d00 0%, #003300 100%); color: white; padding: 18px 25px; border-bottom: 4px solid var(--pn-gold); border-radius: 12px 12px 0 0; }
    .page-header-title { border-left: 5px solid var(--pn-gold); padding-left: 15px; color: var(--pn-green); font-weight: 700; font-size: 1.5rem; }

    .form-label-std { 
        font-size: 14px; 
        font-weight: 500; 
        color: #555; 
        margin-bottom: 8px; 
        display: block; 
    }

    .input-wrapper { 
        display: flex; 
        align-items: center; 
        border: 1px solid var(--border-color); 
        border-radius: 8px; 
        background-color: var(--input-bg); 
        height: 48px; /* Tinggi seragam */
        transition: all 0.2s ease-in-out;
        padding-left: 12px;
    }
    .input-wrapper:focus-within { 
        border-color: var(--pn-green); 
        box-shadow: 0 0 0 3px rgba(0, 77, 0, 0.1); 
    }
    
    .input-icon { color: #aaa; margin-right: 10px; font-size: 16px; }
    .input-wrapper:focus-within .input-icon { color: var(--pn-green); }

    .form-control-clean { 
        border: none; 
        height: 100%; 
        width: 100%; 
        outline: none; 
        font-size: 15px; 
        color: #333; 
        background: transparent; 
        padding-right: 12px;
    }
    .form-control-clean::placeholder { color: #bbb; font-weight: 400; }

    .durasi-box-wrapper {
        background-color: #fffde7; 
        border: 1px solid #ffe082;
        color: #f57f17;
    }
    .durasi-box-wrapper input {
        text-align: center;
        font-weight: 700;
        color: #f57f17;
        font-size: 16px;
        background: transparent;
        cursor: default;
    }
    
    .form-section {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--pn-green);
        font-weight: 700;
        border-bottom: 1px dashed #ddd;
        padding-bottom: 5px;
        margin-top: 10px;
        margin-bottom: 20px;
    }

    .btn-pn-solid { background-color: var(--pn-green); color: white; border-radius: 8px; padding: 12px 30px; border: none; font-weight: 600; font-size: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: 0.2s; }
    .btn-pn-solid:hover { background-color: #003800; transform: translateY(-1px); box-shadow: 0 5px 10px rgba(0,0,0,0.15); color: #fff; }
    .btn-link-cancel { color: #666; font-weight: 500; text-decoration: none; padding: 10px 20px; font-size: 15px; }
    .btn-link-cancel:hover { color: #333; text-decoration: underline; }

    .info-sidebar { background: #fff; border-radius: 12px; padding: 25px; border-left: 5px solid var(--pn-gold); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .info-list li { font-size: 14px; margin-bottom: 12px; position: relative; padding-left: 25px; line-height: 1.5; color: #555; }
    .info-list li::before { content: '\f05a'; font-family: "Font Awesome 5 Free"; font-weight: 900; position: absolute; left: 0; top: 2px; color: var(--pn-gold); }

</style>

<div class="container-fluid mb-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-header-title">Input Cuti Pegawai (Admin)</h1>
        <div class="bg-white px-3 py-2 rounded-pill shadow-sm border font-weight-bold text-secondary" style="font-size: 14px;">
            <i class="far fa-calendar-alt text-warning mr-2"></i> <?php echo date('d F Y'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-pn-modern">
                <div class="card-header-green">
                    <h5 class="m-0 font-weight-bold"><i class="fas fa-pen-nib mr-2"></i> Formulir Pengajuan</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" autocomplete="off">
                        
                        <div class="form-section">A. Data Pegawai & Surat</div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label-std">Nama Pegawai <span class="text-danger">*</span></label>
                                <div class="input-wrapper">
                                    <i class="fas fa-search input-icon"></i>
                                    <input class="form-control-clean" list="list_pegawai" id="input_pegawai" placeholder="Cari nama / NIP..." required>
                                </div>
                                <datalist id="list_pegawai">
                                    <?php foreach($list_pegawai as $u) { echo "<option data-id='$u[id_user]' value='$u[nama_lengkap] (NIP: $u[nip]) | Sisa N: $u[sisa_cuti_n]'>"; } ?>
                                </datalist>
                                <input type="hidden" name="id_user_hidden" id="id_user_hidden">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-std">Nomor Surat</label>
                                <div class="input-wrapper" style="background-color: #f8f9fc;">
                                    <i class="fas fa-hashtag input-icon"></i>
                                    <input type="text" name="nomor_surat" class="form-control-clean" style="color: #444; width: 100%;" value="<?= $nomor_surat_auto ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">B. Detail Waktu Cuti</div>

                        <div class="mb-3">
                            <label class="form-label-std">Jenis Cuti <span class="text-danger">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-list-ul input-icon"></i>
                                <select name="id_jenis" class="form-control-clean" required style="cursor: pointer;">
                                    <option value="">-- Pilih Jenis Cuti --</option>
                                    <?php
                                    $q_jc = mysqli_query($koneksi, "SELECT * FROM jenis_cuti ORDER BY nama_jenis ASC");
                                    while ($j = mysqli_fetch_array($q_jc)) { echo "<option value='$j[id_jenis]'>$j[nama_jenis]</option>"; }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label class="form-label-std">Tanggal Mulai</label>
                                <div class="input-wrapper">
                                    <i class="far fa-calendar-alt input-icon"></i>
                                    <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control-clean" required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label-std">Tanggal Selesai</label>
                                <div class="input-wrapper">
                                    <i class="far fa-calendar-check input-icon"></i>
                                    <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control-clean" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label-std text-center">Durasi</label>
                                <div class="input-wrapper durasi-box-wrapper">
                                    <input type="text" id="durasi_hari" class="form-control-clean" value="0" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div id="info_libur" class="alert alert-warning border-0 small mb-4" style="display:none; background-color: #fff3cd; color: #856404; font-size: 13px;">
                            <i class="fas fa-info-circle mr-1"></i> Perhitungan hari kerja (Melewati Sabtu, Minggu & Libur Nasional).
                        </div>

                        <div class="form-section">C. Keterangan & Pengesahan</div>

                        <div class="mb-3">
                            <label class="form-label-std">Alamat Selama Cuti</label>
                            <div class="input-wrapper">
                                <i class="fas fa-map-marker-alt input-icon"></i>
                                <input type="text" name="alamat" class="form-control-clean" placeholder="Isi alamat lengkap..." required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-std">Alasan Cuti</label>
                            <div class="input-wrapper" style="height: auto; padding-top: 10px; padding-bottom: 10px;">
                                <textarea name="alasan" class="form-control-clean" rows="2" placeholder="Jelaskan alasan pengajuan cuti..." required></textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-std">Atasan Penandatangan <span class="text-danger">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-user-tie input-icon"></i>
                                <select name="id_atasan_hidden" class="form-control-clean" required style="cursor: pointer;">
                                    <option value="">-- Pilih Atasan --</option>
                                    <?php foreach($list_atasan as $u) { 
                                        echo "<option value='$u[id_user]'>$u[nama_lengkap] (NIP: $u[nip])</option>"; 
                                    } ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-std">Pejabat Penandatangan (SK) <span class="text-danger">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-stamp input-icon"></i>
                                <select name="ttd_pejabat" class="form-control-clean" required style="cursor: pointer;">
                                    <option value="">-- Pilih Pejabat Berwenang --</option>
                                    <option value="ketua">KETUA - <?php echo $instansi['ketua_nama']; ?></option>
                                    <option value="wakil">WAKIL KETUA - <?php echo $instansi['wakil_nama']; ?></option>
                                    <option value="plh">PLH / KOSONG (Isi Manual Tulis Tangan)</option>
                                </select>
                            </div>
                            <small class="text-muted font-italic ml-1" style="font-size: 11px;">
                                *Pilih siapa yang akan menandatangani surat izin cuti.
                            </small>
                        </div>

                        <div class="d-flex justify-content-end align-items-center pt-3 border-top mt-4">
                            <a href="index.php?page=laporan_cuti" class="btn-link-cancel mr-3">Batal</a>
                            <button type="submit" name="simpan_cuti" class="btn-pn-solid">
                                <i class="fas fa-save mr-2"></i> Simpan Data
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="info-sidebar">
                <h6 class="font-weight-bold text-success mb-3" style="font-size: 16px;"><i class="fas fa-clipboard-check mr-2"></i> Ketentuan Admin</h6>
                <ul class="list-unstyled info-list">
                    <li><b>Cek Saldo:</b> Pastikan saldo cuti tahunan (N/N-1) mencukupi.</li>
                    <li><b>Hari Kerja:</b> Sistem otomatis melewati Sabtu, Minggu, dan Libur Nasional.</li>
                    <li><b>Status:</b> Pengajuan admin otomatis <b>DISETUJUI</b>.</li>
                    <li><b>Edit:</b> Kesalahan input dapat dihapus melalui menu Laporan Cuti.</li>
                    <li><b>TTD:</b> Pilih Pejabat (Ketua/Wakil) atau PLH untuk TTD basah.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
    // Hanya setup untuk pegawai (yang masih pakai ketik/datalist)
    setupAutocomplete('input_pegawai', 'list_pegawai', 'id_user_hidden');

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
                if (d !== 0 && d !== 6 && !holidays.includes(dateStr)) { count++; }
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