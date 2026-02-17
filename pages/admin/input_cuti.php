<?php
/** @var mysqli $koneksi */

$libur_nasional = [];
$q_libur = mysqli_query($koneksi, "SELECT tanggal FROM libur_nasional");
while ($row = mysqli_fetch_assoc($q_libur)) { $libur_nasional[] = $row['tanggal']; }
$list_pegawai = [];
$list_atasan  = [];
$q_u = mysqli_query($koneksi, "SELECT id_user, nama_lengkap, nip, sisa_cuti_n, sisa_cuti_n1, is_atasan, masa_kerja FROM users WHERE status_akun='aktif' ORDER BY nama_lengkap ASC");
while ($u = mysqli_fetch_assoc($q_u)) {
    $list_pegawai[] = $u;
    if ($u['is_atasan'] == '1') { $list_atasan[] = $u; }
}


$q_instansi = mysqli_query($koneksi, "SELECT * FROM tbl_setting_instansi LIMIT 1");
$instansi   = mysqli_fetch_assoc($q_instansi);


$thn_now = date('Y');
$bln_now = date('n');
$romawi  = [1=>"I", 2=>"II", 3=>"III", 4=>"IV", 5=>"V", 6=>"VI", 7=>"VII", 8=>"VIII", 9=>"IX", 10=>"X", 11=>"XI", 12=>"XII"];

$q_last = mysqli_query($koneksi, "SELECT nomor_surat FROM pengajuan_cuti WHERE nomor_surat LIKE '%/$thn_now' ORDER BY id_pengajuan DESC LIMIT 1");
$no_urut = 1;

if (mysqli_num_rows($q_last) > 0) {
    $d_last = mysqli_fetch_assoc($q_last);
    $parts  = explode('/', $d_last['nomor_surat']);
    if (isset($parts[0]) && is_numeric($parts[0])) {
        $no_urut = intval($parts[0]) + 1;
    }
}
$nomor_surat_auto = sprintf("%03d", $no_urut)."/KPN/W13.U1/KP.05.3/".$romawi[$bln_now]."/".$thn_now;

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

if (isset($_POST['simpan_cuti'])) {
    $id_user   = $_POST['id_user_hidden'];
    $id_atasan = $_POST['id_atasan_hidden'];
    $nomor_surat_final = htmlspecialchars($_POST['nomor_surat']);

    $cek_no = mysqli_query($koneksi, "SELECT id_pengajuan FROM pengajuan_cuti WHERE nomor_surat = '$nomor_surat_final'");
    
    if (empty($id_user)) {
        $swal_script = "Swal.fire({ title: 'Pegawai Tidak Dikenal!', text: 'Mohon pilih nama pegawai dari saran yang muncul.', icon: 'error' });";
    } elseif (empty($id_atasan)) {
        $swal_script = "Swal.fire({ title: 'Atasan Belum Dipilih!', text: 'Mohon pilih atasan penandatangan.', icon: 'warning' });";
    } elseif (mysqli_num_rows($cek_no) > 0) {
        $swal_script = "Swal.fire({ title: 'Nomor Surat Ganda!', text: 'Nomor surat $nomor_surat_final sudah digunakan. Mohon ganti nomor lain.', icon: 'error' });";
    } else {
        $id_jenis     = $_POST['id_jenis'];
        $tgl_mulai    = $_POST['tgl_mulai'];
        $tgl_selesai  = $_POST['tgl_selesai'];
        $durasi_input = $_POST['lama_hari'];
        $alamat_cuti  = htmlspecialchars($_POST['alamat']);
        $alasan       = htmlspecialchars($_POST['alasan']);
        $ttd_pejabat  = $_POST['ttd_pejabat'];
        
        $plh_nama_db = NULL; 
        $plh_nip_db  = NULL; 

        if ($ttd_pejabat == 'plh') {
            $raw_plh_nama = isset($_POST['plh_nama']) ? trim($_POST['plh_nama']) : '';
            $raw_plh_nip  = isset($_POST['plh_nip']) ? trim($_POST['plh_nip']) : '';
            
            if (empty($raw_plh_nama) || empty($raw_plh_nip)) {
                $swal_script = "Swal.fire({ title: 'Data PLH Kurang!', text: 'Nama dan NIP PLH wajib diisi.', icon: 'warning' });";
                $ttd_pejabat = ''; 
            } else {
                $plh_nama_db = htmlspecialchars($raw_plh_nama);
                $plh_nip_db  = htmlspecialchars($raw_plh_nip);
            }
        }

        $durasi = hitungHariKerja($tgl_mulai, $tgl_selesai, $libur_nasional);
        
        if ($durasi == 0) {
            $swal_script = "Swal.fire({ title: 'Durasi Nol!', text: 'Hari kerja 0 (mungkin tanggal merah semua).', icon: 'warning' });";
        } elseif (!empty($ttd_pejabat)) { 
            
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
                    $swal_script = "Swal.fire({ title: 'Kuota Habis!', text: 'Sisa cuti pegawai tidak mencukupi.', icon: 'error' });";
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
                
                $query_insert = "INSERT INTO pengajuan_cuti 
                    (id_user, id_atasan, id_jenis, tgl_mulai, tgl_selesai, lama_hari, dipotong_n, dipotong_n1, alasan, alamat_cuti, status, tgl_pengajuan, nomor_surat, ttd_pejabat, plh_nama, plh_nip) 
                    VALUES 
                    ('$id_user', '$id_atasan', '$id_jenis', '$tgl_mulai', '$tgl_selesai', '$durasi', '$potong_n', '$potong_n1', '$alasan', '$alamat_cuti', 'disetujui', '".date('Y-m-d')."', '$nomor_surat_final', '$ttd_pejabat', '$plh_nama_db', '$plh_nip_db')";
                
                if (mysqli_query($koneksi, $query_insert)) {
                    $mk_input = $_POST['masa_kerja'];
                    if(!empty($mk_input)) {
                        mysqli_query($koneksi, "UPDATE users SET masa_kerja='$mk_input' WHERE id_user='$id_user'");
                    }

                    $swal_script = "Swal.fire({ title: 'Berhasil!', text: 'Data cuti berhasil disimpan & disetujui.', icon: 'success' }).then(() => { window.location='index.php?page=validasi_cuti'; });";
                } else {
                    $swal_script = "Swal.fire({ title: 'Error DB!', text: '".mysqli_error($koneksi)."', icon: 'error' });";
                }
            }
        }
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root { --pn-green: #004d00; --pn-dark-green: #003300; --pn-gold: #F9A825; --pn-gold-dark: #F9A825; --border-color: #d1d3e2; }
    body { font-family: 'Poppins', sans-serif !important; background-color: #f4f6f9; }
    .card-header-pn { background-color: var(--pn-green); color: white; border-bottom: 4px solid var(--pn-gold); padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
    .page-header-title { border-left: 5px solid var(--pn-gold); padding-left: 15px; color: var(--pn-green); font-weight: 700; font-size: 1.6rem; }
    .form-label-pn { font-size: 0.85rem; font-weight: 600; color: var(--pn-green); margin-bottom: 0.5rem; display: block; }
    .input-group-clean { display: flex; align-items: center; border: 1px solid var(--border-color); border-radius: 8px; background-color: #fff; transition: all 0.3s ease; overflow: hidden; height: 48px; width: 100%; position: relative; }
    .input-group-clean:focus-within { border-color: var(--pn-green); box-shadow: 0 0 0 4px rgba(0, 77, 0, 0.1); transform: translateY(-1px); }
    .input-icon-clean { width: 45px; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--pn-green); background-color: #f8f9fc; border-right: 1px solid #eaecf4; font-size: 1rem; flex-shrink: 0; }
    .form-control-clean { flex: 1; border: none; height: 100%; width: 100%; padding: 0 15px; font-size: 0.95rem; color: #495057; background: transparent; outline: none; }
    .form-control-clean:focus { box-shadow: none; }
    select.form-control-clean { padding-top: 12px; padding-bottom: 12px; cursor: pointer; }
    .input-group-clean.textarea-group { height: auto !important; align-items: stretch; }
    .input-group-clean.textarea-group .input-icon-clean { height: auto; min-height: 80px; padding-top: 0; }
    textarea.form-control-clean { padding-top: 15px; padding-bottom: 15px; line-height: 1.5; }
    .input-group-clean.readonly { background-color: #e9ecef; border-color: #dee2e6; }
    .input-group-clean.readonly .input-icon-clean { color: #6c757d; background-color: #e2e6ea; }
    .section-divider { display: flex; align-items: center; margin: 30px 0 20px 0; }
    .section-divider span { background-color: var(--pn-green); color: white; padding: 6px 15px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border-bottom: 2px solid var(--pn-gold-dark); }
    .section-divider hr { flex-grow: 1; border-top: 2px solid #e3e6f0; margin-left: 15px; }
    .btn-pn-solid { background: linear-gradient(45deg, var(--pn-green), var(--pn-dark-green)); color: white; border: none; font-weight: 600; padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.15); transition: 0.3s; }
    .btn-pn-solid:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); color: var(--pn-gold); }
    .card-clean { border: none; border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); overflow: hidden; }
</style>

<div class="container-fluid mb-5 mt-4">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="page-header-title">Input Cuti</h1>
        <div class="bg-white p-2 rounded shadow-sm" style="border-left: 4px solid var(--pn-gold);">
            <span class="small font-weight-bold" style="color: var(--pn-green);">
                <i class="far fa-calendar-alt mr-2"></i><?php echo date('d F Y'); ?>
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-clean mb-4 shadow-sm">
                
                <div class="card-header-pn">
                    <div class="font-weight-bold"><i class="fas fa-user-cog mr-2"></i> Form Admin</div>
                    <small class="badge badge-warning text-dark font-weight-bold shadow-sm">AUTO-APPROVE</small>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" autocomplete="off">
                        
                        <div class="section-divider mt-0">
                            <span>A. Data Pegawai & Surat</span><hr>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Nomor Surat <span class="text-danger">*</span></label>
                            <div class="input-group-clean">
                                <div class="input-icon-clean"><i class="fas fa-hashtag"></i></div>
                                <input type="text" name="nomor_surat" class="form-control-clean"
                                    value="<?= $nomor_surat_auto ?>"
                                    style="font-family: monospace; letter-spacing: 1px; font-weight: 700; color: #2c3e50;"
                                    required>
                            </div>
                            <small class="text-muted font-italic ml-1">
                                *Nomor otomatis dari sistem. Silakan ubah manual jika dibutuhkan.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Nama Pegawai <span class="text-danger">*</span></label>
                            <div class="input-group-clean">
                                <div class="input-icon-clean"><i class="fas fa-search"></i></div>
                                <input class="form-control-clean" list="list_pegawai" id="input_pegawai" placeholder="Ketik nama atau NIP..." required>
                            </div>
                            
                            <datalist id="list_pegawai">
                                <?php foreach($list_pegawai as $u) {
                                    $mk = isset($u['masa_kerja']) ? $u['masa_kerja'] : '';
                                    echo "<option data-id='$u[id_user]' data-masa='$mk' value='$u[nama_lengkap] (NIP: $u[nip]) | Sisa N: $u[sisa_cuti_n]'>";
                                } ?>
                            </datalist>
                            <input type="hidden" name="id_user_hidden" id="id_user_hidden">
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Masa Kerja <span class="small text-muted font-weight-normal">(Opsional / Boleh Kosong)</span></label>
                            <div class="input-group-clean">
                                <div class="input-icon-clean"><i class="fas fa-briefcase"></i></div>
                                <input type="text" name="masa_kerja" id="input_masa_kerja" class="form-control-clean" placeholder="Contoh: 10 Tahun 3 Bulan">
                            </div>
                        </div>
                        
                        <div class="section-divider mt-4">
                            <span>B. Detail Cuti</span><hr>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Jenis Cuti <span class="text-danger">*</span></label>
                            <div class="input-group-clean">
                                <div class="input-icon-clean"><i class="fas fa-list-ul"></i></div>
                                <select name="id_jenis" class="form-control-clean" required>
                                    <option value="">-- Pilih Jenis Cuti --</option>
                                    <?php
                                    $q_jc = mysqli_query($koneksi, "SELECT * FROM jenis_cuti ORDER BY nama_jenis ASC");
                                    while ($j = mysqli_fetch_array($q_jc)) { echo "<option value='$j[id_jenis]'>$j[nama_jenis]</option>"; }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label-pn">Tanggal Mulai <span class="text-danger">*</span></label>
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="far fa-calendar-alt"></i></div>
                                    <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control-clean" required
                                           onclick="this.showPicker()" onfocus="this.showPicker()">
                                </div>
                            </div>

                            <div class="col-md-5 mb-3">
                                <label class="form-label-pn">Tanggal Selesai <span class="text-danger">*</span></label>
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="far fa-calendar-check"></i></div>
                                    <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control-clean" required
                                           onclick="this.showPicker()" onfocus="this.showPicker()">
                                </div>
                            </div>
                            
                            <div class="col-md-2 mb-3">
                                <label class="form-label-pn">Durasi</label>
                                <div class="input-group-clean readonly">
                                    <input type="text" name="lama_hari" id="lama_hari" class="form-control-clean text-center font-weight-bold" style="color: var(--pn-gold-dark); font-size: 1.1rem;" readonly value="0">
                                    <span class="mr-3 font-weight-bold text-muted" style="font-size: 0.75rem;">HARI</span>
                                </div>
                            </div>
                        </div>
                        
                        <div id="info_libur" class="alert alert-warning border-0 small mt-1 shadow-sm" style="display:none; border-left: 4px solid #f6c23e !important;">
                            <i class="fas fa-info-circle mr-1"></i> Perhitungan hari kerja (Melewati Sabtu/Minggu/Libur)
                        </div>

                        <div class="section-divider mt-4">
                            <span>C. Keterangan & Pengesahan</span><hr>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Alamat Selama Cuti</label>
                            <div class="input-group-clean textarea-group">
                                <div class="input-icon-clean"><i class="fas fa-map-marked-alt"></i></div>
                                <textarea name="alamat" class="form-control-clean" rows="2" placeholder="Alamat lengkap..." required></textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Alasan Cuti</label>
                            <div class="input-group-clean textarea-group">
                                <div class="input-icon-clean"><i class="fas fa-align-left"></i></div>
                                <textarea name="alasan" class="form-control-clean" rows="2" placeholder="Jelaskan alasan pengajuan..." required></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-pn">Atasan Langsung <span class="text-danger">*</span></label>
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-user-tie"></i></div>
                                    <select name="id_atasan_hidden" class="form-control-clean" required>
                                        <option value="">-- Pilih Atasan --</option>
                                        <?php foreach($list_atasan as $u) {
                                            echo "<option value='$u[id_user]'>$u[nama_lengkap] (NIP: $u[nip])</option>";
                                        } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label-pn">Pejabat Berwenang <span class="text-danger">*</span></label>
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-stamp"></i></div>
                                    <select name="ttd_pejabat" id="ttd_pejabat_select" class="form-control-clean" required>
                                        <option value="">-- Pilih Pejabat --</option>
                                        <option value="ketua">KETUA - <?php echo $instansi['ketua_nama']; ?></option>
                                        <option value="wakil">WAKIL KETUA - <?php echo $instansi['wakil_nama']; ?></option>
                                        <option value="plh">PLH / MANUAL INPUT</option>
                                    </select>
                                </div>
                            </div>

                            <div id="plh_input_container" style="display: none;" class="col-md-6 mb-3">
                                <label class="form-label-pn">Nama PLH <span class="text-danger">*</span></label>
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-user"></i></div>
                                    <input type="text" name="plh_nama" id="plh_nama_input" class="form-control-clean" placeholder="Nama PLH">
                                </div>
                            </div>

                            <div id="plh_nip_container" style="display: none;" class="col-md-6 mb-3">
                                <label class="form-label-pn">NIP PLH <span class="text-danger">*</span></label>
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-id-card"></i></div>
                                    <input type="text" name="plh_nip" id="plh_nip_input" class="form-control-clean" placeholder="NIP PLH">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-6">
                                <a href="index.php?page=validasi_cuti" class="btn btn-light border btn-block py-2 font-weight-bold text-secondary shadow-sm">
                                    <i class="fas fa-arrow-left mr-2"></i> Batal
                                </a>
                            </div>
                            <div class="col-6">
                                <button type="submit" name="simpan_cuti" class="btn btn-pn-solid btn-block py-2">
                                    <i class="fas fa-save mr-2"></i> Simpan Data
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-clean mb-4 shadow-sm" style="border-left: 5px solid var(--pn-gold);">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center"
                     data-toggle="collapse" href="#collapseKetentuan" role="button" aria-expanded="false"
                     style="cursor: pointer;">
                    <h6 class="font-weight-bold text-dark m-0">
                        <i class="fas fa-info-circle text-warning mr-2"></i> Ketentuan Admin
                    </h6>
                    <i class="fas fa-chevron-down text-muted small"></i>
                </div>

                <div class="collapse" id="collapseKetentuan">
                    <div class="card-body pt-0 pb-3">
                        <ul class="list-unstyled small text-muted mb-0" style="line-height: 1.6;">
                            <li class="mb-3">
                                <strong class="text-dark d-block mb-1"><i class="fas fa-calculator text-primary mr-1"></i> Prioritas Kuota (N-1 > N)</strong>
                                Sistem memprioritaskan pemotongan <b>Sisa Cuti Tahun Lalu (N-1)</b>. Jika habis, baru memotong <b>Cuti Tahun Ini (N)</b>.
                            </li>
                            <li class="mb-3">
                                <strong class="text-dark d-block mb-1"><i class="fas fa-check-double text-success mr-1"></i> Auto-Approve</strong>
                                Data yang diinput Admin <b>langsung disetujui</b>, kuota cuti langsung terpotong.
                            </li>
                            <li class="mb-3">
                                <strong class="text-dark d-block mb-1"><i class="fas fa-calendar-day text-warning mr-1"></i> Perhitungan Hari</strong>
                                Durasi dihitung otomatis. <b>Sabtu, Minggu, & Libur Nasional</b> tidak mengurangi kuota cuti.
                            </li>
                            <li class="mb-3">
                                <strong class="text-dark d-block mb-1"><i class="fas fa-exclamation-triangle text-danger mr-1"></i> Kesalahan Input</strong>
                                Data tidak dapat diedit sebagian. Jika ada kesalahan (misal salah tanggal), data harus <b>diinput ulang.</b>
                            <li>
                                <strong class="text-dark d-block mb-1"><i class="fas fa-file-signature text-info mr-1"></i> Penandatangan (PLH)</strong>
                                Jika pejabat berwenang berhalangan, pilih opsi <b>PLH/Manual</b> dan isi Nama & NIP pejabat pengganti.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1. Script Autocomplete
    function setupAutocomplete(inputId, listId, hiddenId) {
        const inputEl = document.getElementById(inputId);
        const hiddenEl = document.getElementById(hiddenId);
        const masaKerjaEl = document.getElementById('input_masa_kerja');

        inputEl.addEventListener('input', function(e) {
            var inputVal = this.value;
            var listOptions = document.querySelectorAll('#' + listId + ' option');
            hiddenEl.value = "";
            if(masaKerjaEl) masaKerjaEl.value = "";

            for (var i = 0; i < listOptions.length; i++) {
                if (listOptions[i].value === inputVal) {
                    hiddenEl.value = listOptions[i].getAttribute('data-id');
                    if(masaKerjaEl) masaKerjaEl.value = listOptions[i].getAttribute('data-masa');
                    break;
                }
            }
        });
    }
    setupAutocomplete('input_pegawai', 'list_pegawai', 'id_user_hidden');

    // 2. Script Hitung Tanggal
    const holidays = <?php echo json_encode($libur_nasional); ?>;
    const tglMulai = document.getElementById('tgl_mulai');
    const tglSelesai = document.getElementById('tgl_selesai');
    const durasiInput = document.getElementById('lama_hari');
    const infoLibur = document.getElementById('info_libur');

    function hitung() {
        if (tglMulai.value && tglSelesai.value) {
            let start = new Date(tglMulai.value);
            let end = new Date(tglSelesai.value);
            let count = 0;
            let loop = new Date(start);

            if (end < start) {
                Swal.fire('Tanggal Invalid', 'Tanggal selesai tidak boleh mundur.', 'error');
                tglSelesai.value = "";
                durasiInput.value = 0;
                return;
            }

            while (loop <= end) {
                let d = loop.getDay();
                let dateStr = loop.toISOString().split('T')[0];
                if (d !== 0 && d !== 6 && !holidays.includes(dateStr)) { count++; }
                loop.setDate(loop.getDate() + 1);
            }
            durasiInput.value = count;
            let totalDays = (end - start) / (1000 * 60 * 60 * 24) + 1;
            infoLibur.style.display = (count < totalDays) ? 'block' : 'none';
        }
    }
    tglMulai.addEventListener('change', hitung);
    tglSelesai.addEventListener('change', hitung);

    // 3. Script Toggle PLH (SUDAH BENAR)
    const ttdPejabatSelect = document.getElementById('ttd_pejabat_select');
    const plhInputContainer = document.getElementById('plh_input_container');
    const plhNipContainer = document.getElementById('plh_nip_container');
    const plhNamaInput = document.getElementById('plh_nama_input');
    const plhNipInput = document.getElementById('plh_nip_input');

    function togglePlhInputs() {
        if (ttdPejabatSelect.value === 'plh') {
            plhInputContainer.style.display = 'block';
            plhNipContainer.style.display = 'block';
            plhNamaInput.required = true;
            plhNipInput.required = true;
        } else {
            plhInputContainer.style.display = 'none';
            plhNipContainer.style.display = 'none';
            plhNamaInput.required = false;
            plhNipInput.required = false;
            plhNamaInput.value = '';
            plhNipInput.value = '';
        }
    }
    ttdPejabatSelect.addEventListener('change', togglePlhInputs);
    togglePlhInputs();
</script>

<?php if (!empty($swal_script)) echo "<script>$swal_script</script>"; ?>