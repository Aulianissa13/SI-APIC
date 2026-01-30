// assets/js/demo/datatables-demo.js
$(document).ready(function () {
  // jalan hanya kalau #dataTable ada
  if ($('#dataTable').length) {
    // cegah init dobel
    if (!$.fn.DataTable.isDataTable('#dataTable')) {
      $('#dataTable').DataTable();
    }
  }
});
