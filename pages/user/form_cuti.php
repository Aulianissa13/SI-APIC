<?php
/** @var mysqli $koneksi */
// Pastikan session sudah start di header utama
// session_start();

$id_user = $_SESSION['id_user'];
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_array($query_user);
$total_kuota_tahunan = $user['sisa_cuti_n'] + $user['sisa_cuti_n1'];

// --- Query Instansi & Pejabat ---
$q_instansi = mysqli_query($koneksi, "SELECT * FROM tbl_setting_instansi LIMIT 1");
$instansi   = mysqli_fetch_array($q_instansi);

$query_jenis = mysqli_query($koneksi, "SELECT * FROM jenis_cuti ORDER BY id_jenis ASC");
$query_atasan = mysqli_query($koneksi, "SELECT * FROM users WHERE is_atasan = '1' AND id_user != '$id_user' ORDER BY nama_lengkap ASC");

// --- Array Libur Nasional (Untuk JS) ---
$libur_nasional = [];
$q_libur = mysqli_query($koneksi, "SELECT tanggal FROM libur_nasional");
while ($row = mysqli_fetch_assoc($q_libur)) {
    $libur_nasional[] = $row['tanggal'];
}

// --- Penomoran Surat Otomatis ---
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

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --pn-green: #004d00; 
        --pn-dark-green: #003300;
        --pn-gold: #FFC107; 
        --pn-gold-dark: #F9A825;
        --border-color: #d1d3e2;
    }
    
    body { font-family: 'Poppins', sans-serif !important; background-color: #f4f6f9; }

    /* --- HEADER STYLES --- */
    .card-header-pn {
        background-color: var(--pn-green);
        color: white;
        border-bottom: 4px solid var(--pn-gold);
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .page-header-title { 
        border-left: 5px solid var(--pn-gold);
        padding-left: 15px; 
        color: var(--pn-green);
        font-weight: 700; 
        font-size: 1.6rem; 
    }

    /* --- GENERAL FORM STYLES --- */
    .form-label-pn { 
        font-size: 0.85rem; 
        font-weight: 600; 
        color: var(--pn-green); 
        margin-top: 10px; 
    }

    /* Standard Input Wrapper (Tinggi Konsisten) */
    .input-group-clean {
        display: flex;
        align-items: center;
        border: 1px solid var(--border-color);
        border-radius: 6px; 
        background-color: #fff;
        transition: all 0.3s ease;
        overflow: hidden;
        height: 42px; /* TINGGI STANDARD */
        width: 100%;
        position: relative;
    }
    .input-group-clean:focus-within {
        border-color: var(--pn-green);
        box-shadow: 0 0 0 3px rgba(0, 77, 0, 0.1);
    }
    .input-icon-clean {
        width: 45px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--pn-green);
        background-color: #f8f9fc;
        border-right: 1px solid #eaecf4;
        font-size: 1rem;
        flex-shrink: 0; 
    }
    .form-control-clean {
        flex: 1;
        border: none;
        height: 100%;
        width: 100%;
        padding: 0 15px;
        font-size: 0.9rem;
        color: #495057;
        background: transparent;
        outline: none;
    }
    .form-control-clean:focus { box-shadow: none; }
    
    /* Select & Textarea Specific */
    select.form-control-clean { cursor: pointer; -webkit-appearance: none; -moz-appearance: none; appearance: none; }
    .input-group-clean.textarea-group { height: auto !important; align-items: stretch; }
    .input-group-clean.textarea-group .input-icon-clean { height: auto; min-height: 80px; padding-top: 0; }
    textarea.form-control-clean { padding-top: 10px; padding-bottom: 10px; line-height: 1.5; }
    
    /* Readonly State */
    .input-group-clean.readonly { background-color: #eaecf4; border-color: #d1d3e2; }
    .input-group-clean.readonly .input-icon-clean { color: #6c757d; background-color: #dfe2e9; }
    .input-group-clean.readonly .form-control-clean { color: #5a5c69; font-weight: 500; }

    /* =========================================
       REVISI FIXED: TEXT VISIBILITY
       ========================================= */
    
    /* Label Helper */
    .label-helper {
        font-size: 0.7rem;
        font-weight: 700;
        color: #858796;
        margin-bottom: 3px;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Date Inputs */
    input[type="date"].form-control-clean {
        position: relative;
        text-align: center;
        z-index: 2;
    }
    input[type="date"]::-webkit-calendar-picker-indicator {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        width: 100%; height: 100%;
        opacity: 0; /* Area klik penuh */
        cursor: pointer;
    }

    /* Durasi Input FIXED */
    .durasi-input {
        text-align: center !important;
        font-weight: 750 !important;
        color: #2b500a !important; /* HITAM PEKAT */
        font-size: 0,8rem !important; /* Adjust this value */
        background-color: transparent !important;
        opacity: 1 !important;
        padding-right: 35px !important; /* Space for 'HARI' label */
    }
    
    .durasi-suffix {
        position: absolute;
        right: 10px;
        font-size: 0.7rem;
        font-weight: 700;
        color: #858796;
        pointer-events: none;
        background: transparent;
    }

    /* --- LAYOUT LAIN --- */
    .quota-box { transition: all 0.3s ease; border: 1px solid #e0e0e0; background-color: #fff; opacity: 0.8; }
    .quota-active { opacity: 1 !important; transform: scale(1.02); box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important; border: 2px solid var(--pn-gold) !important; background-color: #fff !important; }

    .section-divider { display: flex; align-items: center; margin: 30px 0 20px 0; }
    .section-divider span { background-color: var(--pn-green); color: white; padding: 6px 15px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border-bottom: 2px solid var(--pn-gold-dark); }
    .section-divider hr { flex-grow: 1; border-top: 2px solid #e3e6f0; margin-left: 15px; }

    .btn-pn-solid { background: linear-gradient(45deg, var(--pn-green), var(--pn-dark-green)); color: white; border: none; font-weight: 600; padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.15); transition: 0.3s; }
    .btn-pn-solid:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); color: var(--pn-gold); }
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
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h6 class="font-weight-bold text-dark mb-0"><?php echo $user['nama_lengkap']; ?></h6>
                                <div class="text-xs text-muted font-weight-bold mb-2">NIP. <?php echo $user['nip']; ?></div>
                                <span class="badge py-2 px-3" style="background-color: #fff; color: var(--pn-green); border: 1px solid #d1d3e2; border-radius: 6px;">
                                    <i class="fas fa-briefcase mr-1" style="color: var(--pn-gold);"></i> <?php echo $user['jabatan']; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="text-xs font-weight-bold text-uppercase" style="color: var(--pn-green);">Status Kuota Cuti</div>
                        <div style="height: 1px; width: 50%; background-color: #d1d3e2;"></div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <div id="box-tahunan" class="quota-box p-3 rounded shadow-sm position-relative d-flex align-items-center justify-content-between" 
                                 style="border-left: 4px solid var(--pn-green);">
                                <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #5a5c69;">Sisa Cuti Tahunan</div>
                                    <div class="h4 mb-0 font-weight-bold" style="color: var(--pn-green);">
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
                                 style="border-left: 4px solid var(--pn-green);"> 
                                 <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #5a5c69;">Kuota Sakit</div>
                                    <div class="h4 mb-0 font-weight-bold" style="color: var(--pn-green);"> <?php echo $user['kuota_cuti_sakit']; ?> <small class="text-muted" style="font-size: 12px;">Hari</small>
                                    </div>
                                </div>
                                <div class="p-2 rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 45px; height: 45px; background-color: var(--pn-green);"> <i class="fas fa-briefcase-medical text-warning fa-lg"></i> </div>
                                <input type="hidden" id="max_sakit" value="<?php echo (int)$user['kuota_cuti_sakit']; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div id="box-unlimited" class="alert small mt-3 mb-0 border-0 shadow-sm" 
                         style="display: none; padding: 0.75rem 1rem; background-color: #fff; border-left: 4px solid var(--pn-gold) !important; color: #5a5c69;">
                        <i class="fas fa-info-circle mr-1 text-warning"></i> Cuti ini <b>TIDAK</b> memotong kuota.
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
                                <div class="input-group-clean readonly">
                                    <div class="input-icon-clean"><i class="fas fa-hashtag"></i></div>
                                    <input type="text" name="no_surat" class="form-control-clean" value="<?php echo $no_surat_auto; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Nama & NIP</label>
                            <div class="col-sm-5 mb-2 mb-sm-0">
                                <div class="input-group-clean readonly">
                                    <div class="input-icon-clean"><i class="fas fa-user"></i></div>
                                    <input type="text" class="form-control-clean" value="<?php echo $user['nama_lengkap']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="input-group-clean readonly">
                                    <div class="input-icon-clean"><i class="fas fa-id-badge"></i></div>
                                    <input type="text" class="form-control-clean" value="<?php echo $user['nip']; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Unit Kerja</label>
                            <div class="col-sm-9">
                                <div class="input-group-clean readonly">
                                    <div class="input-icon-clean"><i class="fas fa-building"></i></div>
                                    <input type="text" class="form-control-clean" value="<?php echo $user['unit_kerja']; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Masa Kerja</label>
                            <div class="col-sm-9">
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-clock"></i></div>
                                    <input type="text" name="masa_kerja" class="form-control-clean" placeholder="Contoh: 5 Tahun 2 Bulan">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Nomor Handphone</label>
                            <div class="col-sm-9">
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fab fa-whatsapp"></i></div>
                                    <input type="number" name="no_telepon" class="form-control-clean" value="<?php echo $user['no_telepon']; ?>" placeholder="08xxxxx" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Atasan Langsung</label>
                            <div class="col-sm-9">
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-user-tie"></i></div>
                                    <select name="id_pejabat" class="form-control-clean" required>
                                        <option value="">-- Pilih Atasan Langsung --</option>
                                        <?php 
                                        mysqli_data_seek($query_atasan, 0);
                                        while($atasan = mysqli_fetch_array($query_atasan)) { 
                                        ?>
                                            <option value="<?php echo $atasan['id_user']; ?>">
                                                <?php echo $atasan['nama_lengkap']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Pejabat SK <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-stamp"></i></div>
                                    <select name="ttd_pejabat" id="ttd_pejabat_select" class="form-control-clean" required>
                                        <option value="">-- Pilih Pejabat Berwenang --</option>
                                        <option value="ketua">KETUA - <?php echo $instansi['ketua_nama']; ?></option>
                                        <option value="wakil">WAKIL KETUA - <?php echo $instansi['wakil_nama']; ?></option>
                                        <option value="plh">PLH / MANUAL INPUT</option>
                                    </select>
                                </div>
                                <small class="text-muted font-italic ml-1" style="font-size: 11px;">
                                    *Pilih siapa yang akan menandatangani SK.
                                </small>
                            </div>
                        </div>

                        <!-- Input Manual PLH -->
                        <div id="plh_input_container" style="display: none;" class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">Nama PLH <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-user"></i></div>
                                    <input type="text" name="plh_nama" id="plh_nama_input" class="form-control-clean" placeholder="Nama PLH">
                                </div>
                            </div>
                        </div>

                        <div id="plh_nip_container" style="display: none;" class="form-group row">
                            <label class="col-sm-3 col-form-label form-label-pn">NIP PLH <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-id-card"></i></div>
                                    <input type="text" name="plh_nip" id="plh_nip_input" class="form-control-clean" placeholder="NIP PLH">
                                </div>
                            </div>
                        </div>

                        <div class="section-divider mt-5">
                            <span>B. Detail Cuti</span><hr>
                        </div>

                        <div class="form-group row align-items-center mb-4">
                            <label class="col-sm-3 col-form-label form-label-pn">Jenis Cuti <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-list-ul"></i></div>
                                    <select name="id_jenis" id="jenis_cuti" class="form-control-clean" required onchange="updateKalkulasi()">
                                        <option value="" data-nama="">-- Pilih Jenis Cuti --</option>
                                        <?php mysqli_data_seek($query_jenis, 0); while($j = mysqli_fetch_array($query_jenis)) { ?>
                                            <option value="<?php echo $j['id_jenis']; ?>" data-nama="<?php echo strtolower($j['nama_jenis']); ?>">
                                                <?php echo $j['nama_jenis']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div id="alert-sakit-reminder" class="alert alert-warning mt-2 py-2 shadow-sm" style="display:none; font-size: 0.85rem; border-left: 4px solid #f6c23e;">
                                    <i class="fas fa-exclamation-triangle mr-2"></i> Wajib melampirkan <b>Surat Keterangan Dokter</b>.
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-4">
                            <label class="col-sm-3 col-form-label form-label-pn mt-2">Pelaksanaan <span class="text-danger">*</span></label>
                            
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="col-md-5 mb-3 mb-md-0">
                                        <span class="label-helper">Mulai</span>
                                        <div class="input-group-clean">
                                            <div class="input-icon-clean"><i class="far fa-calendar-alt"></i></div>
                                            <input type="date" name="tgl_mulai" id="tgl_mulai" 
                                                   class="form-control-clean" 
                                                   required onchange="updateKalkulasi()">
                                        </div>
                                    </div>

                                    <div class="col-md-5 mb-3 mb-md-0">
                                        <span class="label-helper">Sampai</span>
                                        <div class="input-group-clean">
                                            <div class="input-icon-clean"><i class="far fa-calendar-check"></i></div>
                                            <input type="date" name="tgl_selesai" id="tgl_selesai" 
                                                   class="form-control-clean" 
                                                   required onchange="updateKalkulasi()">
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <span class="label-helper text-center">Durasi</span>
                                        <div class="input-group-clean readonly" style="position: relative;">
                                            <input type="text" name="lama_hari" id="lama_hari" 
                                                   class="form-control-clean durasi-input" 
                                                   readonly value="0">
                                            <span class="durasi-suffix">HARI</span>
                                        </div>
                                        
                                        <div id="alert-kuota" class="position-absolute" style="display:none; width: 200px; left: -60px; top: 60px; z-index: 10;">
                                            <div class="alert alert-danger py-1 px-2 small font-weight-bold shadow text-center" style="border-radius: 5px; font-size: 0.7rem;">
                                                Kuota Habis!
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-4">
                            <label class="col-sm-3 col-form-label form-label-pn mt-2">Alasan Lengkap <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <div class="input-group-clean textarea-group">
                                    <div class="input-icon-clean"><i class="fas fa-align-left"></i></div>
                                    <textarea name="alasan" class="form-control-clean" rows="3" required placeholder="Jelaskan alasan pengajuan cuti secara rinci..." style="resize: none; line-height: 1.5;"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-4">
                            <label class="col-sm-3 col-form-label form-label-pn mt-2">Alamat Cuti <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <div class="input-group-clean textarea-group">
                                    <div class="input-icon-clean"><i class="fas fa-map-marked-alt"></i></div>
                                    <textarea name="alamat_cuti" class="form-control-clean" rows="2" required placeholder="Lokasi lengkap selama menjalankan cuti..." style="resize: none; line-height: 1.5;"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-6">
                                <a href="index.php?page=riwayat_cuti" class="btn btn-light border btn-block py-2 font-weight-bold text-secondary shadow-sm">
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
        var inputHari = document.getElementById("lama_hari");      
        
        var selectBox = document.getElementById("jenis_cuti");
        var selectedOption = selectBox.options[selectBox.selectedIndex];
        var namaJenis = selectedOption.getAttribute('data-nama') || ""; 

        highlightBox(namaJenis);

        if (tglMulai == "" || tglSelesai == "") return;

        var date1 = new Date(tglMulai);
        var date2 = new Date(tglSelesai);

        if (date2 < date1) {
            Swal.fire({ 
                icon: 'error', 
                title: 'Tanggal Invalid', 
                text: 'Tanggal Selesai tidak boleh lebih awal dari Tanggal Mulai!',
                confirmButtonColor: '#004d00'
            });
            document.getElementById("tgl_selesai").value = "";
            inputHari.value = "0";
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

            // Hitung hari selain Sabtu(6), Minggu(0) dan Libur Nasional
            if(dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(dateString)) {
                count++;
            }
            curDate.setDate(curDate.getDate() + 1);
        }

        inputHari.value = count;
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
            btnSubmit.classList.add("btn-secondary");
            btnSubmit.classList.remove("btn-pn-solid");
            btnSubmit.style.background = "#858796"; 
        } else {
            alertKuota.style.display = "none";
            btnSubmit.disabled = false;
            btnSubmit.classList.remove("btn-secondary");
            btnSubmit.classList.add("btn-pn-solid");
            btnSubmit.style.background = ""; 
        }
    }

    // Toggle PLH Input Fields
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