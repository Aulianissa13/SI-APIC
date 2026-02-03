<?php
/** @var mysqli $koneksi */

// --- 1. LOGIKA PHP (TIDAK BERUBAH) ---
$id_user = $_SESSION['id_user'];
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_array($query_user);

// Hitung total kuota tahunan
$total_kuota_tahunan = $user['sisa_cuti_n'] + $user['sisa_cuti_n1'];

$query_jenis = mysqli_query($koneksi, "SELECT * FROM jenis_cuti ORDER BY id_jenis ASC");
$query_atasan = mysqli_query($koneksi, "SELECT * FROM users WHERE is_atasan_langsung = '1' AND id_user != '$id_user' ORDER BY nama_lengkap ASC");

// Ambil data libur
$libur_nasional = [];
$q_libur = mysqli_query($koneksi, "SELECT tanggal FROM libur_nasional");
while ($row = mysqli_fetch_assoc($q_libur)) {
    $libur_nasional[] = $row['tanggal'];
}

// Generate Nomor Surat Otomatis
$tahun_ini = date('Y');
$bulan_ini = date('n');
$romawi    = [ 1 => "I", 2 => "II", 3 => "III", 4 => "IV", 5 => "V", 6 => "VI", 7 => "VII", 8 => "VIII", 9 => "IX", 10 => "X", 11 => "XI", 12 => "XII" ];
$bulan_romawi = $romawi[$bulan_ini];

$q_cek_no = mysqli_query($koneksi, "SELECT nomor_surat FROM pengajuan_cuti WHERE nomor_surat LIKE '%/$tahun_ini' ORDER BY id_pengajuan DESC LIMIT 1");
$row_no   = mysqli_fetch_array($q_cek_no);

if ($row_no) {
    $pecah_no = explode('/', $row_no['nomor_surat']);
    $last_no  = (int)$pecah_no[0]; 
    $urut     = $last_no + 1;
} else {
    $urut = 1;
}

$no_urut_format = sprintf("%03d", $urut); 
$no_surat_auto  = "$no_urut_format/KPN/W13.U1/KP.05.3/$bulan_romawi/$tahun_ini";
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root { 
        --pn-green: #004d00; 
        --pn-dark-green: #003300;
        --pn-gold: #FFC107; 
        --pn-gold-dark: #F9A825;
    }
    
    body { font-family: 'Poppins', sans-serif !important; background-color: #f4f6f9; }

    /* Header Konsisten (Hijau + Emas) */
    .card-header-pn {
        background-color: var(--pn-green);
        color: white;
        border-bottom: 4px solid var(--pn-gold);
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Judul Halaman - DIPERBAIKI */
    .page-header-title { 
        border-left: 5px solid var(--pn-gold); /* Garis Emas */
        padding-left: 15px; 
        color: var(--pn-green); /* Teks Hijau */
        font-weight: 700; 
        font-size: 1.6rem; 
    }

    /* Form Styles */
    .form-label-pn { font-size: 0.85rem; font-weight: 600; color: var(--pn-green); margin-bottom: 6px; }
    .form-control-pn { border-radius: 6px; border: 1px solid #ced4da; padding: 10px 15px; font-size: 0.9rem; transition: all 0.3s; }
    .form-control-pn:focus { border-color: var(--pn-green); box-shadow: 0 0 0 0.2rem rgba(0, 77, 0, 0.15); }
    .bg-readonly { background-color: #e9ecef; color: #495057; cursor: default; border: 1px solid #dee2e6; }

    /* Quota Box Styles (Animated) */
    .quota-box { 
        transition: all 0.3s ease; 
        border: 1px solid #e0e0e0; 
        background-color: #fff;
        opacity: 0.7; /* Sedikit pudar jika tidak aktif */
    }
    .quota-active { 
        opacity: 1 !important; 
        transform: scale(1.02); 
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important; 
        border: 2px solid var(--pn-gold) !important;
        background-color: #fff !important;
    }
    .quota-active .text-xs { color: var(--pn-green) !important; font-weight: bold; }

    /* Section Divider */
    .section-divider { display: flex; align-items: center; margin: 30px 0 20px 0; }
    .section-divider span { background-color: var(--pn-green); color: white; padding: 6px 15px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border-bottom: 2px solid var(--pn-gold-dark); }
    .section-divider hr { flex-grow: 1; border-top: 2px solid #e3e6f0; margin-left: 15px; }

    /* Button */
    .btn-pn-solid { background: linear-gradient(45deg, var(--pn-green), var(--pn-dark-green)); color: white; border: none; font-weight: 600; padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.15); transition: 0.3s; }
    .btn-pn-solid:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); color: var(--pn-gold); }
    
    /* Custom Card Clean */
    .card-clean { border: none; border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); overflow: hidden; }
</style>

<div class="container-fluid mb-5">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <h1 class="page-header-title">Formulir Pengajuan Cuti</h1>
        <div class="bg-white p-2 rounded shadow-sm" style="border-left: 4px solid var(--pn-gold);">
            <span class="small font-weight-bold" style="color: var(--pn-green);">
                <i class="far fa-calendar-alt mr-2"></i><?php echo date('d F Y'); ?>
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card card-clean mb-4">
                
                <div class="card-header-pn">
                    <h6 class="m-0 font-weight-bold">Informasi Pegawai</h6>
                    <i class="fas fa-id-card text-white-50 fa-lg"></i>
                </div>

                <div class="card-body bg-light">
                    <div class="card bg-white shadow-sm border-0 mb-4 p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 position-relative">
                                <img class="img-profile rounded-circle shadow-sm" src="assets/img/undraw_profile.svg" 
                                     style="width: 70px; height: 70px; border: 3px solid var(--pn-gold); object-fit: cover;">
                                <div style="width: 15px; height: 15px; background: var(--pn-green); border-radius: 50%; position: absolute; bottom: 0; right: 0; border: 2px solid white;"></div>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h6 class="font-weight-bold text-dark mb-0"><?php echo $user['nama_lengkap']; ?></h6>
                                <div class="text-xs text-muted font-weight-bold mb-1">NIP. <?php echo $user['nip']; ?></div>
                                <span class="badge px-2 py-1" style="background-color: #FFF8E1; color: var(--pn-green); border: 1px solid var(--pn-gold);">
                                    <i class="fas fa-briefcase mr-1"></i><?php echo $user['jabatan']; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="text-xs font-weight-bold text-uppercase text-muted">Status Kuota Cuti</div>
                        <div style="height: 2px; width: 50px; background-color: var(--pn-gold);"></div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <div id="box-tahunan" class="quota-box p-3 rounded shadow-sm position-relative d-flex align-items-center justify-content-between" 
                                 style="border-left: 4px solid var(--pn-green);">
                                <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: var(--pn-green);">Sisa Cuti Tahunan</div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $total_kuota_tahunan; ?> <small class="text-muted" style="font-size: 12px;">Hari</small>
                                    </div>
                                    <small class="text-muted font-italic" style="font-size: 10px;">(N: <?php echo $user['sisa_cuti_n']; ?> | N-1: <?php echo $user['sisa_cuti_n1']; ?>)</small>
                                </div>
                                <div class="p-2 rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 45px; height: 45px; background-color: var(--pn-green);">
                                    <i class="fas fa-calendar-check text-warning fa-lg"></i>
                                </div>
                                <input type="hidden" id="max_tahunan" value="<?php echo (int)$total_kuota_tahunan; ?>">
                            </div>
                        </div>

                        <div class="col-12">
                            <div id="box-sakit" class="quota-box p-3 rounded shadow-sm position-relative d-flex align-items-center justify-content-between"
                                 style="border-left: 4px solid #1cc88a;">
                                <div>
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Kuota Sakit</div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $user['kuota_cuti_sakit']; ?> <small class="text-muted" style="font-size: 12px;">Hari</small>
                                    </div>
                                </div>
                                <div class="p-2 rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 45px; height: 45px; background-color: #1cc88a;">
                                    <i class="fas fa-briefcase-medical text-white fa-lg"></i>
                                </div>
                                <input type="hidden" id="max_sakit" value="<?php echo (int)$user['kuota_cuti_sakit']; ?>">
                            </div>
                        </div>
                    </div>

                    <div id="box-unlimited" class="alert alert-info small mt-3 mb-0 border-0 shadow-sm" style="display: none; padding: 0.5rem 1rem; background-color: #e3f2fd; color: #0d47a1;">
                        <i class="fas fa-info-circle mr-1"></i> Cuti ini <b>TIDAK</b> potong kuota.
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-clean mb-4">
                
                <div class="card-header-pn">
                    <div class="font-weight-bold"><i class="fas fa-file-signature mr-2"></i> Lembar Pengajuan</div>
                    <small class="badge badge-warning text-dark font-weight-bold shadow-sm">SI-APIC</small>
                </div>
                
                <div class="card-body p-4">
                    <form action="pages/user/proses_cuti.php" method="POST" id="formCuti">
                        <input type="hidden" name="id_user" value="<?php echo $user['id_user']; ?>">

                        <div class="section-divider mt-0">
                            <span>A. Data Administrasi</span><hr>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Nomor Surat</label>
                            <div class="col-sm-9">
                                <input type="text" name="no_surat" class="form-control form-control-pn bg-readonly font-weight-bold" value="<?php echo $no_surat_auto; ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Nama & NIP</label>
                            <div class="col-sm-5 mb-2 mb-sm-0">
                                <input type="text" class="form-control form-control-pn bg-readonly" value="<?php echo $user['nama_lengkap']; ?>" readonly>
                            </div>
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-pn bg-readonly" value="<?php echo $user['nip']; ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Unit Kerja</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control form-control-pn bg-readonly" value="<?php echo $user['unit_kerja']; ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Masa Kerja</label>
                            <div class="col-sm-9">
                                <input type="text" name="masa_kerja" class="form-control form-control-pn" placeholder="Contoh: 5 Tahun 2 Bulan" required>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">No. WhatsApp</label>
                            <div class="col-sm-9">
                                <input type="number" name="no_telepon" class="form-control form-control-pn" value="<?php echo $user['no_telepon']; ?>" placeholder="08xxxxx" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Atasan (TTD)</label>
                            <div class="col-sm-9">
                                <select name="id_pejabat" class="form-control form-control-pn" required>
                                    <option value="">-- Pilih Atasan Langsung --</option>
                                    <?php while($atasan = mysqli_fetch_array($query_atasan)) { ?>
                                        <option value="<?php echo $atasan['id_user']; ?>">
                                            <?php echo $atasan['nama_lengkap']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="section-divider mt-5">
                            <span>B. Detail Cuti</span><hr>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Jenis Cuti</label>
                            <div class="col-sm-9">
                                <select name="id_jenis" id="jenis_cuti" class="form-control form-control-pn" required onchange="updateKalkulasi()">
                                    <option value="" data-nama="">-- Pilih Jenis Cuti --</option>
                                    <?php mysqli_data_seek($query_jenis, 0); while($j = mysqli_fetch_array($query_jenis)) { ?>
                                        <option value="<?php echo $j['id_jenis']; ?>" data-nama="<?php echo strtolower($j['nama_jenis']); ?>">
                                            <?php echo $j['nama_jenis']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div id="alert-sakit-reminder" class="alert alert-warning mt-2 py-2 shadow-sm" style="display:none; font-size: 0.85rem; border-left: 4px solid #f6c23e;">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Wajib melampirkan <b>Surat Keterangan Dokter</b> ke bagian Kepegawaian.
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Tanggal</label>
                            <div class="col-sm-4">
                                <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control form-control-pn" required onchange="updateKalkulasi()">
                                <small class="text-muted">Mulai</small>
                            </div>
                            <div class="col-sm-1 text-center align-self-center font-weight-bold d-none d-sm-block text-gray-500">-</div>
                            <div class="col-sm-4">
                                <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control form-control-pn" required onchange="updateKalkulasi()">
                                <small class="text-muted">Sampai</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Durasi</label>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <input type="text" name="lama_hari" id="lama_hari" class="form-control form-control-pn bg-readonly text-center font-weight-bold text-dark" readonly value="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-light border-left-0 font-weight-bold text-xs">Hari</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div id="alert-kuota" class="text-danger font-weight-bold mt-2" style="display:none; font-size: 0.9rem;">
                                    <i class="fas fa-times-circle"></i> Melebihi sisa kuota!
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Alasan</label>
                            <div class="col-sm-9">
                                <textarea name="alasan" class="form-control form-control-pn" rows="2" required placeholder="Jelaskan alasan pengajuan cuti..."></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Alamat Cuti</label>
                            <div class="col-sm-9">
                                <textarea name="alamat_cuti" class="form-control form-control-pn" rows="2" required placeholder="Alamat lengkap selama menjalankan cuti..."></textarea>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-6">
                                <a href="index.php?page=riwayat_cuti" class="btn btn-light border btn-block py-2 font-weight-bold text-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i>Batal
                                </a>
                            </div>
                            <div class="col-6">
                                <button type="submit" id="btnSubmit" class="btn btn-pn-solid btn-block py-2">
                                    <i class="fas fa-paper-plane mr-2"></i>Kirim Pengajuan
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        // Validasi Tanggal Mundur
        if (date2 < date1) {
            Swal.fire({ 
                icon: 'error', 
                title: 'Tanggal Invalid', 
                text: 'Tanggal Selesai tidak boleh lebih awal dari Tanggal Mulai!',
                confirmButtonColor: '#004d00'
            });
            document.getElementById("tgl_selesai").value = "";
            outputHari.value = 0;
            return;
        }

        // Hitung Hari Kerja (Skip Sabtu, Minggu, Libur Nasional)
        var count = 0;
        var curDate = new Date(date1.getTime());
        while (curDate <= date2) {
            var dayOfWeek = curDate.getDay();
            // Format YYYY-MM-DD untuk cek array libur
            var year = curDate.getFullYear();
            var month = String(curDate.getMonth() + 1).padStart(2, '0');
            var day = String(curDate.getDate()).padStart(2, '0');
            var dateString = `${year}-${month}-${day}`;

            // 0 = Minggu, 6 = Sabtu
            if(dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(dateString)) {
                count++;
            }
            curDate.setDate(curDate.getDate() + 1);
        }

        outputHari.value = count;
        validasiStok(namaJenis, count);
    }

    // Fungsi visual: Highlight kotak di kiri sesuai pilihan dropdown
    function highlightBox(jenis) {
        var boxTahunan = document.getElementById("box-tahunan");
        var boxSakit = document.getElementById("box-sakit");
        var boxUnlimited = document.getElementById("box-unlimited");
        var alertSakit = document.getElementById("alert-sakit-reminder");

        // Reset semua style
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

    // Fungsi Logika: Cek apakah melebihi kuota
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
            btnSubmit.classList.add("btn-secondary");
            btnSubmit.classList.remove("btn-pn-solid");
            btnSubmit.style.background = "#858796"; // Force gray
        } else {
            alertKuota.style.display = "none";
            btnSubmit.disabled = false;
            btnSubmit.classList.remove("btn-secondary");
            btnSubmit.classList.add("btn-pn-solid");
            btnSubmit.style.background = ""; // Reset inline style
        }
    }

    // SweetAlert sebelum submit
    document.getElementById('formCuti').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Pengajuan',
            text: "Pastikan data tanggal dan alasan sudah benar.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#004d00',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) { e.target.submit(); }
        });
    });
</script>