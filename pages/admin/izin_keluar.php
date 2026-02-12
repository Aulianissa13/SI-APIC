<?php
/** @var mysqli $koneksi */

// --- Helper Tanggal Indonesia ---
$bulanIndo = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
    'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
    'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
];
$hariIni = date('d') . ' ' . $bulanIndo[date('F')] . ' ' . date('Y');
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

    /* --- 1. CUSTOM FLATPICKR (KALENDER HIJAU) --- */
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, 
    .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, 
    .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus, 
    .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus, 
    .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, 
    .flatpickr-day.endRange:hover, .flatpickr-day.selected.prevMonthDay, 
    .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.endRange.prevMonthDay, 
    .flatpickr-day.selected.nextMonthDay, .flatpickr-day.startRange.nextMonthDay, 
    .flatpickr-day.endRange.nextMonthDay {
        background: var(--pn-green) !important;
        border-color: var(--pn-green) !important;
    }
    .flatpickr-months .flatpickr-month {
        background: var(--pn-green) !important;
        color: #fff !important;
        fill: #fff !important;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months .flatpickr-monthDropdown-month {
        background-color: var(--pn-green) !important;
    }
    span.flatpickr-weekday {
        background: var(--pn-green) !important;
        color: #fff !important;
    }

    /* --- 2. CARD & HEADER STYLES --- */
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

    /* --- 3. FORM INPUT STYLES --- */
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
        background-color: #fff; 
        outline: none;
    }
    .form-control-clean:focus { box-shadow: none; }

    select.form-control-clean { padding-top: 12px; padding-bottom: 12px; cursor: pointer; }
    .input-group-clean.textarea-group { height: auto !important; align-items: stretch; }
    .input-group-clean.textarea-group .input-icon-clean { height: auto; min-height: 80px; padding-top: 0; }
    textarea.form-control-clean { padding-top: 15px; padding-bottom: 15px; line-height: 1.5; }

    /* --- 4. BUTTON STYLES --- */
    .btn-pn-solid { 
        background: linear-gradient(45deg, var(--pn-green), var(--pn-dark-green)); 
        color: white; border: none; font-weight: 600; padding: 12px 20px; 
        border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.15); transition: 0.3s; 
    }
    .btn-pn-solid:hover { 
        transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); color: var(--pn-gold); 
    }
    
    .card-clean { border: none; border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); overflow: hidden; }
    .section-divider { display: flex; align-items: center; margin: 30px 0 20px 0; }
    .section-divider span { background-color: var(--pn-green); color: white; padding: 6px 15px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border-bottom: 2px solid var(--pn-gold-dark); }
    .section-divider hr { flex-grow: 1; border-top: 2px solid #e3e6f0; margin-left: 15px; }

    /* --- 5. TABLE STYLES --- */
    .table-pn-head {
        background-color: var(--pn-green);
        color: #fff;
        font-weight: 600;
        border-top: none;
    }
    .table-pn-head th {
        border-bottom: 3px solid var(--pn-gold) !important;
        vertical-align: middle !important;
        text-transform: uppercase;
        font-size: 0.80rem; 
        letter-spacing: 0.5px;
    }
    .table tbody td {
        font-size: 0.75rem !important; 
        vertical-align: middle;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 77, 0, 0.03) !important;
    }

    /* --- 6. CUSTOM PAGINATION STYLE (MODEL KOTAK) --- */
    .dataTables_wrapper .dataTables_paginate .pagination { margin-top: 10px; }
    .dataTables_wrapper .dataTables_paginate .pagination .page-item .page-link {
        padding: .4rem .9rem !important;
        margin-left: 6px !important;
        border-radius: 10px !important;
        border: 1px solid #e5e7eb !important;
        background: #fff !important;
        color: var(--pn-green) !important;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s ease;
    }
    .dataTables_wrapper .dataTables_paginate .pagination .page-item.active .page-link {
        background: var(--pn-green) !important;
        color: #fff !important;
        border: 1px solid var(--pn-green) !important;
    }
    .dataTables_wrapper .dataTables_paginate .pagination .page-item .page-link:hover {
        background: #f0fdf4 !important;
        border-color: var(--pn-green) !important;
        transform: translateY(-2px);
    }
    .dataTables_wrapper .dataTables_paginate .pagination .page-item.disabled .page-link {
        color: #9ca3af !important;
        background: #f9fafb !important;
        border-color: #e5e7eb !important;
        transform: none;
    }

    /* Action Buttons */
    .btn-circle-action { 
        width: 35px; height: 35px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; cursor: pointer; 
    }
    .btn-print { background-color: #e3f2fd; color: #0d47a1; }
    .btn-print:hover { background-color: #bbdefb; transform: scale(1.1); }
</style>

<div class="container-fluid mb-5 mt-4">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="page-header-title">Izin Keluar (Admin)</h1>
        <div class="bg-white p-2 rounded shadow-sm" style="border-left: 4px solid var(--pn-gold);">
            <span class="small font-weight-bold" style="color: var(--pn-green);">
                <i class="far fa-calendar-alt mr-2"></i><?php echo $hariIni; ?>
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card card-clean shadow-sm h-100">
                <div class="card-header-pn">
                    <div class="font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Buat Izin Baru</div>
                </div>
                <div class="card-body p-4">
                    <form action="pages/proses_izin.php" method="POST" autocomplete="off">
                        
                        <div class="section-divider mt-0">
                            <span>A. Data Pegawai</span><hr>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Nama Pegawai <span class="text-danger">*</span></label>
                            <div class="input-group-clean">
                                <div class="input-icon-clean"><i class="fas fa-search"></i></div>
                                <input class="form-control-clean" list="list_pegawai" id="input_pegawai" placeholder="Ketik nama untuk mencari..." required>
                            </div>
                            <datalist id="list_pegawai">
                                <?php
                                $q_all = mysqli_query($koneksi, "SELECT * FROM users ORDER BY nama_lengkap ASC");
                                while($p = mysqli_fetch_array($q_all)){
                                    echo "<option data-id='".$p['id_user']."' value='".$p['nama_lengkap']."'>";
                                }
                                ?>
                            </datalist>
                            <input type="hidden" name="id_user_pemohon" id="id_user_hidden">
                        </div>

                        <div class="mb-3">
                            <label class="form-label-pn">Atasan Langsung <span class="text-danger">*</span></label>
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
                                <input type="text" name="tgl_izin" class="form-control-clean flatpickr-date" value="<?php echo date('Y-m-d'); ?>" required placeholder="Pilih Tanggal">
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
                                <textarea name="keperluan" class="form-control-clean" rows="2" placeholder="Jelaskan keperluan izin..." required></textarea>
                            </div>
                        </div>

                        <button type="submit" name="simpan_izin" class="btn btn-pn-solid btn-block py-2">
                            <i class="fas fa-save mr-2"></i> Simpan Data
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-clean shadow-sm mb-4">
                <div class="card-header-pn">
                    <div class="font-weight-bold"><i class="fas fa-history mr-2"></i>Riwayat Izin (Semua Pegawai)</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dataTableAdmin" width="100%" cellspacing="0">
                            <thead class="table-pn-head">
                                <tr class="text-center">
                                    <th width="5%">No</th>
                                    <th>Pemohon</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Keperluan</th>
                                    <th>Atasan</th>
                                    <th width="12%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $q_riwayat = mysqli_query($koneksi, "SELECT i.*, u.nama_lengkap as pemohon, a.nama_lengkap as atasan 
                                                                     FROM izin_keluar i 
                                                                     JOIN users u ON i.id_user = u.id_user 
                                                                     LEFT JOIN users a ON i.id_atasan = a.id_user
                                                                     ORDER BY i.id_izin DESC");
                                while($row = mysqli_fetch_array($q_riwayat)):
                                ?>
                                <tr>
                                    <td class="text-center font-weight-bold align-middle"><?= $no++; ?></td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark" style="font-size: 15px;"><?= $row['pemohon']; ?></div>
                                    </td>
                                    <td class="align-middle" style="font-size: 0.85rem;">
                                        <div class="font-weight-bold text-primary"><?= date('d/m/Y', strtotime($row['tgl_izin'])); ?></div>
                                        <small class="text-dark d-block mt-1">
                                            <i class="far fa-clock mr-1 text-muted"></i>
                                            <?= date('H:i', strtotime($row['jam_keluar'])); ?> - <?= date('H:i', strtotime($row['jam_kembali'])); ?> WIB
                                        </small>
                                    </td>
                                    <td class="align-middle" style="font-size: 0.85rem;"><?= $row['keperluan']; ?></td>
                                    <td class="align-middle" style="font-size: 0.85rem;"><?= $row['atasan'] ?? '-'; ?></td>
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
    // 1. Setup Autocomplete Nama/Atasan
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
    setupAutocomplete('input_pegawai', 'list_pegawai', 'id_user_hidden');
    setupAutocomplete('input_atasan', 'list_atasan', 'id_atasan_hidden');

    // 2. Setup Flatpickr (Tanggal & Jam)
    document.addEventListener('DOMContentLoaded', function() {
        
        // Config Tanggal (Tampil dd/mm/yyyy, Kirim yyyy-mm-dd)
        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d", 
            altInput: true,      
            altFormat: "d/m/Y",  
            locale: "id",        
            allowInput: true     
        });

        // Config Jam (24 Jam, No AM/PM, Batas 08:00 - 17:00)
        flatpickr(".flatpickr-time", {
            enableTime: true,
            noCalendar: true,    
            dateFormat: "H:i",   
            time_24hr: true,     
            minTime: "08:00",    
            maxTime: "17:00",    
            minuteIncrement: 1   
        });

        // 3. Setup DataTable Bahasa Indonesia + Styling Pagination
        $('#dataTableAdmin').DataTable({
            "pageLength": 7, 
            "lengthMenu": [[7, 10, 25, 50, -1], [7, 10, 25, 50, "Semua"]],
            "ordering": false,
            "language": {
                "sEmptyTable":   "Tidak ada data riwayat izin",
                "sProcessing":   "Sedang memproses...",
                "sLengthMenu":   "Tampilkan _MENU_ entri",
                "sZeroRecords":  "Tidak ditemukan data yang sesuai",
                "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
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