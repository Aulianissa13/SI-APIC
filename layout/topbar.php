<div id="content-wrapper" class="d-flex flex-column">
    <div id="content" style="padding-top: 90px;">
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 fixed-top shadow">

            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">

                <div class="topbar-divider d-none d-sm-block"></div>

                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?php echo $_SESSION['nama_lengkap']; ?>
                        </span>
                        <img class="img-profile rounded-circle" src="assets/img/undraw_profile.svg">
                    </a>

                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown" style="background-color: white;">

                        <a class="dropdown-item" href="index.php?page=ganti_password" style="background-color: white; font-size: 0.8rem; color: #006837;">
                            <i class="fas fa-key fa-sm fa-fw mr-2" style="color: #006837; opacity: 0.7;"></i>
                            Ganti Password
                        </a>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item" href="logout.php" id="tombol-logout-topbar" style="background-color: white; font-size: 0.8rem; color: #006837;">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2" style="color: #006837; opacity: 0.7;"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <div class="container-fluid">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Script logout sama seperti sidebar
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('tombol-logout-topbar');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Yakin ingin keluar?',
                    text: "Sesi Anda akan diakhiri.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#006B3F',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Keluar',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) { window.location.href = href; }
                });
            } else {
                if (confirm('Yakin ingin keluar dari sistem?')) { window.location.href = href; }
            }
        });
    }
});
</script>
