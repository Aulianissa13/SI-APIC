<?php
// 1. Ambil Data User Lengkap
$id_user = $_SESSION['id_user'];
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_array($query_user);

// HITUNG TOTAL KUOTA GABUNGAN (FIFO)
$total_kuota_tahunan = $user['sisa_cuti_n'] + $user['sisa_cuti_n1'];

// 2. Ambil Daftar Jenis Cuti
$query_jenis = mysqli_query($koneksi, "SELECT * FROM jenis_cuti ORDER BY id_jenis ASC");

// 3. Ambil Atasan (Pejabat Penilai)
// Mengambil user yang statusnya atasan (1), kecuali diri sendiri
$query_atasan = mysqli_query($koneksi, "SELECT * FROM users WHERE is_atasan_langsung = '1' AND id_user != '$id_user' ORDER BY nama_lengkap ASC");

// 4. Ambil Libur Nasional untuk JavaScript
$libur_nasional = [];
$q_libur = mysqli_query($koneksi, "SELECT tanggal FROM libur_nasional");
while ($row = mysqli_fetch_assoc($q_libur)) {
    $libur_nasional[] = $row['tanggal'];
}

// 5. Generate Nomor Surat Otomatis (Reset Tiap Tahun)
$tahun_ini = date('Y');
$bulan_ini = date('n');
$romawi    = [ 1 => "I", 2 => "II", 3 => "III", 4 => "IV", 5 => "V", 6 => "VI", 7 => "VII", 8 => "VIII", 9 => "IX", 10 => "X", 11 => "XI", 12 => "XII" ];
$bulan_romawi = $romawi[$bulan_ini];

// Cek surat terakhir yang dibuat TAHUN INI saja
// Pastikan nama kolom di database adalah 'nomor_surat' atau 'no_surat' (sesuaikan dengan DB Anda)
$q_cek_no = mysqli_query($koneksi, "SELECT nomor_surat FROM pengajuan_cuti WHERE nomor_surat LIKE '%/$tahun_ini' ORDER BY id_pengajuan DESC LIMIT 1");
$row_no   = mysqli_fetch_array($q_cek_no);

if ($row_no) {
    // Jika sudah ada surat tahun ini, ambil 3 digit depan (explode berdasarkan /)
    $pecah_no = explode('/', $row_no['nomor_surat']);
    $last_no  = (int)$pecah_no[0]; // Ambil angka paling depan, misal "005" jadi 5
    $urut     = $last_no + 1;
} else {
    // Jika belum ada surat tahun ini, mulai dari 1
    $urut = 1;
}

$no_urut_format = sprintf("%03d", $urut); // Format jadi 001, 002, dst
$no_surat_auto  = "$no_urut_format/KPN/W13.U1/KP.05.3/$bulan_romawi/$tahun_ini";
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

<style>
    /* VARIABEL TEMA PN */
    :root {
        --pn-green: #004d00;
        --pn-green-light: #006B3F;
        --pn-gold: #FFD700;
        --pn-gold-dark: #d4af37;
    }

    body, .h1, .h2, .h3, .h4, .h5, .h6 { font-family: 'Roboto', sans-serif !important; }

    /* HEADER STYLE */
    .page-title-pn {
        font-weight: 700;
        border-left: 5px solid var(--pn-gold);
        padding-left: 15px;
        color: var(--pn-green) !important;
    }

    /* CARD STYLE */
    .card-pn { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
    .card-header-pn { 
        background: linear-gradient(135deg, var(--pn-green) 0%, var(--pn-green-light) 100%); 
        color: white; 
        padding: 15px 20px; 
        border-bottom: 3px solid var(--pn-gold);
    }

    /* KOTAK INFO (TETAP ASLI) */
    .quota-box { transition: all 0.4s ease; opacity: 0.5; filter: grayscale(100%); border: 2px solid transparent; }
    .quota-active { 
        opacity: 1 !important; 
        filter: grayscale(0%) !important; 
        transform: scale(1.03); 
        box-shadow: 0 .5rem 1rem rgba(0,0,0,0.15)!important; 
        border: 2px solid var(--pn-green-light) !important;
    }

    /* FORM COMPONENTS */
    .section-title { border-bottom: 2px solid var(--pn-green-light); padding-bottom: 5px; margin-bottom: 20px; margin-top: 10px; color: var(--pn-green-light); font-weight: 700; font-size: 1rem; }
    .form-label-pro { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #5a5c69; margin-bottom: 5px; }
    .form-control-pro { border-radius: 6px; border: 1px solid #d1d3e2; padding: 10px 12px; }
    .form-control-pro:focus { border-color: var(--pn-green-light); box-shadow: 0 0 0 0.2rem rgba(0, 107, 63, 0.15); }
    .bg-readonly { background-color: #f8f9fc !important; color: #6e707e; border: 1px solid #e3e6f0; cursor: not-allowed; }

    .btn-pn { background-color: var(--pn-green); color: white; font-weight: 700; border: none; transition: 0.3s; }
    .btn-pn:hover { background-color: #003300; color: var(--pn-gold); }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 page-title-pn">Formulir Pengajuan Cuti</h1>
        <span class="text-muted small"><?php echo date('d F Y'); ?></span>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm mb-4" style="border-top: 5px solid var(--pn-gold-dark); border-radius: 15px;">
                <div class="card-body text-center pb-2">
                    <div class="mb-3 mx-auto shadow-sm" style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 4px solid var(--pn-gold-dark); padding: 2px;">
                        <img src="assets/img/undraw_profile.svg" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    </div>
                    <h5 class="font-weight-bold text-dark mb-1"><?php echo $user['nama_lengkap']; ?></h5>
                    <span class="badge badge-light border px-3 py-1 text-muted">NIP. <?php echo $user['nip']; ?></span>
                </div>
                <hr class="mx-4 my-2">
                <div class="card-body pt-1">
                    <h6 class="font-weight-bold text-center mb-3" style="color: var(--pn-green-light); font-size: 0.8rem; letter-spacing: 1px;">STATUS KUOTA CUTI</h6>
                    
                    <div id="box-tahunan" class="quota-box p-3 rounded text-center d-flex flex-column justify-content-center mb-3" style="background-color: #f0fdf4; border: 1px solid var(--pn-green-light);">
                        <small class="text-uppercase font-weight-bold" style="color: var(--pn-green-light); font-size: 0.65rem;">Total Sisa Cuti (N + N-1)</small>
                        <h2 class="font-weight-bold mb-0 mt-1" style="color: var(--pn-green-light);"><?php echo $total_kuota_tahunan; ?></h2>
                        <div style="font-size: 0.65rem; color: var(--pn-green-light);" class="mt-1">
                            (Tahun Ini: <b><?php echo $user['sisa_cuti_n']; ?></b> | Tahun Lalu: <b><?php echo $user['sisa_cuti_n1']; ?></b>)
                        </div>
                        <input type="hidden" id="max_tahunan" value="<?php echo (int)$total_kuota_tahunan; ?>">
                    </div>

                    <div id="box-sakit" class="quota-box rounded p-3 d-flex align-items-center justify-content-between shadow-sm" style="background: linear-gradient(135deg, #006B3F 0%, #004d2d 100%); color: white;">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 35px; height: 35px; color: var(--pn-green-light);">
                                <i class="fas fa-briefcase-medical"></i>
                            </div>
                            <div style="line-height: 1.2;">
                                <span class="d-block font-weight-bold" style="font-size: 0.85rem;">Cuti Sakit</span>
                                <small style="opacity: 0.8; font-size: 0.65rem;">Sisa Kuota</small>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="h4 font-weight-bold mb-0"><?php echo $user['kuota_cuti_sakit']; ?></span>
                            <input type="hidden" id="max_sakit" value="<?php echo (int)$user['kuota_cuti_sakit']; ?>">
                        </div>
                    </div>

                    <div id="box-unlimited" class="mt-3 text-center alert alert-info" style="display: none; font-size: 0.75rem;">
                        <i class="fas fa-info-circle"></i> Jenis cuti ini <b>TIDAK</b> memotong kuota.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-pn mb-4">
                <div class="card-header-pn d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-file-edit mr-2"></i>Lembar Pengajuan Elektronik</h6>
                    <small class="badge badge-warning text-dark font-weight-bold">SI-APIC</small>
                </div>
                <div class="card-body px-4 py-4">
                    <form action="pages/user/proses_cuti.php" method="POST" id="formCuti">
                        <input type="hidden" name="id_user" value="<?php echo $user['id_user']; ?>">

                        <div class="section-title">A. DATA PEGAWAI</div>
                        
                        <div class="form-group">
                            <label class="form-label-pro">Nomor Surat (Otomatis)</label>
                            <input type="text" name="no_surat" class="form-control form-control-pro bg-readonly font-weight-bold" value="<?php echo $no_surat_auto; ?>" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-label-pro">Nama Lengkap</label>
                                <input type="text" class="form-control form-control-pro bg-readonly" value="<?php echo $user['nama_lengkap']; ?>" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label-pro">NIP</label>
                                <input type="text" class="form-control form-control-pro bg-readonly" value="<?php echo $user['nip']; ?>" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-label-pro">Jabatan</label>
                                <input type="text" class="form-control form-control-pro bg-readonly" value="<?php echo $user['jabatan']; ?>" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label-pro">Pangkat / Golongan</label>
                                <input type="text" class="form-control form-control-pro bg-readonly" value="<?php echo $user['pangkat']; ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label-pro">Unit Kerja</label>
                            <input type="text" class="form-control form-control-pro bg-readonly" value="<?php echo $user['unit_kerja']; ?>" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-label-pro">Masa Kerja (Thn/Bln)</label>
                                <input type="text" name="masa_kerja" class="form-control form-control-pro" placeholder="Contoh: 5 Tahun 2 Bulan">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label-pro">No. WhatsApp</label>
                                <input type="number" name="no_telepon" class="form-control form-control-pro" value="<?php echo $user['no_telepon']; ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label-pro">Atasan Langsung (Penandatangan)</label>
                            <select name="id_pejabat" class="form-control form-control-pro" required>
                                <option value="">-- Pilih Nama Atasan --</option>
                                <?php 
                                // Reset pointer data jika perlu, tapi karena ini loop pertama tidak wajib
                                // mysqli_data_seek($query_atasan, 0); 
                                while($atasan = mysqli_fetch_array($query_atasan)) { 
                                ?>
                                    <option value="<?php echo $atasan['id_user']; ?>">
                                        <?php echo $atasan['nama_lengkap']; ?> (NIP. <?php echo $atasan['nip']; ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="section-title mt-4">B. DETAIL CUTI</div>

                        <div class="form-group">
                            <label class="form-label-pro">Jenis Cuti</label>
                            <select name="id_jenis" id="jenis_cuti" class="form-control form-control-pro" required onchange="updateKalkulasi()">
                                <option value="" data-nama="">-- Pilih Jenis Cuti --</option>
                                <?php mysqli_data_seek($query_jenis, 0); while($j = mysqli_fetch_array($query_jenis)) { ?>
                                    <option value="<?php echo $j['id_jenis']; ?>" data-nama="<?php echo strtolower($j['nama_jenis']); ?>">
                                        <?php echo $j['nama_jenis']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <div id="alert-sakit-reminder" class="alert alert-warning mt-2" style="display:none; font-size: 0.8rem;">
                                <i class="fas fa-info-circle mr-1"></i> Wajib lampirkan Surat Dokter ke Kepegawaian.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-5 form-group">
                                <label class="form-label-pro">Mulai</label>
                                <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control form-control-pro" required onchange="updateKalkulasi()">
                            </div>
                            <div class="col-md-5 form-group">
                                <label class="form-label-pro">Selesai</label>
                                <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control form-control-pro" required onchange="updateKalkulasi()">
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="form-label-pro">Hari</label>
                                <input type="text" name="lama_hari" id="lama_hari" class="form-control form-control-pro bg-readonly text-center font-weight-bold" readonly value="0">
                            </div>
                        </div>

                        <div id="alert-kuota" class="alert alert-danger text-center font-weight-bold" style="display:none; font-size: 0.8rem;">
                            <i class="fas fa-exclamation-triangle"></i> Melebihi sisa kuota Anda!
                        </div>

                        <div class="form-group">
                            <label class="form-label-pro">Alasan</label>
                            <textarea name="alasan" class="form-control form-control-pro" rows="2" required placeholder="Tulis alasan singkat..."></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label-pro">Alamat Selama Cuti</label>
                            <textarea name="alamat_cuti" class="form-control form-control-pro" rows="2" required placeholder="Alamat lengkap saat cuti..."></textarea>
                        </div>

                        <div class="row mt-4">
                            <div class="col-6">
                                <a href="index.php" class="btn btn-secondary btn-block py-2">Batal</a>
                            </div>
                            <div class="col-6">
                                <button type="submit" id="btnSubmit" class="btn btn-pn btn-block py-2 shadow">
                                    <i class="fas fa-save mr-2"></i> Simpan & Cetak
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const holidays = <?php echo json_encode($libur_nasional); ?>;

    function updateKalkulasi() {
        var tglMulai = document.getElementById("tgl_mulai").value;
        var tglSelesai = document.getElementById("tgl_selesai").value;
        var outputHari = document.getElementById("lama_hari");
        
        var selectBox = document.getElementById("jenis_cuti");
        var selectedOption = selectBox.options[selectBox.selectedIndex];
        var namaJenis = selectedOption.getAttribute('data-nama') || ""; 

        highlightBox(namaJenis);

        if (tglMulai == "" || tglSelesai == "") return;

        var date1 = new Date(tglMulai);
        var date2 = new Date(tglSelesai);

        if (date2 < date1) {
            Swal.fire({ icon: 'error', title: 'Tanggal Salah', text: 'Tanggal Selesai tidak boleh mundur!' });
            document.getElementById("tgl_selesai").value = "";
            return;
        }

        var count = 0;
        var curDate = new Date(date1.getTime());
        while (curDate <= date2) {
            var dayOfWeek = curDate.getDay();
            var year = curDate.getFullYear();
            var month = String(curDate.getMonth() + 1).padStart(2, '0');
            var day = String(curDate.getDate()).padStart(2, '0');
            var dateString = `${year}-${month}-${day}`;

            if(dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(dateString)) {
                count++;
            }
            curDate.setDate(curDate.getDate() + 1);
        }

        outputHari.value = count;
        validasiStok(namaJenis, count);
    }

    function highlightBox(jenis) {
        var boxTahunan = document.getElementById("box-tahunan");
        var boxSakit = document.getElementById("box-sakit");
        var boxUnlimited = document.getElementById("box-unlimited");
        var alertSakit = document.getElementById("alert-sakit-reminder");

        boxTahunan.classList.remove("quota-active");
        boxSakit.classList.remove("quota-active");
        boxUnlimited.style.display = "none";
        alertSakit.style.display = "none";

        if (jenis.includes("tahunan")) {
            boxTahunan.classList.add("quota-active");
        } else if (jenis.includes("sakit")) {
            boxSakit.classList.add("quota-active");
            alertSakit.style.display = "block";
        } else if (jenis !== "") {
            boxUnlimited.style.display = "block";
        }
    }

    function validasiStok(jenis, lamaHari) {
        var btnSubmit = document.getElementById("btnSubmit");
        var alertKuota = document.getElementById("alert-kuota");
        var maxStok = 999; 
        
        if (jenis.includes("tahunan")) {
            maxStok = parseInt(document.getElementById("max_tahunan").value);
        } else if (jenis.includes("sakit")) {
            maxStok = parseInt(document.getElementById("max_sakit").value);
        }

        if (lamaHari > maxStok) {
            alertKuota.style.display = "block"; 
            btnSubmit.disabled = true;
        } else {
            alertKuota.style.display = "none";
            btnSubmit.disabled = false;
        }
    }

    document.getElementById('formCuti').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi',
            text: "Apakah data pengajuan sudah benar?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#004d00',
            confirmButtonText: 'Ya, Simpan!'
        }).then((result) => {
            if (result.isConfirmed) { e.target.submit(); }
        });
    });
</script>