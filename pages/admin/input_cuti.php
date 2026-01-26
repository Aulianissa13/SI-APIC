<?php
// --- FILE: pages/admin/input_cuti.php ---

// 1. PHP LOGIC (Database & Hitung Hari) - TETAP SAMA
$libur_nasional = [];
$q_libur = mysqli_query($koneksi, "SELECT tanggal FROM libur_nasional");
if ($q_libur) {
    while ($row = mysqli_fetch_assoc($q_libur)) {
        $libur_nasional[] = $row['tanggal'];
    }
}

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

if (isset($_POST['simpan_cuti'])) {
    // AMBIL ID DARI INPUT HIDDEN (Bukan text box)
    $id_user      = $_POST['id_user_hidden']; 
    $nama_ketik   = $_POST['input_nama_pegawai'];

    // Validasi: Pastikan user memilih nama yang valid (ID terisi)
    if (empty($id_user)) {
        $swal_script = "Swal.fire({ title: 'Nama Tidak Dikenal!', text: 'Silakan klik/pilih nama pegawai dari saran yang muncul saat mengetik.', icon: 'error', confirmButtonColor: '#004d00' });";
    } else {
        $id_jenis     = $_POST['id_jenis']; 
        $tgl_mulai    = $_POST['tgl_mulai'];
        $tgl_selesai  = $_POST['tgl_selesai'];
        $alamat_cuti  = htmlspecialchars($_POST['alamat']); 
        $alasan       = htmlspecialchars($_POST['alasan']);
        $nomor_surat  = !empty($_POST['nomor_surat']) ? htmlspecialchars($_POST['nomor_surat']) : ''; 
        
        if ($tgl_selesai < $tgl_mulai) {
            $swal_script = "Swal.fire({ title: 'Gagal!', text: 'Tanggal terbalik.', icon: 'error', confirmButtonColor: '#004d00' });";
        } else {
            $durasi = hitungHariKerja($tgl_mulai, $tgl_selesai, $libur_nasional);
            if ($durasi <= 0) {
                 $swal_script = "Swal.fire({ title: 'Gagal!', text: 'Durasi 0 hari.', icon: 'warning', confirmButtonColor: '#004d00' });";
            } else {
                // Cek Kuota
                $q_cek_jenis = mysqli_query($koneksi, "SELECT nama_jenis FROM jenis_cuti WHERE id_jenis = '$id_jenis'");
                $d_jenis = mysqli_fetch_assoc($q_cek_jenis);
                $nama_jenis_cuti = $d_jenis['nama_jenis']; 
                $lanjut_simpan = true;
                $potong_n1 = 0; $potong_n = 0;

                if (stripos($nama_jenis_cuti, 'Tahunan') !== false) {
                    $cek_user = mysqli_query($koneksi, "SELECT sisa_cuti_n, sisa_cuti_n1 FROM users WHERE id_user = '$id_user'");
                    $data_user = mysqli_fetch_assoc($cek_user);
                    $sisa_n = $data_user['sisa_cuti_n']; $sisa_n1 = $data_user['sisa_cuti_n1'];
                    $total = $sisa_n + $sisa_n1;

                    if ($total < $durasi) {
                        $lanjut_simpan = false;
                        $swal_script = "Swal.fire({ title: 'Gagal!', text: 'Kuota cuti habis.', icon: 'error', confirmButtonColor: '#004d00' });";
                    } else {
                        if ($durasi <= $sisa_n1) { $potong_n1 = $durasi; } 
                        else { $potong_n1 = $sisa_n1; $potong_n = $durasi - $sisa_n1; }
                    }
                }

                if ($lanjut_simpan) {
                    $status = 'Menunggu Konfirmasi';
                    $tgl_pengajuan = date('Y-m-d');
                    $query_insert = "INSERT INTO pengajuan_cuti (id_user, id_jenis, tgl_mulai, tgl_selesai, lama_hari, dipotong_n, dipotong_n1, alasan, alamat_cuti, status, tgl_pengajuan, nomor_surat) 
                                     VALUES ('$id_user', '$id_jenis', '$tgl_mulai', '$tgl_selesai', '$durasi', '$potong_n', '$potong_n1', '$alasan', '$alamat_cuti', '$status', '$tgl_pengajuan', '$nomor_surat')";

                    if (mysqli_query($koneksi, $query_insert)) {
                        if (empty($nomor_surat)) {
                            $last_id = mysqli_insert_id($koneksi);
                            $thn = date('Y'); $bln = date('n');
                            $romawi = [1=>"I", 2=>"II", 3=>"III", 4=>"IV", 5=>"V", 6=>"VI", 7=>"VII", 8=>"VIII", 9=>"IX", 10=>"X", 11=>"XI", 12=>"XII"];
                            $q_last = mysqli_query($koneksi, "SELECT nomor_surat FROM pengajuan_cuti WHERE nomor_surat LIKE '%/$thn' AND id_pengajuan != '$last_id' ORDER BY id_pengajuan DESC LIMIT 1");
                            $no_baru = 1; 
                            if (mysqli_num_rows($q_last) > 0) {
                                $d_last = mysqli_fetch_assoc($q_last);
                                $parts = explode('/', $d_last['nomor_surat']);
                                if (isset($parts[0]) && is_numeric($parts[0])) $no_baru = intval($parts[0]) + 1;
                            }
                            $auto = sprintf("%03d", $no_baru)."/KPN/W13.U1/KP.05.3/".$romawi[$bln]."/".$thn;
                            mysqli_query($koneksi, "UPDATE pengajuan_cuti SET nomor_surat = '$auto' WHERE id_pengajuan = '$last_id'");
                        }
                        $swal_script = "Swal.fire({ title: 'Berhasil!', text: 'Data disimpan.', icon: 'success', confirmButtonColor: '#004d00' }).then(() => { window.location='index.php?page=input_cuti'; });";
                    } else {
                        $swal_script = "Swal.fire({ title: 'Error!', text: 'Database Error', icon: 'error' });";
                    }
                }
            }
        }
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --pn-green: #004d00;
        --pn-gold: #FFD700;
        --bg-light: #f8f9fc;
    }
    body { font-family: 'Poppins', sans-serif !important; background-color: var(--bg-light); }
    .card-pn { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); overflow: hidden; background: white; }
    .card-header-pn { background: linear-gradient(135deg, var(--pn-green) 0%, #006400 100%); color: white; border-bottom: 4px solid var(--pn-gold); padding: 15px 20px; }
    .page-title-pn { font-weight: 700; border-left: 5px solid var(--pn-gold); padding-left: 15px; color: var(--pn-green) !important; margin-bottom: 20px; }
    .form-label { color: var(--pn-green); font-weight: 600; margin-bottom: 5px; }
    .form-control, .form-select { border-radius: 8px; border: 1px solid #ced4da; height: 45px; }
    .form-control:focus, .form-select:focus { border-color: var(--pn-green); box-shadow: 0 0 0 0.2rem rgba(0, 77, 0, 0.25); }
    .btn-pn { background-color: var(--pn-green); color: white; border-radius: 8px; font-weight: 600; padding: 10px 25px; border: none; transition: 0.3s; }
    .btn-pn:hover { background-color: #003300; color: var(--pn-gold); }
    .input-durasi { background-color: rgba(255, 215, 0, 0.1) !important; color: var(--pn-green) !important; border-color: var(--pn-gold) !important; font-weight: bold; text-align: center; }
</style>

<div class="container-fluid mb-5">
    <div class="d-flex align-items-center justify-content-between mt-4 mb-4">
        <h1 class="h3 page-title-pn">Input Cuti Pegawai</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-pn mb-4">
                <div class="card-header-pn">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-pen-fancy mr-2"></i>Formulir Pengajuan</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" autocomplete="off">
                        
                        <div class="form-group mb-4">
                            <label class="form-label">Nama Pegawai <span class="text-danger">*</span></label>
                            
                            <input class="form-control" list="list_pegawai" id="input_cari" name="input_nama_pegawai" 
                                   placeholder="Ketik nama pegawai disini..." required>
                            
                            <datalist id="list_pegawai">
                                <?php
                                $q_user = mysqli_query($koneksi, "SELECT id_user, nama_lengkap, nip, sisa_cuti_n FROM users WHERE role='user' ORDER BY nama_lengkap ASC");
                                while ($u = mysqli_fetch_array($q_user)) {
                                    // Value ditampilkan di list, data-id disimpan di sistem
                                    echo "<option data-id='$u[id_user]' value='$u[nama_lengkap] (NIP: $u[nip]) | Sisa: $u[sisa_cuti_n]'>";
                                }
                                ?>
                            </datalist>

                            <input type="hidden" name="id_user_hidden" id="id_user_hidden">
                            
                            <small class="text-muted ml-1"><i>*Ketik nama, lalu klik pilihan yang muncul.</i></small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Cuti <span class="text-danger">*</span></label>
                                <select name="id_jenis" class="form-select" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <?php
                                    $q_jc = mysqli_query($koneksi, "SELECT * FROM jenis_cuti ORDER BY nama_jenis ASC");
                                    while ($j = mysqli_fetch_array($q_jc)) { echo "<option value='$j[id_jenis]'>$j[nama_jenis]</option>"; }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nomor Surat <small class="text-muted fw-light">(Opsional)</small></label>
                                <input type="text" name="nomor_surat" class="form-control" placeholder="Auto-generate">
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
                                <label class="form-label d-block text-center">Durasi</label>
                                <input type="text" id="durasi_hari" class="form-control input-durasi" value="0" readonly>
                            </div>
                        </div>

                        <div id="info_libur" class="alert alert-warning border-left-warning shadow-sm small py-2 mb-3" style="display:none; border-left: 5px solid var(--pn-gold);">
                            <i class="fas fa-exclamation-circle mr-1"></i> Sabtu, Minggu & Libur Nasional tidak dihitung.
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Alamat Selama Cuti</label>
                            <input type="text" name="alamat" class="form-control" placeholder="Alamat lengkap..." required>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">Alasan Cuti</label>
                            <textarea name="alasan" class="form-control" rows="3" placeholder="Keperluan..." required></textarea>
                        </div>

                        <div class="d-flex justify-content-end pt-2 border-top">
                            <button type="reset" class="btn btn-light mr-2 border">Reset</button>
                            <button type="submit" name="simpan_cuti" class="btn btn-pn"><i class="fas fa-save mr-2"></i> Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-pn mb-4">
                <div class="card-header-pn"><h6 class="m-0 font-weight-bold">Ketentuan</h6></div>
                <div class="card-body small text-secondary">
                    <ul class="pl-3 mb-0" style="line-height:1.8">
                        <li>Pastikan nama pegawai terdaftar.</li>
                        <li>Sistem otomatis melewati Sabtu, Minggu & Libur Nasional.</li>
                        <li>Pastikan kuota cuti mencukupi.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- A. LOGIC PENCARIAN NAMA (SIMPLE & ROBUST) ---
    // Ketika user mengetik atau memilih di input box
    document.getElementById('input_cari').addEventListener('input', function(e) {
        var inputVal = this.value;
        var listOptions = document.querySelectorAll('#list_pegawai option');
        var hiddenId = document.getElementById('id_user_hidden');
        
        // Reset ID dulu (agar kalau user hapus teks, ID juga hilang)
        hiddenId.value = ""; 

        // Cek apakah teks yang diketik COCOK dengan salah satu opsi
        for (var i = 0; i < listOptions.length; i++) {
            if (listOptions[i].value === inputVal) {
                // Jika cocok, ambil ID dari attribute data-id
                hiddenId.value = listOptions[i].getAttribute('data-id');
                break;
            }
        }
    });

    // --- B. LOGIC HITUNG HARI ---
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