</div>
        </div>
    
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                
                <div class="font-weight-bold text-dark mb-1">
                    SI-APIC <span class="mx-1">&bull;</span> Bekerja Sama dengan Pengadilan Negeri Yogyakarta
                </div>
                
                <div class="small text-secondary mb-2">
                    Project Akademik Prodi Sistem Informasi UPN "Veteran" Yogyakarta
                </div>

                <div class="small text-muted">
                    Versi 1.0.0 | &copy; 2026
                </div>

            </div>
        </div>
    </footer>
    </div>
    </div>
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Yakin ingin keluar?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Pilih "Logout" di bawah jika Anda ingin mengakhiri sesi ini.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                <a class="btn btn-primary" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="assets/js/sb-admin-2.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    // Only run on manage_libur page
    if (window.location.href.indexOf('manage_libur') > -1) {
        if ($('#dataTableLibur').length > 0) {
            const table = $('#dataTableLibur').DataTable({
                destroy: true,
                ordering: false,
                searching: true,
                dom: 'rtp',
                pageLength: 5,
                language: {
                    emptyTable: "Belum ada data hari libur",
                    zeroRecords: "Data tidak ditemukan",
                    paginate: {
                        next: "<i class='fas fa-chevron-right'></i>",
                        previous: "<i class='fas fa-chevron-left'></i>"
                    }
                }
            });

            $('#customSearchBox').on('input', function() {
                table.search($(this).val()).draw();
            });
        }
    }
});
</script>

</body>
</html>