<?php
/** @var mysqli $koneksi */
// Pastikan session sudah start di file induk, jika belum, uncomment baris bawah:
// session_start();
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    :root { 
        --pn-green: #004d00; 
        --pn-dark-green: #003300;
        --pn-gold: #F9A825; 
        --pn-gold-dark: #F9A825;
        --border-color: #d1d3e2;
    }
    
    body { font-family: 'Poppins', sans-serif !important; background-color: #f4f6f9; }

    /* --- CUSTOM FLATPICKR (TEMA HIJAU) --- */
    .flatpickr-calendar {
        border: none !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, 
    .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, 
    .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus, 
    .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus, 
    .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, 
    .flatpickr-day.endRange:hover {
        background: var(--pn-green) !important;
        border-color: var(--pn-green) !important;
    }
    .flatpickr-months .flatpickr-month {
        background: var(--pn-green) !important;
        color: #fff !important;
        fill: #fff !important;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months .flatpickr-monthDropdown-month {
        background-color: var(--pn-green) !important;
    }
    span.flatpickr-weekday {
        background: var(--pn-green) !important;
        color: #fff !important;
    }

    /* --- HEADER & CARD --- */
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

    /* --- FORM STYLES --- */
    .form-label-pn { 
        font-size: 0.85rem; 
        font-weight: 600; 
        color: var(--pn-green); 
        margin-bottom: 0.5rem;
        display: block;
    }

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
        background-color: #fff !important; /* Force white background */
        outline: none;
    }
    .form-control-clean:focus { box-shadow: none; }
    
    /* Agar input tanggal terlihat clickable */
    input.flatpickr-date, input.flatpickr-time {
        cursor: pointer;
        background-color: #fff !important;
    }

    /* --- TEXTAREA --- */
    .input-group-clean.textarea-group { height: auto !important; align-items: stretch; }
    .input-group-clean.textarea-group .input-icon-clean { height: auto; min-height: 80px; padding-top: 0; }
    textarea.form-control-clean { padding-top: 15px; padding-bottom: 15px; line-height: 1.5; }

    /* --- BUTTONS --- */
    .btn-pn-solid { 
        background: linear-gradient(45deg, var(--pn-green), var(--pn-dark-green)); 
        color: white; border: none; font-weight: 600; padding: 12px 20px; 
        border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.15); transition: 0.3s; 
    }
    .btn-pn-solid:hover { 
        transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); color: var(--pn-gold); 
    }

    /* --- TABLE & GENERAL --- */
    .card-clean { border: none; border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); overflow: hidden; }
    .section-divider { display: flex; align-items: center; margin: 30px 0 20px 0; }
    .section-divider span { background-color: var(--pn-green); color: white; padding: 6px 15px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border-bottom: 2px solid var(--pn-gold-dark); }
    .section-divider hr { flex-grow: 1; border-top: 2px solid #e3e6f0; margin-left: 15px; }

    .table-pn-head { background-color: var(--pn-green); color: #fff; font-weight: 600; border-top: none; }
    .table-pn-head th { border-bottom: 3px solid var(--pn-gold) !important; vertical-align: middle !important; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
    .table tbody td { font-size: 0.85rem !important; vertical-align: middle; }
    .table-hover tbody tr:hover { background-color: rgba(0, 77, 0, 0.03) !important; }

    .btn-circle-action { width: 35px; height: 35px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; cursor: pointer; }
    .btn-print { background-color: #e3f2fd; color: #0d47a1; }
    .btn-print:hover { background-color: #bbdefb; transform: scale(1.1); }
</style>

<div class="container-fluid mb-5 mt-4">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="page-header-title mb-2 mb-sm-0">Izin Keluar Kantor</h1>
        
        <div class="d-flex flex-column flex-md-row align-items-md-center">
            
            <div class="bg-white py-2 px-3 rounded shadow-sm" style="border-left: 4px solid var(--pn-green);">
                <span class="small font-weight-bold" style="color: var(--pn-green);">
                    <i class="far fa-calendar-alt mr-2"></i>
                    <?php 
                    $bulan = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus','September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];
                    echo date('d') . ' ' . $bulan[date('F')] . ' ' . date('Y');
                    ?>
                </span>
            </div>

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
                                <input type="text" name="tgl_izin" class="form-control-clean flatpickr-date" value="<?php echo date('Y-m-d'); ?>" required placeholder="Klik untuk pilih tanggal...">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-pn">Jam Keluar <span class="text-danger">*</span></label>
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="far fa-clock"></i></div>
                                    <input type="text" name="jam_keluar" class="form-control-clean flatpickr-time" required placeholder="08:00">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label-pn">Jam Kembali <span class="text-danger">*</span></label>
                                <div class="input-group-clean">
                                    <div class="input-icon-clean"><i class="fas fa-history"></i></div>
                                    <input type="text" name="jam_kembali" class="form-control-clean flatpickr-time" required placeholder="17:00">
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
                    
                    <div class="py-2 px-3 rounded shadow-sm d-flex align-items-center mb-3" 
                         style="background-color: #fff8e1; border-left: 4px solid var(--pn-gold); color: #333; font-size: 0.85rem;">
                        <i class="fas fa-exclamation-circle text-warning mr-2" style="font-size: 1.1rem;"></i>
                        <span style="line-height: 1.2;">
                            Catatan: Setelah mencetak bukti izin keluar kantor (Klik tombol <i class="fas fa-print fa-xs text-primary"></i>), 
                                <b>wajib menyerahkan</b> surat tersebut ke <b>Bagian Kepegawaian</b>.
                        </span>
                    </div>

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
                                $q_riwayat = mysqli_query($koneksi, "SELECT i.*, a.nama_lengkap as atasan 
                                                                     FROM izin_keluar i 
                                                                     LEFT JOIN users a ON i.id_atasan = a.id_user
                                                                     WHERE i.id_user = '$id_saya'
                                                                     ORDER BY i.id_izin DESC");
                                while($row = mysqli_fetch_array($q_riwayat)):
                                ?>
                                <tr>
                                    <td class="text-center font-weight-bold align-middle"><?= $no++; ?></td>
                                    <td style="font-size: 0.85rem;" class="align-middle">
                                        <div class="font-weight-bold text-primary"><?= date('d/m/Y', strtotime($row['tgl_izin'])); ?></div>
                                        <small class="text-dark d-block mt-1">
                                            <i class="far fa-clock mr-1 text-muted"></i>
                                            <?= date('H:i', strtotime($row['jam_keluar'])); ?> - <?= date('H:i', strtotime($row['jam_kembali'])); ?> WIB
                                        </small>
                                    </td>
                                    <td style="font-size: 0.85rem;" class="align-middle"><?= $row['keperluan']; ?></td>
                                    <td style="font-size: 0.85rem;" class="align-middle">
                                        <?= $row['atasan'] ? $row['atasan'] : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="pages/cetak_izin_keluar.php?id=<?= $row['id_izin']; ?>" target="_blank" class="btn-circle-action btn-print shadow-sm" title="Cetak Surat">
                                            <i class="fas fa-print fa-sm"></i>
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

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<script>
    // 1. Setup Autocomplete Atasan (Javascript Murni)
    function setupAutocomplete(inputId, listId, hiddenId) {
        const inputEl = document.getElementById(inputId);
        const hiddenEl = document.getElementById(hiddenId);

        if(inputEl) {
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

            inputEl.addEventListener('change', function(e) {
                 if(hiddenEl.value == "") {
                      var inputVal = this.value;
                      var listOptions = document.querySelectorAll('#' + listId + ' option');
                      for (var i = 0; i < listOptions.length; i++) {
                        if (listOptions[i].value === inputVal) {
                            hiddenEl.value = listOptions[i].getAttribute('data-id');
                            break;
                        }
                    }
                 }
            });
        }
    }
    setupAutocomplete('input_atasan', 'list_atasan', 'id_atasan_hidden');

    // 2. Setup Flatpickr (Tanpa jQuery agar lebih aman)
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- TANGGAL: Wajib Kalender (allowInput: false) ---
        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d",   // Format ke database
            altInput: true,        // Tampilan user beda
            altFormat: "d/m/Y",    // Format Indonesia
            locale: "id",          // Bahasa
            allowInput: false,     // PENTING: User GABISA ngetik, wajib klik kalender
            disableMobile: "true"  // Memaksa pakai kalender custom di HP juga
        });

        // --- JAM: 24 Jam & Range 08-17 ---
        flatpickr(".flatpickr-time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,       // Format 24 jam (No AM/PM)
            minTime: "08:00",
            maxTime: "17:00",
            allowInput: false      // Wajib pilih lewat picker
        });

    });

    // 3. Setup DataTable (Pakai jQuery karena bawaan template biasanya)
    $(document).ready(function() {
        $('#dataTableUser').DataTable({
            "pageLength": 7, 
            "lengthMenu": [[7, 10, 25, -1], [7, 10, 25, "Semua"]],
            "ordering": false,
            "language": {
                "sEmptyTable":   "Belum ada riwayat izin",
                "sProcessing":   "Sedang memproses...",
                "sLengthMenu":   "Tampilkan _MENU_ entri",
                "sZeroRecords":  "Tidak ditemukan data",
                "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "sSearch":       "Cari Riwayat:",
                "oPaginate": {
                    "sFirst":    "Pertama",
                    "sPrevious": "<i class='fas fa-chevron-left'></i>",
                    "sNext":     "<i class='fas fa-chevron-right'></i>",
                    "sLast":     "Terakhir"
                }
            }
        });
    });
</script>