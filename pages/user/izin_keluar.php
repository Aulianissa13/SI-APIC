<?php
/** @var mysqli $koneksi */
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --pn-green: #004d00; 
        --pn-dark-green: #003300;
        --pn-gold: #F9A825; 
        --pn-gold-dark: #F9A825;
        --border-color: #d1d3e2;
    }
    
    body { font-family: 'Poppins', sans-serif !important; background-color: #f4f6f9; }

    /* Header Styles */
    .card-header-pn {
        background-color: var(--pn-green);
        color: white;
        border-bottom: 4px solid var(--pn-gold);
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 10px 10px 0 0;
    }

    .page-header-title { 
        border-left: 5px solid var(--pn-gold);
        padding-left: 15px; 
        color: var(--pn-green);
        font-weight: 700; 
        font-size: 1.6rem; 
    }

    /* Form Label */
    .form-label-pn { 
        font-size: 0.85rem; 
        font-weight: 600; 
        color: var(--pn-green); 
        margin-bottom: 0.5rem;
        display: block;
    }

    /* Standard Input Wrapper (Clean Style) */
    .input-group-clean {
        display: flex;
        align-items: center;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background-color: #fff;
        transition: all 0.3s ease;
        overflow: hidden;
        height: 48px; 
        width: 100%;
        position: relative;
    }
    .input-group-clean:focus-within {
        border-color: var(--pn-green);
        box-shadow: 0 0 0 4px rgba(0, 77, 0, 0.1);
        transform: translateY(-1px);
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
        font-size: 0.95rem;
        color: #495057;
        background: transparent;
        outline: none;
    }
    .form-control-clean:focus { box-shadow: none; }
    
    /* Textarea */
    .input-group-clean.textarea-group { height: auto !important; align-items: stretch; }
    .input-group-clean.textarea-group .input-icon-clean { height: auto; min-height: 80px; padding-top: 0; }
    textarea.form-control-clean { padding-top: 15px; padding-bottom: 15px; line-height: 1.5; }

    /* Tombol */
    .btn-pn-solid { 
        background: linear-gradient(45deg, var(--pn-green), var(--pn-dark-green)); 
        color: white; border: none; font-weight: 600; padding: 12px 20px; 
        border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.15); transition: 0.3s; 
    }
    .btn-pn-solid:hover { 
        transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); color: var(--pn-gold); 
    }
    .btn-pn-yellow {
        background-color: var(--pn-gold);
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 5px;
        transition: 0.2s;
    }
    .btn-pn-yellow:hover {
        background-color: #d68f1d;
        color: white;
    }

    .card-clean { border: none; border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); overflow: hidden; }
    .section-divider { display: flex; align-items: center; margin: 30px 0 20px 0; }
    .section-divider span { background-color: var(--pn-green); color: white; padding: 6px 15px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border-bottom: 2px solid var(--pn-gold-dark); }
    .section-divider hr { flex-grow: 1; border-top: 2px solid #e3e6f0; margin-left: 15px; }

    /* Tabel Style */
    .table-pn-head {
        background-color: #f8f9fc;
        color: #333;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
    }
</style>

<div class="container-fluid mb-5 mt-4">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="page-header-title">Izin Keluar Kantor</h1>
        <div class="bg-white p-2 rounded shadow-sm" style="border-left: 4px solid var(--pn-gold);">
            <span class="small font-weight-bold" style="color: var(--pn-green);">
                <i class="far fa-calendar-alt mr-2"></i><?php echo date('d F Y'); ?>
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card card-clean shadow-sm h-100">
                <div class="card-header-pn">
                    <div class="font-weight-bold"><i class="fas fa-edit mr-2"></i> Form Permohonan</div>
                </div>
                <div class="card-body p-4">
                    <form action="pages/proses_izin.php" method="POST" autocomplete="off">
                        
                        <input type="hidden" name="id_user_pemohon" value="<?= $_SESSION['id_user'] ?>">

                        <div class="section-divider mt-0">
                            <span>A. Penanda Tangan</span><hr>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Pilih Atasan Langsung <span class="text-danger">*</span></label>
                            <div class="input-group-clean">
                                <div class="input-icon-clean"><i class="fas fa-user-tie"></i></div>
                                <input class="form-control-clean" list="list_atasan" id="input_atasan" placeholder="Ketik nama atasan..." required>
                            </div>
                            <datalist id="list_atasan">
                                <?php
                                // User hanya memilih atasan (is_atasan = 1)
                                $q_atasan = mysqli_query($koneksi, "SELECT * FROM users WHERE is_atasan='1' ORDER BY nama_lengkap ASC");
                                while($at = mysqli_fetch_array($q_atasan)){
                                    echo "<option data-id='".$at['id_user']."' value='".$at['nama_lengkap']." (".$at['jabatan'].")'>";
                                }
                                ?>
                            </datalist>
                            <input type="hidden" name="id_atasan" id="id_atasan_hidden">
                        </div>

                        <div class="section-divider mt-4">
                            <span>B. Detail Izin</span><hr>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Tanggal Izin <span class="text-danger">*</span></label>
                            <div class="input-group-clean">
                                <div class="input-icon-clean"><i class="far fa-calendar-alt"></i></div>
                                <input type="date" name="tgl_izin" class="form-control-clean" value="<?php echo date('Y-m-d'); ?>" required onclick="this.showPicker()">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-pn">Jam Keluar <span class="text-danger">*</span></label>
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="far fa-clock"></i></div>
                                    <input type="time" name="jam_keluar" class="form-control-clean" required onclick="this.showPicker()">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label-pn">Jam Kembali <span class="text-danger">*</span></label>
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-history"></i></div>
                                    <input type="time" name="jam_kembali" class="form-control-clean" required onclick="this.showPicker()">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-pn">Keperluan <span class="text-danger">*</span></label>
                            <div class="input-group-clean textarea-group">
                                <div class="input-icon-clean"><i class="fas fa-align-left"></i></div>
                                <textarea name="keperluan" class="form-control-clean" rows="2" placeholder="Contoh: Ke Bank BPD DIY..." required></textarea>
                            </div>
                        </div>

                        <button type="submit" name="simpan_izin" class="btn btn-pn-solid btn-block py-2">
                            <i class="fas fa-save mr-2"></i> Simpan Permohonan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-clean shadow-sm mb-4">
                <div class="card-header-pn">
                    <div class="font-weight-bold"><i class="fas fa-history mr-2"></i>Riwayat Izin Saya</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dataTableUser" width="100%" cellspacing="0">
                            <thead class="table-pn-head">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Waktu Izin</th>
                                    <th>Keperluan</th>
                                    <th>Disetujui Oleh</th>
                                    <th width="10%">Cetak</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $id_saya = $_SESSION['id_user'];
                                $no = 1;
                                // Filter data HANYA milik user yang login
                                $q_riwayat = mysqli_query($koneksi, "SELECT i.*, a.nama_lengkap as atasan 
                                                                     FROM izin_keluar i 
                                                                     LEFT JOIN users a ON i.id_atasan = a.id_user
                                                                     WHERE i.id_user = '$id_saya'
                                                                     ORDER BY i.id_izin DESC");
                                while($row = mysqli_fetch_array($q_riwayat)):
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td style="font-size: 0.85rem;">
                                        <div class="font-weight-bold"><?= date('d/m/Y', strtotime($row['tgl_izin'])); ?></div>
                                        <span class="badge badge-light border mt-1"><?= date('H:i', strtotime($row['jam_keluar'])); ?> - <?= date('H:i', strtotime($row['jam_kembali'])); ?></span>
                                    </td>
                                    <td style="font-size: 0.85rem;"><?= $row['keperluan']; ?></td>
                                    <td style="font-size: 0.85rem;">
                                        <?= $row['atasan'] ? $row['atasan'] : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="pages/cetak_izin_keluar.php?id=<?= $row['id_izin']; ?>" target="_blank" class="btn btn-pn-yellow btn-sm shadow-sm" title="Cetak Surat">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi Autocomplete
    function setupAutocomplete(inputId, listId, hiddenId) {
        const inputEl = document.getElementById(inputId);
        const hiddenEl = document.getElementById(hiddenId);

        inputEl.addEventListener('input', function(e) {
            var inputVal = this.value;
            var listOptions = document.querySelectorAll('#' + listId + ' option');
            hiddenEl.value = ""; // Reset hidden ID

            for (var i = 0; i < listOptions.length; i++) {
                if (listOptions[i].value === inputVal) {
                    hiddenEl.value = listOptions[i].getAttribute('data-id');
                    break;
                }
            }
        });

        // Validasi agar user tidak mengetik sembarangan
        inputEl.addEventListener('change', function(e) {
             if(hiddenEl.value == "") {
                 var inputVal = this.value;
                 var listOptions = document.querySelectorAll('#' + listId + ' option');
                 let found = false;
                 for (var i = 0; i < listOptions.length; i++) {
                    if (listOptions[i].value === inputVal) {
                        hiddenEl.value = listOptions[i].getAttribute('data-id');
                        found = true;
                        break;
                    }
                }
                if(!found) {
                    // Opsional: Reset input jika tidak valid
                    // this.value = ''; 
                }
             }
        });
    }

    // Jalankan setup untuk Atasan
    setupAutocomplete('input_atasan', 'list_atasan', 'id_atasan_hidden');

    $(document).ready(function() {
        // DataTable dengan Limit 7 Baris
        $('#dataTableUser').DataTable({
            "pageLength": 7, 
            "lengthMenu": [[7, 10, 25, -1], [7, 10, 25, "Semua"]],
            "ordering": false,
            "language": {
                "search": "Cari:",
                "paginate": {
                    "next": "<i class='fas fa-chevron-right'></i>",
                    "previous": "<i class='fas fa-chevron-left'></i>"
                }
            }
        });
    });
</script>