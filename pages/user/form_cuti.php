<?php
// 1. Ambil Data User Terbaru
$id_user = $_SESSION['id_user'];
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_array($query_user);

// 2. Ambil Daftar Jenis Cuti
$query_jenis = mysqli_query($koneksi, "SELECT * FROM jenis_cuti ORDER BY id_jenis ASC");
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

<style>
    /* Override Font Bawaan */
    body, .h1, .h2, .h3, .h4, .h5, .h6 { font-family: 'Roboto', sans-serif !important; }

    /* Styling Label Form */
    .form-label-pro {
        font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.05em; color: #5a5c69; margin-bottom: 5px;
    }

    /* Input Field */
    .form-control-pro {
        border-radius: 4px; border: 1px solid #d1d3e2; padding: 10px 15px;
        height: auto; font-size: 0.95rem; color: #333;
    }
    .form-control-pro:focus {
        border-color: #006B3F; box-shadow: 0 0 0 0.2rem rgba(0, 107, 63, 0.25);
    }

    /* Readonly */
    .bg-readonly {
        background-color: #f8f9fc !important; color: #6e707e;
        border: 1px solid #e3e6f0; cursor: not-allowed;
    }

    /* Card Header Hijau PN */
    .card-header-pro { background-color: #006B3F; color: white; padding: 15px 20px; }

    /* Divider Section */
    .section-title {
        border-bottom: 2px solid #006B3F; padding-bottom: 5px; margin-bottom: 20px;
        margin-top: 10px; color: #006B3F; font-weight: 700; font-size: 1.1rem;
    }

    /* Efek Transparan untuk Box Kuota yang tidak aktif */
    .quota-box { transition: all 0.3s; opacity: 0.5; filter: grayscale(100%); }
    .quota-active { opacity: 1 !important; filter: grayscale(0%) !important; transform: scale(1.02); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="font-weight: 300;">Formulir Pengajuan Cuti</h1>
    <span class="text-muted small"><?php echo date('d F Y'); ?></span>
</div>

<div class="row">

    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm mb-4" style="border-top: 5px solid #d4af37;">
            <div class="card-body text-center pb-2">
                <div class="mb-3 mx-auto shadow-sm" style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 4px solid #d4af37; padding: 2px;">
                    <img src="assets/img/undraw_profile.svg" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <h5 class="font-weight-bold text-dark mb-1"><?php echo $user['nama_lengkap']; ?></h5>
                <span class="badge badge-light border px-3 py-1 text-muted">NIP. <?php echo $user['nip']; ?></span>
            </div>

            <hr class="mx-4 my-2">

            <div class="card-body pt-1">
                <h6 class="font-weight-bold text-center mb-3" style="color: #006B3F; font-size: 0.8rem; letter-spacing: 1px;">STATUS KUOTA CUTI</h6>
                
                <div class="row no-gutters mb-3">
                    <div class="col-12">
                        <div id="box-tahunan" class="quota-box p-3 rounded text-center d-flex flex-column justify-content-center" 
                             style="background-color: #f0fdf4; border: 1px solid #006B3F;">
                            <small class="text-uppercase font-weight-bold" style="color: #006B3F; font-size: 0.65rem;">Sisa Cuti Tahunan (N)</small>
                            <h2 class="font-weight-bold mb-0 mt-1" style="color: #006B3F;"><?php echo $user['sisa_cuti_n']; ?></h2>
                            <small class="text-muted" style="font-size: 0.7rem;">Hari</small>
                            <input type="hidden" id="max_tahunan" value="<?php echo $user['sisa_cuti_n']; ?>">
                        </div>
                    </div>
                </div>

                <div id="box-sakit" class="quota-box rounded p-3 d-flex align-items-center justify-content-between shadow-sm" 
                     style="background: rgb(0,107,63); background: linear-gradient(135deg, rgba(0,107,63,1) 0%, rgba(0,77,45,1) 100%); color: white;">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; color: #006B3F;">
                            <i class="fas fa-briefcase-medical"></i>
                        </div>
                        <div style="line-height: 1.2;">
                            <span class="d-block font-weight-bold" style="font-size: 0.9rem;">Cuti Sakit</span>
                            <small style="opacity: 0.8; font-size: 0.7rem;">Kuota Tersedia</small>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="h4 font-weight-bold mb-0"><?php echo $user['kuota_cuti_sakit']; ?></span>
                        <input type="hidden" id="max_sakit" value="<?php echo $user['kuota_cuti_sakit']; ?>">
                    </div>
                </div>

                <div id="box-unlimited" class="mt-3 text-center alert alert-info" style="display: none; font-size: 0.8rem;">
                    <i class="fas fa-info-circle"></i> Jenis cuti ini <b>TIDAK</b> memotong kuota tahunan.
                </div>

            </div>
            
            <div class="card-footer bg-white text-center py-2">
                <small class="text-muted" style="font-size: 10px;">Update Terakhir: <?php echo date('d-m-Y H:i'); ?></small>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header-pro rounded-top d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Lembar Pengajuan</h6>
                <small class="text-white-50">SI-APIC</small>
            </div>
            <div class="card-body px-4 py-4">
                
                <form action="pages/user/proses_cuti.php" method="POST" id="formCuti">
                    <input type="hidden" name="id_user" value="<?php echo $user['id_user']; ?>">

                    <div class="section-title">A. DATA PEGAWAI</div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="form-label-pro">Nama Lengkap</label>
                            <input type="text" class="form-control form-control-pro bg-readonly" value="<?php echo $user['nama_lengkap']; ?>" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label-pro">NIP</label>
                            <input type="text" class="form-control form-control-pro bg-readonly" value="<?php echo $user['nip']; ?>" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="form-label-pro">Jabatan</label>
                            <input type="text" class="form-control form-control-pro bg-readonly" value="<?php echo $user['jabatan']; ?>" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label-pro">Pangkat / Golongan</label>
                            <input type="text" class="form-control form-control-pro bg-readonly" value="<?php echo $user['pangkat']; ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label-pro">Unit Kerja</label>
                        <input type="text" class="form-control form-control-pro bg-readonly" value="<?php echo $user['unit_kerja']; ?>" readonly>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="form-label-pro">Masa Kerja <span class="text-muted font-weight-normal text-lowercase">(Tahun/Bulan)</span></label>
                            <input type="text" name="masa_kerja" class="form-control form-control-pro" placeholder="-- Kosongkan jika ditulis tangan --">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label-pro">No. Telepon / WhatsApp</label>
                            <input type="number" name="no_telepon" class="form-control form-control-pro" value="<?php echo $user['no_telepon']; ?>" required>
                        </div>
                    </div>

                    <div class="section-title mt-4">B. DETAIL CUTI</div>

                    <div class="form-group">
                        <label class="form-label-pro">Jenis Cuti yang Diambil</label>
                        <select name="id_jenis" id="jenis_cuti" class="form-control form-control-pro" required onchange="cekJenisCuti()">
                            <option value="" data-nama="">-- Silakan Pilih --</option>
                            <?php while($j = mysqli_fetch_array($query_jenis)) { ?>
                                <option value="<?php echo $j['id_jenis']; ?>" data-nama="<?php echo strtolower($j['nama_jenis']); ?>">
                                    <?php echo $j['nama_jenis']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label class="form-label-pro">Mulai Tanggal</label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control form-control-pro" required>
                        </div>
                        <div class="form-group col-md-5">
                            <label class="form-label-pro">Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control form-control-pro" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="form-label-pro">Durasi</label>
                            <input type="text" name="lama_hari" id="lama_hari" class="form-control form-control-pro bg-readonly" readonly placeholder="0 Hari">
                        </div>
                    </div>
                    
                    <div id="alert-kuota" class="alert alert-danger text-center font-weight-bold" style="display:none; font-size: 0.85rem;">
                        <i class="fas fa-exclamation-triangle"></i> Durasi pengajuan melebihi sisa kuota Anda!
                    </div>

                    <div class="form-group">
                        <label class="form-label-pro">Alasan Cuti</label>
                        <textarea name="alasan" class="form-control form-control-pro" rows="2" placeholder="Jelaskan alasan pengajuan cuti secara singkat..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label-pro">Alamat Selama Cuti</label>
                        <textarea name="alamat_cuti" class="form-control form-control-pro" rows="2" placeholder="Alamat lengkap domisili saat cuti berlangsung..." required></textarea>
                    </div>

                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <a href="index.php" class="btn btn-secondary btn-block py-2">Batal</a>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" id="btnSubmit" class="btn btn-success btn-block py-2 font-weight-bold" style="background-color: #006B3F; border-color: #006B3F;">
                                <i class="fas fa-save mr-2"></i> Simpan & Cetak Formulir
                            </button>
                        </div>
                    </div>
                    
                </form>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($_SESSION['alert'])) : ?>
        <script>
            Swal.fire({
                icon: '<?php echo $_SESSION['alert']['icon']; ?>',
                title: '<?php echo $_SESSION['alert']['title']; ?>',
                text: '<?php echo $_SESSION['alert']['text']; ?>',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Perbaiki'
            });
        </script>
        <?php unset($_SESSION['alert']); // Hapus pesan agar tidak muncul lagi ?>
    <?php endif; ?>
</div>

<script>
    // 1. EVENT LISTENER
    document.getElementById("tgl_mulai").addEventListener("change", updateKalkulasi);
    document.getElementById("tgl_selesai").addEventListener("change", updateKalkulasi);
    document.getElementById("jenis_cuti").addEventListener("change", updateKalkulasi);

    // 2. INTERCEPT SUBMIT DENGAN SWEETALERT (Baru)
    document.getElementById('formCuti').addEventListener('submit', function(e) {
        e.preventDefault(); // Mencegah kirim langsung
        
        // Ambil data untuk pesan konfirmasi
        var tglMulai = document.getElementById("tgl_mulai").value;
        var lama = document.getElementById("lama_hari").value;

        Swal.fire({
            title: 'Konfirmasi Pengajuan',
            html: "Anda akan mengajukan cuti selama <b>" + lama + " Hari</b><br>mulai tanggal <b>" + tglMulai + "</b>.<br>Apakah data sudah benar?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#006B3F', // Warna Hijau Brand
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Ajukan!',
            cancelButtonText: 'Cek Lagi'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika user klik Ya, baru kirim form secara manual
                e.target.submit(); 
            }
        });
    });

    // 3. FUNGSI UTAMA (Menghitung hari & Cek Kuota)
    function updateKalkulasi() {
        var tglMulai = document.getElementById("tgl_mulai").value;
        var tglSelesai = document.getElementById("tgl_selesai").value;
        var outputHari = document.getElementById("lama_hari");
        
        var selectBox = document.getElementById("jenis_cuti");
        var selectedOption = selectBox.options[selectBox.selectedIndex];
        var namaJenis = selectedOption.getAttribute('data-nama') || ""; 

        highlightBox(namaJenis);

        if (tglMulai == "" || tglSelesai == "") {
            outputHari.value = "";
            return;
        }

        var date1 = new Date(tglMulai);
        var date2 = new Date(tglSelesai);

        if (date2 < date1) {
            Swal.fire({ icon: 'error', title: 'Tanggal Salah', text: 'Tanggal Selesai tidak boleh mundur dari Tanggal Mulai!' });
            document.getElementById("tgl_selesai").value = "";
            outputHari.value = "";
            return;
        }

        var count = 0;
        var curDate = new Date(date1.getTime());

        while (curDate <= date2) {
            var dayOfWeek = curDate.getDay();
            if(dayOfWeek !== 0 && dayOfWeek !== 6) {
                count++;
            }
            curDate.setDate(curDate.getDate() + 1);
        }

        outputHari.value = count;
        validasiStok(namaJenis, count);
    }

    // Fungsi Mengatur Tampilan Box Kuota
    function highlightBox(jenis) {
        var boxTahunan = document.getElementById("box-tahunan");
        var boxSakit = document.getElementById("box-sakit");
        var boxUnlimited = document.getElementById("box-unlimited");

        boxTahunan.classList.remove("quota-active");
        boxSakit.classList.remove("quota-active");
        boxUnlimited.style.display = "none";

        if (jenis.includes("tahunan")) {
            boxTahunan.classList.add("quota-active");
        } else if (jenis.includes("sakit")) {
            boxSakit.classList.add("quota-active");
        } else if (jenis !== "") {
            boxUnlimited.style.display = "block";
        }
    }

    // Fungsi Cek Stok vs Input
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
            btnSubmit.classList.remove("btn-success");
        } else {
            alertKuota.style.display = "none";
            btnSubmit.disabled = false;
            btnSubmit.classList.add("btn-success");
            btnSubmit.classList.remove("btn-secondary");
        }
    }
</script>