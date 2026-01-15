<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="sidebar-brand-text mx-3">SI-APIC</div>
    </a>

    <hr class="sidebar-divider my-0">

    <?php if($_SESSION['role'] == 'admin'): ?>
    
        <li class="nav-item">
            <a class="nav-link" href="index.php?page=dashboard_admin">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard Admin</span></a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">ADMINISTRATOR</div>

        <li class="nav-item">
            <a class="nav-link" href="index.php?page=validasi_cuti">
                <i class="fas fa-fw fa-check-double"></i>
                <span>Validasi Cuti</span></a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="index.php?page=input_pegawai_lain">
                <i class="fas fa-fw fa-user-edit"></i>
                <span>Input Cuti Pegawai</span></a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" href="index.php?page=data_pegawai">
                <i class="fas fa-fw fa-users"></i>
                <span>Data Pegawai</span></a>
        </li>

    <?php endif; ?>

    <hr class="sidebar-divider">
    <div class="sidebar-heading">AREA PRIBADI</div>

    <li class="nav-item">
        <a class="nav-link" href="index.php?page=dashboard_user">
            <i class="fas fa-fw fa-home"></i>
            <span>Beranda Saya</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="index.php?page=form_cuti">
            <i class="fas fa-fw fa-file-alt"></i>
            <span>Buat Pengajuan</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="index.php?page=riwayat_cuti">
            <i class="fas fa-fw fa-history"></i>
            <span>Riwayat Pengajuan</span></a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>