</div>
        </div>
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright &copy; SI-APIC Pengadilan Negeri Yogyakarta 2026</span>
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

<!-- DataTables untuk manage_libur -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- Initialize DataTable for manage_libur page -->
<script>
$(document).ready(function() {
    // Only run on manage_libur page
    if (window.location.href.indexOf('manage_libur') > -1) {
        console.log('Initializing DataTable for manage_libur');

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

            console.log('DataTable initialized successfully');
        } else {
            console.error('Table #dataTableLibur not found');
        }
    }
});
</script>

</body>
</html>