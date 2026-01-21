<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar" 
    style="background: linear-gradient(180deg, #006837 10%, #004d29 100%); position: sticky; top: 0; height: 100vh; overflow-y: auto; overflow-x: hidden; z-index: 100;">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php" style="padding: 1.5rem 1rem;">
        <div class="sidebar-brand-icon">
            <?php if(file_exists("assets/img/logo.png")): ?>
                <img src="assets/img/logo.png" style="width: 45px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
            <?php else: ?>
                <i class="fas fa-balance-scale" style="color: #F9A825; font-size: 2rem;"></i>
            <?php endif; ?>
        </div>
        <div class="sidebar-brand-text mx-2" style="font-weight: 800; letter-spacing: 1px;">
            SI-APIC <sup style="color: #F9A825;">PNYK</sup>
        </div>
    </a>

    <hr class="sidebar-divider my-0">

    <?php if($_SESSION['role'] == 'admin'): ?>

    <li class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'dashboard_admin') ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?page=dashboard_admin">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard Admin</span>
        </a>
    </li>

    <div class="sidebar-heading">
        Administrasi
    </div>

    <li class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'data_pegawai') ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?page=data_pegawai">
            <i class="fas fa-fw fa-users"></i>
            <span>Data Pegawai</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Laporan
    </div>

    <li class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'laporan_cuti') ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?page=laporan_cuti">
            <i class="fas fa-fw fa-print"></i>
            <span>Laporan Cuti</span>
        </a>
    </li>

    <li class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'input_cuti') ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?page=input_cuti">
            <i class="fas fa-fw fa-calendar-plus" style="<?php echo (isset($_GET['page']) && $_GET['page'] == 'input_cuti') ? '' : 'color: #F9A825;'; ?>"></i>
            <span>Input Cuti Pegawai</span>
        </a>
    </li>

    <li class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'validasi_cuti') ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?page=validasi_cuti">
            <i class="fas fa-fw fa-check-double"></i>
            <span>Persetujuan Cuti</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Hari Libur
    </div>

    <li class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'manage_libur') ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?page=manage_libur">
            <i class="fas fa-fw fa-calendar-alt"></i>
            <span>Atur Libur Nasional</span>
        </a>
    </li>

    <?php else: ?>

    <li class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'dashboard_user') || !isset($_GET['page']) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?page=dashboard_user">
            <i class="fas fa-fw fa-home"></i>
            <span>Beranda</span>
        </a>
    </li>

    <div class="sidebar-heading">
        Menu Utama
    </div>

    <li class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'form_cuti') ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?page=form_cuti">
            <i class="fas fa-fw fa-calendar-plus" style="<?php echo (isset($_GET['page']) && $_GET['page'] == 'form_cuti') ? '' : 'color: #F9A825;'; ?>"></i>
            <span>Ajukan Cuti Baru</span>
        </a>
    </li>

    <li class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'riwayat_cuti') ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?page=riwayat_cuti">
            <i class="fas fa-fw fa-history"></i>
            <span>Riwayat & Status</span>
        </a>
    </li>

    <?php endif; ?>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Akun
    </div>

    <li class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'ganti_password') ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?page=ganti_password">
            <i class="fas fa-fw fa-key"></i>
            <span>Ganti Password</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="logout.php" id="tombol-logout-sidebar" style="cursor: pointer;">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('tombol-logout-sidebar');
    
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
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            } else {
                if (confirm('Yakin ingin keluar dari sistem?')) {
                    window.location.href = href;
                }
            }
        });
    }
});
</script>